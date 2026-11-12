@extends('master.master')

@section('title', 'Employee Loans')

@section('content')
    <div class="padding">

        <div class="box">

            <div class="p-a white lt box-shadow">
                <div class="row">

                    <div class="col-sm-8">
                        <h4 class="mb-1">
                            Pengajuan Pinjaman Karyawan
                        </h4>

                        <small class="text-danger">
                            List pengajuan pinjaman karyawan.
                        </small>
                    </div>

                    <div class="col-sm-4 text-right">
                        <span class="badge badge-primary">
                            Total: {{ $loans->count() }} Pengajuan
                        </span>
                    </div>

                </div>
            </div>

            <div class="col-12">

                <div class="table-responsive">

                    <table class="table table-bordered table-hover">

                        <thead style="color:white">

                            <tr class="sticky-header" style="font-size:12px;">

                                <th>No</th>
                                <th>Nama</th>
                                <th>Jabatan</th>
                                <th>Department</th>
                                <th>Nominal</th>
                                <th>Alasan Pengajuan</th>
                                <th>Cara Pengembalian</th>
                                <th>Periode</th>
                                <th>Status</th>
                                <th>Approver</th>
                                <th>Action</th>

                            </tr>

                        </thead>

                        <tbody>

                            @forelse($loans as $loan)
                                <tr style="font-size:13px;">

                                    <td>
                                        {{ $loop->iteration }}
                                    </td>

                                    <td>
                                        {{ $loan->nama_karyawan }}
                                    </td>

                                    <td>
                                        {{ $loan->jabatan ?? '-' }}
                                    </td>

                                    <td>
                                        {{ $loan->divisi?->nama ?? '-' }}
                                    </td>

                                    <td style="white-space:nowrap;">
                                        Rp.
                                        {{ number_format((float) $loan->nominal_pengajuan, 0, ',', '.') }}
                                    </td>

                                    <td
                                        style="min-width:200px;
                                           max-width:350px;
                                           white-space:normal;
                                           overflow-wrap:anywhere;">

                                        {{ $loan->alasan_pengajuan ?: '-' }}

                                    </td>

                                    <td>
                                        @if ($loan->cara_pengembalian === 'pemotongan_gaji')
                                            Pemotongan Gaji
                                        @elseif($loan->cara_pengembalian === 'tunai')
                                            Tunai
                                        @else
                                            -
                                        @endif
                                    </td>

                                    <td style="white-space:nowrap;">
                                        {{ $loan->periode_pembayaran }} Bulan
                                    </td>

                                    <td style="white-space:nowrap;">

                                        @if ($loan->status === 'pending')
                                            <span class="badge badge-warning">
                                                Pending
                                            </span>

                                            <form action="{{ route('employee-loans.approve', $loan->id) }}" method="POST"
                                                style="display:inline-block; margin-top:5px;">
                                                @csrf

                                                <button type="submit" class="btn btn-sm btn-success"
                                                    onclick="return confirm('Approve pengajuan ini?')">
                                                    Approve
                                                </button>

                                            </form>
                                        @elseif($loan->status === 'approved')
                                            <span class="badge badge-success">
                                                Approved
                                            </span>
                                        @elseif($loan->status === 'rejected')
                                            <span class="badge badge-danger">
                                                Rejected
                                            </span>
                                        @else
                                            <span class="badge badge-secondary">
                                                {{ ucfirst($loan->status ?? '-') }}
                                            </span>
                                        @endif

                                    </td>

                                    <td>
                                        {{ $loan->approverUser?->name ?? '-' }}
                                    </td>

                                    <td>

                                        <form action="{{ route('employee-loans.destroy', $loan->id) }}" method="POST"
                                            class="form-delete">
                                            @csrf
                                            @method('DELETE')

                                            <button type="submit" class="btn btn-sm btn-danger">
                                                Delete
                                            </button>

                                        </form>

                                    </td>

                                </tr>

                            @empty

                                <tr>
                                    <td colspan="11" class="text-center">
                                        Tidak ada pengajuan pinjaman
                                    </td>
                                </tr>
                            @endforelse

                        </tbody>

                    </table>

                </div>

            </div>

        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @if (session('success'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: @json(session('success')),
                    timer: 2000,
                    showConfirmButton: false
                });
            });
        </script>
    @endif

    @if (session('error'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: @json(session('error'))
                });
            });
        </script>
    @endif

    <script>
        document.addEventListener('DOMContentLoaded', function() {

            document.querySelectorAll('.form-delete').forEach(function(form) {

                form.addEventListener('submit', function(e) {

                    e.preventDefault();

                    Swal.fire({
                        title: 'Are you sure delete bro?',
                        text: 'Data yang dihapus tidak bisa dikembalikan',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#d33',
                        cancelButtonColor: '#3085d6',
                        confirmButtonText: 'Ya, hapus',
                        cancelButtonText: 'Batal'
                    }).then(function(result) {

                        if (result.isConfirmed) {
                            form.submit();
                        }

                    });

                });

            });

        });
    </script>

@endsection
