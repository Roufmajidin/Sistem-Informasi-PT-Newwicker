@extends('master.master')
@section('title', "izin list")
@section('content')
<div class="padding">
    <div class="box">
        <div class="p-a white lt box-shadow">
            <div class="row">
                <div class="col-sm-6">
                    <h4 class="mb-0 _300">List Izin Karyawan</h4>
                    <small class="text-muted">PT. Newwicker Indonesia</small>
                </div>


            </div>
        </div>

        <!-- Tabel Karyawan -->
        <div class="col-12">
            <div class="table-wrapper">
                <table class="table table-bordered">
                    <thead style="color:white">
                        <tr class="sticky-header" style="font-size: 12px;">
                            <th>No.</th>
                            <th class="sticky">Nama Lengkap</th>
                            <th>Date</th>
                            <th>keterangan</th>
                            <th>bukti/</th>
                            <th>Validasi</th>

                        </tr>
                    </thead>
       <tbody>
    @php $no = 1; @endphp
    @forelse($karyawans as $karyawan)
        <tr>
            <td>{{ $no++ }}</td>
            <td>{{ $karyawan->name }}</td>
            <td>
                @foreach($karyawan->absens as $absen)
                    {{ \Carbon\Carbon::parse($absen->tanggal)->format('d-m-Y') }} <br>
                @endforeach
            </td>
            <td>
                @foreach($karyawan->absens as $absen)
                    {{ $absen->keterangan }} <br>
                @endforeach
            </td>
            <td>
                @foreach($karyawan->absens as $absen)
                    @if($absen->foto)
                        <button type="button"
                                class="btn btn-sm btn-outline-primary view-photo"
                                data-foto="{{ asset('storage/' . $absen->foto) }}">
                            <i class="fa fa-eye"></i>
                        </button>
                        <br>
                    @else
                        -
                        <br>
                    @endif
                @endforeach
            </td>
            <td>
                  <div class="col-sm-6 text-right">
                <label id="scan-again-btn" class="btn btn-sm btn-primary">validate?</label>
            </div>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="5" class="text-center">Tidak ada karyawan izin bulan ini</td>
        </tr>
    @endforelse
</tbody>

                </table>
            </div>
        </div>
    </div>
    <!-- Modal hasil import -->
    <div class="modal fade" id="importResultModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Hasil Import</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="resultTableBody">

                </div>
                <div class="text-right mb-2 mr-2">
                    <button class="btn btn-sm btn-success" id="btnBulkSave">Simpan Data Baru</button>
                </div>
            </div>
        </div>
    </div>
</div>


<!-- Modal HTML di bawah table -->
<div class="modal fade" id="fotoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Foto Absen</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body d-flex justify-content-center">
                <img id="fotoPreview" src="" alt="Foto Absen" class="img-fluid" style="max-height:70vh; border-radius:5px; border:1px solid #ddd;">
            </div>
        </div>
    </div>
</div>
<!-- JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>


<script>
    document.addEventListener("DOMContentLoaded", function() {
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
<script>
document.addEventListener("click", function(e) {
    if (e.target.closest(".view-photo")) {
        const btn = e.target.closest(".view-photo");
        const fotoUrl = btn.dataset.foto;

        console.log("Clicked:", fotoUrl); // debug

        document.getElementById("fotoPreview").src = fotoUrl;

        const modal = new bootstrap.Modal(document.getElementById("fotoModal"));
        modal.show();
    }
});
</script>

@endsection
