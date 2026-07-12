<div class="row mb-3">

    <div class="col-md-4">
        <input type="text" id="searchMaterial" class="form-control" placeholder="Cari material...">
    </div>

    <div class="col-md-8 text-right">

        <button
    type="button"
    id="btnOpenModal"
    class="btn btn-primary btn-sm">

    Add Rows

</button>

    </div>

</div>
<div class="table-wrapper">

    <table class="table table-bordered table-striped mb-0">

        <thead>
            <tr>
                <th width="50">No</th>
                <th>Nama Material</th>
                <th>Harga</th>
                <th>Satuan</th>
                <th width="100">Action</th>
            </tr>
        </thead>

        <tbody>

            @forelse($materialPrices ?? [] as $item)

                <tr>

                    <td>{{ $loop->iteration }}</td>

                    <td>
                        <div class="position-relative">

                            <input type="text" class="form-control update-field" data-id="{{ $item->id }}"
                                data-column="nama_material" value="{{ $item->nama_material }}">

                            <small class="save-status text-muted" style="display:none;">
                                Press Enter to save
                            </small>

                        </div>
                    </td>

                    <td>
                        <input type="number" class="form-control update-field" data-id="{{ $item->id }}"
                            data-column="harga"
                            value="{{ number_format($item->harga,0,',','.') }}">

                        <small class="save-status text-muted" style="display:none;">
                            Press Enter to save
                        </small>
                    </td>

                    <td>
                        <input type="text" class="form-control update-field" data-id="{{ $item->id }}"
                            data-column="satuan" value="{{ $item->satuan }}">

                        <small class="save-status text-muted" style="display:none;">
                            Press Enter to save
                        </small>
                    </td>
                    <td>

                        <button class="btn btn-danger btn-xs btn-delete" data-id="{{ $item->id }}">

                            Delete

                        </button>

                    </td>

                </tr>

            @empty

                <tr>
                    <td colspan="5" class="text-center">
                        Belum ada data
                    </td>
                </tr>

            @endforelse

        </tbody>

    </table>

</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
    $('#btnOpenModal').click(function(e){

    e.preventDefault();
    e.stopPropagation();

    $('#modalAddHarga').modal('show');

});
    // SAVE
    $(document).on('click', '#btn-save-material', function () {
        // alert("klik")
        let materials = $('#bulk_material').val();

        if (materials == '') {
            alert('Paste material terlebih dahulu');
            return;
        }

        $.ajax({

            url: "{{ route('material-price.bulk-store') }}",

            type: "POST",

            data: {

                _token: "{{ csrf_token() }}",

                materials: materials

            },

            success: function (res) {

                if (res.success) {

                    $('#modalAddHarga').modal('hide');

                    location.reload();

                }

            }

        });

    });
    // DELETE
    $(document).on('click', '.btn-delete', function () {

        if (!confirm('Hapus material ini ?')) {
            return;
        }

        let id = $(this).data('id');

        $.ajax({

            url: '/material-price/delete/' + id,

            type: 'DELETE',

            data: {
                _token: "{{ csrf_token() }}"
            },

            success: function (res) {

                location.reload();

            }

        });

    });
    // UPDATE
    $(document).on('keypress', '.update-field', function (e) {

        if (e.which != 13) {
            return;
        }

        let row = $(this).closest('tr');

        let id = $(this).data('id');

        let status = $(this)
            .closest('td')
            .find('.save-status');

        status
            .show()
            .text('Saving...')
            .css('opacity', '1');

        $.ajax({

            url: '/material-price/update/' + id,

            type: 'POST',

            data: {

                _token: "{{ csrf_token() }}",

                nama_material: row.find('[data-column="nama_material"]').val(),

                harga: row.find('[data-column="harga"]').val(),

                satuan: row.find('[data-column="satuan"]').val()

            },

            success: function () {

                status
                    .text('Saved ✓');

                setTimeout(function () {

                    status.fadeOut();

                }, 1500);

            }

        });

    });
    // cari

    $('#searchMaterial').on('keyup', function () {

        let keyword = $(this).val().toLowerCase();

        $('table tbody tr').each(function () {

            let material = $(this)
                .find('[data-column="nama_material"]')
                .val()
                .toLowerCase();

            let satuan = $(this)
                .find('[data-column="satuan"]')
                .val();

            satuan = satuan ? satuan.toLowerCase() : '';

            if (
                material.includes(keyword) ||
                satuan.includes(keyword)
            ) {
                $(this).show();
            } else {
                $(this).hide();
            }

        });

    });

    // helper
    $(document).on('input', '.update-field', function () {

        $(this)
            .closest('td')
            .find('.save-status')
            .show()
            .text('Press Enter to save')
            .css('opacity', '.6');

    });

</script>
<style>
    .save-status {
        font-size: 11px;
        display: block;
        margin-top: 2px;
    }

    .update-field {
        transition: .2s;
    }

    .update-field:focus {
        background: #fffce8;
    }

    .table-wrapper {
        max-height: 600px;
        overflow-y: auto;
    }

    .table-wrapper thead th {
        position: sticky;
        top: 0;
        z-index: 10;
        background: #243447;
        color: white;
    }
.table-wrapper th:first-child,
.table-wrapper td:first-child {
    position: sticky;
    left: 0;
    background: white;
    z-index: 5;
}

.table-wrapper thead th:first-child {
    background: #243447;
    z-index: 15;
}
.table-wrapper {
    height: calc(100vh - 250px);
}
</style>
