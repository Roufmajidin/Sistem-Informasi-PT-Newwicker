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
                        <button id="btnSaveData" class="btn btn-sm btn-success">
                            <i class="fa fa-save"></i> Save Data Baru
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabel Karyawan -->
        <div class="col-12">
            <div class="table-wrapper">
                <table class="table table-bordered">
                    <thead style="color:white; background:#2d3e50;">
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
                        @php $no = 1; @endphp
                        @foreach ($karyawan as $i)
                        @php
                        $m = App\Models\Divisi::find($i->divisi_id);
                        @endphp
                        <tr style="font-size: 10px;">
                            <!-- No (tidak editable) -->
                            <td>{{ $no++ }}</td>

                            <!-- ID (tidak perlu editable biasanya) -->
                            <td>{{ $i->id }}</td>

                            <!-- Nama Lengkap -->
                            <td class="sticky">
                                <a href="#" class="editable"
                                    data-name="nama_lengkap"
                                    data-type="text"
                                    data-pk="{{ $i->id }}"
                                    data-url="{{ route('karyawan.updateInline') }}"
                                    data-title="Edit Nama">
                                    {{ $i->nama_lengkap }}
                                </a>
                            </td>

                            <!-- NIK -->
                            <td>
                                <a href="#" class="editable"
                                    data-name="nik"
                                    data-type="text"
                                    data-pk="{{ $i->id }}"
                                    data-url="{{ route('karyawan.updateInline') }}"
                                    data-title="Edit NIK">
                                    {{ $i->nik }}
                                </a>
                            </td>

                            <!-- Jenis Kelamin -->
                            <td>
                                <a href="#" class="editable"
                                    data-name="jenis_kelamin"
                                    data-type="select"
                                    data-pk="{{ $i->id }}"
                                    data-url="{{ route('karyawan.updateInline') }}"
                                    data-title="Pilih Jenis Kelamin"
                                    data-value="{{ $i->jenis_kelamin }}"
                                    data-source='[{"value":"L","text":"Laki-laki"},{"value":"P","text":"Perempuan"}]'>
                                    {{ $i->jenis_kelamin }}
                                </a>
                            </td>

                            <!-- Tempat Lahir -->
                            <td>
                                <a href="#" class="editable"
                                    data-name="tempat"
                                    data-type="text"
                                    data-pk="{{ $i->id }}"
                                    data-url="{{ route('karyawan.updateInline') }}"
                                    data-title="Edit Tempat Lahir">
                                    {{ $i->tempat }}
                                </a>,
                                <a href="#" class="editable"
                                    data-name="tanggal_lahir"
                                    data-type="date"
                                    data-pk="{{ $i->id }}"
                                    data-url="{{ route('karyawan.updateInline') }}"
                                    data-title="Edit Tanggal Lahir">
                                    {{ $i->tanggal_lahir }}
                                </a>
                            </td>

                            <!-- Alamat -->
                            <td>
                                <a href="#" class="editable"
                                    data-name="alamat"
                                    data-type="text"
                                    data-pk="{{ $i->id }}"
                                    data-url="{{ route('karyawan.updateInline') }}"
                                    data-title="Edit Alamat">
                                    {{ $i->alamat }}
                                </a>
                            </td>

                            <!-- Status Perkawinan -->
                            <td>
                                <a href="#" class="editable"
                                    data-name="status_perkawinan"
                                    data-type="select"
                                    data-pk="{{ $i->id }}"
                                    data-url="{{ route('karyawan.updateInline') }}"
                                    data-title="Pilih Status Kawin"
                                    data-value="{{ $i->status_perkawinan }}"
                                    data-source='[{"value":"Kawin","text":"Kawin"},{"value":"Belum Kawin","text":"Belum Kawin"},{"value":"Cerai","text":"Cerai"}]'>
                                    {{ $i->status_perkawinan }}
                                </a>
                            </td>

                            <!-- Divisi -->
                            <td>
                                <a href="#" class="editable"
                                    data-name="divisi_id"
                                    data-type="select"
                                    data-pk="{{ $i->id }}"
                                    data-url="{{ route('karyawan.updateInline') }}"
                                    data-title="Pilih Divisi"
                                    data-value="{{ $i->divisi_id }}"
                                    data-source='@json(App\Models\Divisi::select("id as value","nama as text")->get())'>
                                    {{ $m->nama }}
                                </a>
                            </td>

                            <!-- Status -->
                            <td>
                                <a href="#" class="editable"
                                    data-name="status"
                                    data-type="select"
                                    data-pk="{{ $i->id }}"
                                    data-url="{{ route('karyawan.updateInline') }}"
                                    data-title="Pilih Status"
                                    data-value="{{ $i->status }}"
                                    data-source='[{"value":"Tetap","text":"Tetap"},{"value":"Kontrak","text":"Kontrak"}]'>
                                    {{ $i->status }}
                                </a>
                            </td>

                            <!-- Lokasi -->
                            <td>
                                <a href="#" class="editable"
                                    data-name="lokasi"
                                    data-type="text"
                                    data-pk="{{ $i->id }}"
                                    data-url="{{ route('karyawan.updateInline') }}"
                                    data-title="Edit Lokasi">
                                    {{ $i->lokasi }}
                                </a>
                            </td>

                            <!-- Tanggal Join -->
                            <td>
                                <a href="#" class="editable"
                                    data-name="tanggal_join"
                                    data-type="date"
                                    data-pk="{{ $i->id }}"
                                    data-url="{{ route('karyawan.updateInline') }}"
                                    data-title="Edit Tanggal Join">
                                    {{ $i->tanggal_join }}
                                </a>
                            </td>

                            <!-- Aksi -->
                            <td>
                                <button class="btn btn-xs btn-secondary"><i class="fa fa-lock"></i></button>
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
@endsection



<!-- JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- add u -->
<!-- X-editable -->
<!-- <link href="https://cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.1/bootstrap4-editable/css/bootstrap-editable.css" rel="stylesheet" /> -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.1/bootstrap4-editable/js/bootstrap-editable.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<!-- save data new karyawansss -->
<script>
    $(document).ready(function() {
        $("#btnSaveData").on("click", function() {
            let formData = new FormData();

            // ambil semua field dari row baru
            $("table.table-bordered tbody tr").each(function(index, row) {
                if ($(row).find("td:first").text() === "New") {
                  formData.append("nama_lengkap[]", $(row).find("input[name='nama_lengkap[]']").val());
formData.append("nik[]", $(row).find("input[name='nik[]']").val());
formData.append("jenis_kelamin[]", $(row).find("select[name='jenis_kelamin[]']").val());
formData.append("ttl[]", $(row).find("input[name='ttl[]']").val());
formData.append("alamat[]", $(row).find("input[name='alamat[]']").val());
formData.append("status_perkawinan[]", $(row).find("select[name='status_perkawinan[]']").val());
formData.append("divisi_id[]", $(row).find("select[name='divisi_id[]']").val());
formData.append("status[]", $(row).find("select[name='status[]']").val());
formData.append("lokasi[]", $(row).find("input[name='lokasi[]']").val());
formData.append("tanggal_join[]", $(row).find("input[name='tanggal_join[]']").val());
                    let photo = $(row).find("input[type='file']")[0].files[0];
                    if (photo) {
                        formData.append("photo[]", photo);
                    }
console.log("Divisi:", $(row).find("select[name='divisi_id[]']").val());

                }
            });

            // kirim via ajax
            $.ajax({
                url: "{{ route('karyawan.store') }}",
                type: "POST",
                data: formData,
                contentType: false,
                processData: false,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                success: function(res) {
                    if (res.status === 'success') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: res.message,
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload(); // refresh tabel
                        });
                    }
                },
                error: function(err) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops!',
                        text: 'Gagal menyimpan data baru'
                    });
                }
            });
        });
    });
</script>
<script>
    $(document).ready(function() {
        // Default mode inline
        $.fn.editable.defaults.mode = 'inline';
        $.fn.editable.defaults.ajaxOptions = {
            type: "POST"
        };

        // CSRF untuk Laravel
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });

        // Aktifkan semua editable
        $('.editable').editable();
    });
</script>
<!-- post edit karyawab -->
<script>
    $(document).ready(function() {
        $.fn.editable.defaults.mode = 'inline';
        $.fn.editable.defaults.ajaxOptions = {
            type: "POST"
        };

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });

        $('.editable').editable({
            success: function(response, newValue) {
                if (response.status === 'success') {
                    // pake alert biasa
                    // alert(response.message);

                    // atau pake SweetAlert biar cakep
                    Swal.fire({
                        icon: 'success',
                        title: 'Berhasil',
                        text: response.message,
                        timer: 1500,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text: response.message
                    });
                }
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops!',
                    text: 'Terjadi kesalahan saat menyimpan'
                });
            }
        });
    });
</script>
<!-- jumping -->
<script>
    // Definisi opsi divisi (Blade akan render di server-side)
    const divisiOptions = `
        <option value="">-- Pilih Divisi --</option>
        @foreach(App\Models\Divisi::all() as $div)
            <option value="{{ $div->id }}">{{ $div->nama }}</option>
        @endforeach
    `;
</script>
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
    <td>
        <select name="status_perkawinan[]" class="form-control form-control-sm">
            <option value="">-</option>
            <option value="Kawin">Kawin</option>
            <option value="Belum Kawin">Belum Kawin</option>
            <option value="Cerai">Cerai</option>
        </select>
    </td>
    <td>
        <select name="divisi_id[]" class="form-control form-control-sm">
            ${divisiOptions}
        </select>
    </td>
    <td>
        <select name="status[]" class="form-control form-control-sm">
            <option value="">-- Pilih Status --</option>
            <option value="Tetap">Tetap</option>
            <option value="Kontrak">Kontrak</option>
        </select>
    </td>
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

        // hapus row
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
    $('#btnBulkSave').on('click', function() {
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
            success: function(res) {
                if (res.success) {
                    alert(`${res.inserted} data ditambahkan, ${res.updated} data diperbarui.`);
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
