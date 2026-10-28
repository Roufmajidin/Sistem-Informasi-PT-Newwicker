@extends('master.master')
@section('title', 'Karyawan Absen')
@section('content')
    <style>
        .absen-page{padding:16px}
        .absen-card{background:#fff;border:1px solid #e6eaf0;border-radius:14px;box-shadow:0 4px 18px rgba(25,42,70,.05);overflow:hidden}
        .absen-heading{display:flex;align-items:center;justify-content:space-between;gap:16px;padding:20px 22px;border-bottom:1px solid #edf0f4}
        .absen-heading h4{margin:0 0 4px;font-size:24px;font-weight:600;color:#243447}
        .absen-heading p{margin:0;color:#778394;font-size:13px}
        .absen-heading-icon{display:inline-flex;align-items:center;justify-content:center;width:44px;height:44px;border-radius:12px;background:#edf5ff;color:#1674d1;font-size:21px;flex:0 0 auto}
        .absen-controls{padding:18px 22px;background:#fbfcfe}
        .absen-control-grid{display:grid;grid-template-columns:minmax(120px,1fr) minmax(95px,.7fr) minmax(170px,1.2fr) auto;align-items:end;gap:12px}
        .absen-field label{display:block;margin:0 0 6px;color:#5f6c7b;font-size:12px;font-weight:600}
        .absen-field .form-control{width:100%;height:38px;border-radius:8px;border-color:#dce3eb}
        .absen-actions{display:flex;flex-wrap:wrap;gap:8px;align-items:end}
        .absen-actions .btn{height:38px;border-radius:8px;padding:0 14px;font-weight:500}
        .absen-export{align-items:end;flex-wrap:wrap;gap:10px;margin-top:16px;padding-top:16px;border-top:1px solid #e8edf3}
        .absen-export-title{width:100%;font-size:13px;font-weight:600;color:#445266;margin-bottom:0}
        .absen-export .form-control{min-width:150px;height:38px;border-radius:8px;border-color:#dce3eb}
        .absen-export .btn{height:38px;border-radius:8px;padding:0 14px}
        .absen-table-area{padding:18px 22px 22px}
        .table-wrapper{overflow:auto;max-height:calc(100vh - 300px);border:1px solid #e7ebf0;border-radius:10px}
        .table-wrapper table{min-width:max-content;margin-bottom:0}
        .table-wrapper thead th:first-child{position:sticky;left:0;z-index:30;background:#243447;color:#fff}
        .table-wrapper tbody td:first-child{position:sticky;left:0;z-index:20;background:#fff;font-weight:600}
        #loadingBulanan{position:fixed;inset:0;width:100%;height:100%;background:rgba(255,255,255,.94);z-index:999999;display:none;justify-content:center;align-items:center}
        .loading-content{text-align:center}
        @media(max-width:991px){.absen-control-grid{grid-template-columns:repeat(3,minmax(0,1fr))}.absen-actions{grid-column:1/-1}}
        @media(max-width:575px){.absen-page{padding:8px}.absen-heading{padding:16px}.absen-heading h4{font-size:20px}.absen-controls,.absen-table-area{padding:14px}.absen-control-grid{grid-template-columns:1fr 1fr;gap:10px}.absen-field-date{grid-column:1/-1}.absen-actions{grid-column:1/-1;display:grid;grid-template-columns:1fr 1fr}.absen-actions .btn{width:100%;padding:0 8px}.absen-export{align-items:stretch}.absen-export .form-control{width:100%;min-width:0}.absen-export .btn{width:100%}.table-wrapper{max-height:calc(100vh - 250px)}}
    </style>

    <div class="absen-page">
        <div class="absen-card">
            <div class="absen-heading">
                <div>
                    <h4>Absen Karyawan</h4>
                    <p>PT. Newwicker Indonesia · Kelola dan lihat rekap kehadiran</p>
                </div>
                <div class="absen-heading-icon" aria-hidden="true"><i class="fa fa-calendar-check-o"></i></div>
            </div>

            <div class="absen-controls">
                <form id="filterForm">
                    <div class="absen-control-grid">
                        <div class="absen-field">
                            <label for="month">Bulan</label>
                            <select name="month" id="month" class="form-control">
                                @for ($m = 1; $m <= 12; $m++)
                                    <option value="{{ $m }}" {{ $m == $month ? 'selected' : '' }}>
                                        {{ \Carbon\Carbon::create()->month($m)->translatedFormat('F') }}
                                    </option>
                                @endfor
                            </select>
                        </div>
                        <div class="absen-field">
                            <label for="year">Tahun</label>
                            <select name="year" id="year" class="form-control">
                                @for ($y = now()->year; $y >= 2022; $y--)
                                    <option value="{{ $y }}" {{ $y == $year ? 'selected' : '' }}>{{ $y }}</option>
                                @endfor
                            </select>
                        </div>
                        <div class="absen-field absen-field-date">
                            <label for="date">Tanggal kehadiran</label>
                            <input type="date" name="date" id="date" class="form-control"
                                value="{{ request('date', now()->format('Y-m-d')) }}">
                        </div>
                        <div class="absen-actions">
                            <button type="submit" class="btn btn-primary"><i class="fa fa-filter mr-1"></i> Tampilkan</button>
                            <button type="button" id="btnBulanan" class="btn btn-success"><i class="fa fa-calendar mr-1"></i> Tabel Bulanan</button>
                        </div>
                    </div>
                </form>

                <div id="exportWrapper" class="absen-export" style="display:none;">
                    <div class="absen-export-title"><i class="fa fa-file-excel-o mr-1"></i> Export rekap berdasarkan rentang tanggal</div>
                    <form id="exportForm" class="d-flex flex-wrap align-items-end" style="gap:10px;" method="GET" action="{{ route('absen.export') }}">
                        <div class="absen-field">
                            <label for="start_date">Dari tanggal</label>
                            <input type="date" name="start_date" id="start_date" class="form-control"
                                value="{{ request('start_date', now()->startOfMonth()->format('Y-m-d')) }}">
                        </div>
                        <span class="mb-2">sampai</span>
                        <div class="absen-field">
                            <label for="end_date">Sampai tanggal</label>
                            <input type="date" name="end_date" id="end_date" class="form-control"
                                value="{{ request('end_date', now()->endOfMonth()->format('Y-m-d')) }}">
                        </div>
                        <button type="submit" id="btnExportExcel" class="btn btn-warning"><i class="fa fa-download mr-1"></i> Export Excel</button>
                    </form>
                </div>
            </div>

            <div class="absen-table-area">
                <div class="table-wrapper">
                    <div id="loadingBulanan" style="display:none;">
                        <div class="loading-content">
                            <div class="spinner-border text-success" style="width:60px;height:60px;"></div>
                            <h5 class="mt-4">Sedang mengambil data...</h5>
                            <small class="text-muted">Mohon tunggu sebentar</small>
                        </div>
                    </div>
                    <div id="tableBulanan" class="d-none"><!-- Rekap bulanan dimuat melalui AJAX --></div>
                    <div id="absenTable"><!-- Tabel harian dimuat melalui AJAX --></div>
                </div>
            </div>
        </div>

        {{-- Modal Update Absen --}}
        <div class="modal fade" id="absenModal" tabindex="-1" role="dialog" aria-labelledby="absenModalLabel"
            aria-hidden="true">
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
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('exportForm');
            const start = document.getElementById('start_date');
            const end = document.getElementById('end_date');
            if (form) {
                form.addEventListener('submit', function (event) {
                    if (start.value && end.value && start.value > end.value) {
                        event.preventDefault();
                        alert('Tanggal awal tidak boleh melewati tanggal akhir.');
                    }
                });
            }
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
                    $('#absenTable').toggleClass('d-none');

                } else {
                    $('#absenTable').toggleClass('d-none');

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

            $('#loadingBulanan')
                .css('display', 'flex')
                .hide()
                .fadeIn(150);

            fetch(`/absen/bulanan?month=${month}&year=${year}`)

                .then(res => res.json())

                .then(data => {

                    document.getElementById("tableBulanan").innerHTML = data.html;

                    registerClickHandlers();

                    $('#loadingBulanan').fadeOut(150);

                })

                .catch(err => {

                    console.error(err);

                    $('#loadingBulanan').fadeOut(150);

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

                // Perhatikan: dataset.fotoMasuk dan dataset.fotoKeluar
                const fotoMasuk = btn.dataset.fotoMasuk;
                const fotoKeluar = btn.dataset.fotoKeluar;

                const imgMasuk = document.getElementById("fotoMasuk");
                const imgKeluar = document.getElementById("fotoKeluar");

                if (imgMasuk) imgMasuk.src = fotoMasuk || "http://127.0.0.1:8000/assets/images/no-image.png";
                if (imgKeluar) imgKeluar.src = fotoKeluar || "http://127.0.0.1:8000/assets/images/no-image.png";

                console.log("Masuk:", fotoMasuk);
                console.log("Keluar:", fotoKeluar);

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

    <script>
        /*
         * Pencarian rekap bulanan.
         * Dipasang di document karena isi #tableBulanan dimuat melalui AJAX
         * menggunakan innerHTML, sehingga script dari partial tidak dijalankan.
         */
        (function () {
            if (window.__absenSearchHandlerRegistered) return;
            window.__absenSearchHandlerRegistered = true;

            function normalizeText(value) {
                return (value || '')
                    .toLocaleLowerCase('id-ID')
                    .normalize('NFD')
                    .replace(/[\\u0300-\\u036f]/g, '')
                    .trim();
            }

            document.addEventListener('input', function (event) {
                const input = event.target;

                if (!input || input.id !== 'searchKaryawan') return;

                // Cari panel tabel yang sama dengan kolom pencarian.
                const panel = input.closest('.absensi-panel');
                if (!panel) {
                    console.warn('Pencarian absensi: .absensi-panel tidak ditemukan.');
                    return;
                }

                // Jangan bergantung pada ID tbody, karena tabel dirender via AJAX.
                const rows = panel.querySelectorAll('.absensi-table tbody tr');
                const keyword = normalizeText(input.value);
                let visibleCount = 0;

                rows.forEach(function (row) {
                    // Kolom pertama adalah nomor, nama ada di td.absensi-name.
                    const nameCell = row.querySelector('td.absensi-name')
                        || (row.cells.length > 1 ? row.cells[1] : null);

                    const name = normalizeText(nameCell ? nameCell.textContent : '');
                    const matched = name.includes(keyword);

                    row.hidden = !matched;
                    row.style.display = matched ? '' : 'none';

                    if (matched) visibleCount++;
                });

                // Simpan jumlah hasil untuk debugging dan aksesibilitas bila diperlukan.
                panel.dataset.searchResultCount = String(visibleCount);
            });

            // Pastikan pencarian tetap bekerja jika fokus input dan browser mengirim keyup.
            document.addEventListener('keyup', function (event) {
                if (event.target && event.target.id === 'searchKaryawan') {
                    event.target.dispatchEvent(new Event('input', { bubbles: true }));
                }
            });
        })();
    </script>
@endsection
