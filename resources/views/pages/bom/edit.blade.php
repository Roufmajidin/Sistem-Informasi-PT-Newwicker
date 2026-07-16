@extends('master.master')

@section('title','Edit BOM')

@section('content')

<div class="padding">

    @include('pages.bom.partials.create_bom')

</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>

  let isEdit = @json($isEdit);

let bomId = {{ $bom->id }};

let bomData = @json($bomData);

$(document).ready(function(){

    // HEADER
    $('[name="item"]').val(bomData.name);
    $('[name="article_code"]').val(bomData.article_number);

    // DIMENSION
    $('[name="panjang"]').val(bomData.panjang);
    $('[name="lebar"]').val(bomData.lebar);
    $('[name="tinggi"]').val(bomData.tinggi);

    // CARTON SIZE
    $('[name="carton_panjang"]').val(bomData.carton_panjang);
    $('[name="carton_lebar"]').val(bomData.carton_lebar);
    $('[name="carton_tinggi"]').val(bomData.carton_tinggi);

    // LOADABILITY
    $('[name="loadability_pcs"]').val(bomData.loadability_pcs);
    $('[name="loadability_cbm"]').val(bomData.loadability_cbm);

    // IMAGE
    if (bomData.image) {
        $('#preview').attr(
            'src',
            '/storage/' + bomData.image
        );
    }

    // GROUP & ITEM
    renderDraft(bomData);

    // ==========================
    // VIEW ONLY
    // ==========================
    if (!isEdit) {

        // disable semua input
        $('input, textarea, select').prop('disabled', true);

        // input readonly tetap terlihat normal
        $('input[readonly]').prop('disabled', false);

        // sembunyikan tombol aksi
        $('#btn-update-bom').hide();
        $('#btn-save-bom').hide();
        $('#btn-add-header').hide();
        $('#btn-add-summary').hide();

        $('.btn-add-child').hide();
        $('.btn-add-sub-price').hide();

        $('.btn-remove-child').hide();
        $('.btn-remove-header').hide();
        $('.btn-remove-sub-price').hide();
        $('.btn-remove-summary').hide();

        $('.btn-select-material').hide();

        // sembunyikan upload gambar
        $('#bom_image').hide();
    }

});
</script>

@endsection
