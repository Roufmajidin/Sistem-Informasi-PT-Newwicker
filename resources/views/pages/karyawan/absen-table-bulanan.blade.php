@php
    use Carbon\Carbon;
    $jumlahHari = $bulanSekarang->daysInMonth;
@endphp
<style>
    table th, table td {
        padding: 4px !important;   /* kecilin padding */
        vertical-align: middle !important; /* biar teks center vertikal */
        font-size: 12px; /* perkecil font supaya hemat ruang */
    }
    thead th {
        line-height: 1;
    }
</style>
<table class="table table-bordered table-sm">
    <thead>
        <tr style class="bg-info text-white">
                        <th rowspan="2" class="text-start">Nama</th>

            @for ($i = 1; $i <= $jumlahHari; $i++)
                @php
                    $tanggal = $bulanSekarang->copy()->day($i);
                    $isWeekend = $tanggal->isSaturday() || $tanggal->isSunday();
                @endphp
                <th rowspan="2" class="{{ $isWeekend ? 'bg-danger text-white' : '' }}">
                    {{ $i }}
                </th>
            @endfor
            <th colspan="4">Total Kehadiran</th>
            <th rowspan="2">Total Jam Kerja</th>
        </tr>
        <tr class="bg-info text-white align-middle">
            <th>S</th>
            <th>I</th>
            <th>C</th>
            <th>A</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($karyawans as $karyawan)
            @php
                $totalJam = 0;
                $totalS = 0; $totalI = 0; $totalC = 0; $totalA = 0;
            @endphp
            <tr>
                <td class="text-start">{{ $karyawan->name }}</td>
                @for ($i = 1; $i <= $jumlahHari; $i++)
                    @php
                        $tanggal = $bulanSekarang->copy()->day($i);
                        $isWeekend = $tanggal->isSaturday() || $tanggal->isSunday();
                        $tanggalStr = $tanggal->format('Y-m-d');
                        $absen = $karyawan->absens->firstWhere('tanggal', $tanggalStr);

                        $jamKerja = 0;
                        $jamKeluar = null;

                        if ($absen && $absen->jam_masuk) {
                            if ($absen->jam_keluar) {
                                $jamKeluar = $absen->jam_keluar;
                            } else {
                                if (Carbon::now()->greaterThan(Carbon::parse($tanggalStr.' 17:00'))) {
                                    $jamKeluar = '17:00';
                                }
                            }

                            if ($jamKeluar) {
                                $jamKerja = Carbon::parse($absen->jam_masuk)
                                            ->diffInMinutes(Carbon::parse($jamKeluar)) / 60;
                                $totalJam += $jamKerja;
                            }
                        }

                        // hitung kehadiran
                        if ($absen) {
                            if ($absen->keterangan == 'sakit') $totalS++;
                            elseif ($absen->keterangan == 'i') $totalI++;
                            elseif ($absen->keterangan == 'cuti') $totalC++;
                        } else {
                            $totalA++;
                        }
                    @endphp
                    <td class="{{ $isWeekend ? 'bg-danger text-white' : '' }}"
                        @if ($jamKerja > 0)
                            data-bs-toggle="tooltip" data-bs-placement="top"
                            title="{{ number_format($jamKerja, 2) }} jam"
                        @endif>
                        @if ($absen)
                            @if ($absen->keterangan == 'sakit')
                                S
                            @elseif ($absen->keterangan == 'i')
                                I
                            @elseif ($absen->keterangan == 'cuti')
                                C
                            @else
                                @if ($absen->jam_masuk && !$absen->jam_keluar && $jamKeluar === '17:00')
                                    H <small class="text-muted">(auto)</small>
                                @else
                                    H
                                @endif
                            @endif
                        @else
                            -
                        @endif
                    </td>
                @endfor
                <td>{{ $totalS }}</td>
                <td>{{ $totalI }}</td>
                <td>{{ $totalC }}</td>
                <td>{{ $totalA }}</td>
                <td>{{ number_format($totalJam, 2) }} jam</td>
            </tr>
        @endforeach
    </tbody>
</table>

<script>
document.addEventListener("DOMContentLoaded", function () {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    })
})
</script>
