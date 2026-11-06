
@extends('master.master')

@section('title','BOM Samples')

@section('content')

@php
    $isFactory = str_starts_with(
        strtolower(auth()->user()->email ?? ''),
        'factory@'
    );
@endphp

<div class="padding">
    <div class="box">

        <div class="box-header">
            <h2>BOM Samples</h2>
        </div>

        <div class="box-body">

            {{-- GLOBAL LOADER --}}
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

            {{-- TAB NAVIGATION --}}
            <ul class="nav nav-tabs" role="tablist">

                {{-- BOM DRAFT --}}
                @if (!$isFactory)
                    <li class="nav-item active">
                        <a class="nav-link active"
                           data-toggle="tab"
                           href="#bom">
                            BOM Draft
                        </a>
                    </li>
                @endif

                {{-- LIST HARGA --}}
                <li class="nav-item">
                    <a class="nav-link"
                       data-toggle="tab"
                       href="#harga">
                        List Harga
                    </a>
                </li>

                {{-- MATERIAL FINISHING --}}
                <li class="nav-item">
                    <a class="nav-link"
                       data-toggle="tab"
                       href="#finishing">
                        Material Finishing
                    </a>
                </li>

                {{-- CREATE BOM --}}
                <li class="nav-item">
                    <a class="nav-link"
                       data-toggle="tab"
                       href="#create-bom">
                        Create BOM
                    </a>
                </li>

                {{-- RELEASED BOM --}}
                <li class="nav-item {{ $isFactory ? 'active' : '' }}">
                    <a class="nav-link {{ $isFactory ? 'active' : '' }}"
                       data-toggle="tab"
                       href="#released-bom">
                        Released BOM
                    </a>
                </li>

                {{-- CAD --}}
                <li class="nav-item">
                    <a class="nav-link"
                       data-toggle="tab"
                       href="#cad">
                        C A D
                    </a>
                </li>

            </ul>

            {{-- TAB CONTENT --}}
            <div class="tab-content p-a-3">

                {{-- BOM DRAFT --}}
                @if (!$isFactory)
                    <div class="tab-pane active" id="bom">
                        @include('pages.bom.partials.bill_of_material')
                    </div>
                @endif

                {{-- LIST HARGA --}}
                <div class="tab-pane" id="harga"></div>

                {{-- MATERIAL FINISHING --}}
                <div class="tab-pane" id="finishing"></div>

                {{-- CREATE BOM --}}
                <div class="tab-pane" id="create-bom"></div>

                {{-- RELEASED BOM --}}
                <div class="tab-pane {{ $isFactory ? 'active' : '' }}"
                     id="released-bom">
                </div>

                {{-- CAD --}}
                <div class="tab-pane" id="cad"></div>

            </div>

        </div>

    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

@include('pages.bom.partials.modal_add_harga')

<script>

$(document).ready(function () {

    const isFactory = @json($isFactory);

    /*
    |--------------------------------------------------------------------------
    | CREATE BOM
    |--------------------------------------------------------------------------
    */

    $.get('/bom/create-partial', function (html) {

        $('#create-bom').html(html);

        console.log('Create BOM Loaded');
        console.log(typeof updateDimensionCalculation);

    });

    /*
    |--------------------------------------------------------------------------
    | LIST HARGA
    |--------------------------------------------------------------------------
    */

    $('a[href="#harga"]').one('click', function () {

        $('#harga').load('/bom/harga-partial');

    });

    /*
    |--------------------------------------------------------------------------
    | MATERIAL FINISHING
    |--------------------------------------------------------------------------
    */

    $('a[href="#finishing"]').one('click', function () {

        $('#finishing').load('/bom/finishing-partial');

    });

    /*
    |--------------------------------------------------------------------------
    | RELEASED BOM
    |--------------------------------------------------------------------------
    */

    let releasedLoaded = false;

    function loadReleasedBom() {

        if (releasedLoaded) {
            return;
        }

        releasedLoaded = true;

        $('#released-bom').load(
            '/bom/released-partial',
            function (response, status, xhr) {

                if (status === 'error') {

                    releasedLoaded = false;

                    console.error(
                        'Released BOM Error:',
                        xhr.status
                    );

                } else {

                    console.log('Released BOM Loaded');

                }

            }
        );

    }

    $('a[href="#released-bom"]').on('click', function () {

        loadReleasedBom();

    });

    /*
    |--------------------------------------------------------------------------
    | INITIAL TAB
    |--------------------------------------------------------------------------
    */

    if (isFactory) {

        // Factory langsung masuk Released BOM
        loadReleasedBom();

        console.log('FACTORY ACCOUNT: RELEASED BOM ACTIVE');

    } else {

        console.log('NON FACTORY ACCOUNT: BOM DRAFT ACTIVE');

    }

});

</script>

@endsection
