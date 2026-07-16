<style>
    .table-wrapper {
        max-height: 650px;
        overflow-y: auto;
    }
    .table-wrapper thead th {
        position: sticky;
        top: 0;
        background: #3c4b64;
        color: white;
        z-index: 10;
    }
    .editable {
        cursor: pointer;
    }
    .editable:hover {
        background: #fff8d6;
    }
    .save-status {
        font-size: 11px;
        color: #999;
    }
</style>
<div class="row mb-3">
    <div class="col-md-4">
        <input type="text" id="searchFinishing" class="form-control" placeholder="Cari finishing...">
    </div>
    <div class="col-md-8 text-right">
     <button
    type="button"
    class="btn btn-primary btn-sm"
    id="btnOpenFinishingModal">

    <i class="fa fa-plus"></i>
    Add Rows

</button>
    </div>
</div>
<div class="table-wrapper">
    <table class="table table-bordered table-striped" id="finishingTable">
        <thead>
            <tr>
                <th width="50">No</th>
                <th>Nama</th>
                <th>Propan</th>
                <th>Diva</th>
                <th>Warna Prima</th>
                <th>Legenda</th>
                <th width="100">Action</th>
            </tr>
        </thead>
        <tbody>
            @forelse($materialFinishings as $item)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td class="editable" data-id="{{ $item->id }}" data-column="nama">
                        {{ $item->nama }}
                    </td>
                    <td class="editable" data-id="{{ $item->id }}" data-column="jenis_propan">
                        {{ number_format($item->jenis_propan,0,',','.') }}
                    </td>
                    <td class="editable" data-id="{{ $item->id }}" data-column="jenis_diva">
                        {{ number_format($item->jenis_diva,0,',','.') }}
                    </td>
                    <td class="editable" data-id="{{ $item->id }}" data-column="jenis_warna_prima">
                        {{ number_format($item->jenis_warna_prima,0,',','.') }}
                    </td>
                    <td class="editable" data-id="{{ $item->id }}" data-column="jenis_legenda">
                        {{ number_format($item->jenis_legenda,0,',','.') }}
                    </td>
                    <td>
                        <button class="btn btn-danger btn-xs btn-delete-finishing" data-id="{{ $item->id }}">
                            Delete
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center">
                        Belum ada data
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
{{-- MODAL --}}
<div class="modal fade" id="modalAddFinishing">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    Add Material Finishing
                </h5>
            </div>
            <div class="modal-body">
                <textarea id="bulk_finishing" class="form-control" rows="10" placeholder="Abaca Loreng,9000,10000,12000,15000
Adjustable Glide,2000,3000,5000,6000"></textarea>
                <small class="text-muted">
                    Format:
                    Nama,Propan,Diva,Warna Prima,Legenda
                </small>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-dismiss="modal">
                    Close
                </button>
                <button class="btn btn-success" id="btn-save-finishing">
                    Simpan
                </button>
            </div>
        </div>
    </div>
</div>
<script>
    let asc = true;

$('#sortBom').on('click', function () {

    let btn = $(this);

    btn.prop('disabled', true);

    btn.html('<span class="spinner-border spinner-border-sm"></span>');

    setTimeout(function () {

        let tbody = $('#finishingTable tbody');

        let rows = tbody.find('tr').filter(function () {
            return $(this).find('[data-column="nama"]').length;
        }).get();

        rows.sort(function (a, b) {

            let nameA = $(a)
                .find('[data-column="nama"]')
                .text()
                .trim()
                .toLowerCase();

            let nameB = $(b)
                .find('[data-column="nama"]')
                .text()
                .trim()
                .toLowerCase();

            return asc
                ? nameA.localeCompare(nameB)
                : nameB.localeCompare(nameA);

        });

        $.each(rows, function (_, row) {
            tbody.append(row);
        });

        asc = !asc;

        btn.html(
            asc
                ? '<i class="fa fa-sort-alpha-asc"></i>'
                : '<i class="fa fa-sort-alpha-desc"></i>'
        );

        btn.prop('disabled', false);

    }, 200);

});
    $(document).on(
    'click',
    '#btnOpenFinishingModal',
    function(e){

        e.preventDefault();
        e.stopPropagation();

        $('#modalAddFinishing')
            .modal('show');

    }
);
    $(function () {
        // SEARCH
        $('#searchFinishing').on('keyup', function () {
            let keyword = $(this).val().toLowerCase();
            $('#finishingTable tbody tr').each(function () {
                let found = false;
                $(this).find('td').each(function () {
                    if (
                        $(this)
                        .text()
                        .toLowerCase()
                        .includes(keyword)
                    ) {
                        found = true;
                    }
                });
                $(this).toggle(found);
            });
        });
    });
    // BULK STORE
    $(document).on(
        'click',
        '#btn-save-finishing',
        function () {
            $.ajax({
                url: "{{ route('material-finishing.bulk-store') }}",
                type: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    materials: $('#bulk_finishing').val()
                },
                success: function () {
                    location.reload();
                }
            });
        });
    // DELETE
    $(document).on(
        'click',
        '.btn-delete-finishing',
        function () {
            if (!confirm('Hapus data ini ?')) {
                return;
            }
            let id =
                $(this).data('id');
            $.ajax({
                url: '/material-finishing/delete/' + id,
                type: 'DELETE',
                data: {
                    _token: "{{ csrf_token() }}"
                },
                success: function () {
                    location.reload();
                }
            });
        });
    // INLINE EDIT
    $(document).on(
        'click',
        '.editable',
        function () {
            if (
                $(this)
                .find('input')
                .length
            ) {
                return;
            }
            let value =
                $(this)
                .text()
                .trim()
                .replace(/\./g, '');
            $(this).html(
                '<input type="text" class="form-control finishing-input" value="' + value + '">' +
                '<small class="save-status">Press Enter to save</small>'
            );
            $(this)
                .find('input')
                .focus();
        });
    // SAVE ENTER
    $(document).on(
        'keypress',
        '.finishing-input',
        function (e) {
            if (e.which != 13) {
                return;
            }
            let td =
                $(this)
                .closest('td');
            let id =
                td.data('id');
            let column =
                td.data('column');
            let value =
                $(this).val();
            td.html('Saving...');
            $.ajax({
                url: '/material-finishing/update/' + id,
                type: 'POST',
                data: {
                    _token: "{{ csrf_token() }}",
                    column: column,
                    value: value
                },
                success: function () {
                    if (
                        column != 'nama'
                    ) {
                        td.html(
                            Number(value)
                            .toLocaleString('id-ID')
                        );
                    } else {
                        td.html(value);
                    }
                }
            });
        });
</script>
