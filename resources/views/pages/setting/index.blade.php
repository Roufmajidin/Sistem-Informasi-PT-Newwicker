@extends('master.master')
@section('title', "Index QC")

@section('content')
<div class="padding">
    <div class="box">
        <div class="box-header">
            <h2>QC Progress</h2>
            <small>___</small>
        </div>

        <div class="box-body">
            {{-- FORM IMPORT --}}
            <div class="row">

                {{-- KATEGORI --}}
                <div class="col-md-6">
                    <div class="box">
                        <div class="box-header d-flex justify-content-between">
                            <h4>Kategori</h4>
                            <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#modalKategori">
                                + Add
                            </button>
                        </div>

                        <table class="table table-bordered">
                            <tr>
                                <th>#</th>
                                <th>Nama</th>
                            </tr>
                            @foreach($kategori as $k)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $k->kategori }}</td>
                            </tr>
                            @endforeach
                        </table>
                    </div>
                </div>

                {{-- CHECKPOINT --}}
                <div class="col-md-6">
                    <div class="box">
                        <div class="box-header d-flex justify-content-between">
                            <h4>Checkpoint</h4>
                            <button class="btn btn-success btn-sm" data-toggle="modal" data-target="#modalCheckpoint">
                                + Add
                            </button>
                        </div>
<div class="table-responsive">
                        <table class="table table-striped table-bordered">

                            <tr>
                                <th>#</th>
                                <th>Checkpoint</th>
                                <th>Kategori</th>
                            </tr>

    @foreach($checkpoint as $c)
        <tr class="checkpoint-row {{ $loop->iteration > 5 ? 'd-none extra-row' : '' }}">
            <td>{{ $loop->iteration }}</td>
            <td>{{ $c->name }}</td>
            <td>
                @if ($c->kategori->kategori == "Rangka")
                    <a href="#" class="btn btn-sm btn-primary">
                        {{ $c->kategori->kategori ?? '-' }}
                    </a>
                @else
                    <a href="#" class="btn btn-sm btn-danger">
                        {{ $c->kategori->kategori ?? '-' }}
                    </a>
                @endif
            </td>

    <td>
        <form action="{{ route('checkpoint.destroy', $c->id) }}" method="POST"
              onsubmit="return confirm('Hapus checkpoint ini?')">
            @csrf
            @method('DELETE')
            <button class="btn btn-danger btn-sm">
                Delete
            </button>
        </form>
    </td>


        </tr>
    @endforeach
                        </table>
</div>@if($checkpoint->count() > 5)
    <div class="text-center mt-2">
        <button id="btn-show-more" class="btn btn-outline-primary btn-sm">
            Show More
        </button>
    </div>
@endif
                    </div>
                </div>
                <!-- modal 1 -->
                <div class="modal fade" id="modalKategori">
                    <div class="modal-dialog">
                        <form method="POST" action="/setting/kategori">
                            @csrf
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5>Add Kategori</h5>
                                </div>
                                <div class="modal-body">
                                    <input type="text" name="nama" class="form-control" placeholder="Nama kategori">
                                </div>
                                <div class="modal-footer">
                                    <button class="btn btn-primary">Simpan</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <!-- modal 2 -->
                <!-- MODAL CHECKPOINT -->
                <div class="modal fade" id="modalCheckpoint" tabindex="-1">
                    <div class="modal-dialog modal-lg">

                        <div class="modal-content">
                            <form method="POST" action="{{ route('checkpoint.store.mass') }}">
                                @csrf

                                <div class="modal-header">
                                    <h4 class="modal-title">Add Checkpoint (Mass Input)</h4>
                                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                                </div>

                                <div class="modal-body">

                                    <div class="form-group">
                                        <label>Kategori</label>
                                        <select name="kategori_id" class="form-control" required>
                                            <option value="">-- Pilih Kategori --</option>
                                            @foreach($kategori as $k)
                                            <option value="{{ $k->id }}">{{ $k->kategori }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label>Paste Checkpoint</label>
                                        <textarea
                                            name="checkpoints_raw"
                                            rows="8"
                                            class="form-control"
                                            placeholder="Paste checkpoint di sini, pisahkan dengan ENTER / koma / titik dua"
                                            required></textarea>

                                        <small class="text-muted">
                                            ✔ ENTER / newline<br>
                                            ✔ koma (,)<br>
                                            ✔ tanda :
                                        </small>
                                    </div>

                                </div>

                                <div class="modal-footer">
                                    <button type="submit" class="btn btn-success">Simpan</button>
                                    <button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
                                </div>

                            </form>
                        </div>

                    </div>
                </div>

            </div>
        </div>


<script>
    document.addEventListener('DOMContentLoaded', function () {
        const btn = document.getElementById('btn-show-more');
        if (!btn) return;

        let expanded = false;

        btn.addEventListener('click', function () {
            const rows = document.querySelectorAll('.extra-row');

            rows.forEach(row => {
                row.classList.toggle('d-none');
            });

            expanded = !expanded;
            btn.textContent = expanded ? 'Show Less' : 'Show More';
        });
    });
</script>

        @endsection
