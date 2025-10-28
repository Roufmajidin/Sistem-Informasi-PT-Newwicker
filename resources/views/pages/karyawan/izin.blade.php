@extends('master.master')
@section('title', "List Izin Karyawan")
@section('content')
<div class="padding">
    <div class="box">
        <div class="p-a white lt box-shadow">
            <div class="row">
                <div class="col-sm-6">
                    <h4 class="mb-0 _300">List Izin Karyawan</h4>
                    <small class="text-muted">PT. Newwicker Indonesia</small>
                </div>
                <div class="col-sm-6">
                    <div class="d-flex justify-content-end mb-2">
                        <form id="filterForm" class="form-inline d-flex flex-wrap gap-2" method="GET" action="{{ url('izin-karyawan') }}">
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

                            @if(request()->has('date') && request('date') != '')
                                <a href="{{ url('izin-karyawan') }}" class="btn btn-sm btn-secondary">
                                    <i class="fa fa-undo"></i> Reset
                                </a>
                            @endif
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabel -->
        <div class="col-12">
            <div class="table-wrapper">
                <table class="table table-bordered table-striped">
                    <thead style="color:white; background-color:#343a40">
                        <tr style="font-size: 12px;">
                            <th>No.</th>
                            <th>Nama Lengkap</th>
                            <th>Tanggal</th>
                            <th>Keterangan</th>
                            <th>Bukti</th>
                            <th>Messages</th>
                            <th>Validasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $no = ($absens->currentPage() - 1) * $absens->perPage() + 1; @endphp
                        @forelse($absens as $absen)
                            <tr>
                                <td>{{ $no++ }}</td>
                                <td>{{ $absen->user->name }}</td>
                                <td>{{ \Carbon\Carbon::parse($absen->tanggal)->format('d-m-Y') }}</td>
                                <td>{{ $absen->keterangan }}</td>
                                <td>
                                    @if($absen->foto)
                                        <button type="button"
                                            class="btn btn-sm btn-outline-primary view-photo"
                                            data-foto="{{ asset('storage/' . $absen->foto) }}">
                                            <i class="fa fa-eye"></i>
                                        </button>
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>{{ $absen->messages }}</td>
                                <td>
                                    @if($absen->validate == 1)
                                        <span class="badge bg-success">Sudah divalidasi</span>
                                    @else
                                        <button class="btn btn-sm btn-success validate-btn"
                                            data-id="{{ $absen->id }}">
                                            Validate
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">Tidak ada karyawan izin</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-2">
                {{ $absens->links() }}
            </div>
        </div>
    </div>
</div>

<!-- Modal Foto -->
<div class="modal fade" id="fotoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bukti File</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body d-flex justify-content-center">
                <img id="fotoPreview" src="" alt="Foto Absen" class="img-fluid"
                    style="max-height:70vh; border-radius:5px; border:1px solid #ddd;">
            </div>
        </div>
    </div>
</div>

<!-- JS -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(document).ready(function() {
    // Tampilkan foto
    $(document).on('click', '.view-photo', function() {
        const fotoUrl = $(this).data('foto');
        $('#fotoPreview').attr('src', fotoUrl);
        $('#fotoModal').modal('show');
    });

    // Validasi izin
    let validateAbsenId = null;

    $(document).on('click', '.validate-btn', function() {
        validateAbsenId = $(this).data('id');
        if(confirm('Apakah Anda yakin ingin memvalidasi izin ini?')) {
            $.ajax({
                url: 'validate-izin/' + validateAbsenId,
                type: 'POST',
                data: { _token: '{{ csrf_token() }}' },
                success: function(res) {
                    alert(res.message);
                    location.reload();
                },
                error: function() {
                    alert('Terjadi kesalahan');
                }
            });
        }
    });
});
</script>
@endsection
