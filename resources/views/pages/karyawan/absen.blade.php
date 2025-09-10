@extends('master.master')
@section('title', "Karyawan Absen")
@section('content')
<div class="padding">
    <div class="box">
        <div class="p-a white lt box-shadow">
            <div class="row">
                <div class="col-sm-6">
                    <h4 class="mb-0 _300">Absen Karyawan</h4>
                    <small class="text-muted">PT. Newwicker Indonesia</small>
                </div>

                <div class="col-sm-6 d-flex justify-content-end align-items-center mb-4">
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

                        {{-- Tambahan input tanggal --}}
                        <input type="date"
                            name="date"
                            id="date"
                            class="form-control mr-2"
                            value="{{ request('date', now()->format('Y-m-d')) }}">

                        <button type="submit" class="btn btn-sm btn-primary">Tampilkan</button>
                    </form>
                </div>
                <div class="col-12">
                    <div class="table-wrapper">
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
@endsection
