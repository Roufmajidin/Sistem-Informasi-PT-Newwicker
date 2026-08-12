<div class="row mb-3">

    <div class="col-md-4 mt-4">

        <input
            type="text"
            id="searchBom"
            class="form-control"
            placeholder="Cari Name / Article Number...">

    </div>

</div>
<div id="global-loader" style="display:none">
    <div class="loader-content">

        <div class="spinner-border text-success"
             style="width:70px;height:70px">
        </div>

        <h4 class="mt-4">
            Sedang mencocokkan data...
        </h4>

        <small class="text-muted">
            Mohon tunggu sebentar
        </small>

    </div>
</div>
<div class="table-responsive mt-4">

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

            @foreach($boms as $bom)

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
                        href="/bom-produksi/show/{{$bom->id}}"
                           class="btn btn-info btn-sm btn-open-bom">


                        Detail

                    </a>

                    <a
                        href="/bom-produksi/edit/{{$bom->id}}"
    class="btn btn-warning btn-sm btn-open-bom">

                        <i class="fa fa-edit"></i>

                    </a>
                    <a
                        href="/bom-produksi/bom/{{$bom->id}}/export-excel"
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

            url: '/bom-produksi/delete/' + id,

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
        url: '/bom-produksi/bom/' + checkbox.data('id') + '/toggle-release',
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
$('#searchBom').on('keyup', function () {

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
