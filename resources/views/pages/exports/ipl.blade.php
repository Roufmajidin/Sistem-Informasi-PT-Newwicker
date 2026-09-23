@extends('master.master')
{{-- IPL BLADE --}}
@section('content')
    <div class="card">

        <div class="card-header d-flex justify-content-between mt-4">

            <h4>Invoice Packing list</h4>

            <a href="{{ route('export.index') }}" class="btn btn-primary">

                <i class="fa fa-plus"></i>

                Create IPL

            </a>

        </div>

        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-bordered table-hover">

                    <thead class="thead-light">

                        <tr>

                            <th width="50">No</th>

                            <th>Invoice No</th>

                            <th>Sales Order</th>

                            <th>Buyer</th>

                            {{-- <th>PO</th> --}}

                            <th>Items Total</th>

                            <th>Container</th>

                            <th>ETD</th>

                            <th>Created By</th>


                            <th width="180">Released</th>
                            <th width="180">Action</th>
                            <th width="180">Download</th>

                        </tr>

                    </thead>

                    <tbody>

                        @forelse($datas as $data)
                            <tr>

                                <td>

                                    {{ $datas->firstItem() + $loop->index }}

                                </td>

                                <td>

                                    {{ $data->invoice_no }}

                                </td>

                                <td>

                                    {{ $data->sales_order }}

                                </td>

                                <td>

                                    {{ $data->buyer }}

                                </td>



                                <td class="text-center">

                                    {{ $data->items_count }}

                                </td>

                                <td>

                                    {{ $data->container_type }}

                                </td>

                                <td>

                                    {{ optional($data->etd)->format('d M Y') }}

                                </td>
                                <td>

                                    {{ optional($data->creator)->name }}

                                </td>
                                <td class="text-center">

                                    @if (empty($data->released))
                                        <div class="d-flex align-items-center justify-content-center gap-2">

                                            <input type="checkbox" value="{{ $data->id }}" class="release-checkbox"
                                                data-id="{{ $data->id }}" data-invoice="{{ $data->invoice_no }}"
                                                style="width:18px;height:18px;cursor:pointer;">

                                            <span class="text-muted">
                                                Not Yet
                                            </span>

                                        </div>
                                    @else
                                        <span class="badge badge-success px-3 py-2">
                                            <i class="fa fa-check-circle mr-1"></i>
                                            {{ \Carbon\Carbon::parse($data->release_date)->format('d/m/Y') }}
                                        </span>
                                    @endif

                                </td>

                                <td>

                                    {{-- <a href="#" class="btn btn-sm btn-info">

                                        <i class="fa fa-eye"></i>

                                    </a> --}}

                                    <a href="/export/{{ $data->id }}/edit" class="btn btn-sm btn-warning">

                                        <i class="fa fa-edit"></i>

                                    </a>

                                    <button class="btn btn-sm btn-danger">

                                        <i class="fa fa-trash"></i>

                                    </button>

                                </td>
                                <td class="text-end">
                                    <div class="d-flex justify-content-end gap-2" style="margin-right: 20px">
                                        <a href="{{ route('export.packing-list', $data->id) }}"
                                            class="btn btn-sm btn-success btn-download">
                                            <i class="fa fa-download download-icon"></i>
                                            <span class="btn-text">PL</span>
                                        </a>

                                        <a href="{{ route('export.inv-list', $data->id) }}"
                                            class="btn btn-sm btn-download btn-warning">
                                            <i class="fa fa-download download-icon"></i>
                                            <span class="btn-text">IL</span>
                                        </a>
                                    </div>
                                </td>


                            </tr>

                        @empty

                            <tr>

                                <td colspan="10" class="text-center">

                                    Tidak ada data.

                                </td>

                            </tr>
                        @endforelse

                    </tbody>

                </table>

            </div>

        </div>

        <div class="card-footer">

            {{ $datas->links() }}

        </div>

    </div>
    <script>
        $(document).on('click', '.btn-download', function() {

            let btn = $(this);

            btn.addClass('disabled');
            btn.css('pointer-events', 'none');

            btn.find('.download-icon')
                .removeClass('fa-download')
                .addClass('fa-spinner fa-spin');

            btn.find('.btn-text').text(' Preparing...');

            setTimeout(function() {

                btn.removeClass('disabled');
                btn.css('pointer-events', '');

                btn.find('.download-icon')
                    .removeClass('fa-spinner fa-spin')
                    .addClass('fa-download');

                btn.find('.btn-text').text(' Download');

            }, 5000);

        });
    </script>
    <script>
        $(document).on('change', '.release-checkbox', function() {

            const checkbox = $(this);

            const id = checkbox.data('id');
            const invoice = checkbox.data('invoice');

            // Jika checkbox sedang dicentang
            if (!checkbox.is(':checked')) {
                return;
            }

            Swal.fire({
                title: 'Yakin?',
                html: `
                <div style="font-size:15px;">
                    Anda akan melakukan release IPL
                    <br>
                    <strong>${invoice}</strong>
                </div>
            `,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Ya, Release',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d'
            }).then((result) => {

                /*
                |--------------------------------------------------------------------------
                | CANCEL
                |--------------------------------------------------------------------------
                */

                if (!result.isConfirmed) {

                    checkbox.prop('checked', false);

                    return;
                }

                /*
                |--------------------------------------------------------------------------
                | LOADING
                |--------------------------------------------------------------------------
                */

                Swal.fire({
                    title: 'Processing...',
                    html: 'Sedang melakukan release IPL dan update ETD PO.',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                checkbox.prop('disabled', true);

                /*
                |--------------------------------------------------------------------------
                | AJAX
                |--------------------------------------------------------------------------
                */

                $.ajax({

                    url: "{{ url('/export') }}/" + id + "/release",

                    type: "POST",

                    data: {
                        _token: "{{ csrf_token() }}"
                    },

                    success: function(response) {

                        if (response.success) {

                            Swal.fire({
                                icon: 'success',
                                title: 'Berhasil!',
                                html: `
                                <div>
                                    IPL <strong>${invoice}</strong>
                                    berhasil di-release.
                                    <br><br>
                                    <strong>ETD:</strong>
                                    ${response.etd}
                                    <br>
                                    <strong>PO Updated:</strong>
                                    ${response.updated_po}
                                </div>
                            `,
                                confirmButtonText: 'OK',
                                confirmButtonColor: '#28a745'
                            }).then(() => {

                                /*
                                |--------------------------------------------------------------------------
                                | Ubah checkbox menjadi badge
                                |--------------------------------------------------------------------------
                                */

                                checkbox.closest('td').html(`
                                <span class="badge badge-success px-3 py-2">
                                    <i class="fa fa-check-circle mr-1"></i>
                                    ${response.release_date}
                                </span>
                            `);

                            });

                        } else {

                            checkbox.prop('checked', false);
                            checkbox.prop('disabled', false);

                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: response.message || 'Gagal melakukan release.'
                            });
                        }
                    },

                    error: function(xhr) {

                        checkbox.prop('checked', false);
                        checkbox.prop('disabled', false);

                        let message = 'Terjadi kesalahan saat melakukan release.';

                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal',
                            text: message
                        });

                    }

                });

            });

        });
    </script>
@endsection
