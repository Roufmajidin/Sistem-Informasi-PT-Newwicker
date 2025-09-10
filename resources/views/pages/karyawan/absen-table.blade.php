<table class="table table-striped">
    <thead>
        <tr>
            <th>No</th>
            <th class="sticky">Nama</th>
            <!-- <th>Status</th> -->
            <th>Divisi</th>
            <th>Tanggal</th>
            <th>Jam Masuk</th>
            <th>Jam Keluar</th>
            <th>Lokasi Masuk</th>
            <th>Lokasi Keluar</th>
            <th>Keterangan</th>
            <th>-</th>
            <th>Foto</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($absens as $index => $absen)
        @php
        $formatMenit = function($menit) {
        $menit = abs((int) $menit);
        if ($menit >= 60) {
        $jam = floor($menit / 60);
        $sisa = $menit % 60;
        return $jam . ' jam ' . ($sisa > 0 ? $sisa . ' menit' : '');
        }
        return $menit . ' menit';
        };

        $jamMasuk = \Carbon\Carbon::parse($absen->jam_masuk);
        $jamKeluar = $absen->jam_keluar ? \Carbon\Carbon::parse($absen->jam_keluar) : null;

        $batasMasuk = \Carbon\Carbon::createFromTime(8, 1);
        $batasPulang = \Carbon\Carbon::createFromTime(17, 0);

        $keterangan = [];

        if ($jamMasuk->gt($batasMasuk)) {
        $menitTerlambat = $batasMasuk->diffInMinutes($jamMasuk);
        $keterangan[] = "Terlambat " . $formatMenit($menitTerlambat);
        }

        if ($jamKeluar && $jamKeluar->lt($batasPulang)) {
        $menitCepat = $batasPulang->diffInMinutes($jamKeluar);
        $keterangan[] = "Pulang cepat " . $formatMenit($menitCepat);
        }

        if ($jamKeluar && $jamKeluar->gt($batasPulang)) {
        $keterangan[] = "";
        }

        if (empty($keterangan)) {
        $keterangan[] = "Tepat waktu";
        }
        @endphp


        <tr>
            <td>{{ $index + 1 }}</td>
            <td class="sticky">{{ $absen->user->name ?? '-' }}</td>
            <!-- <td>{{ $absen->status }}</td> -->
            <td>{{ $absen->user->divisi_id ?? '-' }}</td>
            <td class="absen-td" data-user="{{ $absen->user_id }}" data-date="{{ $absen->tanggal }}">
                {{ $absen->tanggal }}
            </td>
            <td>{{ $absen->jam_masuk }}</td>
            <td>{{ $absen->jam_keluar ?? '-' }}</td>
            <td style="max-width: 150px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                @if($absen->latitude && $absen->longitude)
                <a href="https://www.google.com/maps?q={{ $absen->latitude }},{{ $absen->longitude }}"
                    target="_blank"
                    title="{{ $absen->latitude }}, {{ $absen->longitude }}">
                    {{ $absen->latitude }}, {{ $absen->longitude }}
                </a>
                @else
                -
                @endif
            </td>

            <td style="max-width: 150px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"
                title="{{ $absen->latitude_k ?? '-' }}, {{ $absen->longitude_k ?? '-' }}">
                {{ $absen->latitude_k ?? '-' }}, {{ $absen->longitude_k ?? '-' }}
            </td>
              <td style="max-width: 150px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"
                title="{{ $absen->latitude_k ?? '-' }}, {{ $absen->longitude_k ?? '-' }}">
                {{ $absen->keterangan ?? '-' }}
            </td>

            <td class="text-center"
                style="max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"
                title="{{ implode(', ', $keterangan) }}">
                {!! implode(', ', $keterangan) !!}
            </td>

            <td>
                @if($absen->foto)
                <i class="fa fa-eye"></i>
                <img src="{{ asset('storage/' . $absen->foto) }}" width="50" height="50">
                <img src="{{ asset('storage/' . $absen->foto_keluar) }}" width="50" height="50">
                @else - @endif
            </td>
            <td><i class="fa fa-ellipsis-v"></i></td>
        </tr>
        @endforeach
    </tbody>
</table>
