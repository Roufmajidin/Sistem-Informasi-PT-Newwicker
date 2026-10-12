
@extends('master.master')

@section('title', 'List Izin Karyawan')

@section('content')
<div class="padding">
    <div class="box">

        {{-- HEADER --}}
        <div class="p-a white lt box-shadow">
            <div class="row">
                <div class="col-sm-6">
                    <h4 class="mb-0 _300">List Izin Karyawan</h4>
                    <small class="text-muted">PT. Newwicker Indonesia</small>
                </div>

                <div class="col-sm-6">
                    <div class="d-flex justify-content-end mb-2">
                        <form
                            id="filterForm"
                            class="form-inline d-flex flex-wrap gap-2"
                            method="GET"
                            action="{{ url('izin-karyawan') }}"
                        >
                            <input
                                type="date"
                                name="date"
                                id="date"
                                class="form-control form-control-sm"
                                value="{{ $date ?? '' }}"
                            >

                            <button type="submit" class="btn btn-sm btn-primary">
                                <i class="fa fa-search"></i> Tampilkan
                            </button>

                            @if(request()->filled('date'))
                                <a
                                    href="{{ url('izin-karyawan') }}"
                                    class="btn btn-sm btn-secondary"
                                >
                                    <i class="fa fa-undo"></i> Reset
                                </a>
                            @endif
                        </form>
                    </div>
                </div>
            </div>
        </div>

        {{-- TABEL IZIN --}}
        <div class="col-12">
            <div class="table-wrapper">
                <table class="table table-bordered table-striped">
                    <thead style="color:white; background-color:#343a40">
                        <tr style="font-size:12px;">
                            <th>No.</th>
                            <th>Nama Lengkap</th>
                            <th>Tanggal</th>
                            <th>Created At</th>
                            <th>Keterangan</th>
                            <th>Bukti</th>
                            <th>Messages</th>
                            <th>Validasi</th>
                        </tr>
                    </thead>

                    <tbody>
                        @php
                            $no = ($absens->currentPage() - 1)
                                * $absens->perPage() + 1;
                        @endphp

                        @forelse($absens as $absen)
                            <tr>
                                {{-- NOMOR --}}
                                <td>{{ $no++ }}</td>

                                {{-- NAMA KARYAWAN --}}
                                <td>
                                    {{ optional($absen->user)->name ?? '-' }}
                                </td>

                                {{-- RENTANG TANGGAL IZIN --}}
                                <td>
                                    @php
                                        $mulai = $absen->mulai_tanggal ?? null;
                                        $sampai = $absen->sampai_tanggal ?? null;

                                        $formatTanggal = function ($value) {
                                            if (!$value) {
                                                return '-';
                                            }

                                            try {
                                                return \Carbon\Carbon::parse($value)
                                                    ->format('d-m-Y');
                                            } catch (\Throwable $e) {
                                                return '-';
                                            }
                                        };
                                    @endphp

                                    {{ $formatTanggal($mulai) }}

                                    @if($sampai && $sampai != $mulai)
                                        s/d {{ $formatTanggal($sampai) }}
                                    @endif
                                </td>

                                {{-- WAKTU DATA DIBUAT --}}
                                <td>
                                    @if($absen->created_at)
                                        {{ $absen->created_at->format('d-m-Y H:i') }}
                                    @else
                                        -
                                    @endif
                                </td>

                                {{-- ALASAN IZIN --}}
                                <td class="text-wrap">
                                    {{ $absen->alasan ?? '-' }}
                                </td>

                                {{-- FILE BUKTI --}}
                                <td>
                                    @if(!empty($absen->file))
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-primary view-file"
                                            data-file="{{ asset('storage/' . $absen->file) }}"
                                            title="Lihat bukti izin"
                                        >
                                            <i class="fa fa-eye"></i>
                                        </button>
                                    @else
                                        -
                                    @endif
                                </td>

                                {{-- MESSAGES BELUM DIKETAHUI KOLOMNYA --}}
                                <td>
                                    -
                                </td>

                                {{-- VALIDASI BERDASARKAN STATUS --}}
                                <td>
                                    @php
                                        $status = strtolower(
                                            trim((string) ($absen->status ?? ''))
                                        );
                                    @endphp

                                    @if(in_array($status, [
                                        'approved',
                                        'disetujui',
                                        'validated',
                                        'valid'
                                    ]))
                                        <span class="badge bg-success">
                                            Sudah divalidasi
                                        </span>
                                    @else
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-success validate-btn"
                                            data-id="{{ $absen->id }}"
                                        >
                                            Validate
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center">
                                    Tidak ada karyawan izin
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- PAGINATION --}}
            <div class="mt-2">
                {{ $absens->links() }}
            </div>
        </div>
    </div>
</div>

{{-- MODAL BUKTI IZIN --}}
<div class="modal fade" id="fileModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bukti Izin Karyawan</h5>

                <button
                    type="button"
                    class="close"
                    data-dismiss="modal"
                    aria-label="Close"
                >
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body text-center">
                <img
                    id="fileImagePreview"
                    src=""
                    alt="Bukti Izin"
                    class="img-fluid"
                    style="max-height:70vh; display:none;"
                >

                <iframe
                    id="filePdfPreview"
                    src=""
                    style="width:100%;height:70vh;display:none;border:0;"
                    title="Bukti Izin"
                ></iframe>

                <div id="fileOtherPreview" style="display:none;">
                    File tidak dapat ditampilkan di sini.
                    <br>
                    <a
                        id="fileOpenLink"
                        href="#"
                        target="_blank"
                        rel="noopener noreferrer"
                    >
                        Buka atau unduh file
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .text-wrap {
        white-space: normal !important;
        word-break: break-word;
        overflow-wrap: anywhere;
    }

    .table td {
        white-space: normal !important;
        overflow-wrap: anywhere;
        max-width: 250px;
        vertical-align: middle;
    }

    .table-wrapper {
        width: 100%;
        overflow-x: auto;
    }

    .table-wrapper table {
        min-width: 900px;
    }

    #fileImagePreview {
        object-fit: contain;
        border-radius: 5px;
        border: 1px solid #ddd;
    }
</style>

<script>
$(document).ready(function () {

    // Tampilkan bukti foto atau PDF
    $(document).on('click', '.view-file', function () {
        const fileUrl = $(this).data('file');
        const extension = fileUrl.split('?')[0].split('.').pop().toLowerCase();

        $('#fileImagePreview').hide().attr('src', '');
        $('#filePdfPreview').hide().attr('src', '');
        $('#fileOtherPreview').hide();
        $('#fileOpenLink').attr('href', fileUrl);

        if (['jpg', 'jpeg', 'png', 'gif', 'webp'].includes(extension)) {
            $('#fileImagePreview').attr('src', fileUrl).show();
        } else if (extension === 'pdf') {
            $('#filePdfPreview').attr('src', fileUrl).show();
        } else {
            $('#fileOtherPreview').show();
        }

        $('#fileModal').modal('show');
    });

    // Bersihkan preview ketika modal ditutup
    $('#fileModal').on('hidden.bs.modal', function () {
        $('#fileImagePreview').attr('src', '');
        $('#filePdfPreview').attr('src', '');
        $('#fileOtherPreview').hide();
    });

    // Validasi izin
    $(document).on('click', '.validate-btn', function () {
        const izinId = $(this).data('id');

        if (!confirm('Apakah Anda yakin ingin memvalidasi izin ini?')) {
            return;
        }

        $.ajax({
            url: '{{ url("validate-izin") }}/' + izinId,
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },

            success: function (res) {
                alert(res.message || 'Permintaan validasi berhasil diproses');
                location.reload();
            },

            error: function (xhr) {
                let message = 'Terjadi kesalahan saat memvalidasi izin';

                if (xhr.responseJSON && xhr.responseJSON.message) {
                    message = xhr.responseJSON.message;
                }

                alert(message);
            }
        });
    });

});
</script>
@endsection
