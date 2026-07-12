@extends('master.master')
@section('title', "CAD - master data")
@section('content')

@php
$detail = $find->detail ?? [];
@endphp

<div class="padding">
    <div class="box">


        {{-- HEADER --}}
        <div class="box-header">
            <h2>CAD</h2>
            <small>___</small>
        </div>

        <input type="hidden" id="role" value="{{ auth()->user()->role }}">
        <input type="hidden" id="id" value="{{ auth()->user()->role }}">

        {{-- ================= HEADER INFO ================= --}}
        <div class="box-body">
            <!-- modal -->
            <div id="modal-upload" style="display:none;">
                <div style="background:#fff;padding:20px;border:1px solid #ddd;max-width:400px">

                    <h4>Upload CAD</h4>

                    <input type="file" id="cad-file" class="form-control"><br>
                    <input type="text" id="master-sample" class="form-control" placeholder="Master Sample / Ukuran">

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

            {{-- ================= 2 COLUMN ================= --}}
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
                                            <th>Master Sample</th>
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

                                            <td><b>V{{ $cad->version }}</b></td>
                                            <td>{{ $cad->user->name ?? '-' }}</td>
                                            <td>{{ $cad->status ?? '-' }}</td>
                                            <td>{{ $cad->master_sample ?? '-' }}</td>
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

                        {{-- HEADER --}}
                        <div class="box-header">
                            <div class="row align-items-center">

                                <div class="col-md-6">
                                    <h4>Master Data</h4>
                                </div>

                                <div class="col-md-6 text-end">
                                    <form action="/bom/import" method="POST" enctype="multipart/form-data">
                                        @csrf

                                        <input type="file"
                                            name="file"
                                            id="fileInput"
                                            accept=".xls,.xlsx"
                                            hidden
                                            onchange="this.form.submit()">

                                        <button type="button"
                                            class="btn btn-success btn-sm"
                                            onclick="document.getElementById('fileInput').click()">
                                            Import Excel
                                        </button>
                                    </form>
                                </div>

                            </div>
                        </div>

                        {{-- BODY --}}
                        <div class="box-body">

                            {{-- ===== TITLE ===== --}}
                            <div class="bom-header">
                                BILL OF MATERIAL
                            </div>

                            {{-- ===== INFO ===== --}}
                            <table class="table bom-info">
                                <tr>
                                    <td width="40%"><b>PRODUCT NAME</b></td>
                                   <td>{{ optional($bom)->name ?? '-' }}</td>

                                </tr>
                                <tr>
                                    <td><b>ARTICLE NR</b></td>
                                    <td>{{ optional($bom)->article_number ?? '-' }}</td>

                                </tr>
                                <tr>
                                    <td><b>ORDER NO.</b></td>
                                    <td>{{ optional($bom)->order_number ?? '-' }}</td>

                                </tr>
                                <tr>
                                    <td><b>BUYER</b></td>
                                    <td>{{ optional($bom)->buyer ?? '-' }}</td>

                                </tr>
                            </table>

                            {{-- ===== TABLE ===== --}}
                            <div style="max-height:300px; overflow-y:auto;">
                                <table class="table bom-table">

                                    <thead>
                                        <tr>
                                            <th>KOMPONEN</th>
                                            <th width="15%">QTY</th>
                                            <th width="15%">UNIT</th>
                                            <th width="30%">NOTES / FOTO TIMBANGAN</th>
                                        </tr>
                                    </thead>

                                 <tbody>

    @forelse(optional($bom)->groups ?? [] as $group)

        {{-- GROUP --}}
        <tr style="background:#ddd;font-weight:bold">
            <td colspan="4">
                {{ strtoupper($group->name) }}
            </td>
        </tr>

        {{-- ITEMS --}}
        @foreach($group->items as $item)
        <tr>
            <td style="padding-left:20px;">
                {{ $item->name }}
            </td>
            <td>{{ $item->qty ?? '-' }}</td>
            <td>{{ $item->unit ?? '-' }}</td>
            <td>-</td>
        </tr>
        @endforeach

    @empty
        <tr>
            <td colspan="4" class="text-center text-muted">
                ⚠️ Belum ada data BOM
            </td>
        </tr>
    @endforelse

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
            formData.append('master_sample', $('#master-sample').val());
            formData.append('_token', "{{ csrf_token() }}");

            $('.progress').show();
            console.log($('#master-sample').val());
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
        /*  */
        .bom-header {
            text-align: center;
            font-weight: bold;
            font-size: 16px;
            border: 1px solid #000;
            padding: 8px;
        }

        .bom-info td {
            border: 1px solid #000;
            padding: 6px;
        }

        .bom-table th,
        .bom-table td {
            border: 1px solid #000;
            padding: 6px;
        }

        .bom-table th {
            text-align: center;
        }

        /*  */
        .box {
            height: 100%;
        }

        .table-scroll {
            max-height: 400px;
            overflow-y: auto;
            position: relative;
        }

        /* freeze header */
        .table-scroll thead th {
            position: sticky;
            top: 0;
            /* background: #f5f5f5; */
            z-index: 2;
        }

        /* optional: freeze kolom pertama */
        .table-scroll th:first-child,
        .table-scroll td:first-child {
            position: sticky;
            left: 0;
            /* background: #fff; */
            z-index: 1;
        }

        /* biar header lebih tegas */
        .table-scroll thead th {
            border-bottom: 2px solid #ddd;
        }
    </style>
    @endsection
