@extends('master.master')
@section('title', "Karyawan Absen")
@section('content')
<div class="padding">
    <div class="box">
        <div class="p-a white lt box-shadow">
            <div class="row align-items-center">
                {{-- Kiri: Judul --}}
                <div class="col-sm-6">
                    <h4 class="mb-0 _300">Absen Karyawan</h4>
                    <small class="text-muted">PT. Newwicker Indonesia</small>
                </div>

                {{-- Kanan: Filter & Export --}}
                <div class="col-sm-6">
                    {{-- Baris 1: Filter Bulanan & Harian --}}
                    <div class="d-flex justify-content-end mb-2">
                        <form id="filterForm" class="form-inline d-flex flex-wrap gap-2">
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
                    <div class="table-wrapper">
                        <div id="tableBulanan" class="d-none">
                            {{-- Tabel bulanan akan di-load via AJAX --}}
                        </div>
                        <div id="absenTable">
                            {{-- Tabel akan di-load via AJAX --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Update Absen --}}
    <div class="modal fade" id="absenModal" tabindex="-1" role="dialog" aria-labelledby="absenModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <form id="absenForm" method="POST" action="{{ route('absen.update') }}">
                @csrf
                <input type="hidden" name="user_id" id="modalUserId">
                <input type="hidden" name="tanggal" id="modalTanggal">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Update Kehadiran</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
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
</div>

{{-- JS --}}
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById('exportForm').addEventListener('submit', function(e) {
        const start = document.getElementById('start_date');
        const end = document.getElementById('end_date');

        // Convert d-m-Y ke yyyy-mm-dd jika perlu
        start.value = start.value.split('-').reverse().join('-');
        end.value = end.value.split('-').reverse().join('-');
    });
    document.addEventListener("DOMContentLoaded", function() {
        flatpickr("#start_date", {
            dateFormat: "d-m-Y", // tampil & kirim dd-mm-yyyy
            allowInput: true
        });

        flatpickr("#end_date", {
            dateFormat: "d-m-Y",
            allowInput: true
        });
    });
</script>
<script>
    $(function() {
        $('#btnBulanan').on('click', function() {
             if (exportWrapper.style.display === 'none') {
        exportWrapper.style.display = 'flex'; // sekarang muncul dengan flex
    } else {
        exportWrapper.style.display = 'none'; // sembunyi
    }
            $('#tableBulanan').toggleClass('d-none');


            if (!$('#tableBulanan').hasClass('d-none')) {
                $(this).text('Kembali ke Harian');

                const month = document.getElementById("month").value;
                const year = document.getElementById("year").value;

                 loadAbsenBulanan(month, year); // Panggil fungsi AJAX bulanan
            } else {
            $('#tableHarian').toggleClass('d-none');

                $(this).text('Tabel Bulanan');

                const today = new Date();
                const todayStr = today.toISOString().split('T')[0];
                loadAbsenData(today.getMonth() + 1, today.getFullYear(), todayStr);
            }
        });
    });
</script>
<script>
    function registerClickHandlers() {
        document.querySelectorAll(".absen-td").forEach(cell => {
            cell.addEventListener("click", function() {
                document.getElementById("modalUserId").value = this.dataset.user;
                document.getElementById("modalTanggal").value = this.dataset.date;
                $('#absenModal').modal('show');
            });
        });
    }

    function loadAbsenData(month, year, date) {
        fetch(`/absen/filter?month=${month}&year=${year}&date=${date}`)
            .then(res => res.json())
            .then(data => {
                document.getElementById("absenTable").innerHTML = data.html;
                registerClickHandlers();
            })
            .catch(err => {
                console.error("Gagal memuat data:", err);
            });
    }

    document.addEventListener("DOMContentLoaded", function() {
        const today = new Date();
        const todayStr = today.toISOString().split('T')[0];

        // default load
        loadAbsenData(today.getMonth() + 1, today.getFullYear(), todayStr);

        document.getElementById("filterForm").addEventListener("submit", function(e) {
            e.preventDefault();
            const month = document.getElementById("month").value;
            const year = document.getElementById("year").value;
            const date = document.getElementById("date").value;
            loadAbsenData(month, year, date);
        });
    });

    function loadAbsenBulanan(month, year) {
        fetch(`/absen/bulanan?month=${month}&year=${year}`)
            .then(res => res.json())
            .then(data => {
                document.getElementById("tableBulanan").innerHTML = data.html;
                registerClickHandlers();
            })
            .catch(err => {
                console.error("Gagal memuat tabel bulanan:", err);
            });
    }
</script>

<script>
    function registerClickHandlers() {
        document.querySelectorAll(".absen-td").forEach(cell => {
            cell.addEventListener("click", function() {
                document.getElementById("modalUserId").value = this.dataset.user;
                document.getElementById("modalTanggal").value = this.dataset.date;
                $('#absenModal').modal('show');
            });
        });
    }

    function loadAbsenData(month, year, date) {
        fetch(`/absen/filter?month=${month}&year=${year}&date=${date}`)
            .then(res => res.json())
            .then(data => {
                document.getElementById("absenTable").innerHTML = data.html;
                registerClickHandlers();
            })
            .catch(err => {
                console.error("Gagal memuat data:", err);
            });
    }

    document.addEventListener("DOMContentLoaded", function() {
        const today = new Date();
        const todayStr = today.toISOString().split('T')[0];

        // default load
        loadAbsenData(today.getMonth() + 1, today.getFullYear(), todayStr);

        document.getElementById("filterForm").addEventListener("submit", function(e) {
            e.preventDefault();
            const month = document.getElementById("month").value;
            const year = document.getElementById("year").value;
            const date = document.getElementById("date").value;
            loadAbsenData(month, year, date);
        });
    });
</script>

<script>
    document.getElementById('month').addEventListener('change', setDateLimit);
    document.getElementById('year').addEventListener('change', setDateLimit);

    function setDateLimit() {
        const month = document.getElementById('month').value;
        const year = document.getElementById('year').value;
        const dateInput = document.getElementById('date');

        if (month && year) {
            let firstDay = new Date(year, month - 1, 1);
            let lastDay = new Date(year, month, 0);

            dateInput.min = firstDay.toISOString().split('T')[0];
            dateInput.max = lastDay.toISOString().split('T')[0];
        }
    }

    // jalankan saat pertama kali load
    setDateLimit();
</script>
<script>
    if (!document.getElementById('date').value) {
        let today = new Date().toISOString().split('T')[0];
        document.getElementById('date').value = today;
    }
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
<script>
function copyTextToClipboard(el) {
    const text = el.getAttribute("data-full");
    const jarak = el.getAttribute("data-jarak");
    const finalText = jarak ? `${text} (±${jarak} m)` : text;

    navigator.clipboard.writeText(finalText).then(() => {
        alert(`Tersalin: ${finalText}`);
    });
}
</script>


@endsection
