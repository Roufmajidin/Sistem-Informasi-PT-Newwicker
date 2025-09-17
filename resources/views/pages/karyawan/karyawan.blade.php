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

                       <button id="btnAddRow" class="btn btn-sm btn-primary">
    <i class="fa fa-plus"></i> Add
</button>
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
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $no = 1;
                        @endphp
                        @foreach ($karyawan as $i)
                        @php
                        $m = App\Models\Divisi::find($i->divisi_id);
                        @endphp
                        <tr style="font-size: 10px;">
                            <td>{{ $no++ }}</td>
                            <td>{{ $i->id }}</td>
                            <td class="sticky">{{ $i->nama_lengkap }}</td>
                            <td>{{ $i->nik }}</td>
                            <td>{{ $i->jenis_kelamin }}</td>
                            <td>{{ $i->tempat }}, {{ $i->tanggal_lahir }}</td>
                            <td>{{ $i->alamat }}</td>
                            <td>{{ $i->status_perkawinan }}</td>
                            <td>{{ $m->nama }}</td>
                            <td>{{ $i->status }}</td>
                            <td>{{ $i->lokasi }}</td>
                            <td>{{ $i->tanggal_join }}</td>
                            <td>
                                <button><i class="fa fa-lock"></i></button>
                            </td>
                        </tr>
                        @endforeach
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



<!-- JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- add u -->
 <script>
$(document).ready(function() {
    $("#btnAddRow").on("click", function() {
        const newRow = `
            <tr style="font-size: 10px;">
                <td>New</td>
                <td><input type="file" name="photo[]" class="form-control form-control-sm"></td>
                <td class="sticky"><input type="text" name="nama_lengkap[]" class="form-control form-control-sm"></td>
                <td><input type="text" name="nik[]" class="form-control form-control-sm"></td>
                <td>
                    <select name="jenis_kelamin[]" class="form-control form-control-sm">
                        <option value="">-</option>
                        <option value="L">L</option>
                        <option value="P">P</option>
                    </select>
                </td>
                <td><input type="text" name="ttl[]" class="form-control form-control-sm"></td>
                <td><input type="text" name="alamat[]" class="form-control form-control-sm"></td>
                <td><input type="text" name="status_perkawinan[]" class="form-control form-control-sm"></td>
                <td><input type="text" name="divisi_id[]" class="form-control form-control-sm"></td>
                <td><input type="text" name="status[]" class="form-control form-control-sm"></td>
                <td><input type="text" name="lokasi[]" class="form-control form-control-sm"></td>
                <td><input type="date" name="tanggal_join[]" class="form-control form-control-sm"></td>
                <td>
                    <button class="btn btn-sm btn-danger btnRemoveRow">
                        <i class="fa fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
        $("table.table-bordered tbody").prepend(newRow);
    });

    // remove row jika tombol trash diklik
    $(document).on("click", ".btnRemoveRow", function() {
        $(this).closest("tr").remove();
    });
});
</script>

<script>
    let highlightedNiks = [];

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
                            <th>user id</th>
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
                        <td>${row.user_id ?? ''}</td>
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
    $('#btnBulkSave').on('click', function () {
    if (!globalRows.length) {
        alert("Tidak ada data untuk disimpan.");
        return;
    }

    $.ajax({
        url: '{{ route("karyawan.bulk_save") }}',
        method: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            rows: globalRows // kirim semua, backend akan filter dan update
        },
        success: function (res) {
            if (res.success) {
                alert(`${res.inserted} data ditambahkan, ${res.updated} data diperbarui.`);
                $('#importResultModal').modal('hide');
                location.reload();
            } else {
                alert('Gagal simpan: ' + (res.message || ''));
            }
        },
        error: function (xhr) {
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
