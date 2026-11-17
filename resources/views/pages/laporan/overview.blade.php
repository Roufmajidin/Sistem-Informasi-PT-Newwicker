@extends('master.master')

@section('content')
    <div class="container-fluid px-3 px-lg-4 py-3 whov">
        <div class="whov-titlebar">
            <div>
                <div class="whov-eyebrow">WAREHOUSE · STOCK CONTROL</div>
                <h4>Warehouse Overview</h4>
                <p>Monitoring seluruh jenis stok yang sudah opname · Perhitungan cut-off opname terakhir</p>
            </div>
            <span class="whov-pill"><i class="bi bi-check2-circle"></i> Has Opname Only</span>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-6 col-xl-3">
                <div class="whov-kpi"><span class="whov-ico blue"><i class="bi bi-box-seam"></i></span>
                    <div><small>ITEM HAS
                            OPNAME</small><strong>{{ number_format($summary['items']) }}</strong><em>{{ number_format($summary['jenis']) }}
                            jenis stok</em></div>
                </div>
            </div>
            <div class="col-6 col-xl-3">
                <div class="whov-kpi"><span class="whov-ico green"><i class="bi bi-box-arrow-in-down"></i></span>
                    <div><small>TOTAL MASUK (NILAI)</small><strong>Rp
                            {{ number_format($summary['in_value'], 0, ',', '.') }}</strong><em>Setelah cut-off opname</em>
                    </div>
                </div>
            </div>
            <div class="col-6 col-xl-3">
                <div class="whov-kpi"><span class="whov-ico red"><i class="bi bi-box-arrow-up"></i></span>
                    <div><small>TOTAL PENGELUARAN (NILAI)</small><strong>Rp
                            {{ number_format($summary['out_value'], 0, ',', '.') }}</strong><em>Setelah cut-off opname</em>
                    </div>
                </div>
            </div>
            <div class="col-6 col-xl-3">
                <div class="whov-kpi"><span class="whov-ico purple"><i class="bi bi-wallet2"></i></span>
                    <div><small>SALDO STOK (NILAI)</small><strong>Rp
                            {{ number_format($summary['saldo_value'], 0, ',', '.') }}</strong><em>Qty saldo × harga
                            master</em></div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-12 col-xl-8">
                <section class="whov-panel h-100">
                    <div class="whov-panel-head">
                        <div>
                            <h6>Stock In vs Stock Out</h6>
                            <p>Pergerakan nilai transaksi 6 bulan terakhir · hanya stok Has Opname</p>
                        </div><span class="whov-unit">Rupiah</span>
                    </div>
                    <div id="whov-movement" class="whov-chart"></div>
                </section>
            </div>
            <div class="col-12 col-xl-4">
                <section class="whov-panel h-100">
                    <div class="whov-panel-head">
                        <div>
                            <h6>Saldo Stok per Jenis</h6>
                            <p>Komposisi nilai saldo semua kategori stok</p>
                        </div>
                    </div>
                    <div id="whov-jenis" class="whov-chart"></div>
                </section>
            </div>
        </div>

        <section class="whov-panel mb-4">
            <div class="whov-panel-head whov-table-head">
                <div>
                    <h6>Detail Stok Has Opname</h6>
                    <p>Seluruh kategori bahan baku dan material yang sudah memiliki opname</p>
                </div>
                <span class="whov-unit">{{ number_format($stoks->total()) }} item</span>
            </div>
            <form method="GET" action="{{ route('warehouse.overview') }}" class="whov-filters">
                <div class="whov-tabs"><span class="whov-tab active"><i class="bi bi-check-circle"></i> Has Opname
                        <b>{{ number_format($summary['items']) }}</b></span></div>
                <div class="whov-search"><i class="bi bi-search"></i><input type="search" name="search"
                        value="{{ $search }}" placeholder="Cari kode / nama barang..."></div>
                <select name="jenis" aria-label="Filter jenis stok">
                    <option value="">Semua Jenis Stok</option>
                    @foreach ($jenisOptions as $opt)
                        <option value="{{ $opt }}" @selected($jenis === $opt)>{{ $opt }}</option>
                    @endforeach
                </select>
                <button class="whov-filterbtn" type="submit">Terapkan</button>
                <a href="{{ route('warehouse.overview') }}" class="whov-reset">Reset</a>
            </form>
            <div class="table-responsive">
                <table class="table whov-table mb-0">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Kode Barang</th>
                            <th>Nama Barang</th>
                            <th>Jenis Stok</th>
                            <th>Satuan</th>
                            <th>Opname Terakhir</th>
                            <th class="text-end">Qty Opname</th>
                            <th class="text-end">Total Masuk</th>
                            <th class="text-end">Pengeluaran</th>
                            <th class="text-end">Saldo Stok</th>
                            <th class="text-end">Nilai Saldo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($stoks as $stok)
                            <tr>
                                <td class="whov-muted">{{ $stoks->firstItem() + $loop->index }}</td>
                                <td><span class="whov-code">{{ $stok->kode_barang }}</span></td>
                                <td class="whov-name">{{ $stok->nama_barang }}</td>
                                <td><span class="whov-jenis">{{ $stok->jenis ?: 'Tanpa Jenis' }}</span></td>
                                <td>{{ $stok->satuan ?: '-' }}</td>
                                <td class="whov-muted">
                                    {{ $stok->opname_at ? \Carbon\Carbon::parse($stok->opname_at)->format('d/m/Y H:i') : '-' }}
                                </td>
                                <td class="text-end whov-num">{{ number_format((float) $stok->stok_awal, 3, ',', '.') }}
                                </td>
                                <td class="text-end whov-num whov-positive">
                                    {{ number_format($stok->total_in, 3, ',', '.') }}</td>
                                <td class="text-end whov-num whov-negative">
                                    {{ number_format($stok->total_out, 3, ',', '.') }}</td>
                                <td class="text-end whov-num"><b>{{ number_format($stok->saldo, 3, ',', '.') }}</b></td>
                                <td class="text-end whov-num">Rp {{ number_format($stok->nilai_saldo, 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center py-5 whov-muted">Tidak ada stok Has Opname untuk
                                    filter ini.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="whov-footer"><span>Menampilkan {{ $stoks->firstItem() ?? 0 }}–{{ $stoks->lastItem() ?? 0 }} dari
                    {{ $stoks->total() }} item</span>
                <div>{{ $stoks->links() }}</div>
            </div>
        </section>
        <p class="whov-footnote">Catatan: KPI dan grafik menggunakan nilai Rupiah agar satuan berbeda (KG, PCS, METER, dll.)
            tidak dijumlahkan. Harga menggunakan harga master saat ini. Total Masuk/Pengeluaran adalah transaksi setelah
            cut-off opname, bukan seluruh transaksi historis.</p>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof ApexCharts === 'undefined') return;
            const movement = @json($movement);
            const jenis = @json($byJenis);
            const rupiah = value => 'Rp ' + Number(value || 0).toLocaleString('id-ID', {
                maximumFractionDigits: 0
            });
            const movementEl = document.querySelector('#whov-movement');
            if (movementEl) new ApexCharts(movementEl, {
                chart: {
                    type: 'area',
                    height: 320,
                    toolbar: {
                        show: false
                    },
                    zoom: {
                        enabled: false
                    },
                    fontFamily: 'inherit'
                },
                series: [{
                        name: 'Stock In',
                        data: movement.map(x => Number(x.stock_in))
                    },
                    {
                        name: 'Stock Out',
                        data: movement.map(x => Number(x.stock_out))
                    }
                ],
                xaxis: {
                    categories: movement.map(x => x.month)
                },
                colors: ['#15946e', '#e15a66'],
                stroke: {
                    curve: 'smooth',
                    width: 3
                },
                fill: {
                    type: 'solid',
                    opacity: 0.12
                },
                dataLabels: {
                    enabled: false
                },
                markers: {
                    size: 3
                },
                yaxis: {
                    labels: {
                        formatter: v => v >= 1000000 ? 'Rp ' + (v / 1000000).toLocaleString('id-ID', {
                            maximumFractionDigits: 1
                        }) + ' jt' : rupiah(v)
                    }
                },
                tooltip: {
                    shared: true,
                    y: {
                        formatter: rupiah
                    }
                },
                grid: {
                    borderColor: '#eef1f5',
                    strokeDashArray: 4
                },
                legend: {
                    position: 'top',
                    horizontalAlign: 'left'
                }
            }).render();
            const jenisEl = document.querySelector('#whov-jenis');
            if (jenisEl) new ApexCharts(jenisEl, {
                chart: {
                    type: 'donut',
                    height: 320,
                    fontFamily: 'inherit'
                },
                series: jenis.map(x => Math.max(0, Number(x.saldo_value) || 0)),
                labels: jenis.map(x => x.jenis),
                colors: ['#3878d8', '#18a37a', '#efad4e', '#8c73d9', '#e26d76', '#39a8ba', '#8993a5'],
                dataLabels: {
                    enabled: false
                },
                legend: {
                    position: 'bottom',
                    fontSize: '11px'
                },
                tooltip: {
                    y: {
                        formatter: rupiah
                    }
                },
                plotOptions: {
                    pie: {
                        donut: {
                            size: '70%'
                        }
                    }
                },
                noData: {
                    text: 'Belum ada nilai saldo'
                }
            }).render();
        });
    </script>
    <style>
        .whov {
            color: #1b293b;
            font-size: 12px
        }

        .whov-titlebar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 19px
        }

        .whov-titlebar h4 {
            font-size: 23px;
            font-weight: 800;
            margin: 4px 0
        }

        .whov-titlebar p,
        .whov-panel-head p {
            color: #8c98a7;
            font-size: 11px;
            margin: 0
        }

        .whov-eyebrow {
            font-size: 10px;
            letter-spacing: 1.4px;
            color: #7291b4;
            font-weight: 800
        }

        .whov-pill,
        .whov-unit {
            border: 1px solid #e1e9f1;
            border-radius: 8px;
            background: white;
            padding: 8px 10px;
            color: #53667a;
            font-size: 10px;
            font-weight: 700;
            white-space: nowrap
        }

        .whov-pill {
            color: #198764;
            background: #ecf8f2;
            border-color: #d5f0e3
        }

        .whov-kpi,
        .whov-panel {
            background: #fff;
            border: 1px solid #e5eaf0;
            border-radius: 13px;
            box-shadow: 0 4px 18px rgba(28, 46, 70, .045)
        }

        .whov-kpi {
            min-height: 112px;
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 17px 14px
        }

        .whov-ico {
            height: 43px;
            width: 43px;
            flex: 0 0 43px;
            display: grid;
            place-items: center;
            border-radius: 11px;
            font-size: 19px
        }

        .whov-ico.blue {
            background: #ebf3ff;
            color: #3479ce
        }

        .whov-ico.green {
            background: #eaf9f2;
            color: #198965
        }

        .whov-ico.red {
            background: #fff0f0;
            color: #d64f5b
        }

        .whov-ico.purple {
            background: #f2edff;
            color: #7a60c4
        }

        .whov-kpi small {
            display: block;
            font-size: 9px;
            color: #8390a2;
            font-weight: 800;
            letter-spacing: .5px
        }

        .whov-kpi strong {
            display: block;
            font-size: 16px;
            line-height: 1.5;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 220px
        }

        .whov-kpi em {
            display: block;
            font-size: 9px;
            color: #9ba5b2;
            font-style: normal
        }

        .whov-panel {
            overflow: hidden
        }

        .whov-panel-head {
            padding: 17px 19px;
            border-bottom: 1px solid #edf0f5;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 10px
        }

        .whov-panel-head h6 {
            font-size: 13px;
            font-weight: 800;
            margin: 0 0 4px
        }

        .whov-chart {
            min-height: 320px;
            padding: 8px 12px
        }

        .whov-filters {
            display: flex;
            align-items: center;
            gap: 9px;
            flex-wrap: wrap;
            padding: 12px 17px;
            border-bottom: 1px solid #edf0f5
        }

        .whov-tabs {
            margin-right: auto
        }

        .whov-tab {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            background: #edf5ff;
            border: 1px solid #d4e5ff;
            color: #326dbb;
            border-radius: 8px;
            padding: 8px 12px;
            font-weight: 800;
            font-size: 11px
        }

        .whov-tab b {
            background: #d8e9ff;
            padding: 1px 5px;
            border-radius: 5px
        }

        .whov-search {
            position: relative
        }

        .whov-search i {
            position: absolute;
            left: 10px;
            top: 10px;
            color: #96a2af
        }

        .whov-search input,
        .whov-filters select {
            border: 1px solid #dfe5ec;
            border-radius: 7px;
            background: #fff;
            height: 34px;
            padding: 0 10px;
            font-size: 11px;
            outline: none
        }

        .whov-search input {
            padding-left: 30px;
            min-width: 220px
        }

        .whov-filterbtn {
            border: 0;
            border-radius: 7px;
            background: #256bb8;
            color: white;
            font-size: 11px;
            font-weight: 700;
            padding: 9px 14px
        }

        .whov-reset {
            font-size: 11px;
            color: #6d7886;
            text-decoration: none;
            padding: 8px
        }

        .whov-table {
            font-size: 11px;
            white-space: nowrap
        }

        .whov-table thead th {
            background: #f5f7fa;
            color: #6d7b8d;
            font-size: 10px;
            letter-spacing: .1px;
            padding: 12px 11px;
            border-bottom: 1px solid #e5eaf0
        }

        .whov-table tbody td {
            padding: 11px;
            border-color: #edf0f4;
            vertical-align: middle
        }

        .whov-table tbody tr:hover {
            background: #f8fbff
        }

        .whov-name {
            font-weight: 700;
            white-space: normal;
            min-width: 180px
        }

        .whov-code {
            font-family: monospace;
            font-weight: 700;
            color: #326eb4
        }

        .whov-jenis {
            background: #f0f3f7;
            border-radius: 6px;
            padding: 5px 7px;
            color: #536276
        }

        .whov-muted {
            color: #96a0ad
        }

        .whov-num {
            font-variant-numeric: tabular-nums
        }

        .whov-positive {
            color: #14845f
        }

        .whov-negative {
            color: #d2535e
        }

        .whov-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
            padding: 14px 17px;
            font-size: 10px;
            color: #8b95a3
        }

        .whov-footer .pagination {
            margin: 0
        }

        .whov-footnote {
            font-size: 10px;
            color: #8e99a7;
            margin: 12px 3px
        }

        @media(max-width:600px) {
            .whov-kpi strong {
                font-size: 13px
            }

            .whov-titlebar h4 {
                font-size: 19px
            }

            .whov-search,
            .whov-search input {
                width: 100%
            }

            .whov-filters select {
                flex: 1
            }
        }
    </style>
@endsection
