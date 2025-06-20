@extends('master.master')
@section('title', "karyawan list")
@section('content')
<div class="padding">
    <div class="box">
        <div class="p-a white lt box-shadow">
            <div class="row">
                <div class="col-sm-6">
                    <h4 class="mb-0 _300">List Karyawan</h4>
                    <small class="text-muted">PT. Newwicker Indonesia</small>
                </div>

                <div class="col-sm-6 text-sm-right">
                    <div class="m-y-sm">

                        <!-- Form Import Excel -->
                        <form id="importForm" enctype="multipart/form-data" onsubmit="return false;">
                            @csrf
                            <label for="fileUpload" class="btn btn-xs white">Bulk Data</label>
                            <input type="file" id="fileUpload" name="file" style="display: none;">
                        </form>

                        <span class="m-r-sm">Short:</span>
                        <div class="btn-group dropdown">
                            <button class="btn white btn-sm">Filter</button>
                            <button class="btn white btn-sm dropdown-toggle" data-toggle="dropdown"></button>
                            <div class="dropdown-menu dropdown-menu-scale pull-right">
                                <a class="dropdown-item" href="#">Bulanan</a>
                                <a class="dropdown-item" href="#">Mingguan</a>
                                <a class="dropdown-item" href="#">Borongan</a>
                                <div class="dropdown-divider"></div>
                            </div>
                        </div>
                    </div>
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
                            <th>Photo</th>
                            <th class="sticky">Nama Lengkap</th>
                            <th>NIK</th>
                            <th>L/P</th>
                            <th>Tempat, Tanggal Lahir</th>
                            <th>Alamat</th>
                            <th>Kawin/Lainnya</th>
                            <th>Divisi</th>
                            <th>Status</th>
                            <th>Lokasi</th>
                            <th>Tanggal Join</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $no = 1; @endphp
                        @foreach ($karyawan as $i)
                        <tr style="font-size: 10px;">
                            <td>{{ $no++ }}</td>
                            <td>{{ $i->id }}</td>
                            <td class="sticky">{{ $i->nama_lengkap }}</td>
                            <td>{{ $i->nik }}</td>
                            <td>{{ $i->jenis_kelamin }}</td>
                            <td>{{ $i->tempat }}, {{ $i->tanggal_lahir }}</td>
                            <td>{{ $i->alamat }}</td>
                            <td>{{ $i->status_perkawinan }}</td>
                            <td>{{ $i->divisi_id }}</td>
                            <td>{{ $i->status }}</td>
                            <td>{{ $i->lokasi }}</td>
                            <td>{{ $i->tanggal_join }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
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
            <div class="text-right mt-3">
                <button class="btn btn-sm btn-success" id="btnBulkSave">Simpan Data Baru</button>
            </div>
        </div>
    </div>
</div>

<!-- JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    let globalRows = [];
    let globalExistingNames = [];
    $(document).ready(function() {

        $('#fileUpload').on('change', function() {
            const file = this.files[0];
            if (!file) return;

            const formData = new FormData($('#importForm')[0]);

            $.ajax({
                url: `{{ route("karyawan.import") }}`,
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    if (res.success) {
                        renderTable(res.rows); // render hasil
                        $('#importResultModal').modal('show'); // tampilkan modal
                    } else {
                        alert('Gagal import: ' + (res.message || 'Format tidak valid'));
                    }
                },
                error: function(xhr) {
                    alert('Upload gagal. Status: ' + xhr.status);
                    console.error(xhr.responseText);
                }
            });
        });


        function renderTable(rows) {
            if (!Array.isArray(rows)) {
                console.error('Data rows bukan array:', rows);
                return;
            }

            const names = rows.map(r => r.nama_lengkap).filter(n => n);

            $.ajax({
                url: '{{ route("karyawan.check_existing_names") }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    names: names
                },
                success: function(existingNames) {
                    globalRows = rows;
                    globalExistingNames = existingNames;
                    let html = `
                    <div class="table-wrapper">
                        <table class="table table-bordered table-striped small-font-table">
                        <thead>
                            <tr class="sticky-header">
                            <th>No.</th>
                            <th class="sticky">Nama Lengkap</th>
                            <th>NIK</th>
                            <th>L/P</th>
                            <th>Tempat, Tanggal Lahir</th>
                            <th>Alamat</th>
                            <th>Kawin/Lainnya</th>
                            <th>Divisi</th>
                            <th>Status</th>
                            <th>Lokasi</th>
                            <th>Tanggal Join</th>
                            </tr>
                        </thead>
                        <tbody>`;

                    let nomor = 1;
                    rows.forEach((row) => {
                        if (!row.nama_lengkap && !row.nik && !row.jenis_kelamin) return;

                        const isDuplicate = existingNames.some(dbName =>
                            dbName.toLowerCase().includes((row.nama_lengkap ?? '').toLowerCase())
                        );
                        html += `<tr>
                        <td>${nomor++}</td>
                        <td class="sticky ${isDuplicate ? 'bg-danger text-white' : ''}">${row.nama_lengkap ?? ''}</td>
                        <td>${row.nik ?? ''}</td>
                        <td>${row.jenis_kelamin ?? ''}</td>
                        <td>${row.tempat_tanggal_lahir ?? ''}</td>
                        <td>${row.alamat ?? ''}</td>
                        <td>${row.status_perkawinan ?? ''}</td>
                        <td>${row.divisi ?? ''}</td>
                        <td>${row.status_karyawan ?? ''}</td>
                        <td>${row.lokasi ?? ''}</td>
                        <td>${row.tanggal_join ?? ''}</td>
                        </tr>`;
                    });

                    html += '</tbody></table></div>';

                    $('#resultTableBody').html(html);
                },
                error: function(xhr) {
                    alert('Gagal cek nama duplikat. Status: ' + xhr.status);
                }
            });
        }

    })
    $('#btnBulkSave').on('click', function() {
        if (!globalRows.length) return;

        const filtered = globalRows.filter(row => {
            if (!row.nama_lengkap) return false;

            return !globalExistingNames.some(dbName =>
                dbName.toLowerCase().includes(row.nama_lengkap.toLowerCase())
            );
        });

        if (filtered.length === 0) {
            alert("Semua data sudah ada, tidak ada yang disimpan.");
            return;
        }

        $.ajax({
            url: '{{ route("karyawan.bulk_save") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                rows: filtered
            },
            success: function(res) {
                if (res.success) {
                    alert('Data berhasil disimpan!');
                    $('#importResultModal').modal('hide');
                    location.reload();
                } else {
                    alert('Gagal simpan: ' + (res.message || ''));
                }
            },
            error: function(xhr) {
                alert('Gagal simpan. Status: ' + xhr.status);
                console.error(xhr.responseText);
            }
        });
    });
</script>


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

@endsection
