@extends('master.master')
@section('title', "Karyawan Lembur")
@section('content')
<div class="padding">
    <div class="box">
        <div class="p-a white lt box-shadow">
            <div class="row align-items-center">
                {{-- Kiri: Judul --}}
                <div class="col-sm-6">
                    <h4 class="mb-0 _300">Lembur Karyawan</h4>
                    <small class="text-muted">PT. Newwicker Indonesia</small>
                </div>

                {{-- Kanan: Filter & Export --}}
                <div class="col-sm-6">
                    {{-- Baris 1: Filter Bulanan & Harian --}}
                    <div class="d-flex justify-content-end mb-2">
                       <form id="filterForm"
      method="GET"
      action="{{ route('karyawan.lembur') }}"
      class="form-inline d-flex flex-wrap gap-2">
                            {{-- Pilih Bulan --}}
                            <select name="month" id="month" class="form-control form-control-sm">
                                @for ($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>
                                    {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                                    </option>
                                    @endfor
                            </select>

                            {{-- Pilih Tahun --}}
                            <select name="year" id="year" class="form-control form-control-sm">
                                @for ($y = now()->year; $y >= 2022; $y--)
                                <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>
                                    {{ $y }}
                                </option>
                                @endfor
                            </select>

                            {{-- Filter Harian --}}
                            <input type="date"
                                name="date"
                                id="date"
                                class="form-control form-control-sm"
                                value="{{ request('date', now()->format('Y-m-d')) }}">

                            {{-- Tombol --}}
                            <button type="submit" class="btn btn-sm btn-primary">Tampilkan</button>
                            <button type="button" id="btnBulanan" class="btn btn-sm btn-success">Tabel Bulanan</button>
                        </form>
                    </div>

<div id="exportWrapper" class="justify-content-end" style="display:none;">
                        <form id="exportForm" class="d-flex gap-2" method="GET" action="{{ route('absen.export') }}">
                            <input type="date"
                                name="start_date"
                                id="start_date"
                                class="form-control form-control-sm"
                                value="{{ request('start_date', now()->startOfMonth()->format('Y-m-d')) }}"
                                style="width:auto;">

                            <span class="mt-1">s/d</span>

                            <input type="date"
                                name="end_date"
                                id="end_date"
                                class="form-control form-control-sm"
                                value="{{ request('end_date', now()->endOfMonth()->format('Y-m-d')) }}"
                                style="width:auto;">

                            <button type="submit" id="btnExportExcel" class="btn btn-sm btn-warning">Export Excel</button>
                        </form>
                    </div>

                </div>






                <div class="col-12 mt-3">
               <div class="table-responsive">
    <table class="table table-bordered table-striped">
        <thead>
            <tr>
                <th>No</th>
                <th>Karyawan</th>
                <th>Tanggal</th>
                <th>Jam Masuk</th>
                <th>Jam Keluar</th>
                <th>Lokasi Masuk</th>
                <th>Lokasi Keluar</th>
                <th>Foto Keluar</th>
                <th>Keterangan</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($lemburs as $key => $lembur)
            <tr>
                <td>{{ $key + 1 }}</td>

                <td>
                    {{ $lembur->user->name ?? '-' }}
                </td>

                <td>
                    {{ \Carbon\Carbon::parse($lembur->tanggal)->format('d/m/Y') }}
                </td>

                <td>{{ $lembur->jam_masuk }}</td>

                <td>
                    {{ $lembur->jam_keluar ?? '-' }}
                </td>

                <td>
                    <small>
                        {{ $lembur->latitude }},
                        {{ $lembur->longitude }}
                    </small>
                </td>

                <td>
                    <small>
                        {{ $lembur->latitude_k }},
                        {{ $lembur->longitude_k }}
                    </small>
                </td>

                <td class="text-center">
                    @if($lembur->foto_keluar)
                    <a href="{{ asset('storage/'.$lembur->foto_keluar) }}" target="_blank">
                        <img src="{{ asset('storage/'.$lembur->foto_keluar) }}"
                             width="60"
                             class="img-thumbnail">
                    </a>
                    @endif
                </td>

                <td>{{ $lembur->keterangan }}</td>

                <td class="text-center">
                    @if($lembur->validate)
                    <span class="label success">
                        Valid
                    </span>
                    @else
                    <span class="label warning">
                        Pending
                    </span>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="10" class="text-center">
                    Tidak ada data lembur
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
                </div>
            </div>
        </div>
    </div>

{{-- JS --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

@endsection
