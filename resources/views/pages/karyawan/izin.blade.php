
@extends('master.master')

@section('title', 'List Izin Karyawan')

@section('content')
<div class="padding">
    <div class="box">
        {{-- Header dan Filter --}}
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

        {{-- Tabel Izin --}}
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
                                <td>{{ $no++ }}</td>

                                <td>
                                    {{ optional($absen->user)->name ?? '-' }}
                                </td>

                                {{-- Tanggal: tanpa Carbon::parse --}}
                                <td>
                                    @php
                                        $tanggalValue = $absen->tanggal ?? null;
                                        $tanggalText = '-';

                                        if ($tanggalValue instanceof \DateTimeInterface) {
                                            $tanggalText = $tanggalValue->format('d-m-Y');
                                        } elseif (
                                            is_string($tanggalValue) &&
                                            preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggalValue)
                                        ) {
                                            $tanggalText = substr($tanggalValue, 8, 2)
                                                . '-'
                                                . substr($tanggalValue, 5, 2)
                                                . '-'
                                                . substr($tanggalValue, 0, 4);
                                        } elseif (
                                            is_string($tanggalValue) &&
                                            preg_match('/^\d{8}$/', $tanggalValue)
                                        ) {
                                            $tanggalText = substr($tanggalValue, 6, 2)
                                                . '-'
                                                . substr($tanggalValue, 4, 2)
                                                . '-'
                                                . substr($tanggalValue, 0, 4);
                                        }
                                    @endphp

                                    {{ $tanggalText }}
                                </td>

                                {{-- Created At: tanpa parsing teks keterangan --}}
                                <td>
                                    @php
                                        $createdValue = $absen->created_at ?? null;
                                        $createdText = '-';

                                        if ($createdValue instanceof \DateTimeInterface) {
                                            $createdText = $createdValue->format('d-m-Y H:i');
                                        } elseif (
                                            is_string($createdValue) &&
                                            preg_match(
                                                '/^\d{4}-\d{2}-\d{2}[ T]\d{2}:\d{2}/',
                                                $createdValue
                                            )
                                        ) {
                                            $createdText = substr($createdValue, 8, 2)
                                                . '-'
                                                . substr($createdValue, 5, 2)
                                                . '-'
                                                . substr($createdValue, 0, 4)
                                                . ' '
                                                . substr($createdValue, 11, 5);
                                        }
                                    @endphp

                                    {{ $createdText }}
                                </td>

                                {{-- Keterangan adalah teks biasa --}}
                                <td class="text-wrap">
                                    {{ $absen->keterangan ?? '-' }}
                                </td>

                                {{-- Bukti Foto --}}
                                <td>
                                    @if(!empty($absen->foto))
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-primary view-photo"
                                            data-foto="{{ asset('storage/' . $absen->foto) }}"
                                            title="Lihat bukti"
                                        >
                                            <i class="fa fa-eye"></i>
                                        </button>
                                    @else
                                        -
                                    @endif
                                </td>

                                {{-- Pesan --}}
                                <td>
                                    {{ $absen->messages ?? '-' }}
                                </td>

                                {{-- Status Validasi --}}
                                <td>
                                    @if((int) ($absen->validate ?? 0) === 1)
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

            {{-- Pagination --}}
            <div class="mt-2">
                {{ $absens->links() }}
            </div>
        </div>
    </div>
</div>

{{-- Modal Foto --}}
<div class="modal fade" id="fotoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bukti File</h5>

                <button
                    type="button"
                    class="close"
                    data-dismiss="modal"
                    aria-label="Close"
                >
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body d-flex justify-content-center">
                <img
                    id="fotoPreview"
                    src=""
                    alt="Foto Izin"
                    class="img-fluid"
                    style="max-height:70vh; border-radius:5px; border:1px solid #ddd;"
                >
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

    #fotoPreview {
        object-fit: contain;
    }
</style>

<script>
$(document).ready(function () {

    // Tampilkan foto bukti izin
    $(document).on('click', '.view-photo', function () {
        const fotoUrl = $(this).data('foto');

        $('#fotoPreview').attr('src', fotoUrl);
        $('#fotoModal').modal('show');
    });

    // Bersihkan foto saat modal ditutup
    $('#fotoModal').on('hidden.bs.modal', function () {
        $('#fotoPreview').attr('src', '');
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
                alert(res.message || 'Izin berhasil divalidasi');
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
