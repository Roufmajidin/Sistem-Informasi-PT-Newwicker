@extends('master.master')

@section('title', 'Absen History')

@section('content')
<div class="container py-4">

    {{-- HEADER --}}
    <div class="text-center mb-4">
        <h3 class="fw-bold">Riwayat Absensi</h3>
        <p class="text-muted">Lihat catatan kehadiran Anda.</p>
    </div>

    {{-- FILTER BULAN & TAHUN --}}
    <div class="card shadow-sm mb-4 p-3">
        <form id="filterForm" class="row g-2">
            <div class="col-md-3">
                <label for="bulan" class="form-label">Bulan</label>
                <select id="bulan" name="bulan" class="form-select">
                    @foreach (range(1, 12) as $b)
                        <option value="{{ $b }}" {{ $b == $bulan ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::create()->month($b)->translatedFormat('F') }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="tahun" class="form-label">Tahun</label>
                <select id="tahun" name="tahun" class="form-select">
                    @foreach (range(date('Y') - 3, date('Y')) as $t)
                        <option value="{{ $t }}" {{ $t == $tahun ? 'selected' : '' }}>{{ $t }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 align-self-end">
                <button type="submit" class="btn btn-primary w-100">Tampilkan</button>
            </div>
        </form>
    </div>

    {{-- TABEL RIWAYAT --}}
    <div class="card shadow-sm p-3">
        <h5 class="mb-3">
            Data Absensi Bulan {{ \Carbon\Carbon::create()->month($bulan)->translatedFormat('F') }} {{ $tahun }}
        </h5>

        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Tanggal</th>
                        <th>Jam Masuk</th>
                        <th>Jam Keluar</th>
                        <th>Status</th>
                        <th>Jarak (m)</th>
                        <th>Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($riwayat as $absen)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($absen->tanggal)->translatedFormat('d F Y') }}</td>
                            <td>{{ $absen->jam_masuk ?? '-' }}</td>
                            <td>{{ $absen->jam_keluar ?? '-' }}</td>
                            <td>
                                @if ($absen->jam_masuk && $absen->jam_keluar)
                                    <span class="badge bg-success">Hadir</span>
                                @elseif ($absen->jam_masuk && ! $absen->jam_keluar)
                                    <span class="badge bg-warning text-dark">Belum Pulang</span>
                                @else
                                    <span class="badge bg-danger">Tidak Hadir</span>
                                @endif
                            </td>
                            <td>{{ $absen->jarak ? number_format($absen->jarak, 1) : '-' }}</td>
                            <td>{{ $absen->keterangan ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted">
                                Tidak ada data absensi untuk bulan ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

{{-- SCRIPT FILTER --}}
<script>
    document.getElementById('filterForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const bulan = document.getElementById('bulan').value;
        const tahun = document.getElementById('tahun').value;
        window.location.href = `?bulan=${bulan}&tahun=${tahun}`;
    });
</script>
@endsection
