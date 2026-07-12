@extends('master.master')
@section('title','BOM Produksi')

@section('content')

<div class="padding">
    <div class="box">

        <div class="box-header">
            <h2>BOM Production</h2>
        </div>

        <div class="box-body">

            <ul class="nav nav-tabs" role="tablist">

                <li class="nav-item active">
                    <a class="nav-link active"
                        data-toggle="tab"
                        href="#bom">
                        Bill Of Material
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

            </ul>

            <div class="tab-content p-a-3">

                <div class="tab-pane active" id="bom">
                    @include('pages.bom.partials.bill_of_material')
                </div>

                <div class="tab-pane" id="harga">
                    @include('pages.bom.partials.list_harga')
                </div>

                <div class="tab-pane" id="finishing">
                    @include('pages.bom.partials.material_finishing')
                </div>

                <div class="tab-pane" id="create-bom">
                    @include('pages.bom.partials.create_bom')
                </div>

            </div>

        </div>

    </div>
</div>

@include('pages.bom.partials.modal_add_harga')

@endsection
