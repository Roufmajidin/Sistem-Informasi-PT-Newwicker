@extends('master.master')
@section('title', "karyawan absen")
@section('content')
<div class="padding">
    <div class="box">
        <div class="p-a white lt box-shadow">
            <div class="row">
                <div class="col-sm-6">
                    <h4 class="mb-0 _300">List Absen karyawan</h4>
                    <small class="text-muted">PT. Newwicker Indonesia</small>
                </div>


                <!-- Kolom kanan: Form bulan/tahun -->
                <div class="col-sm-6 d-flex justify-content-end align-items-center  mb-4">
                    <form id="filterForm" class="form-inline">
                        <select name="month" id="month" class="form-control mr-2">

                        </select>

                        <select name="year" id="year" class="form-control mr-2">

                        </select>

                        <button type="submit" class="btn btn-sm btn-primary">Tampilkan</button>
                    </form>
                </div>



                <!-- Tabel absen -->
                <div class="col-12">
                    <div class="table-wrapper">
                        <table class="table table-bordered">
                            <thead style="color:white">
                                <tr class="sticky-header">
                                    <th>No.</th>
                                    <th class="sticky">Nama Lengkap</th>
                                    <th>Status</th>
                                    <th>Divisi kerja</th>

                                    <th>Tanggal</th>
                                    <th>Masuk</th>
                                    <th>Keluar</th>
                                    <th>lokasi Masuk</th>
                                    <th>lokasi Keluar</th>
                                    <th>Terlambat</th>
                                    <th>Foto</th>
                                    <th>#</th>

                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($karyawans as $index => $karyawan)
                                @php

                                @endphp
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    @php $kar = \App\Models\Karyawan::find($karyawan->karyawan_id); @endphp
                                    <td class="sticky">{{ $karyawan->name }}</td>
                                    <td>{{ $kar->status }}</td>
                                    <td>{{ $kar->divisi_id }}</td>
                                    @foreach ($karyawan->absens as $k )
                                    @php

                                    // Ubah jam masuk & keluar ke objek waktu
                                    $jamMasuk = \Carbon\Carbon::parse($k->jam_masuk);
                                    $jamKeluar = $k->jam_keluar ? \Carbon\Carbon::parse($k->jam_keluar) : null;

                                    // Batas waktu
                                    $batasMasuk = \Carbon\Carbon::createFromTime(8, 15); // batas toleransi
                                    $batasPulang = \Carbon\Carbon::createFromTime(17, 0);

                                    // Hitung menit keterlambatan
                                    $terlambat = $jamMasuk->gt($batasMasuk);
                                    $menitTerlambat = 0;
                                    if ($terlambat) {
                                    $detikTerlambat = $jamMasuk->diffInSeconds($batasMasuk);
                                    $menitTerlambat = number_format($detikTerlambat / 60, 1); // hasilkan 20.5 dst
                                    }
                                    $lewatPulang = $jamKeluar && $jamKeluar->gt($batasPulang);
                                    @endphp
                                    <td> {{$k->tanggal}}</td>
                                    <td> {{$k->jam_masuk}}</td>
                                    <td> {{$k->jam_keluar ?? "-"}}</td>
                                    <td>

                                        @if ($k->latitude && $k->longitude)
                                        <a href="https://www.google.com/maps?q={{ $k->latitude }},{{ $k->longitude }}" target="_blank">
                                            {{ $k->latitude }}, {{ $k->longitude }}
                                        </a>
                                        @else
                                        -
                                        @endif
                                    </td>
                                    <td> {{$k->latitude_k ?? "-"}}, {{$k->longitude_k ?? "-"}}</td>
                                    <td class="text-center">
                                        @if ($terlambat && $lewatPulang)
                                        {{ $menitTerlambat}} menit
                                        @elseif ($terlambat)
                                        Terlambat {{ $menitTerlambat }} menit
                                        @elseif ($lewatPulang)
                                        Lembur
                                        @else
                                        -
                                        @endif
                                    </td>
                                    <td>
                                        @if ($k->foto)
                                        <img src="{{ asset('storage/' . $k->foto) }}" alt="Foto" width="50" height="50">
                                        <img src="{{ asset('storage/' . $k->foto_keluar) }}" alt="Foto" width="50" height="50">
                                        @else
                                        <span>-</span>
                                        @endif
                                    </td>
                                    <td><i class="fa  fa-ellipsis-v"></i></td>
                                    @endforeach





                                </tr>
                                @endforeach
                            </tbody>
                        </table>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>




<!-- JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Ambil bulan & tahun sekarang, load data awal
        const today = new Date();
        const currentMonth = today.getMonth() + 1;
        const currentYear = today.getFullYear();
        loadAbsenData(currentMonth, currentYear);

        // Event filter form
        document.getElementById("filterForm").addEventListener("submit", function(e) {
            e.preventDefault();
            const month = document.getElementById("month").value;
            const year = document.getElementById("year").value;
            loadAbsenData(month, year);
        });

        // Sticky header scroll handler
        const tableWrapper = document.querySelector(".table-wrapper");
        const headerCells = document.querySelectorAll("thead th");
        tableWrapper.addEventListener("scroll", function() {
            if (tableWrapper.scrollTop > 0) {
                headerCells.forEach(th => th.classList.add("scrolled"));
            } else {
                headerCells.forEach(th => th.classList.remove("scrolled"));
            }
        });
    });
</script>


@endsection
