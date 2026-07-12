@extends('master.master')

@section('title','Edit BOM')

@section('content')

<div class="padding">

    @include('pages.bom.partials.create_bom')

</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>

let isEdit = true;

let bomId = {{ $bom->id }};

let bomData = @json($bomData);

$(document).ready(function(){

    // HEADER

    $('[name="item"]')
        .val(
            bomData.name
        );

    $('[name="article_code"]')
        .val(
            bomData.article_number
        );

    // DIMENSION

    $('[name="panjang"]')
        .val(
            bomData.panjang
        );

    $('[name="lebar"]')
        .val(
            bomData.lebar
        );

    $('[name="tinggi"]')
        .val(
            bomData.tinggi
        );

    // CARTON SIZE

    $('[name="carton_panjang"]')
        .val(
            bomData.carton_panjang
        );

    $('[name="carton_lebar"]')
        .val(
            bomData.carton_lebar
        );

    $('[name="carton_tinggi"]')
        .val(
            bomData.carton_tinggi
        );

    // LOADABILITY

    $('[name="loadability_pcs"]')
        .val(
            bomData.loadability_pcs
        );

    $('[name="loadability_cbm"]')
        .val(
            bomData.loadability_cbm
        );

    // IMAGE

    if(bomData.image){

        $('#preview').attr(
            'src',
            '/storage/' + bomData.image
        );

    }

    // GROUP & ITEM

    renderDraft(
        bomData
    );

});

</script>

@endsection
