@extends('master.master')
@section('title', "CAD - master data")
@section('content')

@php
$detail = $find->detail ?? [];
@endphp
<div class="padding">
    <div class="box">
        <div class="box-header">
            <h2>CAD</h2>
            <small>___</small>
        </div>
        <input type="hidden" id="role" value="{{ auth()->user()->role }}">
        <input type="hidden" id="id" value="{{ auth()->user()->role }}">
        <!-- modal -->
        <div id="modal-upload" style="display:none;">
            <div style="background:#fff;padding:20px;border:1px solid #ddd;max-width:400px">

                <h4>Upload CAD</h4>

                <input type="file" id="cad-file" class="form-control"><br>

                <div class="progress" style="height:20px; display:none;">
                    <div id="progress-bar"
                        style="height:100%;width:0%;background:#28a745;color:#fff;text-align:center;">
                        0%
                    </div>
                </div>

                <br>

                <button class="btn btn-success btn-sm" id="btn-submit-upload">
                    Upload
                </button>

                <button class="btn btn-default btn-sm" id="btn-close-modal">
                    Close
                </button>

            </div>
        </div>
        <div class="box-body">

    {{-- HEADER INFO --}}
    <table class="table table-bordered">
        <tr>
            <td width="200" rowspan="2">
                @if(!empty($detail['photo']))
                    <img src="{{ $detail['photo'] }}" width="180">
                @else
                    <span class="text-muted">No Image</span>
                @endif
            </td>

            <td width="150"><b>Article Code</b></td>
            <td>
                {{ $detail['article_nr_']
                    ?? $detail['article_nr_nw']
                    ?? $detail['nw_code']
                    ?? '-' }}
            </td>
        </tr>

        <tr>
            <td><b>Description</b></td>
            <td>{{ $detail['description'] ?? '-' }}</td>
        </tr>
    </table>

    {{-- 2 COLUMN --}}
    <div class="row">

        {{-- ================= LEFT : CAD ================= --}}
        <div class="col-md-6">

            <div class="box">
                <div class="box-header">
                    <h4>CAD Files</h4>
                </div>

                <div class="box-body">

                    <button class="btn btn-primary btn-sm mb-2" id="btn-upload-cad">
                        Upload CAD
                    </button>

                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>File</th>
                                    <th>Ver</th>
                                    <th>By</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse($cads as $i => $cad)
                                <tr>
                                    <td>{{ $i+1 }}</td>

                                    <td>
                                        <a href="{{ asset('storage/'.$cad->file_path) }}" target="_blank">
                                            View
                                        </a>
                                    </td>

                                    <td>
                                        <b>V{{ $cad->version }}</b>
                                    </td>

                                    <td>{{ $cad->user->name ?? '-' }}</td>

                                    <td>{{ $cad->status ?? '-' }}</td>

                                    <td>
                                        <a href="/cad/edit/{{ $cad->id }}" class="btn btn-xs btn-warning">
                                            Edit
                                        </a>

                                        <button class="btn btn-xs btn-danger btn-delete"
                                            data-id="{{ $cad->id }}">
                                            Del
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">
                                        Belum ada CAD
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>

                        </table>
                    </div>

                </div>
            </div>

        </div>

        {{-- ================= RIGHT : MASTER DATA ================= --}}
        <div class="col-md-6 mt-4">
            <div class="box">
                <div class="box-header">
                    <h4>Master Data</h4>
                </div>

                  <div class="box-body">



                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>w</th>
                                    <th>d</th>
                                    <th>h</th>
                                    <th>Pw</th>
                                    <th>PD</th>
                                    <th>PH</th>
                                    <th>Action</th>
                                </tr>
                            </thead>

                            <tbody>
                              <tr></tr>
                            </tbody>

                        </table>
                    </div>

                </div>
            </div>

        </div>

    </div>
</div>
    </div>

    <!-- script -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>

    <script>
        $(document).on('click', '#btn-upload-cad', function() {
            $('#modal-upload').fadeIn();
        });

        $(document).on('click', '#btn-close-modal', function() {
            $('#modal-upload').fadeOut();
        });

        $(document).on('click', '#btn-submit-upload', function() {

            let file = $('#cad-file')[0].files[0];

            if (!file) {
                alert('Pilih file dulu');
                return;
            }

            let formData = new FormData();
            formData.append('file', file);
            formData.append('article_code', "{{ $id }}"); // dari controller
            formData.append('_token', "{{ csrf_token() }}");

            $('.progress').show();

            $.ajax({
                url: '/cad/upload',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,

                xhr: function() {
                    let xhr = new window.XMLHttpRequest();

                    xhr.upload.addEventListener("progress", function(e) {
                        if (e.lengthComputable) {
                            let percent = Math.round((e.loaded / e.total) * 100);

                            $('#progress-bar')
                                .css('width', percent + '%')
                                .text(percent + '%');
                        }
                    });

                    return xhr;
                },

                success: function(res) {
                    alert('Upload berhasil');

                    $('#modal-upload').hide();
                    $('#progress-bar').css('width', '0%').text('0%');

                    location.reload(); // refresh list CAD
                },

                error: function() {
                    alert('Upload gagal');
                }
            });
        });
    </script>
    <style>
        table td {
            vertical-align: middle;
        }

        /* BACKDROP */
        .custom-modal {
            display: none;
            position: fixed;
            z-index: 9999;
            /* SUPER PENTING */
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;

            background: rgba(0, 0, 0, 0.5);

            /* center */
            display: flex;
            justify-content: center;
            align-items: center;
        }

        /* BOX */
        .custom-modal-content {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            width: 400px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);

            animation: fadeIn .2s ease;
        }

        /* ANIMASI */
        @keyframes fadeIn {
            from {
                transform: scale(0.9);
                opacity: 0;
            }

            to {
                transform: scale(1);
                opacity: 1;
            }
        }
    </style>
    @endsection
