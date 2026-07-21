<div class="row mb-3">

    <div class="col-md-4">

        <input
            type="text"
            id="searchBoms"
            class="form-control"
            placeholder="Cari Name / Article Number...">

    </div>

</div>

<div class="table-responsive table-sticky mt-4">

<table class="table table-bordered" id="bomTable">
        <thead>

            <tr>

                <th>No</th>
                <th>Name</th>
                <th>Article Number</th>
                <th>release</th>
                <th width="200">Action</th>

            </tr>

        </thead>

        <tbody>

            @foreach($boms_released as $bom)

            <tr>

                <td>
                    {{ $loop->iteration }}
                </td>

                <td>
                    {{ $bom->name }}
                </td>

                <td>
                    {{ $bom->article_number }}
                </td>
                <td>
                <div class="form-check">
                    <input
                        type="checkbox"
                        class="form-check-input release-checkbox"
                        data-id="{{ $bom->id }}"
                        {{ $bom->released ? 'checked' : '' }}
                    >

                    <small class="release-date text-muted d-block">
                        {{ $bom->released_date ? \Carbon\Carbon::parse($bom->released_date)->format('d M Y H:i') : 'not yet' }}
                    </small>
                </div>
            </td>

                <td>

                    <a
                        href="{{ route('bom.show',$bom->id) }}"
                           class="btn btn-info btn-sm btn-open-bom">


                        Detail

                    </a>

                    <a
                        href="{{ route('bom.edit',$bom->id) }}"
    class="btn btn-warning btn-sm btn-open-bom">

                        <i class="fa fa-edit"></i>

                    </a>
                    <a
                        href="{{ route('bom.export.excel',$bom->id) }}"
                        class="btn btn-success">

                        <i class="fa fa-download"></i>



                    </a>
                    <button
    type="button"
    class="btn btn-danger btn-sm btn-delete-bom"
    data-id="{{ $bom->id }}">
    <i class="fa fa-trash"></i>
</button>
                </td>

            </tr>

            @endforeach

        </tbody>

    </table>

</div>
<script>
    $(document).on('click', '.btn-delete-bom', function () {

    let id = $(this).data('id');

    Swal.fire({

        title: 'Hapus BOM?',
        text: 'Semua Group, Item, Summary dan gambar BOM akan ikut dihapus.',
        icon: 'warning',

        showCancelButton: true,

        confirmButtonColor: '#d33',

        cancelButtonColor: '#6c757d',

        confirmButtonText: 'Ya, Hapus',

        cancelButtonText: 'Batal'

    }).then((result) => {

        if (!result.isConfirmed) return;

        $.ajax({

            url: '/bom/' + id,

            type: 'DELETE',

            data: {
                _token: '{{ csrf_token() }}'
            },

            beforeSend: function () {

                Swal.fire({

                    title: 'Menghapus...',
                    text: 'Mohon tunggu',

                    allowOutsideClick: false,

                    didOpen: () => {
                        Swal.showLoading();
                    }

                });

            },

            success: function (res) {

                Swal.fire({

                    icon: 'success',

                    title: 'Berhasil',

                    text: res.message

                }).then(() => {

                    location.reload();

                });

            },

            error: function (xhr) {

                Swal.fire({

                    icon: 'error',

                    title: 'Gagal',

                    text: xhr.responseJSON?.message ?? 'Terjadi kesalahan.'

                });

            }

        });

    });

});
    $(document).on('change', '.release-checkbox', function () {

    let checkbox = $(this);

    $.ajax({
        url: '/bom/' + checkbox.data('id') + '/toggle-release',
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            released: checkbox.is(':checked')
        },

        success: function (res) {

            let dateElement = checkbox
                .closest('td')
                .find('.release-date');

            if(res.released){
                dateElement.text(res.released_date);
            }else{
                dateElement.text('');
            }

            Swal.fire({
                toast:true,
                position:'top-end',
                timer:2000,
                showConfirmButton:false,
                icon:'success',
                title:res.message
            }).then(() => {
        location.reload();
    });

        },

        error:function(){

            checkbox.prop(
                'checked',
                !checkbox.is(':checked')
            );

            Swal.fire(
                'Error',
                'Gagal mengubah status.',
                'error'
            );

        }

    });

});
// SEARCH BOM
$('#searchBoms').on('keyup', function () {

    let keyword = $(this).val().toLowerCase();

    $('#bomTable tbody tr').each(function () {

        let name = $(this)
            .find('td:eq(1)')
            .text()
            .toLowerCase();

        let article = $(this)
            .find('td:eq(2)')
            .text()
            .toLowerCase();

        $(this).toggle(
            name.includes(keyword) ||
            article.includes(keyword)
        );

    });

});
$(document).on('click','.btn-open-bom',function(e){

    e.preventDefault();

    let url = $(this).attr('href');

    $('#global-loader')
        .css('display','flex')
        .hide()
        .fadeIn(150);

    setTimeout(function(){

        window.location.href = url;

    },150);

});
window.addEventListener('pageshow', function (event) {

    $('#global-loader').hide();

});
</script>

<style>
    .table-sticky{
    max-height: calc(100vh - 260px);
    overflow-y: auto;
    overflow-x: auto;
    border: 1px solid #dee2e6;
}

.table-sticky table{
    margin-bottom: 0;
}

.table-sticky thead th{
    position: sticky;
    top: 0;
    z-index: 20;
    background: #243447;
    color: #fff;
    white-space: nowrap;
    box-shadow: inset 0 -1px 0 #dee2e6;
}

/* Freeze kolom No */
.table-sticky th:first-child,
.table-sticky td:first-child{
    position: sticky;
    left: 0;
    z-index: 21;
    background: #fff;
}

/* Header kolom No */
.table-sticky thead th:first-child{
    background: #243447;
    color: #fff;
    z-index: 30;
}

/* Freeze kolom Action */
.table-sticky th:last-child,
.table-sticky td:last-child{
    position: sticky;
    right: 0;
    background: #fff;
    z-index: 21;
}

/* Header kolom Action */
.table-sticky thead th:last-child{
    background: #243447;
    color: #fff;
    z-index: 30;
}
    #global-loader{

    position:fixed;

    top:0;
    left:0;

    width:100%;
    height:100%;

    background:rgba(255,255,255,.96);

    z-index:999999;

    display:none;

    justify-content:center;
    align-items:center;

}

.loader-content{

    text-align:center;

}
</style>
