@extends('master.master')
@section('title', "Inventory Assets")
@section('content')
<div class="padding">
    <div class="box">
        <div class="p-a white lt box-shadow">
            <div class="row">
                <div class="col-sm-6">
                    <!-- <h6 class="mb-0 _300">List Inventory</h6> -->
                    <smal class="text-muted">List Inventory</small>
                        <br>
                        <small class="text-danger">Double &lt;klik>, untuk mengupdate data.</small>
                <div class="mb-2">
                    <button class="btn btn-sm btn-primary" id="btnTambahRow">+ Tambah Data</button>
                </div>
                </div>


                <div class="col-sm-6 text-sm-right">
                    <div class="m-y-sm">

                        <!-- Form Import Excel -->
                        <form id="importForm" enctype="multipart/form-data" onsubmit="return false;">
                            @csrf
                            <label for="fileUpload" class="btn btn-xs white">Bulk Data</label>
                            <input type="file" id="fileUpload" name="file" style="display: none;">
                        </form>

                        <span class="">Short:</span>
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

        <!-- Tabel invent -->
        <div class="col-12">
            <div class="table-wrapper">
                <table class="table table-bordered" id="inventoryTable">
                    <thead style="color:white">
                        <tr class="sticky-header" style="font-size: 12px;">
                            <th>No.</th>
                            <th class="sticky">Merk</th>
                            <th>Jenis</th>
                            <th>decription</th>
                            <th>Pemegang</th>
                            <th>Keterangan</th>
                            <th>catatan</th>
                            <th>foto</th>
                            <th>updated_at</th>

                        </tr>
                    </thead>
                    <tbody>
                        @php $no = 1; @endphp
                        @foreach ($data as $i)
                        <tr style="font-size: 10px;">
                            <td>{{ $no++ }}</td>
                            <td class="sticky">
                                <a href="#" class="editable-merk" data-name="merk" data-pk="{{ $i->id }}" data-type="text" data-url="/inventory-inline-update" data-title="Enter merk">
                                    {{ $i->merk }}
                                </a>
                            </td>
                            <td>
                                <a href="#" class="editable-jenis" data-name="jenis" data-pk="{{ $i->id }}" data-type="text" data-url="/inventory-inline-update" data-title="Enter jenis">
                                    {{ $i->jenis }}
                                </a>
                            </td>
                            <td>
                                <a href="#" class="editable-deskripsi" data-name="deskripsi" data-pk="{{ $i->id }}" data-type="text" data-url="/inventory-inline-update" data-title="Enter deskripsi">
                                    {{ $i->deskripsi }}
                                </a>
                            </td>
                            </td>
                            <td>
                                <a href="#" class="editable-nama" data-name="karyawan" data-pk="{{ $i->id }}" data-type="text" data-url="/inventory-inline-update" data-title="Enter karyawan">
                                    {{ $i->karyawan->nama_lengkap }}
                                </a>

                            </td>
                            <td>
                                <a href="#" class="editable-keterangan" data-name="keterangan" data-pk="{{ $i->id }}" data-type="text" data-url="/inventory-inline-update" data-title="Enter keterangan">
                                    {{ $i->keterangan }}
                                </a>
                            </td>
                            <td>
                                <a href="#" class="editable-catatan" data-name="catatan" data-pk="{{ $i->id }}" data-type="text" data-url="/inventory-inline-update" data-title="Enter catatan">
                                    {{ $i->catatan }}
                                </a>
                            </td>
                            <td>
                                <a href="#"
                                    class="upload-foto"
                                    data-id="{{ $i->id }}">
                                    @if ($i->foto)
<img src="{{ asset('foto_inventory/' . $i->foto) }}" width="60">
                                    @else
                                    <span>Pilih Gambar</span>
                                    @endif
                                </a>

                                <input type="file"
                                    accept="image/*"
                                    class="input-foto d-none"
                                    id="foto-input-{{ $i->id }}"
                                    data-id="{{ $i->id }}">
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

<!-- ================= JS ================ -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    $.ajaxSetup({
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
    });

    $(document).ready(function() {
        // ========== Tambah Row Baru ==========
        $("#btnTambahRow").on("click", function() {
            if ($("#rowInput").length > 0) return; // Cegah row ganda

            let newRow = `
                <tr id="rowInput">
                    <td>New</td>
                    <td><input type="text" class="form-control form-control-sm" name="merk"></td>
                    <td><input type="text" class="form-control form-control-sm" name="jenis"></td>
                    <td><input type="text" class="form-control form-control-sm" name="deskripsi"></td>
                    <td>
                        <input type="text" class="form-control form-control-sm karyawan-input" name="karyawan_text" placeholder="Cari nama...">
                        <input type="hidden" name="karyawan_id" class="karyawan-id">
                    </td>
                    <td><input type="text" class="form-control form-control-sm" name="keterangan"></td>
                    <td><input type="text" class="form-control form-control-sm" name="catatan"></td>
                    <td><input type="file" class="form-control-file form-control-sm" name="foto"></td>
                    <td>
                        <button class="btn btn-success btn-sm" id="btnSaveRow">Simpan</button>
                        <button class="btn btn-danger btn-sm" id="btnCancelRow">Batal</button>
                    </td>
                </tr>
            `;
            $("#inventoryTable tbody").prepend(newRow);
        });

        // ========== Batal Tambah ==========
        $(document).on("click", "#btnCancelRow", function() {
            $("#rowInput").remove();
        });

        // ========== Simpan Row ==========
        $(document).on("click", "#btnSaveRow", function() {
            let formData = new FormData();
            $("#rowInput")
                .find("input")
                .each(function() {
                    let name = $(this).attr("name");
                    if ($(this).attr("type") === "file") {
                        if (this.files[0]) formData.append(name, this.files[0]);
                    } else {
                        formData.append(name, $(this).val());
                    }
                });

            $.ajax({
                url: "/inventory",
                method: "POST",
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    if (res.success) {
                        location.reload();
                    }
                },
                error: function(xhr) {
                    alert("Gagal simpan: " + xhr.responseText);
                },
            });
        });

        // ========== Upload Foto ==========
        $(document).on("click", ".upload-foto", function(e) {
            console.log("f")
            e.preventDefault();
            let id = $(this).data("id");
            $("#foto-input-" + id).trigger("click");
        });

        // ketika pilih file → preview langsung
        $(document).on("change", ".input-foto", function() {
            let id = $(this).data("id");
            let file = this.files[0];

            if (file) {
                let reader = new FileReader();
                reader.onload = function(e) {
                    $(`a.upload-foto[data-id="${id}"]`).html(
                        `<img src="${e.target.result}" height="30">`
                    );
                };
                reader.readAsDataURL(file);

                // kalau mau langsung upload ke server via ajax
                let formData = new FormData();
                formData.append("foto", file);
                formData.append("id", id);

                $.ajax({
                    url: `/inventory/${id}/upload-foto`,
                    type: "POST",
                    data: formData,
                    contentType: false,
                    processData: false,
                    success: function(res) {
                        console.log("Upload berhasil", res);
                    },
                    error: function(err) {
                        console.error("Upload gagal", err);
                    },
                });
            }
        });

        // ========== Autocomplete Karyawan ==========
        $(document).on("focus", ".karyawan-input", function() {
            if (!$(this).data("ui-autocomplete")) {
                $(this).autocomplete({
                    source: function(request, response) {
                        $.ajax({
                            url: "{{ route('karyawan.search') }}",
                            data: {
                                term: request.term
                            },
                            success: function(data) {
                                response(
                                    $.map(data, function(item) {
                                        return {
                                            label: item.value,
                                            value: item.value,
                                            id: item.id,
                                        };
                                    })
                                );
                            },
                        });
                    },
                    minLength: 2,
                    select: function(event, ui) {
                        $(this).val(ui.item.value);
                        $(this).closest("td")
                            .find(".karyawan-id")
                            .val(ui.item.id);
                        return false;
                    },
                });
            }
        });

        // ========== Efek Scroll Header Tabel ==========
        const tableWrapper = document.querySelector(".table-wrapper");
        if (tableWrapper) {
            const headerCells = document.querySelectorAll("thead th");
            tableWrapper.addEventListener("scroll", function() {
                if (tableWrapper.scrollTop > 0) {
                    headerCells.forEach((th) => th.classList.add("scrolled"));
                } else {
                    headerCells.forEach((th) =>
                        th.classList.remove("scrolled")
                    );
                }
            });
        }
    });
</script>

@endsection
