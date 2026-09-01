<div class="row mb-3">

  <div class="col-md-4">

        <div class="input-group">

            <input
                type="text"
                id="searchMaterial"
                class="form-control"
                placeholder="Cari material...">

            <button
                class="btn btn-outline-secondary"
                id="sortMaterial"
                title="Sort Nama">

                <i class="fa fa-sort-alpha-asc"></i>

            </button>

        </div>

    </div>

    <div class="col-md-8 text-end">

        <button
            type="button"
            id="btnOpenModal"
            class="btn btn-primary btn-sm">

            Tambha material

        </button>

    </div>



</div>
<div class="table-wrapper">

    <table
    id="materialTable"
    class="table table-bordered table-striped mb-0">

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
                        <input
                            type="text"
                            inputmode="decimal"
                            class="form-control update-field harga-field"
                            data-id="{{ $item->id }}"
                            data-column="harga"
                            value="{{ rtrim(rtrim(number_format((float) $item->harga, 2, ',', '.'), '0'), ',') }}"
                            autocomplete="off">

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
{{-- <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> --}}

<script>
  // =========================
// SEARCH
// =========================
$('#searchMaterial').on('keyup', function () {

    let keyword = $(this).val().toLowerCase();

    $('#materialTable tbody tr').each(function () {

        let material = (
            $(this)
            .find('[data-column="nama_material"]')
            .val() || ''
        ).toLowerCase();

        let satuan = (
            $(this)
            .find('[data-column="satuan"]')
            .val() || ''
        ).toLowerCase();

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

// =========================
// SORT
// =========================

let asc = true;

$('#sortMaterial').on('click', function () {

    let btn = $(this);

    btn.prop('disabled', true);

    btn.html(
        '<span class="spinner-border spinner-border-sm"></span>'
    );

    setTimeout(function () {

        let tbody = $('#materialTable tbody');

        let rows = tbody.find('tr').filter(function () {

            return $(this)
                .find('[data-column="nama_material"]')
                .length;

        }).get();

        rows.sort(function (a, b) {

            let nameA = (
                $(a)
                .find('[data-column="nama_material"]')
                .val() || ''
            ).toLowerCase();

            let nameB = (
                $(b)
                .find('[data-column="nama_material"]')
                .val() || ''
            ).toLowerCase();

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
    $('#btnOpenModal').click(function(e){

    e.preventDefault();
    e.stopPropagation();

  const modal = new bootstrap.Modal(
    document.getElementById('modalAddHarga')
);

modal.show();

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

            url: "/cog-material-price/store",

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

            url: '/cog-material-price/delete/' + id,

            type: 'DELETE',

            data: {
                _token: "{{ csrf_token() }}"
            },

            success: function (res) {

                location.reload();

            }

        });

    });
    // =========================
    // UPDATE
    // =========================

  function normalizeHarga(value) {

    value = String(value ?? '').trim();

    if (value === '') {
        return '';
    }

    // Hapus spasi
    value = value.replace(/\s/g, '');


    /*
     * FORMAT INDONESIA
     *
     * 3.596.400
     * 3.596.400,50
     * 1.500
     * 1.500,25
     *
     * menjadi:
     *
     * 3596400
     * 3596400.50
     * 1500
     * 1500.25
     */


    // Ada koma = koma dianggap desimal
    if (value.includes(',')) {

        value = value
            .replace(/\./g, '')
            .replace(',', '.');

        return value;
    }


    /*
     * Tidak ada koma.
     *
     * Kalau titik lebih dari satu:
     *
     * 3.596.400
     *
     * pasti format ribuan.
     */

    const dotCount =
        (value.match(/\./g) || []).length;


    if (dotCount > 1) {

        return value.replace(/\./g, '');

    }


    /*
     * Satu titik.
     *
     * 3.596 -> 3596
     *
     * Tetapi:
     *
     * 3.5 -> 3.5
     */

    if (
        dotCount === 1 &&
        /^\d+\.\d{3}$/.test(value)
    ) {

        return value.replace('.', '');

    }


    /*
     * Contoh:
     *
     * 3.5
     * 12.25
     *
     * dianggap angka desimal.
     */

    return value;
}
    function showSaveStatus(status, text, color) {

        status
            .stop(true, true)
            .show()
            .text(text)
            .css({
                opacity: '1',
                color: color || ''
            });
    }

    $(document).on('keypress', '.update-field', function (e) {

        if (e.which !== 13) {
            return;
        }

        e.preventDefault();

        const input = $(this);
        const row = input.closest('tr');
        const id = input.data('id');

        const status = input
            .closest('td')
            .find('.save-status');

        /*
         * Cegah double request pada row yang sama.
         * Ini penting agar dua request tidak saling menimpa.
         */
        if (row.data('saving')) {
            return;
        }

        row.data('saving', true);

        // Semua field dalam row dikunci selama proses save
        row.find('.update-field').prop('disabled', true);

        showSaveStatus(status, 'Saving...', '#667085');

        let harga = row
            .find('[data-column="harga"]')
            .val();

        harga = normalizeHarga(harga);

        $.ajax({

            url: '/cog-material-price/update/' + id,

            type: 'POST',

            timeout: 10000,

            data: {

                _token: "{{ csrf_token() }}",

                nama_material: row
                    .find('[data-column="nama_material"]')
                    .val(),

                harga: harga,

                satuan: row
                    .find('[data-column="satuan"]')
                    .val()

            },

            success: function (res) {

                /*
                 * Jika controller mengembalikan success=false,
                 * anggap sebagai gagal.
                 */
                if (res && res.success === false) {

                    showSaveStatus(
                        status,
                        res.message || 'Gagal menyimpan ✕',
                        '#dc2626'
                    );

                    return;
                }

                showSaveStatus(
                    status,
                    'Saved ✓',
                    '#16a34a'
                );

                setTimeout(function () {

                    status.fadeOut(300);

                }, 1500);

            },

            error: function (xhr, textStatus) {

                console.error(
                    'Update material gagal:',
                    xhr.status,
                    xhr.responseText
                );

                let message = 'Gagal menyimpan ✕';

                if (textStatus === 'timeout') {

                    message = 'Server terlalu lama merespons ✕';

                } else if (xhr.status === 419) {

                    message = 'Session expired, refresh halaman ✕';

                } else if (xhr.status === 422) {

                    message = 'Data tidak valid ✕';

                } else if (xhr.status >= 500) {

                    message = 'Server error ✕';

                }

                showSaveStatus(
                    status,
                    message,
                    '#dc2626'
                );

            },

            complete: function () {

                row.data('saving', false);

                // Kembalikan field agar bisa diedit lagi
                row.find('.update-field').prop('disabled', false);

            }

        });

    });

    // helper
    $(document).on('input', '.update-field', function () {

        const row = $(this).closest('tr');

        // Jangan mengubah status saat request masih berjalan
        if (row.data('saving')) {
            return;
        }

        $(this)
            .closest('td')
            .find('.save-status')
            .show()
            .text('Press Enter to save')
            .css({
                opacity: '.6',
                color: ''
            });

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

    .update-field:disabled {
        opacity: .75;
        cursor: wait;
    }

    .harga-field {
        text-align: right;
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