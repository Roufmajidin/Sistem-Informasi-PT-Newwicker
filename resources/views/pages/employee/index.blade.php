@extends('master.master')

@section('title', "Inventory Assets")
@section('content')
<div class="padding">

    <div class="box">

        <div class="p-a white lt box-shadow">

            <div class="row">

                <div class="col-sm-6">

                    <!-- <small class="text-muted">
                        Pengajuan Pinjaman Karyawan
                    </small> -->

                    <br>

                    <small class="text-danger">
                        List pengajuan pinjaman karyawan.
                    </small>

                </div>

                <div class="col-sm-6 text-right">



                </div>

            </div>

        </div>

        <div class="col-12">

            <div class="table-responsive">

                <table class="table table-bordered">

                    <thead style="color:white">

                        <tr class="sticky-header"
                            style="font-size:12px;">

                            <th>No</th>
                            <th>Nama</th>
                            <th>Jabatan</th>
                            <th>Department</th>
                            <th>Nominal</th>
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
                                {{ $loan->jabatan }}
                            </td>

                            <td>
                                {{ $loan->divisi?->nama }}
                            </td>

                            <td>
                                Rp.
                                {{ number_format($loan->nominal_pengajuan,0,',','.') }}
                            </td>

                            <td>

                                @if($loan->cara_pengembalian == 'pemotongan_gaji')

                                    Pemotongan Gaji

                                @else

                                    Tunai

                                @endif

                            </td>

                            <td>
                                {{ $loan->periode_pembayaran }} Bulan
                            </td>

                          <td>

    @if($loan->status == 'pending')

        <form action="{{ route('employee-loans.approve', $loan->id) }}"
              method="POST"
              style="display:inline;">

            @csrf

            <button class="btn btn-sm btn-success"
                    onclick="return confirm('Approve pengajuan ini?')">

                Approve

            </button>

        </form>

    @else

        approved

    @endif

</td>

                            <td>
                                {{ $loan->approverUser?->name ?? '-' }}
                            </td>
<td>

    <form action="{{ route('employee-loans.destroy', $loan->id) }}"
          method="POST"
          class="form-delete">

        @csrf
        @method('DELETE')

        <button type="submit"
                class="btn btn-sm btn-danger">

            Delete

        </button>

    </form>

</td>
                        </tr>

                        @empty

                        <tr>

                            <td colspan="9"
                                class="text-center">

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
@if(session('success'))

<script>

    Swal.fire({

        icon: 'success',

        title: 'Berhasil',

        text: '{{ session('success') }}',

        timer: 2000,

        showConfirmButton: false

    });

</script>

@endif


@if(session('error'))

<script>

    Swal.fire({

        icon: 'error',

        title: 'Oops...',

        text: '{{ session('error') }}'

    });

</script>

@endif
<script>

document.querySelectorAll('.form-delete').forEach(form => {

    form.addEventListener('submit', function(e){

        e.preventDefault();

        Swal.fire({

            title: 'Are you sure delete bro ?',

            text: 'Data yang dihapus tidak bisa dikembalikan',

            icon: 'warning',

            showCancelButton: true,

            confirmButtonColor: '#d33',

            cancelButtonColor: '#3085d6',

            confirmButtonText: 'Ya, hapus',

            cancelButtonText: 'Batal'

        }).then((result) => {

            if (result.isConfirmed) {

                this.submit();

            }

        });

    });

});

</script>
@endsection
