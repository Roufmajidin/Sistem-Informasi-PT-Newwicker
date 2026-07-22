@extends('master.master')
@section('title','BOM Produksi')

@section('content')

<div class="padding">
    <div class="box">

        <div class="box-header">
            <h2>BOM Production</h2>
        </div>

        <div class="box-body">
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
            <ul class="nav nav-tabs" role="tablist">

                <li class="nav-item active">
                    <a class="nav-link active"
                        data-toggle="tab"
                        href="#bom">
                        BOM Draft
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link"
                        data-toggle="tab"
                        href="#harga">
                        List Harga
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link"
                        data-toggle="tab"
                        href="#finishing">
                        Material Finishing
                    </a>
                </li>

                <li class="nav-item">
                    <a class="nav-link"
                        data-toggle="tab"
                        href="#create-bom">
                        Create BOM
                    </a>
                </li>
                 <li class="nav-item">
                    <a class="nav-link"
                        data-toggle="tab"
                        href="#released-bom">
                        Released BOM
                    </a>
                </li>
                 <li class="nav-item">
                    <a class="nav-link"
                        data-toggle="tab"
                        href="#cad">
                        C A D
                    </a>
                </li>

            </ul>

            <div class="tab-content p-a-3">

               <div class="tab-pane active" id="bom">
                @include('pages.bom.partials.bill_of_material')
                </div>

                <div class="tab-pane" id="harga"></div>

                <div class="tab-pane" id="finishing"></div>

                <div class="tab-pane" id="create-bom"></div>

                <div class="tab-pane" id="released-bom"></div>
                <div class="tab-pane" id="cad"></div>

            </div>

        </div>

    </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

@include('pages.bom.partials.modal_add_harga')
<script>
   $.get('/bom/create-partial', function (html) {

    $('#create-bom').html(html);

    console.log('Create BOM Loaded');
    console.log(typeof updateDimensionCalculation);

});

$('a[href="#harga"]').one('click', function () {

    $('#harga').load('/bom/harga-partial');

});

$('a[href="#finishing"]').one('click', function () {

    $('#finishing').load('/bom/finishing-partial');

});

$('a[href="#released-bom"]').one('click', function () {

    $('#released-bom').load('/bom/released-partial');

});
</script>
@endsection
