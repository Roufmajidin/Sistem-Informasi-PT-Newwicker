@php
    use Carbon\Carbon;
    use App\Models\Absen;

    $jumlahHari = $bulanSekarang->daysInMonth;
@endphp

<style>
    table th, table td {
        padding: 4px !important;
        vertical-align: middle !important;
        font-size: 12px;
    }
    thead th {
        line-height: 1;
    }
</style>
<div class="mb-2">
    <input type="text"
           id="searchKaryawan"
           class="form-control form-control-sm"
           placeholder="Cari nama karyawan...">
</div>


<table class="table table-bordered table-sm">
    <thead>
        <tr class="bg-info text-white">
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
                <td class="text-start">{{ $karyawan->name }}</td>

                @for ($i = 1; $i <= $jumlahHari; $i++)

                    @php
                        $tanggal = $bulanSekarang->copy()->day($i);
                        $tanggalStr = $tanggal->format('Y-m-d');
                        $isWeekend = $tanggal->isSaturday() || $tanggal->isSunday();

                        // ambil langsung dari DB (ANTI ERROR RELASI)
                        $absens = Absen::where('user_id', $karyawan->id)
                                    ->whereDate('tanggal', $tanggalStr)
                                    ->orderBy('jam_masuk')
                                    ->get();

                        $jamKerjaHari = 0;
                        $jamList = [];

                        if ($absens->count()) {

                            foreach ($absens as $absen) {

                                $masuk  = $absen->jam_masuk;
                                $keluar = $absen->jam_keluar;

                                if (!$masuk || !$keluar) continue;

                                try {

                                    $start = Carbon::createFromFormat('Y-m-d H:i:s', $tanggalStr.' '.$masuk);
                                    $end   = Carbon::createFromFormat('Y-m-d H:i:s', $tanggalStr.' '.$keluar);

                                    $menit = $start->diffInMinutes($end);

                                    if ($menit > 0) {
                                        $jam = $menit / 60;

                                        $jamKerjaHari += $jam;
                                        $totalJam += $jam;

                                        $jamList[] =
                                            substr($masuk,0,5) . ' - ' .
                                            substr($keluar,0,5);
                                    }

                                } catch (\Exception $e) {
                                    // skip kalau format rusak
                                }

                                // kategori
                                if ($absen->keterangan == 'sakit') $totalS++;
                                elseif ($absen->keterangan == 'izin') $totalI++;
                                elseif ($absen->keterangan == 'cuti') $totalC++;
                            }

                        } else {
                            $totalA++;
                        }
                    @endphp

                    <td class="{{ $isWeekend ? 'bg-danger text-white' : '' }}"
                        @if ($jamKerjaHari > 0)
                            data-bs-toggle="tooltip"
                            title="{{ number_format($jamKerjaHari, 2) }} jam"
                        @endif>

                        @if ($jamList)
                            {!! implode('<br>', $jamList) !!}
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
