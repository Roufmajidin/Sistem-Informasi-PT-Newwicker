<table class="table table-striped">
    <thead>
        <tr>
            <th>No</th>
            <th class="sticky">Nama</th>
            <th>Status</th>
            <th>Divisi</th>
            <th>Tanggal</th>
            <th>Jam Masuk</th>
            <th>Jam Keluar</th>
            <th>Lokasi Masuk</th>
            <th>Lokasi Keluar</th>
            <th>Keterangan</th>
            <th>Foto</th>
            <th>Aksi</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($karyawans as $index => $karyawan)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td class="sticky">{{ $karyawan->name }}</td>
                <td>{{ $karyawan->status }}</td>
                <td>{{ $karyawan->divisi_id }}</td>

                @forelse ($karyawan->absens as $k)
                    @php
                        $jamMasuk = \Carbon\Carbon::parse($k->jam_masuk);
                        $jamKeluar = $k->jam_keluar ? \Carbon\Carbon::parse($k->jam_keluar) : null;
                        $batasMasuk = \Carbon\Carbon::createFromTime(8, 15);
                        $batasPulang = \Carbon\Carbon::createFromTime(17, 0);
                        $terlambat = $jamMasuk->gt($batasMasuk);
                        $menitTerlambat = $terlambat ? number_format($jamMasuk->diffInSeconds($batasMasuk)/60, 1) : 0;
                        $lewatPulang = $jamKeluar && $jamKeluar->gt($batasPulang);
                    @endphp
                    <td class="absen-td" data-user="{{ $karyawan->id }}" data-date="{{ $k->tanggal }}">{{ $k->tanggal }}</td>
                    <td>{{ $k->jam_masuk }}</td>
                    <td>{{ $k->jam_keluar ?? '-' }}</td>
                    <td>
                        @if($k->latitude && $k->longitude)
                            <a href="https://www.google.com/maps?q={{ $k->latitude }},{{ $k->longitude }}" target="_blank">
                                {{ $k->latitude }}, {{ $k->longitude }}
                            </a>
                        @else - @endif
                    </td>
                   <td>
    @if($k->latitude_k && $k->longitude_k)
        <a href="https://www.google.com/maps?q={{ $k->latitude_k }},{{ $k->longitude_k }}" target="_blank">
            {{ $k->latitude_k }}, {{ $k->longitude_k }}
        </a>
    @else - @endif
</td>
                    <td class="text-center">
                        @if($terlambat && $lewatPulang) {{ $menitTerlambat }} menit
                        @elseif($terlambat) Terlambat {{ $menitTerlambat }} menit
                        @elseif($lewatPulang) Lembur
                        @else - @endif
                    </td>
                    <td>
                        @if($k->foto)
                            <img src="{{ asset('storage/' . $k->foto) }}" width="50" height="50">
                            <img src="{{ asset('storage/' . $k->foto_keluar) }}" width="50" height="50">
                        @else - @endif
                    </td>
                    <td><i class="fa fa-ellipsis-v"></i></td>
                @empty
                    <td colspan="8" class="text-center">Belum ada absen</td>
                @endforelse
            </tr>
        @endforeach
    </tbody>
</table>
