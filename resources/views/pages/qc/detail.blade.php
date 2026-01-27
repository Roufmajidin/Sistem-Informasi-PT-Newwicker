@extends('master.master')
@section('title', "detail QC")

@section('content')
<div class="padding">
    <div class="row">

        {{-- LEFT --}}
        <div class="col-md-8">
            <div class="box-header">
                <h2>QC Progress</h2>
                <input type="hidden" id="input-jenis" name="jenis">
                <input type="hidden" id="detail-po-id" name="detail_po_id">


                <small>___</small>
            </div>
            {{-- ORDER DETAILS --}}
            <div class="box m-b">
                <div class="box-header d-flex justify-content-between align-items-center">
                    <h3 class="no-margin">Order Details</h3>

                    <div class="dropdown">
                         <button id="btn-jenis"
        class="btn btn-success dropdown-toggle"
        data-toggle="dropdown"
        aria-haspopup="true"
        aria-expanded="false">
                            Jenis <span class="caret"></span>
                        </button>

                        <ul class="dropdown-menu dropdown-menu-right">
                            @foreach ($jenis as $i)
                            <li>
                                <a href="javascript:void(0)"
                                    class="item-jenis"
                                    data-jenis="{{ $i->kategori }}"
                                    data-po-id="{{ $i->id }}">

                                    {{ $i->kategori }}
                                </a>
                            </li>
                            @endforeach
                        </ul>
                    </div>

                </div>

                    <div class="box-body">
                        <div class="row">
                            <div class="col-sm-6">
                                <p><strong>No. PO:</strong> {{$data->order_no}}</p>
                <input type="hidden" id="po-id" name="po_id" value="{{$data->id}}">

                                <p>QTY : <strong id="qtyText">-</strong></p>
                                <p><strong>Progrs QTY:</strong> -</p>
                                    <span id="progressQty">-</span>

                            </div>
                            <div class="col-sm-6">
                                <p><strong>QC:</strong> Soy</p>
                                <p><strong>QC date:</strong> Dec 24, 2025</p>
                            </div>
                        </div>
                    </div>
                </div>

            <!-- order -->
            @include('pages.qc.partial.order-table', ['detailP' => $detailP])

            <!-- defect -->

            @include('pages.qc.partial.defect', ['detailP' => $detailP])



        </div>


        {{-- RIGHT --}}
        <div class="col-md-4">

            {{-- STATUS --}}
            <div class="box m-b">
                <div class="box-body text-center">
                    <h4>Jenis</h4>
                    <span class="label success">APPROVED</span>
                </div>
            </div>
{{-- COMMENTS --}}
            <div class="box">
                <div class="box-header">
                    <h4>Comments</h4>
                </div>
                <div class="box-body">
                    <p><strong>QC :</strong><br><small>Dec 24, 2025</small></p>
                    <p>No comment text</p>
                </div>
            </div>

            {{-- batch --}}
            <div class="box m-b">
                <div class="box-header">
                    <h4>Batch</h4>
                </div>
                <div class="box-body">
                    <div id="batch-container" class="btn-group">
                        <span class="text-muted">Pilih batch</span>
                    </div>
                </div>
            </div>


            {{-- CORRECTIVE ACTION --}}
            <!-- <div class="box m-b">
        <div class="box-header">
          <h4>Corrective Actions</h4>
        </div>
        <div class="box-body">
          <p>No corrective actions on this inspection</p>
        </div>
      </div> -->

            {{-- WATCHERS --}}
            <!-- <div class="box m-b">
        <div class="box-header">
          <h4>Watchers</h4>
        </div>
        <div class="box-body">
          <span class="label">Indonesia QC</span>
          <span class="label">Mingfeng Chen</span>
        </div>
      </div> -->


        </div>

    </div>
</div>

@endsection
