@extends('master.master')

@section('content')

<div class="container-fluid px-4 py-3 finishing-overview">

    @section('btn')
    <div class="page-head">
        
        <div class="head-badge">
            <i class="bi bi-bar-chart-line"></i>
            Warehouse Overview
        </div>
    </div>
    @endsection


    {{-- ================================================================
         1. WAREHOUSE MOVEMENT — PALING ATAS
         ================================================================ --}}

    <div class="section-heading warehouse-heading mt-4">
        <div>
            <div class="section-kicker">WAREHOUSE</div>
            <h6>Stock Movement</h6>
            <p>Pergerakan nilai Stock In dan Stock Out selama 6 bulan terakhir</p>
        </div>

        <span class="unit-badge">Juta Rupiah</span>
    </div>

    <div class="panel mb-4">
        <div class="panel-head">
            <div>
                <h6>Stock In vs Stock Out</h6>
                <p>Nilai transaksi warehouse per bulan</p>
            </div>
        </div>

        <div class="panel-body chart-stock">
            <div id="stockMovementChart"></div>
        </div>
    </div>


    {{-- ================================================================
         2. FINISHING KPI
         PRODUKSI TIDAK DIHITUNG
         ================================================================ --}}

    @php
        /*
        |--------------------------------------------------------------------------
        | PRODUKSI DIKECUALIKAN DARI PERHITUNGAN FINISHING
        |--------------------------------------------------------------------------
        |
        | Yang dihitung hanya:
        | TOMO
        | DARTO
        | SAMPEL
        |
        | PRODUKSI tetap ditampilkan sebagai informasi, tetapi tidak masuk
        | ke total invoice, pemotongan, sisa, status, chart, maupun pending.
        */

        $finishingIncludedSubs = ['TOMO', 'DARTO', 'SAMPEL'];

        $finishingDisplaySubs = ['TOMO', 'DARTO', 'PRODUKSI', 'SAMPEL'];

        /*
        |--------------------------------------------------------------------------
        | HITUNG ULANG DARI DETAIL INVOICE
        |--------------------------------------------------------------------------
        | Sumber "remaining" dari controller tidak dipakai untuk dashboard.
        | Rumus yang dipakai di halaman ini adalah:
        |
        |   Sisa Pemotongan = Nilai Invoice - Total Pemotongan SPK
        |
        | Dengan cara ini angka card, chart, detail invoice, dan pending
        | selalu menggunakan dasar perhitungan yang sama.
        |
        | PRODUKSI tetap dikeluarkan dari perhitungan.
        */

        $dashboardInvoiceTotal = 0;
        $dashboardCutTotal = 0;
        $dashboardRemainingTotal = 0;
        $dashboardInvoiceCount = 0;
        $dashboardCompleted = 0;
        $dashboardPartial = 0;
        $dashboardPending = 0;

        $dashboardInvoices = collect();

        foreach ($finishingIncludedSubs as $sub) {
            $s = $finishingSummary[$sub] ?? [];
            $invoices = collect($s['invoices'] ?? []);

            /*
            | Hitung ulang setiap invoice.
            | remaining TIDAK diambil dari nilai lama controller.
            */
            $normalizedInvoices = $invoices->map(function ($invoice) use ($sub) {

                $invoiceTotal = (float) ($invoice['invoice_total'] ?? 0);
                $cutTotal     = (float) ($invoice['cut_total'] ?? 0);

                // Sisa pemotongan murni dari invoice dikurangi pemotongan SPK.
                $remaining = max(0, $invoiceTotal - $cutTotal);

                $percentage = $invoiceTotal > 0
                    ? min(100, round(($cutTotal / $invoiceTotal) * 100))
                    : 0;

                if ($remaining <= 0 && $invoiceTotal > 0) {
                    $status = 'completed';
                } elseif ($cutTotal > 0) {
                    $status = 'partial';
                } else {
                    $status = 'pending';
                }

                $invoice['sub']         = $sub;
                $invoice['invoice_total'] = $invoiceTotal;
                $invoice['cut_total']     = $cutTotal;
                $invoice['remaining']     = $remaining;
                $invoice['percentage']    = $percentage;
                $invoice['status']       = $status;

                return $invoice;
            })->values();

            /*
            | Semua angka dashboard diambil dari invoice yang sudah
            | dinormalisasi di atas.
            */
            $dashboardInvoiceTotal += $normalizedInvoices->sum('invoice_total');
            $dashboardCutTotal += $normalizedInvoices->sum('cut_total');
            $dashboardRemainingTotal += $normalizedInvoices->sum('remaining');

            $dashboardInvoiceCount += $normalizedInvoices->count();
            $dashboardCompleted += $normalizedInvoices->where('status', 'completed')->count();
            $dashboardPartial += $normalizedInvoices->where('status', 'partial')->count();
            $dashboardPending += $normalizedInvoices->where('status', 'pending')->count();

            $dashboardInvoices = $dashboardInvoices->concat($normalizedInvoices);
        }

        /*
        | Pengaman akhir:
        | total sisa selalu = total invoice - total pemotongan.
        | Jadi tidak mungkin muncul angka sisa yang berbeda karena
        | nilai "remaining" lama dari controller.
        */
        $dashboardRemainingTotal = max(
            0,
            $dashboardInvoiceTotal - $dashboardCutTotal
        );

        $dashboardCompletion = $dashboardInvoiceTotal > 0
            ? min(100, round(($dashboardCutTotal / $dashboardInvoiceTotal) * 100))
            : 0;

        $dashboardActionCount = $dashboardPending + $dashboardPartial;

        /*
        | Pending/partial juga dibuat dari perhitungan invoice yang sama.
        | PRODUKSI otomatis tidak masuk karena hanya mengambil
        | $finishingIncludedSubs.
        */
        $dashboardPendingInvoices = $dashboardInvoices
            ->filter(function ($item) {
                return ($item['remaining'] ?? 0) > 0;
            })
            ->values();
    @endphp


    <div class="section-heading">
        <div>
            <div class="section-kicker">FINISHING CONTROL</div>
            <h6>Finishing Overview</h6>
            {{-- <p>Produksi tidak masuk dalam perhitungan pemotongan finishing</p> --}}
        </div>
    </div>


    <div class="row g-3 mb-4">

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="kpi-icon kpi-blue">
                    <i class="bi bi-receipt"></i>
                </div>

                <div class="kpi-info">
                    <div class="kpi-label">Invoice</div>
                    <div class="kpi-value">
                        {{ number_format($dashboardInvoiceCount) }}
                    </div>
                    <div class="kpi-note">
                        TOMO · DARTO · SAMPEL
                    </div>
                </div>
            </div>
        </div>


        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="kpi-icon kpi-green">
                    <i class="bi bi-scissors"></i>
                </div>

                <div class="kpi-info">
                    <div class="kpi-label">PEMOTONGAN SPK</div>
                    <div class="kpi-value currency">
                        Rp {{ number_format($dashboardCutTotal, 0, ',', '.') }}
                    </div>
                    <div class="kpi-note">
                        {{ $dashboardCompletion }}% dari nilai invoice
                    </div>
                </div>
            </div>
        </div>


        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="kpi-icon kpi-orange">
                    <i class="bi bi-hourglass-split"></i>
                </div>

                <div class="kpi-info">
                    <div class="kpi-label">SISA PEMOTONGAN</div>
                    <div class="kpi-value currency">
                        Rp {{ number_format($dashboardRemainingTotal, 0, ',', '.') }}
                    </div>
                    <div class="kpi-note">
                        Nilai invoice yang belum dipotong
                    </div>
                </div>
            </div>
        </div>


        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi-card">
                <div class="kpi-icon kpi-red">
                    <i class="bi bi-exclamation-circle"></i>
                </div>

                <div class="kpi-info">
                    <div class="kpi-label">PERLU TINDAKAN</div>
                    <div class="kpi-value">
                        {{ number_format($dashboardActionCount) }}
                    </div>
                    <div class="kpi-note">
                        {{ number_format($dashboardPending) }} belum ·
                        {{ number_format($dashboardPartial) }} partial
                    </div>
                </div>
            </div>
        </div>

    </div>


    {{-- ================================================================
         3. MAIN FINISHING CHARTS
         ================================================================ --}}

    @php
        /*
        |--------------------------------------------------------------------------
        | CHART FINISHING
        |--------------------------------------------------------------------------
        | Chart juga dihitung ulang dari invoice detail supaya:
        |
        | Invoice - Pemotongan = Sisa
        |
        | selalu balance.
        */
        $finishingChartFiltered = collect($finishingIncludedSubs)
            ->map(function ($sub) use ($finishingSummary) {

                $invoices = collect(
                    $finishingSummary[$sub]['invoices'] ?? []
                );

                $invoiceTotal = $invoices->sum(function ($invoice) {
                    return (float) ($invoice['invoice_total'] ?? 0);
                });

                $cutTotal = $invoices->sum(function ($invoice) {
                    return (float) ($invoice['cut_total'] ?? 0);
                });

                // JANGAN ambil remaining dari controller.
                $remaining = max(0, $invoiceTotal - $cutTotal);

                return [
                    'sub'           => $sub,
                    'invoice_total' => $invoiceTotal,
                    'cut_total'     => $cutTotal,
                    'remaining'     => $remaining,
                ];
            })
            ->values()
            ->all();

        $statusChartFiltered = [
            'labels' => ['Selesai', 'Sebagian', 'Belum Dipotong'],
            'series' => [
                $dashboardCompleted,
                $dashboardPartial,
                $dashboardPending,
            ],
        ];
    @endphp


    <div class="row g-3 mb-4">

        <div class="col-12 col-xl-8">
            <div class="panel h-100">

                <div class="panel-head">
                    <div>
                        <h6>Total Invoice vs Pemotongan per Sub</h6>
                        <p>TOMO, DARTO, dan SAMPEL</p>
                    </div>

                    <span class="unit-badge">Juta Rupiah</span>
                </div>

                <div class="panel-body chart-main">
                    <div id="finishingAccountingChart"></div>
                </div>

            </div>
        </div>


        <div class="col-12 col-xl-4">
            <div class="panel h-100">

                <div class="panel-head">
                    <div>
                        <h6>Status Invoice</h6>
                        <p>Tanpa kategori PRODUKSI</p>
                    </div>
                </div>

                <div class="panel-body status-panel">

                    <div id="finishingStatusChart"></div>

                    <div class="status-list">

                        <div class="status-item">
                            <span>
                                <i class="dot dot-success"></i>
                                Selesai
                            </span>
                            <strong>{{ number_format($dashboardCompleted) }}</strong>
                        </div>

                        <div class="status-item">
                            <span>
                                <i class="dot dot-warning"></i>
                                Sebagian
                            </span>
                            <strong>{{ number_format($dashboardPartial) }}</strong>
                        </div>

                        <div class="status-item">
                            <span>
                                <i class="dot dot-danger"></i>
                                Belum Dipotong
                            </span>
                            <strong>{{ number_format($dashboardPending) }}</strong>
                        </div>

                    </div>

                </div>

            </div>
        </div>

    </div>


    {{-- ================================================================
         4. SUB SUMMARY
         PRODUKSI TETAP DITAMPILKAN, TAPI TIDAK DIHITUNG
         ================================================================ --}}

    <div class="section-heading">
        <div>
            <div class="section-kicker">BREAKDOWN</div>
            <h6>Finishing per Sub</h6>
            <p>Produksi ditampilkan sebagai informasi saja dan tidak masuk perhitungan finishing</p>
        </div>
    </div>


    <div class="row g-3 mb-4">

        @foreach($finishingDisplaySubs as $sub)

            @php
                $summary = $finishingSummary[$sub] ?? [
                    'invoice_count' => 0,
                    'invoice_total' => 0,
                    'cut_total' => 0,
                    'remaining' => 0,
                    'completed' => 0,
                    'partial' => 0,
                    'pending' => 0,
                    'invoices' => [],
                ];

                $isProduction = $sub === 'PRODUKSI';

                $percent = (!$isProduction && $summary['invoice_total'] > 0)
                    ? min(100, round(($summary['cut_total'] / $summary['invoice_total']) * 100))
                    : 0;

                $subClass = strtolower($sub);
            @endphp


            <div class="col-12 col-md-6 col-xl-3">

                <div class="sub-card {{ $subClass }} {{ $isProduction ? 'sub-excluded' : '' }}">

                    <div class="sub-top">

                        <div class="sub-identity">
                            <span class="sub-icon">
                                {{ substr($sub, 0, 1) }}
                            </span>

                            <div>
                                <div class="sub-name">{{ $sub }}</div>

                                <div class="sub-count">
                                    {{ number_format($summary['invoice_count']) }} invoice
                                </div>
                            </div>
                        </div>


                        @if($isProduction)

                            <span class="small-status neutral">
                                Tidak dihitung
                            </span>

                        @elseif($summary['pending'] > 0)

                            <span class="small-status danger">
                                {{ $summary['pending'] }} belum
                            </span>

                        @elseif($summary['partial'] > 0)

                            <span class="small-status warning">
                                {{ $summary['partial'] }} partial
                            </span>

                        @else

                            <span class="small-status success">
                                Selesai
                            </span>

                        @endif

                    </div>


                    @if($isProduction)

                        <div class="excluded-message">
                            <i class="bi bi-info-circle"></i>

                            <div>
                                <strong>Maintenance</strong>
                                <span>
                                    
                                </span>
                            </div>
                        </div>

                    @else

                        <div class="sub-progress">

                            <div class="progress">
                                <div
                                    class="progress-bar"
                                    style="width: {{ $percent }}%"
                                ></div>
                            </div>

                            <div class="progress-meta">
                                <span>{{ $percent }}% selesai</span>
                                <span>{{ number_format($summary['completed']) }} selesai</span>
                            </div>

                        </div>


                        <div class="sub-money">

                            <div>
                                <span>Invoice</span>
                                <strong>
                                    Rp {{ number_format($summary['invoice_total'], 0, ',', '.') }}
                                </strong>
                            </div>

                            <div>
                                <span>Pemotongan</span>
                                <strong class="money-positive">
                                    Rp {{ number_format($summary['cut_total'], 0, ',', '.') }}
                                </strong>
                            </div>

                            <div class="money-total">
                                <span>Sisa</span>
                                <strong class="{{ $summary['remaining'] > 0 ? 'money-negative' : 'money-positive' }}">
                                    Rp {{ number_format($summary['remaining'], 0, ',', '.') }}
                                </strong>
                            </div>

                        </div>

                    @endif

                </div>

            </div>

        @endforeach

    </div>


    {{-- ================================================================
         5. DETAIL INVOICE
         HANYA TOMO / DARTO / SAMPEL
         ================================================================ --}}

    <div class="panel mb-4">

        <div class="panel-head detail-head">

            <div>
                <h6>Detail Invoice Finishing</h6>
                <p>Produksi tidak ditampilkan dalam perhitungan pemotongan</p>
            </div>

            <div class="detail-legend">
                <span><i class="dot dot-success"></i> Selesai</span>
                <span><i class="dot dot-warning"></i> Partial</span>
                <span><i class="dot dot-danger"></i> Belum</span>
            </div>

        </div>


        <div class="sub-tabs" id="finishingSubTabs">

            @foreach($finishingIncludedSubs as $index => $sub)

                <button
                    type="button"
                    class="sub-tab {{ $index === 0 ? 'active' : '' }}"
                    data-sub="{{ $sub }}"
                >
                    <span>{{ $sub }}</span>

                    <small>
                        {{ number_format($finishingSummary[$sub]['invoice_count'] ?? 0) }}
                    </small>
                </button>

            @endforeach

        </div>


        @foreach($finishingIncludedSubs as $index => $sub)

            @php
                $summary = $finishingSummary[$sub] ?? [
                    'invoices' => []
                ];
            @endphp

            <div
                class="sub-table-wrap {{ $index === 0 ? 'active' : '' }}"
                data-table-sub="{{ $sub }}"
            >

                @if(count($summary['invoices'] ?? []) > 0)

                    <div class="table-responsive">

                        <table class="table invoice-table mb-0">

                            <thead>
                                <tr>
                                    <th>Invoice</th>
                                    <th>Tanggal</th>
                                    <th class="text-end">Nilai Invoice</th>
                                    <th class="text-end">Pemotongan</th>
                                    <th class="text-end">Sisa</th>
                                    <th>Progress</th>
                                    <th class="text-center">Status</th>
                                </tr>
                            </thead>

                            <tbody>

                                @foreach($summary['invoices'] as $invoice)

                                    @php
                                        $status = $invoice['status'] ?? 'pending';

                                        $statusText = match($status) {
                                            'completed' => 'Selesai',
                                            'partial' => 'Sebagian',
                                            default => 'Belum Dipotong'
                                        };

                                        $statusClass = match($status) {
                                            'completed' => 'success',
                                            'partial' => 'warning',
                                            default => 'danger'
                                        };
                                    @endphp

                                    <tr>

                                        <td>
                                            <div class="invoice-no">
                                                {{ $invoice['invoice'] }}
                                            </div>

                                            @if(empty($invoice['cut_details']))
                                                <div class="invoice-note danger-text">
                                                    Belum ada pemotongan SPK
                                                </div>
                                            @else
                                                <div class="invoice-note">
                                                    {{ count($invoice['cut_details']) }} pemotongan SPK
                                                </div>
                                            @endif
                                        </td>


                                        <td class="muted-cell">
                                            @if(!empty($invoice['tanggal']))
                                                {{ \Carbon\Carbon::parse($invoice['tanggal'])->format('d/m/Y') }}
                                            @else
                                                -
                                            @endif
                                        </td>


                                        <td class="text-end money">
                                            Rp {{ number_format($invoice['invoice_total'] ?? 0, 0, ',', '.') }}
                                        </td>


                                        <td class="text-end money money-positive">
                                            Rp {{ number_format($invoice['cut_total'] ?? 0, 0, ',', '.') }}
                                        </td>


                                        <td class="text-end money {{ ($invoice['remaining'] ?? 0) > 0 ? 'money-negative' : 'money-positive' }}">
                                            Rp {{ number_format($invoice['remaining'] ?? 0, 0, ',', '.') }}
                                        </td>


                                        <td class="progress-cell">

                                            <div class="progress-text">
                                                <span>{{ $invoice['percentage'] ?? 0 }}%</span>
                                                <span>{{ $statusText }}</span>
                                            </div>

                                            <div class="progress invoice-progress">
                                                <div
                                                    class="progress-bar bg-{{ $statusClass }}"
                                                    style="width: {{ min(100, $invoice['percentage'] ?? 0) }}%"
                                                ></div>
                                            </div>

                                        </td>


                                        <td class="text-center">
                                            <span class="status-badge {{ $statusClass }}">
                                                {{ $statusText }}
                                            </span>
                                        </td>

                                    </tr>

                                @endforeach

                            </tbody>

                        </table>

                    </div>

                @else

                    <div class="empty-box">
                        <i class="bi bi-inbox"></i>
                        <strong>Belum ada invoice {{ $sub }}</strong>
                        <span>Data invoice akan muncul di sini.</span>
                    </div>

                @endif

            </div>

        @endforeach

    </div>


    {{-- ================================================================
         6. INVOICE BELUM SELESAI
         PRODUKSI DIKECUALIKAN
         ================================================================ --}}

    <div class="panel mb-4">

        <div class="panel-head">

            <div>
                <h6>
                    <i class="bi bi-exclamation-triangle warning-icon"></i>
                    Invoice Belum Selesai
                </h6>

                <p>
                    Invoice TOMO, DARTO, dan SAMPEL yang belum selesai dipotong
                </p>
            </div>

            <span class="pending-badge">
                {{ number_format($dashboardPendingInvoices->count()) }} invoice
            </span>

        </div>


        @if($dashboardPendingInvoices->count() > 0)

            <div class="table-responsive">

                <table class="table invoice-table pending-table mb-0">

                    <thead>
                        <tr>
                            <th>Invoice</th>
                            <th>Sub</th>
                            <th>Tanggal</th>
                            <th class="text-end">Nilai Invoice</th>
                            <th class="text-end">Pemotongan</th>
                            <th class="text-end">Sisa</th>
                            <th>Progress</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>

                    <tbody>

                        @foreach($dashboardPendingInvoices as $item)

                            @php
                                $statusClass = ($item['status'] ?? '') === 'partial'
                                    ? 'warning'
                                    : 'danger';

                                $statusText = ($item['status'] ?? '') === 'partial'
                                    ? 'Sebagian'
                                    : 'Belum Dipotong';
                            @endphp

                            <tr>

                                <td class="invoice-no">
                                    {{ $item['invoice'] }}
                                </td>

                                <td>
                                    <span class="sub-tag">
                                        {{ $item['sub'] }}
                                    </span>
                                </td>

                                <td class="muted-cell">
                                    @if(!empty($item['tanggal']))
                                        {{ \Carbon\Carbon::parse($item['tanggal'])->format('d/m/Y') }}
                                    @else
                                        -
                                    @endif
                                </td>

                                <td class="text-end money">
                                    Rp {{ number_format($item['invoice_total'] ?? 0, 0, ',', '.') }}
                                </td>

                                <td class="text-end money money-positive">
                                    Rp {{ number_format($item['cut_total'] ?? 0, 0, ',', '.') }}
                                </td>

                                <td class="text-end money money-negative">
                                    Rp {{ number_format($item['remaining'] ?? 0, 0, ',', '.') }}
                                </td>

                                <td class="progress-cell">

                                    <div class="progress-text">
                                        <span>{{ $item['percentage'] ?? 0 }}%</span>
                                    </div>

                                    <div class="progress invoice-progress">
                                        <div
                                            class="progress-bar bg-{{ $statusClass }}"
                                            style="width: {{ min(100, $item['percentage'] ?? 0) }}%"
                                        ></div>
                                    </div>

                                </td>

                                <td class="text-center">
                                    <span class="status-badge {{ $statusClass }}">
                                        {{ $statusText }}
                                    </span>
                                </td>

                            </tr>

                        @endforeach

                    </tbody>

                </table>

            </div>

        @else

            <div class="all-clear">
                <div class="all-clear-icon">
                    <i class="bi bi-check-lg"></i>
                </div>

                <div>
                    <strong>Semua invoice sudah selesai</strong>
                    <span>Tidak ada pemotongan finishing yang perlu ditindaklanjuti.</span>
                </div>
            </div>

        @endif

    </div>

</div>


{{-- ================================================================
     APEXCHARTS
     ================================================================ --}}

<script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {

    const finishingChart = @json($finishingChartFiltered);
    const finishingStatus = @json($statusChartFiltered);
    const movement = @json($stockMovement ?? []);


    function rupiahJuta(value) {
        return 'Rp ' + Number(value || 0).toLocaleString('id-ID', {
            maximumFractionDigits: 2
        }) + ' Jt';
    }


    /*
    |--------------------------------------------------------------------------
    | STOCK MOVEMENT — PALING ATAS
    |--------------------------------------------------------------------------
    */

    const movementEl = document.querySelector('#stockMovementChart');

    if (movementEl && movement.length) {

        new ApexCharts(movementEl, {

            series: [
                {
                    name: 'Stock In',
                    data: movement.map(x => Number(x.stock_in || 0))
                },
                {
                    name: 'Stock Out',
                    data: movement.map(x => Number(x.stock_out || 0))
                }
            ],

            chart: {
                type: 'area',
                height: 315,
                toolbar: { show: false },
                zoom: { enabled: false }
            },

            dataLabels: {
                enabled: false
            },

            stroke: {
                curve: 'smooth',
                width: 3
            },

            colors: [
                '#168b60',
                '#df4660'
            ],

            fill: {
                type: 'solid',
                opacity: 0.10
            },

            markers: {
                size: 4,
                hover: {
                    size: 6
                }
            },

            xaxis: {
                categories: movement.map(x => x.month)
            },

            yaxis: {
                labels: {
                    formatter: value =>
                        'Rp ' +
                        Number(value).toLocaleString('id-ID') +
                        ' Jt'
                }
            },

            tooltip: {
                shared: true,
                intersect: false,

                custom: function({ dataPointIndex }) {

                    const row = movement[dataPointIndex];

                    if (!row) return '';

                    return `
                        <div class="chart-tooltip">
                            <div class="chart-tooltip-title">${row.month}</div>

                            <div class="chart-tooltip-line positive">
                                <span>Stock In</span>
                                <b>${row.stock_in_text}</b>
                            </div>

                            <div class="chart-tooltip-line negative">
                                <span>Stock Out</span>
                                <b>${row.stock_out_text}</b>
                            </div>

                            <div class="chart-tooltip-divider"></div>

                            <div class="chart-tooltip-line">
                                <span>${row.status}</span>
                                <b>${Math.abs(Number(row.difference || 0)).toLocaleString('id-ID')} Jt</b>
                            </div>
                        </div>
                    `;
                }
            },

            legend: {
                position: 'top',
                horizontalAlign: 'left'
            },

            grid: {
                borderColor: '#edf0f4',
                strokeDashArray: 4
            }

        }).render();

    }


    /*
    |--------------------------------------------------------------------------
    | FINISHING ACCOUNTING CHART
    |--------------------------------------------------------------------------
    */

    const accountingEl =
        document.querySelector('#finishingAccountingChart');

    if (accountingEl && finishingChart.length) {

        new ApexCharts(accountingEl, {

            series: [
                {
                    name: 'Invoice',
                    data: finishingChart.map(
                        x => Number(x.invoice_total || 0)
                    )
                },
                {
                    name: 'Pemotongan',
                    data: finishingChart.map(
                        x => Number(x.cut_total || 0)
                    )
                },
                {
                    name: 'Sisa',
                    data: finishingChart.map(
                        x => Number(x.remaining || 0)
                    )
                }
            ],

            chart: {
                type: 'bar',
                height: 340,
                toolbar: { show: false }
            },

            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '42%',
                    borderRadius: 5
                }
            },

            colors: [
                '#4b9cf5',
                '#19a36b',
                '#ef476f'
            ],

            dataLabels: {
                enabled: false
            },

            xaxis: {
                categories: finishingChart.map(x => x.sub),

                labels: {
                    style: {
                        fontWeight: 700
                    }
                }
            },

            yaxis: {
                labels: {
                    formatter: value =>
                        Number(value).toLocaleString('id-ID') + ' Jt'
                }
            },

            tooltip: {
                shared: true,
                intersect: false,

                y: {
                    formatter: value => rupiahJuta(value)
                }
            },

            legend: {
                position: 'top',
                horizontalAlign: 'left'
            },

            grid: {
                borderColor: '#edf0f4',
                strokeDashArray: 4
            }

        }).render();

    }


    /*
    |--------------------------------------------------------------------------
    | STATUS DONUT
    |--------------------------------------------------------------------------
    */

    const statusEl =
        document.querySelector('#finishingStatusChart');

    if (statusEl) {

        new ApexCharts(statusEl, {

            series: (finishingStatus.series || []).map(Number),

            labels: finishingStatus.labels || [],

            chart: {
                type: 'donut',
                height: 245
            },

            colors: [
                '#19a36b',
                '#f5a623',
                '#ef476f'
            ],

            dataLabels: {
                enabled: false
            },

            legend: {
                show: false
            },

            stroke: {
                width: 3,
                colors: ['#fff']
            },

            plotOptions: {
                pie: {
                    donut: {
                        size: '72%',

                        labels: {
                            show: true,

                            name: {
                                show: true,
                                fontSize: '11px',
                                color: '#7b8494'
                            },

                            value: {
                                show: false,
                                fontSize: '24px',
                                fontWeight: 800,
                                color: '#172033',

                                formatter: value =>
                                    Number(value).toLocaleString('id-ID')
                            },

                            total: {
                                show: true,
                                label: '',

                                formatter: () =>
                                    '{{ $dashboardInvoiceCount }}'
                            }
                        }
                    }
                }
            },

            tooltip: {
                y: {
                    formatter: value =>
                        Number(value).toLocaleString('id-ID') +
                        ' Invoice'
                }
            }

        }).render();

    }


    /*
    |--------------------------------------------------------------------------
    | SUB TABS
    |--------------------------------------------------------------------------
    */

    const tabs =
        document.querySelectorAll('.sub-tab');

    const tables =
        document.querySelectorAll('.sub-table-wrap');


    tabs.forEach(function(tab) {

        tab.addEventListener('click', function() {

            const target =
                this.dataset.sub;


            tabs.forEach(function(item) {
                item.classList.remove('active');
            });


            tables.forEach(function(item) {
                item.classList.remove('active');
            });


            this.classList.add('active');


            const table =
                document.querySelector(
                    '[data-table-sub="' +
                    target +
                    '"]'
                );


            if (table) {
                table.classList.add('active');
            }

        });

    });

});
</script>


<style>
/* ================================================================
   SCOPED DASHBOARD
   Tidak menggunakan .green / .red / .success global
   ================================================================ */

.finishing-overview {
    color: #172033;
}

.finishing-overview .page-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    margin-bottom: 20px;
}

.finishing-overview .page-kicker,
.finishing-overview .section-kicker {
    font-size: 9px;
    font-weight: 800;
    letter-spacing: .13em;
    color: #8a94a6;
    text-transform: uppercase;
}

.finishing-overview .page-title {
    margin: 3px 0 2px;
    color: #172033;
    font-size: 23px;
    font-weight: 800;
}

.finishing-overview .page-subtitle {
    margin: 0;
    color: #8a94a6;
    font-size: 11px;
}

.finishing-overview .head-badge,
.finishing-overview .unit-badge {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 7px 10px;
    border: 1px solid #e4e9ef;
    border-radius: 8px;
    background: #fff;
    color: #697487;
    font-size: 9px;
    font-weight: 800;
    white-space: nowrap;
}


/* ================================================================
   SECTION
   ================================================================ */

.finishing-overview .section-heading {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 15px;
    margin: 22px 0 11px;
}

.finishing-overview .section-heading h6 {
    margin: 3px 0;
    color: #172033;
    font-size: 14px;
    font-weight: 800;
}

.finishing-overview .section-heading p {
    margin: 0;
    color: #8a94a6;
    font-size: 9px;
}

.finishing-overview .warehouse-heading {
    margin-top: 0;
}


/* ================================================================
   PANEL
   ================================================================ */

.finishing-overview .panel {
    overflow: hidden;
    border: 1px solid #e8edf2;
    border-radius: 13px;
    background: #fff;
    box-shadow: 0 4px 17px rgba(20, 30, 50, .045);
}

.finishing-overview .panel-head {
    min-height: 64px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 15px;
    padding: 15px 18px;
    border-bottom: 1px solid #edf0f3;
}

.finishing-overview .panel-head h6 {
    margin: 0 0 3px;
    color: #172033;
    font-size: 12px;
    font-weight: 800;
}

.finishing-overview .panel-head p {
    margin: 0;
    color: #8a94a6;
    font-size: 9px;
}

.finishing-overview .panel-body {
    padding: 10px 15px 15px;
}

.finishing-overview .chart-stock {
    min-height: 320px;
}

.finishing-overview .chart-main {
    min-height: 350px;
}


/* ================================================================
   KPI
   ================================================================ */

.finishing-overview .kpi-card {
    min-height: 108px;
    display: flex;
    align-items: center;
    gap: 13px;
    padding: 17px;
    border: 1px solid #e8edf2;
    border-radius: 13px;
    background: #fff;
    box-shadow: 0 4px 17px rgba(20, 30, 50, .045);
    transition: transform .15s ease, box-shadow .15s ease;
}

.finishing-overview .kpi-card:hover {
    transform: translateY(-1px);
    box-shadow: 0 7px 22px rgba(20, 30, 50, .065);
}

.finishing-overview .kpi-icon {
    width: 44px;
    height: 44px;
    flex: 0 0 44px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 11px;
    font-size: 18px;
}

.finishing-overview .kpi-blue {
    background: #edf5ff;
    color: #347fca;
}

.finishing-overview .kpi-green {
    background: #edf9f4;
    color: #25885f;
}

.finishing-overview .kpi-orange {
    background: #fff6e8;
    color: #bd7b13;
}

.finishing-overview .kpi-red {
    background: #fff1f3;
    color: #d9425b;
}

.finishing-overview .kpi-info {
    min-width: 0;
}

.finishing-overview .kpi-label {
    color: #8a94a6;
    font-size: 9px;
    font-weight: 800;
    letter-spacing: .06em;
}

.finishing-overview .kpi-value {
    margin-top: 2px;
    color: #172033;
    font-size: 18px;
    font-weight: 800;
    line-height: 1.35;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.finishing-overview .kpi-value.currency {
    font-size: 15px;
}

.finishing-overview .kpi-note {
    margin-top: 2px;
    color: #9aa3b1;
    font-size: 9px;
}


/* ================================================================
   STATUS
   ================================================================ */

.finishing-overview .status-panel {
    padding-top: 5px;
}

.finishing-overview .status-list {
    margin: 0 7px;
    padding-top: 7px;
    border-top: 1px solid #edf0f3;
}

.finishing-overview .status-item {
    min-height: 29px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    color: #697487;
    font-size: 10px;
}

.finishing-overview .status-item strong {
    color: #172033;
}

.finishing-overview .dot {
    width: 7px;
    height: 7px;
    display: inline-block;
    margin-right: 6px;
    border-radius: 50%;
}

.finishing-overview .dot-success {
    background: #19a36b;
}

.finishing-overview .dot-warning {
    background: #f5a623;
}

.finishing-overview .dot-danger {
    background: #ef476f;
}


/* ================================================================
   SUB CARD
   ================================================================ */

.finishing-overview .sub-card {
    position: relative;
    min-height: 211px;
    overflow: hidden;
    padding: 16px;
    border: 1px solid #e8edf2;
    border-radius: 13px;
    background: #fff;
    box-shadow: 0 4px 17px rgba(20, 30, 50, .045);
}

.finishing-overview .sub-card::before {
    content: "";
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 3px;
    background: #dce3ea;
}

.finishing-overview .sub-card.tomo::before {
    background: #4b9cf5;
}

.finishing-overview .sub-card.darto::before {
    background: #39aa7b;
}

.finishing-overview .sub-card.produksi::before {
    background: #c9cfd7;
}

.finishing-overview .sub-card.sampel::before {
    background: #8a67d9;
}

.finishing-overview .sub-top {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 8px;
}

.finishing-overview .sub-identity {
    display: flex;
    align-items: center;
    gap: 9px;
}

.finishing-overview .sub-icon {
    width: 33px;
    height: 33px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 9px;
    background: #f1f4f7;
    color: #435064;
    font-size: 11px;
    font-weight: 800;
}

.finishing-overview .sub-card.tomo .sub-icon {
    background: #edf5ff;
    color: #347fca;
}

.finishing-overview .sub-card.darto .sub-icon {
    background: #edf9f4;
    color: #25885f;
}

.finishing-overview .sub-card.produksi .sub-icon {
    background: #f1f3f5;
    color: #7a8493;
}

.finishing-overview .sub-card.sampel .sub-icon {
    background: #f3effc;
    color: #7651bb;
}

.finishing-overview .sub-name {
    color: #172033;
    font-size: 13px;
    font-weight: 800;
}

.finishing-overview .sub-count {
    margin-top: 1px;
    color: #9aa3b1;
    font-size: 9px;
}

.finishing-overview .small-status {
    display: inline-flex;
    align-items: center;
    padding: 4px 7px;
    border-radius: 6px;
    font-size: 8px;
    font-weight: 800;
    white-space: nowrap;
}

.finishing-overview .small-status.success {
    background: #edf9f4;
    color: #168b60;
}

.finishing-overview .small-status.warning {
    background: #fff6e5;
    color: #b9790c;
}

.finishing-overview .small-status.danger {
    background: #fff1f3;
    color: #d9425b;
}

.finishing-overview .small-status.neutral {
    background: #f1f3f5;
    color: #727d8c;
}


/* ================================================================
   PRODUCTION EXCLUDED
   ================================================================ */

.finishing-overview .sub-excluded {
    background: #fafbfc;
}

.finishing-overview .excluded-message {
    min-height: 123px;
    margin-top: 18px;
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 13px;
    border: 1px dashed #dfe4e9;
    border-radius: 9px;
    background: #f8f9fa;
    color: #7b8494;
}

.finishing-overview .excluded-message > i {
    flex: 0 0 auto;
    color: #929ba8;
    font-size: 16px;
}

.finishing-overview .excluded-message strong,
.finishing-overview .excluded-message span {
    display: block;
}

.finishing-overview .excluded-message strong {
    margin-bottom: 3px;
    color: #606b7b;
    font-size: 10px;
}

.finishing-overview .excluded-message span {
    color: #8b94a1;
    font-size: 8px;
    line-height: 1.45;
}


/* ================================================================
   PROGRESS / MONEY
   ================================================================ */

.finishing-overview .sub-progress {
    margin-top: 17px;
}

.finishing-overview .sub-progress .progress,
.finishing-overview .invoice-progress {
    height: 6px;
    overflow: hidden;
    border-radius: 20px;
    background: #edf0f4 !important;
}

.finishing-overview .sub-progress .progress-bar {
    border-radius: 20px;
    background: #168b60 !important;
}

.finishing-overview .progress-meta {
    display: flex;
    justify-content: space-between;
    margin-top: 5px;
    color: #8993a3;
    font-size: 8px;
}

.finishing-overview .sub-money {
    margin-top: 13px;
}

.finishing-overview .sub-money > div {
    min-height: 27px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
}

.finishing-overview .sub-money span {
    color: #8a94a6;
    font-size: 9px;
}

.finishing-overview .sub-money strong {
    color: #273246;
    font-size: 9px;
    white-space: nowrap;
}

.finishing-overview .money-positive {
    color: #168b60 !important;
    background: transparent !important;
}

.finishing-overview .money-negative {
    color: #df4660 !important;
    background: transparent !important;
}

.finishing-overview .money-total {
    margin-top: 5px;
    padding-top: 7px;
    border-top: 1px solid #edf0f3;
}


/* ================================================================
   DETAIL TABS
   ================================================================ */

.finishing-overview .detail-head {
    border-bottom: 0;
}

.finishing-overview .detail-legend {
    display: flex;
    align-items: center;
    gap: 11px;
    color: #8993a3;
    font-size: 8px;
    white-space: nowrap;
}

.finishing-overview .sub-tabs {
    display: flex;
    gap: 3px;
    padding: 0 15px;
    border-bottom: 1px solid #e8edf2;
    background: #fbfcfd;
    overflow-x: auto;
}

.finishing-overview .sub-tab {
    position: relative;
    border: 0;
    border-bottom: 2px solid transparent;
    margin-bottom: -1px;
    padding: 10px 12px;
    background: transparent !important;
    color: #7d8797 !important;
    font-size: 9px;
    font-weight: 800;
    cursor: pointer;
    white-space: nowrap;
}

.finishing-overview .sub-tab:hover {
    color: #1976d2 !important;
}

.finishing-overview .sub-tab.active {
    border-bottom-color: #1976d2;
    color: #1976d2 !important;
}

.finishing-overview .sub-tab small {
    display: inline-flex;
    margin-left: 5px;
    padding: 2px 5px;
    border-radius: 5px;
    background: #eef1f4 !important;
    color: #7d8797 !important;
    font-size: 7px;
}

.finishing-overview .sub-tab.active small {
    background: #eaf3ff !important;
    color: #1976d2 !important;
}

.finishing-overview .sub-table-wrap {
    display: none;
}

.finishing-overview .sub-table-wrap.active {
    display: block;
}


/* ================================================================
   TABLE
   ================================================================ */

.finishing-overview .invoice-table {
    width: 100%;
    margin: 0;
    font-size: 9px;
}

.finishing-overview .invoice-table thead th {
    height: 36px;
    padding: 9px 11px;
    background: #f8f9fb !important;
    border-bottom: 1px solid #e7ebef !important;
    color: #7e8796 !important;
    font-size: 7px;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .04em;
    white-space: nowrap;
}

.finishing-overview .invoice-table tbody td {
    height: 55px;
    padding: 10px 11px;
    background: #fff !important;
    border-color: #eef1f4 !important;
    vertical-align: middle;
}

.finishing-overview .invoice-table tbody tr:hover td {
    background: #f9fbfd !important;
}

.finishing-overview .invoice-no {
    color: #172033 !important;
    font-size: 9px;
    font-weight: 800;
    white-space: nowrap;
}

.finishing-overview .invoice-note {
    margin-top: 2px;
    color: #9aa3b1 !important;
    font-size: 7px;
}

.finishing-overview .danger-text {
    color: #df4660 !important;
}

.finishing-overview .muted-cell {
    color: #8993a3 !important;
    white-space: nowrap;
}

.finishing-overview .money {
    color: #273246 !important;
    font-weight: 700;
    white-space: nowrap;
}

.finishing-overview .progress-cell {
    min-width: 145px;
}

.finishing-overview .progress-text {
    display: flex;
    justify-content: space-between;
    margin-bottom: 4px;
    color: #8993a3;
    font-size: 7px;
}

.finishing-overview .invoice-progress .progress-bar {
    border-radius: 20px;
}

.finishing-overview .invoice-progress .bg-success {
    background: #19a36b !important;
}

.finishing-overview .invoice-progress .bg-warning {
    background: #f5a623 !important;
}

.finishing-overview .invoice-progress .bg-danger {
    background: #ef476f !important;
}

.finishing-overview .status-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 4px 7px;
    border: 1px solid transparent;
    border-radius: 6px;
    font-size: 7px;
    font-weight: 800;
    white-space: nowrap;
}

.finishing-overview .status-badge.success {
    background: #edf9f4 !important;
    border-color: #d7f0e4 !important;
    color: #168b60 !important;
}

.finishing-overview .status-badge.warning {
    background: #fff6e5 !important;
    border-color: #fae8bd !important;
    color: #b9790c !important;
}

.finishing-overview .status-badge.danger {
    background: #fff1f3 !important;
    border-color: #f8d9df !important;
    color: #d9425b !important;
}


/* ================================================================
   PENDING
   ================================================================ */

.finishing-overview .warning-icon {
    margin-right: 5px;
    color: #e6a21a;
}

.finishing-overview .pending-badge {
    padding: 6px 9px;
    border-radius: 7px;
    background: #fff1f3 !important;
    color: #d9425b !important;
    font-size: 8px;
    font-weight: 800;
    white-space: nowrap;
}

.finishing-overview .pending-table tbody td:first-child {
    border-left: 2px solid #f1c3cc !important;
}

.finishing-overview .sub-tag {
    display: inline-flex;
    padding: 4px 7px;
    border-radius: 6px;
    background: #f1f3f6 !important;
    color: #596476 !important;
    font-size: 7px;
    font-weight: 800;
}

.finishing-overview .all-clear {
    min-height: 120px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 11px;
}

.finishing-overview .all-clear-icon {
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    background: #eaf8f1;
    color: #159a66;
    font-size: 17px;
}

.finishing-overview .all-clear strong,
.finishing-overview .all-clear span {
    display: block;
}

.finishing-overview .all-clear strong {
    color: #159a66;
    font-size: 11px;
}

.finishing-overview .all-clear span {
    margin-top: 2px;
    color: #8a94a6;
    font-size: 8px;
}


/* ================================================================
   CHART TOOLTIP
   ================================================================ */

.chart-tooltip {
    min-width: 210px;
    padding: 11px;
    background: #fff;
    color: #172033;
}

.chart-tooltip-title {
    margin-bottom: 8px;
    font-weight: 800;
}

.chart-tooltip-line {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    padding: 3px 0;
    color: #697487;
    font-size: 10px;
}

.chart-tooltip-line b {
    color: #172033;
}

.chart-tooltip-line.positive b {
    color: #168b60;
}

.chart-tooltip-line.negative b {
    color: #df4660;
}

.chart-tooltip-divider {
    height: 1px;
    margin: 6px 0;
    background: #edf0f3;
}


/* ================================================================
   RESPONSIVE
   ================================================================ */

@media (max-width: 767.98px) {

    .finishing-overview {
        padding-left: 10px !important;
        padding-right: 10px !important;
    }

    .finishing-overview .page-head {
        align-items: flex-start;
        flex-direction: column;
    }

    .finishing-overview .head-badge {
        display: none;
    }

    .finishing-overview .page-title {
        font-size: 20px;
    }

    .finishing-overview .section-heading {
        align-items: flex-start;
        flex-direction: column;
    }

    .finishing-overview .detail-legend {
        display: none;
    }

    .finishing-overview .panel-head {
        padding: 13px;
    }

    .finishing-overview .panel-body {
        padding: 8px 10px 12px;
    }

    .finishing-overview .invoice-table {
        min-width: 850px;
    }

}
</style>

@endsection
