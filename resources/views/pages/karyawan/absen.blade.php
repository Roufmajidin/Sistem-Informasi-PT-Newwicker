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
                            @for ($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>
                                {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                                </option>
                                @endfor
                        </select>

                        <select name="year" id="year" class="form-control mr-2">
                            @for ($y = now()->year; $y >= 2022; $y--)
                            <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>

                        <button type="submit" class="btn btn-sm btn-primary">Tampilkan</button>
                    </form>
                </div>



                <!-- Tabel absen -->
                <div class="col-12">
                    <div class="table-wrapper">
                        <div id="absenTable">
                        </div>


                    </div>
                </div>
            </div>
            <!-- modal absen -->
            <!-- Modal Form -->
            <div class="modal fade" id="absenModal" tabindex="-1" role="dialog" aria-labelledby="absenModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <form id="absenForm" method="POST" action="{{ route('absen.update') }}">
                        @csrf
                        <input type="hidden" name="user_id" id="modalUserId">
                        <input type="hidden" name="tanggal" id="modalTanggal">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="absenModalLabel">Update Kehadiran</h5>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">
                                <label for="status">Pilih Status Kehadiran:</label>
                                <select name="status" id="status" class="form-control" required>
                                    <option value="izin">Izin</option>
                                    <option value="cuti">Cuti</option>
                                    <option value="sakit">Sakit</option>
                                </select>
                            </div>
                            <div class="modal-footer">
                                <button type="submit" class="btn btn-primary">Simpan</button>
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                            </div>
                        </div>
                    </form>
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



        <!-- JS -->
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
      <script>
    function registerClickHandlers() {
        document.querySelectorAll(".absen-td").forEach(cell => {
            cell.addEventListener("click", function () {
                const userId = this.dataset.user;
                const tanggal = this.dataset.date;

                document.getElementById("modalUserId").value = userId;
                document.getElementById("modalTanggal").value = tanggal;
                $('#absenModal').modal('show');
            });
        });
    }

    function loadAbsenData(month, year) {
        fetch(`/absen/filter?month=${month}&year=${year}`)
            .then(res => res.json())
            .then(data => {
                document.getElementById("absenTable").innerHTML = data.html;
                registerClickHandlers(); // wajib panggil ulang handler
            })
            .catch(err => {
                console.error("Gagal memuat data:", err);
                alert("Terjadi kesalahan saat memuat data.");
            });
    }

    document.addEventListener("DOMContentLoaded", function () {
        // Ambil bulan & tahun sekarang, load data awal
        const today = new Date();
        const currentMonth = today.getMonth() + 1;
        const currentYear = today.getFullYear();
        loadAbsenData(currentMonth, currentYear);

        // Event filter form
        document.getElementById("filterForm").addEventListener("submit", function (e) {
            e.preventDefault();
            const month = document.getElementById("month").value;
            const year = document.getElementById("year").value;
            loadAbsenData(month, year);
        });

        // Sticky header scroll handler
        const tableWrapper = document.querySelector(".table-wrapper");
        const headerCells = document.querySelectorAll("thead th");
        tableWrapper.addEventListener("scroll", function () {
            if (tableWrapper.scrollTop > 0) {
                headerCells.forEach(th => th.classList.add("scrolled"));
            } else {
                headerCells.forEach(th => th.classList.remove("scrolled"));
            }
        });
    });
</script>


        @endsection
