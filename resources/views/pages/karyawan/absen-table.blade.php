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
        $keterangan[] = "";
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
@php
    $officeLat = config('office.lat');
    $officeLng = config('office.lon');
    $officeRadius = config('office.radius'); // misal 100 meter

    $jarakMasuk = ($absen->latitude && $absen->longitude)
        ? hitungJarak($absen->latitude, $absen->longitude, $officeLat, $officeLng)
        : null;
     $jarakKeluar= ($absen->latitude_k && $absen->longitude_k)
        ? hitungJarak($absen->latitude_k, $absen->longitude_k, $officeLat, $officeLng)
        : null;

    $jarakMasukFormatted = $jarakMasuk ? formatJarak($jarakMasuk) : '-';
    $jarakKeluarFormatted = $jarakKeluar ? formatJarak($jarakKeluar) : '-';
    $statusKantor = ($jarakMasuk !== null && $jarakMasuk <= $officeRadius) ? 'Di kantor' : 'Di luar kantor';
@endphp

<td>
    @if($absen->latitude && $absen->longitude)
        <div
            title="Koordinat: {{ number_format($absen->latitude, 6) }}, {{ number_format($absen->longitude, 6) }} | Jarak: {{ $jarakMasukFormatted }}"
            ondblclick="copyTextToClipboard(this)"
            data-full="{{ $absen->latitude }},{{ $absen->longitude }}">
            {{ number_format($absen->latitude, 6) }}, {{ number_format($absen->longitude, 6) }}
            <br>
            <small class="text-muted">Jarak ke kantor: ±{{ $jarakMasukFormatted }} ({{ $statusKantor }})</small>
        </div>
    @else
        -
    @endif
</td>


<td>
    @if($absen->latitude_k && $absen->longitude_k)
        <div
            title="Koordinat: {{ number_format($absen->latitude_k, 6) }}, {{ number_format($absen->longitude_k, 6) }} | Jarak: {{ formatJarak($jarakKeluar) }}"
            ondblclick="copyTextToClipboard(this)"
            data-full="{{ $absen->latitude_k }},{{ $absen->longitude_k }}">
            {{ $absen->latitude_k }}, {{ $absen->longitude_k }}
            <small class="text-muted">(±    ({{ $jarakKeluarFormatted }})
)</small>
        </div>
    @else
        -
    @endif
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
     @if($absen->foto || $absen->foto_keluar)
        <button type="button"
            class="btn btn-sm btn-outline-primary view-photo"
            data-foto-masuk="{{ $absen->foto ? asset('storage/' . $absen->foto) : '' }}"
            data-foto-keluar="{{ $absen->foto_keluar ? asset('storage/' . $absen->foto_keluar) : '' }}">
            <i class="fa fa-eye"></i>
        </button>
    @else
        -
    @endif
</td>
            <td><i class="fa fa-ellipsis-v"></i></td>
        </tr>
        @endforeach
    </tbody>
</table>
<!-- Modal HTML di bawah table -->

<!-- Modal -->
<div class="modal fade" id="fotoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Foto Absen</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
                <div class="row">
                    <div class="col" id="colMasuk">
                        <h6>Masuk</h6>
                        <img id="fotoMasuk" src="" alt="Foto Masuk" class="img-fluid" style="max-height:60vh; border-radius:5px; border:1px solid #ddd;">
                    </div>
                    <div class="col" id="colKeluar">
                        <h6>Keluar</h6>
                        <img id="fotoKeluar" src="" alt="Foto Keluar" class="img-fluid" style="max-height:60vh; border-radius:5px; border:1px solid #ddd;">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
