@extends('master.master')
@section('title', "Labeling List")

@section('content')
<div class="padding">
    <div class="box">

        <div class="p-a white lt box-shadow">
            <div class="row">
                <div class="col-sm-6">
                    <h4 class="mb-0 _300">Labeling</h4>
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
        <!-- Table -->
        <div class="col-12">
            <div class="table-wrapper">
                <table class="table table-bordered">
                    <thead style="color:white; background:#2d3e50;">
                        <tr class="sticky-header" style="font-size: 12px;">
                            <th>No.</th>
                            <th>Description</th>
                            <th>List label</th>
                            <th>Jadwal Container</th>
                         <th style="width: 180px;">Note (Rouf)</th>
                    <th style="width: 180px;">Note (Yogi)</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($labelings as $i => $label)
                        <tr data-id="{{ $label->id }}">
                            <td>{{ $i + 1 }}</td>
                            <td contenteditable="true">{{ $label->description }}</td>
                            <td>
                                <ul style="padding-left:18px; margin:0;" contenteditable="true">
                                    @foreach($label->labels ?? [] as $item)
                                    <li>{{ $item }}</li>
                                    @endforeach
                                </ul>
                            </td>
                            <td contenteditable="true">{{ $label->jadwal_container }}</td>
                                             <td class="note-cell" contenteditable="true">{{ $label->status_rouf }}</td>
                    <td class="note-cell" contenteditable="true">{{ $label->status_yogi }}</td>

                            <td>
                                <button class="btn btn-sm btn-success save-btn">Save</button>
                                <button class="btn btn-sm btn-danger delete-btn">Delete</button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted">Belum ada data labeling</td>
                        </tr>
                        @endforelse
                    </tbody>

                </table>
            </div>
        </div>
    </div>
</div>
<!-- new style for css;s -->
  <style>
        .table-wrapper {
            overflow-x: auto;
        }

        .table th,
        .table td {
            vertical-align: middle;
            font-size: 13px;
        }

        /* Kolom Note biar wrap text */
        .table td.note-cell {
            white-space: pre-wrap; /* biar teks panjang turun ke bawah */
            word-break: break-word;
        }


        /* Sticky header biar tetap di atas waktu scroll */
        .sticky-header {
            position: sticky;
            top: 0;
            z-index: 2;
        }
           .row-done {
            background-color: #d4edda !important;
        }
        /* row actif */
        .row-active {
    background-color: #d0ebff !important;
    transition: background-color 0.3s ease;
}
    </style>
@endsection


<!-- JS -->
 @section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        function updateRowColors() {
            document.querySelectorAll('table tbody tr').forEach(row => {
                const roufCell = row.querySelector('td:nth-child(5)');
                if (roufCell && roufCell.textContent.trim().toLowerCase() === 'done') {
                    row.classList.add('row-done');
                } else {
                    row.classList.remove('row-done');
                }
            });
        }

        // Jalankan saat awal halaman dimuat
        updateRowColors();

        // Jalankan setiap kali user mengubah isi Note (Rouf)
        document.querySelectorAll('td:nth-child(5)').forEach(cell => {
            cell.addEventListener('input', updateRowColors);
        });
    });
</script>
<script>

$(document).ready(function() {
    // hover click typingg
    // Highlight baris yang sedang aktif
$(document).on('focus', 'td[contenteditable="true"], ul[contenteditable="true"]', function() {
    // Hapus highlight dari semua baris
    $('table tbody tr').removeClass('row-active');

    // Tambah highlight ke baris yang sedang diklik / aktif
    $(this).closest('tr').addClass('row-active');
});

// Kalau kehilangan fokus, bisa tetap biru, atau kalau mau hilang bisa pakai event blur
$(document).on('blur', 'td[contenteditable="true"], ul[contenteditable="true"]', function() {
    // Kalau mau hilang saat blur, aktifkan baris di bawah:
    // $(this).closest('tr').removeClass('row-active');
});

    $('#btnAddRow').click(function() {
        const rowCount = $('table tbody tr').length + 1;
        const newRow = `
            <tr>
                <td>${rowCount}</td>
                <td contenteditable="true"></td>
                <td>
                    <ul style="padding-left:18px; margin:0;" contenteditable="true">
                        <li></li>
                    </ul>
                </td>
                <td contenteditable="true"></td>
                <td contenteditable="true"></td>
                <td contenteditable="true"></td>
                <td>
                    <button class="btn btn-sm btn-success save-btn">Save</button>
                    <button class="btn btn-sm btn-danger delete-btn">Delete</button>
                </td>
            </tr>
        `;
        $('table tbody').append(newRow);
    });

    // ✅ SAVE (update atau insert)
    $(document).on('click', '.save-btn', function() {
        const row = $(this).closest('tr');
        const id = row.data('id') || null;

        const data = {
            _token: '{{ csrf_token() }}',
            id: id,
            description: row.children().eq(1).text().trim(),
            labels: row.find('ul li').map(function() { return $(this).text().trim(); }).get().filter(l => l !== ""),
            jadwal: row.children().eq(3).text().trim(),
            status_rouf: row.children().eq(4).text().trim(),
            status_yogi: row.children().eq(5).text().trim(),
        };

        $.ajax({
            url: '{{ route("labeling.store") }}',
            method: 'POST',
            data: data,
            success: function(res) {
                Swal.fire({
                    icon: 'success',
                    title: 'Sukses!',
                    text: res.message,
                    timer: 1200,
                    showConfirmButton: false
                });

                // Tambahkan ID baru ke baris jika barusan disimpan
                if (!id && res.id) {
                    row.attr('data-id', res.id);
                }
            },
            error: function() {
                Swal.fire('Gagal!', 'Terjadi kesalahan saat menyimpan data', 'error');
            }
        });
    });

    // 🗑️ Hapus baris
    $(document).on('click', '.delete-btn', function() {
        const row = $(this).closest('tr');
        const id = row.data('id');

        if (!id) {
            row.remove();
            return;
        }

        Swal.fire({
            title: 'Yakin hapus?',
            text: "Data ini akan dihapus permanen!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Ya, hapus',
            cancelButtonText: 'Batal'
        }).then(result => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '/labeling/' + id,
                    method: 'DELETE',
                    data: { _token: '{{ csrf_token() }}' },
                    success: function() {
                        row.remove();
                        Swal.fire('Terhapus!', 'Data berhasil dihapus.', 'success');
                    }
                });
            }
        });
    });
});
</script>
