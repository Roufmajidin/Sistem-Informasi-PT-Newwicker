@extends('master.master')
@section('title', "Inventory Assets")
@section('content')
<div class="padding">
    <div class="box">
        <div class="p-a white lt box-shadow">
            <div class="row">
                <div class="col-sm-6">
                    <h4 class="mb-0 _300">List Inventory</h4>
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
                <table class="table table-bordered">
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
                                <a href="#" class="upload-foto" data-id="{{ $i->id }}">
                                    @if ($i->foto)
                                    <img src="{{ asset('storage/foto_inventory/' . $i->foto) }}" height="30">
                                    @else
                                    <span>Pilih Gambar</span>
                                    @endif
                                </a>

                                <input type="file" accept="image/*" class="input-foto d-none" id="foto-input-{{ $i->id }}" data-id="{{ $i->id }}">

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
<script>
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $(document).ready(function() {
        $('.upload-foto').on('click', function(e) {
            e.preventDefault();
            var id = $(this).data('id');
            $('#foto-input-' + id).click();
        });

        $('.input-foto').on('change', function() {
            var id = $(this).data('id');
            var fileInput = this;

            var formData = new FormData();
            formData.append('foto', fileInput.files[0]);

            $.ajax({
                url: '/inventory/' + id + '/upload-foto',
                method: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(res) {
                    if (res.success) {
                        location.reload();
                    }
                },
                error: function(xhr) {
                    alert('Upload gagal: ' + xhr.responseText);
                }
            });
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
