@extends('master.master')
@section('title', 'Agenda')
@section('content')

<div class="row">

    {{-- ================= KIRI : FORM ================= --}}
    <div class="col-md-6">
        <div class="box">
            <div class="box-header">
                <h2>Hallo, Newwicker</h2>
                <small>Silahkan isi sesuai form dibawah ini</small>
            </div>

            <div class="box-divider m-0"></div>

            <div class="box-body">
                <form action="{{ route('agenda.store') }}" method="POST">
                    @csrf

                    {{-- Jenis Agenda --}}
                    <div class="form-group row">
                        <label class="col-sm-3 form-control-label">Jenis Agenda</label>
                        <div class="col-sm-9">
                            <select name="jenis_agenda" class="form-control" required>
                                <option value="">-- Pilih Jenis --</option>
                                <option value="photo sample">Photo Sample</option>
                                <option value="photo produksi">Photo Produksi</option>
                                <option value="Maintenance">Maintenance</option>
                            </select>
                        </div>
                    </div>

                    {{-- Kode Agenda --}}
                    <div class="form-group row d-none" id="kode-field">
                        <label class="col-sm-3 form-control-label" id="kode-label">Kode</label>
                        <div class="col-sm-9">
                            <input type="text" name="kode_agenda" id="kode-input"
                                class="form-control">
                            <small class="text-muted" id="kode-help"></small>
                        </div>
                    </div>

                    {{-- Dibuat Oleh --}}
                    <div class="form-group row">
                        <label class="col-sm-3 form-control-label">Dibuat Oleh</label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control"
                                value="{{ auth()->user()->name }}" readonly>
                            <input type="hidden" name="dibuat_oleh"
                                value="{{ auth()->user()->name }}">
                        </div>
                    </div>

                    {{-- Tanggal --}}
                    <div class="form-group row">
                        <label class="col-sm-3 form-control-label">Tanggal</label>
                        <div class="col-sm-9">
                            <input type="date" name="tanggal" class="form-control"
                                value="{{ now()->toDateString() }}">
                        </div>
                    </div>

                    {{-- Status --}}
                    <div class="form-group row">
                        <label class="col-sm-3 form-control-label">Status</label>
                        <div class="col-sm-9">
                            <select name="status" class="form-control" required>
                                <option value="">-- Pilih Status --</option>
                                <option value="urgent">Urgent</option>
                                <option value="non urgent">Non Urgent</option>
                            </select>
                        </div>
                    </div>

                    {{-- Catatan --}}
                    <div class="form-group row">
                        <label class="col-sm-3 form-control-label">Catatan</label>
                        <div class="col-sm-9">
                            <textarea name="catatan" class="form-control" rows="3"></textarea>
                        </div>
                    </div>

                    {{-- Submit --}}
                    <div class="form-group row">
                        <div class="col-sm-offset-3 col-sm-9">
                            <button type="submit" class="btn btn-primary">
                                Simpan Agenda
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- ================= KANAN : TABEL ================= --}}

    <div class="col-md-6">
        <div class="box">
            <div class="box-header">
                <h3>Daftar Agenda</h3>
                <small>Agenda yang sudah dibuat</small>
            </div>

            <div class="box-divider m-0"></div>

            <div class="box-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Tanggal</th>
                                <th>Jenis</th>
                                <th>Kode</th>
                                <th>Status</th>
                                <th>Dibuat Oleh</th>
                                <th>Remark</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($agendas as $agenda)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $agenda->tanggal->format('d-m-Y') }}</td>
                                <td>{{ ucfirst($agenda->jenis_agenda) }}</td>
                                <td>{{ $agenda->kode_agenda ?? '-' }}</td>
                                <td>
                                    <span class="label {{ $agenda->status == 'urgent' ? 'label-danger' : 'label-success' }}">
                                        {{ ucfirst($agenda->status) }}
                                    </span>
                                </td>
                                <td>{{ $agenda->dibuat_oleh }}</td>

                                {{-- REMARK --}}

                                <td>
                                    @if ($agenda->remark_rouf)
                                    {{ $agenda->remark_rouf }}
                                    @else
                                    <em class="text-muted">Belum ada</em>
                                    @endif
                                </td>


                                {{-- AKSI --}}

                                <td width="140">
                                    @if (in_array(auth()->user()->karyawan_id, [184, 199, 181]))
                                <button type="button"
    class="btn btn-sm btn-warning btn-remark"
    data-id="{{ $agenda->id }}"
    data-remark="{{ $agenda->remark_rouf }}">
    Add / Update
</button>
@endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center">Belum ada agenda</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

</div>
<div class="modal fade" id="remarkModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <form method="POST" action="{{ route('agenda.remark') }}">
            @csrf
            <input type="hidden" name="agenda_id" id="agendaIdInput">

            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Update Remark</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <h8 id="agendaIdText"></h8>
                    <div class="form-group">
                        <label>Remark</label>
                        <textarea name="remark_rouf"
                            id="remarkInput"
                            class="form-control"
                            rows="3"
                            placeholder="Tambahkan remark..."></textarea>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button"
                        class="btn btn-secondary"
                        data-dismiss="modal">
                        Batal
                    </button>
                    <button type="submit"
                        class="btn btn-primary">
                        Simpan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
$(document).ready(function () {

    $(document).on('click', '.btn-remark', function () {

        let agendaId = $(this).data('id');
        let remark   = $(this).data('remark');

        $('#agendaIdInput').val(agendaId);
        $('#agendaIdText').text('Agenda ID: ' + agendaId);
        $('#remarkInput').val(remark ?? '');

        // 🔥 INI KUNCI
        $('#remarkModal').modal('show');
    });

});
</script>

{{-- ================= SCRIPT ================= --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const jenisAgenda = document.querySelector('select[name="jenis_agenda"]');
        const field = document.getElementById('kode-field');
        const input = document.getElementById('kode-input');
        const label = document.getElementById('kode-label');
        const help = document.getElementById('kode-help');

        function resetField() {
            field.classList.add('d-none');
            input.removeAttribute('required');
            input.value = '';
            help.innerText = '';
        }

        function setupField(prefix) {
            field.classList.remove('d-none');
            input.setAttribute('required', 'required');
            input.value = prefix + ' ';
            help.innerText = 'Ketik 4 angka saja, contoh: 2512';
        }

        jenisAgenda.addEventListener('change', function() {
            resetField();
            if (this.value === 'photo sample') {
                label.innerText = 'Kode Photo Sample';
                setupField('NWS');
            }
            if (this.value === 'photo produksi') {
                label.innerText = 'Kode Photo Produksi';
                setupField('NW');
            }
        });

        input.addEventListener('input', function() {
            let digits = this.value.replace(/\D/g, '').slice(0, 4);
            const prefix = jenisAgenda.value === 'photo sample' ? 'NWS' : 'NW';

            if (digits.length > 2) {
                this.value = `${prefix} ${digits.slice(0,2)} - ${digits.slice(2)}`;
            } else {
                this.value = `${prefix} ${digits}`;
            }
        });
    });
</script>


@endsection
