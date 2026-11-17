@extends('master.master')

@section('content')
    <div class="container-fluid px-3 px-lg-4 py-3 whov">

        <div class="whov-titlebar">

            <div>

                <div class="whov-eyebrow">WAREHOUSE · STOCK CONTROL</div>

                <h4>Warehouse Overview</h4>

                {{-- <p>Monitoring seluruh jenis stok yang sudah opname · Perhitungan cut-off opname terakhir</p> --}}

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

                    <div><small>TOTAL NILAI (has Opname)</small><strong>Rp

                            {{ number_format($summary['in_value'], 0, ',', '.') }}</strong><em>without bahan finishing</em>
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

        <section class="whov-panel mb-3">

            <div class="whov-panel-head">

                <div>

                    <h6>Pergerakan Saldo & Pengeluaran Stok</h6>

                    <p>Stock Movement — Starting October 2026 · Has Opname · Saldo kumulatif dan pengeluaran mingguan</p>

                    {{-- <p>Akumulasi mingguan (Senin–Minggu) · opname + pemasukan − pengeluaran · 16 minggu terakhir</p> --}}

                </div>

            </div>

            <div id="whov-movement" class="whov-chart" style="min-height:400px"></div>

        </section>

        <section class="whov-panel mb-3">

            <div class="whov-panel-head">

                <div>

                    <h6>Finishing Monitoring — Tomo &amp; Darto</h6>

                    <p>Invoice, pemotongan SPK, dan saldo kumulatif berdasarkan ledger monitoring Finishing</p>

                </div>

                <a class="whov-unit" href="{{ url('/produksi/monitoring-finishing') }}">Lihat Monitoring Finishing</a>

            </div>

            {{-- Dua widget, masing-masing berisi Total Invoice / Potongan Invoice / Saldo --}}

            <div class="row g-3 px-3 pt-3">

                @foreach (['tomo' => 'Tomo', 'darto' => 'Darto'] as $key => $label)
                    @php

                        $totalInvoice = (float) ($finishingTotals[$key . '_in'] ?? 0);

                        $totalPotongan = (float) ($finishingTotals[$key . '_out'] ?? 0);

                        $totalSaldo = $totalInvoice - $totalPotongan;

                    @endphp

                    <div class="col-12 col-lg-6">

                        <div class="whov-finishing-summary">

                            <div class="whov-finishing-summary-title">Finishing {{ $label }}</div>

                            <div class="whov-finishing-summary-grid">

                                <div><small>Total Invoice</small><strong>Rp

                                        {{ number_format($totalInvoice, 0, ',', '.') }}</strong></div>

                                <div><small>Potongan Invoice</small><strong>Rp

                                        {{ number_format($totalPotongan, 0, ',', '.') }}</strong></div>

                                <div><small>Saldo</small><strong>Rp {{ number_format($totalSaldo, 0, ',', '.') }}</strong>

                                </div>

                            </div>

                        </div>

                    </div>
                @endforeach

            </div>

            <div id="whov-finishing" class="whov-chart" style="min-height:370px"></div>

        </section>

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

                <select name="sort_stok" aria-label="Analisis stok" title="Urutan analisis stok">

                    <option value="nama" @selected(($sortStok ?? 'nama') === 'nama')>Urut Nama Barang</option>

                    <option value="keluar_terbanyak" @selected(($sortStok ?? '') === 'keluar_terbanyak')>Pengeluaran Terbanyak</option>

                    <option value="lama_tidak_keluar" @selected(($sortStok ?? '') === 'lama_tidak_keluar')>Lama Tidak Keluar</option>

                </select>

                <select name="idle_days" aria-label="Minimal hari tanpa pengeluaran"
                    title="Berlaku untuk filter Lama Tidak Keluar">

                    @foreach ([30, 60, 90, 180] as $days)
                        <option value="{{ $days }}" @selected(($idleDays ?? 30) == $days)>≥ {{ $days }} hari

                        </option>
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

                            <th class="text-end">Harga</th>

                            <th class="text-end">Qty Opname</th>

                            <th class="text-end">Total Masuk</th>

                            <th class="text-end">Pengeluaran</th>

                            <th class="text-end">Saldo Stok</th>

                            <th class="text-end">Nilai Saldo</th>

                            <th>Terakhir Keluar</th>

                            <th class="text-end">Tidak Keluar</th>

                        </tr>

                    </thead>

                    <tbody>

                        @forelse($stoks as $stok)
                            <tr>

                                <td class="whov-muted">{{ $stoks->firstItem() + $loop->index }}</td>

                                <td><span class="whov-code">{{ $stok->kode_barang }}</span></td>

                                <td class="whov-name">{{ $stok->nama_barang }}</td>

                                <td>@php

                                    $jenisKey = strtolower(trim($stok->jenis ?? ''));

                                    $jenisClass = match ($jenisKey) {
                                        'bahan baku' => 'whov-jenis-baku',

                                        'bahan penolong' => 'whov-jenis-penolong',

                                        'bahan penolong alat' => 'whov-jenis-alat',

                                        'bahan finishing' => 'whov-jenis-finishing',

                                        default => 'whov-jenis-lain',
                                    };

                                @endphp

                                    <span
                                        class="whov-jenis {{ $jenisClass }}">{{ $stok->jenis ?: 'Tanpa Jenis' }}</span>

                                </td>

                                <td>{{ $stok->satuan ?: '-' }}</td>

                                <td class="whov-muted">

                                    {{ $stok->opname_at ? \Carbon\Carbon::parse($stok->opname_at)->format('d/m/Y H:i') : '-' }}

                                </td>

                                <td class="text-end whov-num">Rp

                                    {{ number_format((float) ($stok->harga ?? 0), 0, ',', '.') }}</td>

                                <td class="text-end whov-num">

                                    {{ rtrim(rtrim(number_format((float) $stok->stok_awal, 3, ',', '.'), '0'), ',') }}</td>

                                <td class="text-end whov-num whov-positive">

                                    {{ rtrim(rtrim(number_format((float) $stok->total_in, 3, ',', '.'), '0'), ',') }}</td>

                                <td class="text-end whov-num whov-negative">

                                    {{ rtrim(rtrim(number_format((float) $stok->total_out, 3, ',', '.'), '0'), ',') }}</td>

                                <td class="text-end whov-num">

                                    <b>{{ rtrim(rtrim(number_format((float) $stok->saldo, 3, ',', '.'), '0'), ',') }}</b>

                                </td>

                                <td class="text-end whov-num">Rp {{ number_format($stok->nilai_saldo, 0, ',', '.') }}</td>

                                <td class="whov-muted">

                                    {{ $stok->last_out_date ? \Carbon\Carbon::parse($stok->last_out_date)->format('d/m/Y') : 'Belum pernah keluar' }}

                                </td>

                                <td class="text-end whov-num">{{ number_format($stok->days_without_out, 0, ',', '.') }}

                                    hari</td>

                            </tr>

                        @empty

                            <tr>

                                <td colspan="14" class="text-center py-5 whov-muted">Tidak ada stok Has Opname yang sesuai

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

        <p class="whov-footnote">Catatan: Saldo stok adalah nilai kumulatif hasil cut-off opname terakhir ditambah IN dan
            dikurangi OUT setelah opname.

            Garis pengeluaran menampilkan nilai OUT setiap minggu (bukan kumulatif). Nilai menggunakan harga master saat
            ini. Grafik Warehouse

            menampilkan data mingguan mulai Oktober 2026, dengan akumulasi periode sebelumnya tetap dibawa maju. Minggu

            berjalan menunjukkan posisi sampai data transaksi terakhir.</p>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {



            if (typeof ApexCharts === 'undefined') {



                document.querySelector('#whov-finishing').textContent =



                    'ApexCharts gagal dimuat. Periksa koneksi atau gunakan file ApexCharts lokal.';



                return;



            }



            const finishing = @json($finishingChart ?? []);



            const finishingEl = document.querySelector('#whov-finishing');



            if (finishingEl) {



                const rupiahFinishing = v => 'Rp ' + Number(v || 0).toLocaleString('id-ID', {



                    maximumFractionDigits: 0



                });



                // Dua kelompok (Tomo dan Darto), masing-masing dua cabang: invoice & potongan.



                // Saldo tetap ditampilkan di tooltip, bukan sebagai garis keenam.



                new ApexCharts(finishingEl, {



                    chart: {



                        type: 'line',



                        height: 370,



                        toolbar: {



                            show: true



                        },



                        zoom: {



                            enabled: false



                        },



                        fontFamily: 'inherit'



                    },



                    series: [{



                            name: 'Tomo · Total Invoice',



                            data: finishing.map(x => Number(x.tomo_in || 0))



                        },



                        {



                            name: 'Tomo · Potongan Invoice',



                            data: finishing.map(x => Number(x.tomo_out || 0))



                        },



                        {



                            name: 'Darto · Total Invoice',



                            data: finishing.map(x => Number(x.darto_in || 0))



                        },



                        {



                            name: 'Darto · Potongan Invoice',



                            data: finishing.map(x => Number(x.darto_out || 0))



                        }



                    ],



                    colors: ['#2563eb', '#93c5fd', '#16a34a', '#86efac'],



                    stroke: {



                        curve: 'smooth',



                        width: [3, 2, 3, 2],



                        dashArray: [0, 5, 0, 5]



                    },



                    markers: {



                        size: 3,



                        hover: {



                            size: 6



                        }



                    },



                    xaxis: {



                        categories: finishing.map(x => x.month)



                    },



                    yaxis: {



                        labels: {



                            formatter: v => 'Rp ' + (v / 1000000).toLocaleString('id-ID', {



                                maximumFractionDigits: 1



                            }) + ' jt'



                        }



                    },



                    tooltip: {



                        shared: false,



                        intersect: false,



                        custom: function({



                            seriesIndex,



                            dataPointIndex,



                            w



                        }) {



                            const row = finishing[dataPointIndex];



                            if (!row) return '';



                            const key = seriesIndex < 2 ? 'tomo' : 'darto';



                            const title = key === 'tomo' ? 'Tomo' : 'Darto';



                            const inv = Number(row[key + '_in'] || 0);



                            const out = Number(row[key + '_out'] || 0);



                            const saldo = inv - out;



                            const period = String(row.month || '');



                            return `<div style="padding:11px 14px;min-width:230px;font-size:12px;line-height:1.9;background:#fff;border:1px solid #e5eaf0;border-radius:8px;box-shadow:0 6px 20px #0001">



                        <div style="font-weight:800;margin-bottom:6px">Finishing ${title} · ${period}</div>



                        <div style="display:flex;justify-content:space-between;gap:20px"><span>Total Invoice</span><b>${rupiahFinishing(inv)}</b></div>



                        <div style="display:flex;justify-content:space-between;gap:20px"><span>Potongan Invoice</span><b>${rupiahFinishing(out)}</b></div>



                        <div style="display:flex;justify-content:space-between;gap:20px;border-top:1px solid #e5eaf0;padding-top:4px"><span>Saldo</span><b>${rupiahFinishing(saldo)}</b></div>



                    </div>`;



                        }



                    },



                    legend: {



                        position: 'bottom'



                    },



                    grid: {



                        borderColor: '#edf0f4',



                        strokeDashArray: 4



                    },



                    noData: {



                        text: 'Belum ada data Finishing Tomo / Darto'



                    }



                }).render();



            }



        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const element = document.querySelector('#whov-movement');
            if (!element) return;
            if (typeof ApexCharts === 'undefined') {
                element.textContent = 'ApexCharts tidak dapat dimuat.';
                return;
            }

            // Menggunakan data controller yang sudah ada. Tidak mengubah rumus cut-off.
            const allMovement = @json($movement ?? []);
            const movement = allMovement.filter(row =>
                row.week_start && String(row.week_start).slice(0, 10) >= '2026-10-01'
            );
            const money = value => 'Rp ' + Number(value || 0).toLocaleString('id-ID', {
                maximumFractionDigits: 0
            });
            const escapeHtml = value => String(value ?? '').replace(/[&<>"']/g, char => ({
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;'
            } [char]));
            const nowLabel = @json('Per sekarang · ' . now()->format('d/m/Y H:i'));
            const labels = movement.map((row, i) =>
                i === movement.length - 1 ? nowLabel : row.month
            );
            const lastIndex = movement.length - 1;

            // Saldo: kumulatif (balance).
            // Pengeluaran: nilai transaksi OUT pada minggu itu saja (weekly_out).
            // Jangan memakai stock_out karena itu pengeluaran kumulatif.
            const chart = new ApexCharts(element, {
                chart: {
                    type: 'line',
                    height: 395,
                    fontFamily: 'inherit',
                    toolbar: {
                        show: false
                    },
                    zoom: {
                        enabled: false
                    }
                },
                series: [{
                        name: 'Saldo Stok (Kumulatif)',
                        data: movement.map(row => Number(row.balance || 0))
                    },
                    {
                        name: 'Pengeluaran Mingguan',
                        data: movement.map(row => Number(row.weekly_out || 0))
                    }
                ],
                colors: ['#15946e', '#e15a66'],
                stroke: {
                    curve: 'smooth',
                    width: [3, 3]
                },
                markers: {
                    size: 3,
                    hover: {
                        size: 7
                    }
                },
                dataLabels: {
                    enabled: false
                },
                xaxis: {
                    categories: labels,
                    tickAmount: 8,
                    labels: {
                        rotate: -35,
                        hideOverlappingLabels: true,
                        trim: true,
                        style: {
                            fontSize: '10px'
                        }
                    }
                },
                yaxis: {
                    min: 0,
                    labels: {
                        formatter: value => 'Rp ' + (value / 1000000).toLocaleString('id-ID', {
                            maximumFractionDigits: 1
                        }) + ' jt'
                    }
                },
                legend: {
                    position: 'top',
                    horizontalAlign: 'left'
                },
                grid: {
                    borderColor: '#eef1f5',
                    strokeDashArray: 4,
                    padding: {
                        right: 20,
                        left: 8,
                        top: 8
                    }
                },
                tooltip: {
                    shared: true,
                    intersect: false,
                    custom: function({
                        dataPointIndex
                    }) {
                        const row = movement[dataPointIndex];
                        if (!row) return '';
                        const isNow = dataPointIndex === lastIndex;
                        const period = isNow ? nowLabel : 'Periode ' + (row.month || '');
                        const saldo = Number(row.balance || 0);
                        const keluar = Number(row.weekly_out || 0);
                        const hasWeeklyIn = row.weekly_in !== undefined && row.weekly_in !== null;
                        const perubahan = hasWeeklyIn ? Number(row.weekly_in) - keluar : null;
                        const changeText = perubahan === null ? '' : `
                            <div style="border-top:1px solid #e8edf3;padding-top:8px;margin-top:8px;
                                        display:flex;justify-content:space-between;gap:15px">
                                <span>Perubahan Stok Mingguan</span>
                                <b style="color:${perubahan >= 0 ? '#15946e' : '#e15a66'}">
                                    ${perubahan > 0 ? '+' : ''}${money(perubahan)}
                                </b>
                            </div>`;
                        return `<div style="background:#fff;border:1px solid #e1e7ef;border-radius:10px;
                                      box-shadow:0 7px 22px rgba(0,0,0,.12);padding:13px 15px;
                                      min-width:270px;font-size:12px;color:#24374d;line-height:1.6">
                            <div style="font-weight:800;margin-bottom:9px">${escapeHtml(period)}</div>
                            <div style="display:flex;justify-content:space-between;gap:18px;margin:7px 0">
                                <span><i style="display:inline-block;width:8px;height:8px;border-radius:50%;
                                    background:#15946e;margin-right:6px"></i>Saldo Stok</span>
                                <b>${money(saldo)}</b>
                            </div>
                            <div style="display:flex;justify-content:space-between;gap:18px;margin:7px 0">
                                <span><i style="display:inline-block;width:8px;height:8px;border-radius:50%;
                                    background:#e15a66;margin-right:6px"></i>Pengeluaran Mingguan</span>
                                <b>${money(keluar)}</b>
                            </div>
                            ${changeText}
                            <div style="font-size:10px;color:#8491a1;margin-top:8px">
                                Saldo kumulatif · pengeluaran pada periode ini saja
                            </div>
                        </div>`;
                    }
                },
                noData: {
                    text: 'Belum ada data stok Has Opname sejak Oktober 2026'
                }
            });
            chart.render();
        });
    </script>

    <style>
        .whov-now-pin {

            display: flex;

            flex-direction: column;

            gap: 3px;

            padding: 8px 12px;

            border: 1px solid #d9e7f9;

            background: #f5f9ff;

            border-radius: 9px;

            font-size: 10px;

            color: #33475f;

            min-width: 230px;

        }

        .whov-now-pin-title {

            font-weight: 800;

            color: #2563a8;

            font-size: 9px;

            letter-spacing: .3px
        }

        .whov-now-pin strong {

            color: #162e52;

            font-variant-numeric: tabular-nums
        }

        @media(max-width:600px) {

            .whov-now-pin {

                min-width: 0;

                width: 100%
            }

        }

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

            display: inline-block;

            border-radius: 6px;

            padding: 5px 8px;

            font-weight: 700;

            white-space: nowrap;

        }

        .whov-jenis-baku {

            background: #e8f1ff;

            color: #245eb1;

        }

        .whov-jenis-penolong {

            background: #e8f8ef;

            color: #147a4b;

        }

        .whov-jenis-alat {

            background: #fff2df;

            color: #a76208;

        }

        .whov-jenis-finishing {

            background: #f1eaff;

            color: #7746b4;

        }

        .whov-jenis-lain {

            background: #f0f3f7;

            color: #536276;

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

        .whov-finishing-summary {

            border: 1px solid #e1e8f1;

            border-radius: 10px;

            background: #f8fafc;

            padding: 12px 14px
        }

        .whov-finishing-summary-title {

            font-weight: 800;

            font-size: 12px;

            margin-bottom: 10px;

            color: #1e3658
        }

        .whov-finishing-summary-grid {

            display: grid;

            grid-template-columns: repeat(3, minmax(0, 1fr));

            gap: 10px
        }

        .whov-finishing-summary-grid>div {

            min-width: 0
        }

        .whov-finishing-summary-grid small {

            display: block;

            font-size: 10px;

            color: #8190a1;

            margin-bottom: 3px
        }

        .whov-finishing-summary-grid strong {

            display: block;

            font-size: 13px;

            white-space: nowrap;

            overflow: hidden;

            text-overflow: ellipsis
        }

        @media(max-width:575px) {

            .whov-finishing-summary-grid {

                grid-template-columns: 1fr
            }

            .whov-finishing-summary-grid strong {

                font-size: 12px
            }

        }
    </style>
@endsection
