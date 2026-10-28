@php

    use Carbon\Carbon;

    use App\Models\Absen;

    $jumlahHari = $bulanSekarang->daysInMonth;

@endphp



<style>
    .absensi-panel {
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 4px 18px rgba(15, 23, 42, .045);
        font-family: Inter, system-ui, -apple-system, 'Segoe UI', sans-serif
    }

    .absensi-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
        padding: 20px 22px;
        border-bottom: 1px solid #e9eef5
    }

    .absensi-heading {
        font-size: 16px;
        font-weight: 750;
        color: #172b46;
        margin: 0
    }

    .absensi-subtitle {
        font-size: 12px;
        color: #7a8799;
        margin: 5px 0 0
    }

    .absensi-search {
        position: relative;
        width: min(100%, 310px)
    }

    .absensi-search svg {
        position: absolute;
        top: 50%;
        left: 13px;
        transform: translateY(-50%);
        color: #94a3b8;
        pointer-events: none
    }

    .absensi-search .form-control {
        height: 40px;
        border: 1px solid #dbe3ed;
        border-radius: 9px;
        padding: 8px 12px 8px 39px;
        font-size: 13px;
        box-shadow: none
    }

    .absensi-search .form-control:focus {
        border-color: #6687b2;
        box-shadow: 0 0 0 3px #dce9f6
    }

    .absensi-scroll {
        overflow: auto;
        max-width: 100%;
        max-height: calc(100vh - 330px);
        min-height: 220px;
        position: relative;
        overscroll-behavior: contain;
        scrollbar-color: #cbd5e1 #f8fafc
    }

    .absensi-table {
        border-collapse: separate !important;
        border-spacing: 0;
        margin: 0 !important;
        width: max-content;
        min-width: 100%;
        font-size: 12px;
        color: #334155
    }

    .absensi-table th,
    .absensi-table td {
        border: 0 !important;
        border-right: 1px solid #e6ebf2 !important;
        border-bottom: 1px solid #e6ebf2 !important;
        padding: 10px 8px !important;
        text-align: center;
        vertical-align: middle !important;
        white-space: nowrap;
        line-height: 1.4
    }

    .absensi-table thead th {
        background: #1b304e !important;
        color: #fff !important;
        font-weight: 650;
        position: sticky;
        top: 0;
        z-index: 10;
        box-shadow: 0 1px 0 rgba(255,255,255,.12)
    }

    .absensi-table thead th.absensi-weekend {
        background: #a64a54 !important;
        color: #fff !important
    }

    .absensi-table tbody tr:nth-child(even) td {
        background: #f8fafc
    }

    .absensi-table tbody tr:hover td {
        background: #edf4fc
    }

    .absensi-table tbody td.absensi-weekend {
        background: #fff1f2 !important;
        color: #8b3944
    }

    .absensi-table tbody tr:nth-child(even) td.absensi-weekend {
        background: #ffe8eb !important
    }

    .absensi-table .absensi-number {
        position: sticky;
        left: 0;
        z-index: 4;
        min-width: 48px;
        width: 48px;
        font-weight: 650;
        background: #fff
    }

    .absensi-table thead .absensi-number {
        z-index: 25;
        top: 0;
        background: #1b304e !important
    }

    .absensi-table tbody tr:nth-child(even) .absensi-number {
        background: #f8fafc
    }

    .absensi-table tbody tr:hover .absensi-number {
        background: #edf4fc
    }

    .absensi-table .absensi-name {
        position: sticky;
        left: 48px;
        z-index: 3;
        min-width: 190px;
        max-width: 250px;
        text-align: left !important;
        font-weight: 600;
        white-space: normal;
        background: #fff;
        box-shadow: 3px 0 7px rgba(15, 23, 42, .04)
    }

    .absensi-table thead .absensi-name {
        z-index: 24;
        top: 0;
        background: #1b304e !important
    }

    .absensi-table tbody tr:nth-child(even) .absensi-name {
        background: #f8fafc
    }

    .absensi-table tbody tr:hover .absensi-name {
        background: #edf4fc
    }

    .absensi-table .absensi-day {
        min-width: 70px;
        font-variant-numeric: tabular-nums
    }

    .absensi-table .absensi-summary {
        min-width: 42px;
        font-weight: 650;
        font-variant-numeric: tabular-nums
    }

    .absensi-table .absensi-hours {
        min-width: 112px;
        font-weight: 700;
        color: #183c62;
        font-variant-numeric: tabular-nums
    }

    .absensi-table .absensi-muted {
        color: #a0aabb
    }


    .absensi-scrollbar {
        width: 100%;
        max-width: 100%;
        overflow-x: auto;
        overflow-y: hidden;
        height: 20px;
        margin-bottom: 6px;
        scrollbar-color: #64748b #e2e8f0;
        scrollbar-width: auto;
        border: 1px solid #e2e8f0;
        border-radius: 5px;
        background: #f1f5f9
    }

    .absensi-scrollbar-track {
        height: 1px
    }

    .absensi-bottom {
        padding: 11px 20px;
        background: #fbfcfe;
        color: #8190a3;
        font-size: 11px;
        display: flex;
        gap: 14px;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap
    }

    .absensi-legend {
        display: flex;
        align-items: center;
        gap: 7px
    }

    .absensi-legend-dot {
        width: 10px;
        height: 10px;
        border-radius: 3px;
        background: #f5bdc3;
        border: 1px solid #e9a5ae
    }

    @media(max-width:600px) {
        .absensi-toolbar {
            padding: 16px
        }

        .absensi-search {
            width: 100%
        }

        .absensi-table .absensi-name {
            min-width: 145px;
            max-width: 170px
        }

        .absensi-table th,
        .absensi-table td {
            padding: 9px 6px !important
        }

        .absensi-scroll {
            max-height: calc(100vh - 280px)
        }
    }
</style>



<div class="absensi-panel">

    <div class="absensi-toolbar">

        <div>

            <h3 class="absensi-heading">Rekapitulasi Absensi</h3>

            <p class="absensi-subtitle">Periode {{ $bulanSekarang->copy()->translatedFormat('F Y') }}</p>

        </div>

        <div class="absensi-search">

            <svg width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <circle cx="11" cy="11" r="8" />
                <path d="m21 21-4.35-4.35" />
            </svg>

            <input type="text" id="searchKaryawan" class="form-control form-control-sm"
                placeholder="Cari nama karyawan..." aria-label="Cari nama karyawan">

        </div>

    </div>



    <div class="absensi-scrollbar" id="absensiTopScrollbar" aria-label="Geser tabel absensi ke kanan atau kiri">
        <div class="absensi-scrollbar-track" id="absensiScrollbarTrack"></div>
    </div>

    <div class="absensi-scroll" id="absensiTableScroll">

        <table class="table table-bordered table-sm absensi-table">

            <thead>

                <tr>

                    <th class="absensi-number">No</th>

                    <th class="absensi-name">Nama</th>

                    @for ($i = 1; $i <= $jumlahHari; $i++)
                        @php

                            $tanggal = $bulanSekarang->copy()->day($i);

                            $isWeekend = $tanggal->isSaturday() || $tanggal->isSunday();

                        @endphp

                        <th class="absensi-day {{ $isWeekend ? 'absensi-weekend' : '' }}">{{ $tanggal->format('d/m') }}
                        </th>
                    @endfor

                    <th class="absensi-summary" title="Sakit">S</th>

                    <th class="absensi-summary" title="Izin">I</th>

                    <th class="absensi-summary" title="Cuti">C</th>

                    <th class="absensi-summary" title="Alfa">A</th>

                    <th class="absensi-hours">Total Jam Kerja</th>

                </tr>
            </thead>

            <tbody id="tableKaryawan">

                @foreach ($karyawans as $karyawan)
                    @php

                        $totalJam = 0;

                        $totalS = 0;

                        $totalI = 0;

                        $totalC = 0;

                        $totalA = 0;

                    @endphp

                    <tr>

                        <td class="absensi-number">{{ $loop->iteration }}</td>

                        <td class="absensi-name">{{ $karyawan->name }}</td>

                        @for ($i = 1; $i <= $jumlahHari; $i++)
                            @php

                                $tanggal = $bulanSekarang->copy()->day($i);

                                $tanggalStr = $tanggal->format('Y-m-d');

                                $isWeekend = $tanggal->isSaturday() || $tanggal->isSunday();

                                // Query asli tetap dipertahankan.

                                $absens = Absen::where('user_id', $karyawan->id)

                                    ->whereDate('tanggal', $tanggalStr)

                                    ->orderBy('jam_masuk')

                                    ->get();

                                $jamKerjaHari = 0;

                                $jamList = [];

                                if ($absens->count()) {
                                    foreach ($absens as $absen) {
                                        $masuk = $absen->jam_masuk;

                                        $keluar = $absen->jam_keluar;

                                        if (!$masuk || !$keluar) {
                                            continue;
                                        }

                                        try {
                                            $start = Carbon::createFromFormat(
                                                'Y-m-d H:i:s',
                                                $tanggalStr . ' ' . $masuk,
                                            );

                                            $end = Carbon::createFromFormat('Y-m-d H:i:s', $tanggalStr . ' ' . $keluar);

                                            $menit = $start->diffInMinutes($end);

                                            if ($menit > 0) {
                                                $jam = $menit / 60;

                                                $jamKerjaHari += $jam;

                                                $totalJam += $jam;

                                                $jamList[] = substr($masuk, 0, 5) . ' - ' . substr($keluar, 0, 5);
                                            }
                                        } catch (\Exception $e) {
                                            // skip kalau format rusak
                                        }

                                        if ($absen->keterangan == 'sakit') {
                                            $totalS++;
                                        } elseif ($absen->keterangan == 'izin') {
                                            $totalI++;
                                        } elseif ($absen->keterangan == 'cuti') {
                                            $totalC++;
                                        }
                                    }
                                } else {
                                    $totalA++;
                                }

                            @endphp

                            <td class="absensi-day {{ $isWeekend ? 'absensi-weekend' : '' }}"
                                @if ($jamKerjaHari > 0) data-bs-toggle="tooltip"

                                    title="{{ number_format($jamKerjaHari, 2) }} jam" @endif>

                                @if ($jamList)
                                    {!! implode('<br>', $jamList) !!}
                                @else
                                    <span class="absensi-muted">-</span>
                                @endif

                            </td>
                        @endfor

                        <td class="absensi-summary">{{ $totalS }}</td>

                        <td class="absensi-summary">{{ $totalI }}</td>

                        <td class="absensi-summary">{{ $totalC }}</td>

                        <td class="absensi-summary">{{ $totalA }}</td>

                        <td class="absensi-hours">{{ number_format($totalJam, 2) }} jam</td>

                    </tr>
                @endforeach

            </tbody>

        </table>

    </div>

    <div class="absensi-bottom">

        <span>Geser tabel ke samping untuk melihat seluruh tanggal dan rekap.</span>

        <span class="absensi-legend"><span class="absensi-legend-dot"></span> Sabtu &amp; Minggu</span>

    </div>

</div>



<script>
    document.addEventListener('DOMContentLoaded', function() {
        const topBar = document.getElementById('absensiTopScrollbar');
        const tableScroll = document.getElementById('absensiTableScroll');
        const track = document.getElementById('absensiScrollbarTrack');
        if (topBar && tableScroll && track) {
            let syncing = false;

            function resizeTrack() {
                track.style.width = tableScroll.scrollWidth + 'px';
            }
            topBar.addEventListener('scroll', function() {
                if (syncing) return;
                syncing = true;
                tableScroll.scrollLeft = topBar.scrollLeft;
                syncing = false;
            });
            tableScroll.addEventListener('scroll', function() {
                if (syncing) return;
                syncing = true;
                topBar.scrollLeft = tableScroll.scrollLeft;
                syncing = false;
            });
            resizeTrack();
            window.addEventListener('resize', resizeTrack);
            if ('ResizeObserver' in window) new ResizeObserver(resizeTrack).observe(tableScroll);
        }
        document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(function(element) {
            if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) bootstrap.Tooltip
                .getOrCreateInstance(element);
        });
    });

    // Pencarian ditangani oleh event delegation di Blade utama (absen.blade.php).
    // Partial ini dimasukkan melalui AJAX innerHTML, jadi jangan pasang handler di sini.
</script>
