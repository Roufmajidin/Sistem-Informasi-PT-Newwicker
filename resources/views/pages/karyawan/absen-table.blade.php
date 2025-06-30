<table class="table table-bordered">
    <thead style="color:white">
        <tr class="sticky-header">
            <th>No.</th>
            <th class="sticky">Nama Lengkap</th>
            <th>Status</th>
            <th>Divisi kerja</th>
            @for ($i = 1; $i <= $daysInMonth; $i++)
                @php
                $date=\Carbon\Carbon::createFromDate($year, $month, $i);
                $isWeekend=in_array($date->format('l'), ['Saturday', 'Sunday']);
                @endphp
                <th style="{{ $isWeekend ? 'color:red' : '' }}">{{ $i }}</th>
                @endfor
                <th>Hadir</th>
                <th>Izin</th>
                <th>Alfa</th>
                <th>Sakit</th>
                <th>Cuti</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($karyawans as $index => $karyawan)
        @php
       $hadir = 0;
        $alfa  = 0;
        $sakit = 0;
        $izin  = 0;
        $cuti  = 0;
        @endphp
        <tr>
            <td>{{ $index + 1 }}</td>
            @php $kar = \App\Models\Karyawan::find($karyawan->karyawan_id); @endphp
            <td class="sticky">{{ $karyawan->name }}</td>
            <td>{{ $kar->status }}</td>
            <td>{{ $kar->divisi_id }}</td>

            @for ($i = 1; $i <= $daysInMonth; $i++)
                @php
                $tanggal=\Carbon\Carbon::create($year, $month, $i)->toDateString();
                $absen = $karyawan->absens->where('tanggal', $tanggal)->first();
                @endphp
                <td class="text-center absen-td" data-user="{{ $karyawan->id }}" data-date="{{ $tanggal }}">
                    @if ($absen)
                    @php $status = strtolower($absen->keterangan); @endphp
                    @if ($status === 'hadir')
                    H @php $hadir++; @endphp
                    @elseif ($status === 'izin')
                    I @php $izin++; @endphp
                    @elseif ($status === 'cuti')
                    C @php $cuti++; @endphp
                    @elseif ($status === 'sakit')
                    S @php $sakit++; @endphp
                    @else
                    ?
                    @endif
                    @else
                    . @php $alfa++; @endphp
                    @endif
                </td>
                @endfor

                <td class="text-center">{{ $hadir }}</td>
                <td class="text-center">{{ $izin }}</td>
                <td class="text-center">{{ $alfa }}</td>
                <td class="text-center">{{ $sakit }}</td>
                <td class="text-center">{{ $cuti }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
