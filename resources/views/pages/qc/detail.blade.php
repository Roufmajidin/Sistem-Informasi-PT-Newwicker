@extends('master.master')
@section('title', "detail QC")

@section('content')
<div class="padding">
    <div class="row">

        {{-- LEFT --}}
        <div class="col-md-8">
            <div class="box-header">
                <h2>QC Progress</h2>
                <small>___</small>
            </div>
            {{-- ORDER DETAILS --}}
            <div class="box m-b">
               <div class="box-header d-flex justify-content-between align-items-center">
    <h3 class="no-margin">Order Details</h3>

    <div class="dropdown">
        <button class="btn btn-success dropdown-toggle" data-toggle="dropdown">
            Jenis <span class="caret"></span>
        </button>

        <ul class="dropdown-menu dropdown-menu-right">
            <li><a href="#">Rangka</a></li>
            <li><a href="#">Anyam</a></li>
            <li><a href="#">Unfinish</a></li>
            <li class="divider"></li>
            <li><a href="#">Final</a></li>
        </ul>
    </div>
</div>

                <div class="box-body">
                    <div class="row">
                        <div class="col-sm-6">
                            <p><strong>Order qty:</strong> 90</p>
                            <p><strong>Progrs QTY:</strong> 8/90</p>
                            <p><strong>No. PO:</strong> NW 25 - 12</p>
                        </div>
                        <div class="col-sm-6">
                            <p><strong>QC:</strong> Soy</p>
                            <p><strong>QC date:</strong> Dec 24, 2025</p>
                            <!-- <p><strong>Duration:</strong> 8h 25m</p> -->
                        </div>
                    </div>
                </div>
            </div>

            {{-- ORDER ITEMS --}}
            <div class="box m-b">

                <p class="text-mute d p-2">Details</p>
                <div class="m-b-md">


                    <div class="box-body">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Order No</th>
                                    <th>Item No</th>
                                    <th>Item Name</th>
                                    <th>Qty</th>
                                    <th>Suplyer</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>PO012998</td>
                                    <td>TDW-DS2002</td>
                                    <td>Dining Chair</td>
                                    <td>Nama Supl</td>
                                    <td>30</td>
                                    <td><a href="#">detail</a></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>


        {{-- DEFECT size --}}
            <div class="box m-b">
                <div class="box-header">
                    <h3>Size Control</h3>
                </div>

                <div class="box-body">

                    {{-- DEFECT ITEM --}}
                     <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Seat Height (D)</th>
                                    <th>Seat Width (B)</th>
                                    <th>SW</th>
                                    <th>SD</th>
                                    <th>Arm Height</th>
                                    <th>Bentang kaki (B)</th>
                                    <th>Bentang kaki (D)</th>
                                    <th>Bentang Samping</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>120</td>
                                    <td>130</td>
                                    <td>20</td>
                                    <td>40</td>
                                    <td>45</td>
                                    <td>67</td>
                                    <td>90</td>
                                    <td>88</td>
                                </tr>
                            </tbody>
                        </table>
                        <!-- imagess -->
                    <div class="defect-item m-b">

                        <div class="defect-header d-flex justify-content-between">
                            <div>
                                <span class="label info">Minor</span>
                                <strong class="m-l-sm">
                                    1.1 Gambar
                                </strong>
                                <div class="text-sm text-muted m-t-xs">
                                    Filler mark on front seat frame. Defect repaired directly by factory
                                </div>
                            </div>
                        </div>

                        {{-- IMAGE GRID --}}
                        <div class="row m-t-sm">
                            <div class="col-sm-3">
                                <p>Seat Height (Depan)</p>
                                <img src="/images/defect1.jpg" class="img-responsive img-thumbnail">
                            </div>
                            <div class="col-sm-3">
                                <p>Seat Height (Belakang)</p>

                                <img src="/images/defect2.jpg" class="img-responsive img-thumbnail">
                            </div>
                            <div class="col-sm-3">
                                <p>Seat Width</p>

                                <img src="/images/defect3.jpg" class="img-responsive img-thumbnail">
                            </div>
                            <div class="col-sm-3">
                                <p>Seat Depth</p>

                                <img src="/images/defect4.jpg" class="img-responsive img-thumbnail">
                            </div>
                              <div class="col-sm-3">
                                <p>Arm Height</p>

                                <img src="/images/defect4.jpg" class="img-responsive img-thumbnail">
                            </div>
                             <div class="col-sm-3">
                                <p>Bentang kaki belakang</p>

                                <img src="/images/defect4.jpg" class="img-responsive img-thumbnail">
                            </div>
                             <div class="col-sm-3">
                                <p>Bentang kaki belakang</p>

                                <img src="/images/defect4.jpg" class="img-responsive img-thumbnail">
                            </div>
                        </div>

                    </div>



                </div>
            </div>


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

            {{-- INSPECTOR CONCLUSION --}}
            <div class="box m-b">
                <div class="box-header">
                    <h4>QC Conclusion</h4>
                </div>
                <div class="box-body">
                    <p>
                        ISI kesimpulan..
                    </p>
                    <p><strong>Approver:</strong> Siapa</p>
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

        </div>

    </div>
</div>

@endsection
