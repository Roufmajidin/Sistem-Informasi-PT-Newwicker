@extends('master.master')
@section('title', "Absen Sekarang")

@section('content')
<div class="container py-4">

    {{-- HEADER --}}
    <div class="text-center mb-4">
        <h3 class="fw-bold">Absen Sekarang</h3>
        <p class="text-muted">Pastikan wajah dan posisi kamera jelas.</p>
    </div>

    {{-- INFO USER --}}
    <div class="card mb-4 shadow-sm p-3">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h5>Nama: <b>{{ auth()->user()->name }}</b></h5>
<h6>Divisi: <b>{{ $divId->nama ?? '-' }}</b></h6>

            </div>
            <div class="col-md-6 text-end">
                <h5>Jam Sekarang:</h5>
                <h2 id="clock" class="fw-bold text-primary"></h2>
            </div>
        </div>
    </div>

    {{-- KAMERA & LOKASI --}}
    <div class="card mb-4 shadow-sm p-3 text-center">
        <h5 class="mb-3">Kamera</h5>
        <video id="camera" autoplay playsinline style="width: 100%; max-width: 400px; border-radius: 10px;"></video>
        <canvas id="snapshot" width="400" height="300" class="d-none"></canvas>

        <div class="mt-3">
            <p id="lokasi-info" class="mb-2 text-muted">Mendeteksi lokasi...</p>
            <p id="jarak-info" class="fw-bold"></p>
        </div>

        <div class="mt-3">
            <button class="btn btn-success" id="btnAbsenMasuk" disabled>Absen Masuk</button>
            <button class="btn btn-danger" id="btnAbsenKeluar" disabled>Absen Keluar</button>
        </div>
    </div>

    {{-- RIWAYAT ABSEN --}}
    <div class="card shadow-sm p-3">
        <h5 class="mb-3">Riwayat Absen Hari Ini</h5>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Jam Masuk</th>
                    <th>Jam Keluar</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($riwayat as $absen)
                    <tr>
                        <td>{{ $absen->tanggal }}</td>
                        <td>{{ $absen->jam_masuk ?? '-' }}</td>
                        <td>{{ $absen->jam_keluar ?? '-' }}</td>
                        <td>{{ $absen->status }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted">Belum ada data absen hari ini</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
<script>
    // ================= QC JOGJA =================
    // 180 utnuk pa rohmad jogja
    const isQcJogja = {{ auth()->user()->id == 180 ? 'true' : 'false' }};
    console.log(isQcJogja)
    // ================= JAM REALTIME =================
    function updateClock() {
        const now = new Date();
        const time = now.toLocaleTimeString('id-ID', { hour12: false });
        document.getElementById('clock').textContent = time;
    }
    setInterval(updateClock, 1000);
    updateClock();

    // ================= AKTIFKAN KAMERA =================
    const video = document.getElementById('camera');
    const canvas = document.getElementById('snapshot');
    const context = canvas.getContext('2d');

    navigator.mediaDevices.getUserMedia({ video: true })
        .then(stream => video.srcObject = stream)
        .catch(err => alert('Kamera tidak dapat diakses: ' + err.message));

    // ================= DATA KANTOR =================
    const kantorLat = {{ $officeLat }};
    const kantorLng = {{ $officeLng }};
    const maxRadius = {{ $radius }}; // meter

    // ================= DETEKSI LOKASI =================
    let latitude = null, longitude = null;

    function hitungJarak(lat1, lon1, lat2, lon2) {
        const R = 6371;
        const dLat = (lat2 - lat1) * Math.PI / 180;
        const dLon = (lon2 - lon1) * Math.PI / 180;
        const a = Math.sin(dLat / 2) ** 2 +
                  Math.cos(lat1 * Math.PI / 180) *
                  Math.cos(lat2 * Math.PI / 180) *
                  Math.sin(dLon / 2) ** 2;
        const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
        return R * c;
    }

    function perbaruiLokasi(pos) {
        latitude = pos.coords.latitude;
        longitude = pos.coords.longitude;

        const jarakKm = hitungJarak(latitude, longitude, kantorLat, kantorLng);
        const jarakMeter = jarakKm * 1000;
        const jarakBulatan = Math.round(jarakMeter);

        document.getElementById('lokasi-info').textContent =
            `Koordinat: ${latitude.toFixed(5)}, ${longitude.toFixed(5)}`;

        /*
        ================= QC JOGJA =================
        */
        if (isQcJogja) {

            document.getElementById('jarak-info').classList.remove('text-danger');
            document.getElementById('jarak-info').classList.add('text-primary');

            document.getElementById('jarak-info').textContent =
                `Hy QC jogja anda berada di jarak ${jarakBulatan} meter`;

            document.getElementById('btnAbsenMasuk').disabled = false;
            document.getElementById('btnAbsenKeluar').disabled = false;

            return;
        }

        /*
        ================= USER NORMAL =================
        */
        document.getElementById('jarak-info').textContent =
            `Jarak dari kantor: ${jarakKm.toFixed(2)} km`;

        if (jarakMeter <= maxRadius) {

            document.getElementById('jarak-info').classList.remove('text-danger');
            document.getElementById('jarak-info').classList.add('text-success');

            document.getElementById('btnAbsenMasuk').disabled = false;
            document.getElementById('btnAbsenKeluar').disabled = false;

        } else {

            document.getElementById('jarak-info').classList.remove('text-success');
            document.getElementById('jarak-info').classList.add('text-danger');

            document.getElementById('jarak-info').textContent +=
                ' (Terlalu jauh dari kantor)';

            document.getElementById('btnAbsenMasuk').disabled = true;
            document.getElementById('btnAbsenKeluar').disabled = true;
        }
    }

    function gagalLokasi(err) {
        document.getElementById('lokasi-info').textContent =
            'Lokasi tidak dapat dideteksi: ' + err.message;
        document.getElementById('jarak-info').textContent = '';
    }

    navigator.geolocation.getCurrentPosition(perbaruiLokasi, gagalLokasi, {
        enableHighAccuracy: true,
        timeout: 10000,
        maximumAge: 0
    });

    // ================= KIRIM ABSEN =================
    function ambilFoto(status) {

        context.drawImage(video, 0, 0, canvas.width, canvas.height);

        canvas.toBlob(blob => {

            const formData = new FormData();
            formData.append('status', status);
            formData.append('foto', blob, 'absen.png');
            formData.append('latitude', latitude);
            formData.append('longitude', longitude);

            fetch('{{ route("absen.storeAbsen") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: formData
            })
        .then(async res => {

    const data = await res.json();

    // ================= 403 =================
    if (res.status === 403) {
        Swal.fire({
            icon: 'warning',
            title: 'Tidak Diizinkan',
            text: data.message
        });
        return;
    }

    // ================= ERROR LAIN =================
    if (!res.ok) {
        throw new Error(data.message || 'Terjadi kesalahan');
    }

    return data;
})
.then(data => {

    if (!data) return;

    Swal.fire({
        icon: 'success',
        title: 'Berhasil',
        text: data.message,
        timer: 2000,
        showConfirmButton: false
    }).then(() => location.reload());

})
.catch(err => {

    Swal.fire({
        icon: 'error',
        title: 'Error',
        text: err.message
    });

});

        }, 'image/png');
    }

    document.getElementById('btnAbsenMasuk')
        .addEventListener('click', () => ambilFoto('masuk'));

    document.getElementById('btnAbsenKeluar')
        .addEventListener('click', () => ambilFoto('keluar'));
</script>

@endsection
