   <div class="card shadow-sm mb-3">

       <div class="card-header bg-white d-flex justify-content-between align-items-center">

           <h5 class="mb-0">
               <i class="fas fa-shipping-fast me-2"></i>
               Shipment Information
           </h5>

           <button class="btn btn-sm btn-outline-secondary" type="button" data-bs-toggle="collapse"
               data-bs-target="#shipmentInformation" aria-expanded="true">

               <i class="fas fa-minus" id="shipmentIcon"></i>

           </button>

       </div>

       <div class="collapse show" id="shipmentInformation">

           <div class="card-body">

               <div class="row">

                   {{-- LEFT --}}
               <div class="col-md-6">

    <table class="table table-borderless table-sm mb-0 w-100">

        <colgroup>
            <col style="width:130px;">
            <col style="width:15px;">
            <col>
        </colgroup>

        <tr>
            <th colspan="3" class="bg-light">BUYER :</th>
        </tr>

        <tr>
            <td colspan="3">
                <input id="buyer_name"
                    name="buyer_name"
                    class="form-control form-control-sm">
            </td>
        </tr>

        <tr>
            <td colspan="3">
                <textarea id="buyer_address"
                    name="buyer_address"
                    rows="3"
                    class="form-control form-control-sm"></textarea>
            </td>
        </tr>

        <tr>
            <th>Date</th>
            <td>:</td>
            <td>
                <input type="date"
                    id="date"
                    name="date"
                    class="form-control form-control-sm">
            </td>
        </tr>

        <tr>
            <th>Sales Order No</th>
            <td>:</td>
            <td style="position:relative">

                <input type="text"
                    class="form-control form-control-sm"
                    id="sales_order"
                    autocomplete="off">

                <input type="hidden"
                    id="po_id"
                    name="po_id">

                <div id="poResult"
                    class="list-group shadow-sm"
                    style="display:none;
                           position:absolute;
                           width:100%;
                           z-index:9999;
                           max-height:250px;
                           overflow:auto;">
                </div>

            </td>
        </tr>

        <tr>
            <th>Invoice No</th>
            <td>:</td>
            <td>
                <input type="text"
                    name="invoice_no"
                    id="invoice_no"
                    class="form-control form-control-sm">
            </td>
        </tr>

        <tr>
            <th>Customer Code</th>
            <td>:</td>
            <td>
                <input type="text"
                    name="customer_code"
                    id="customer_code"
                    class="form-control form-control-sm">
            </td>
        </tr>

        <tr>
            <th>Customer PO No</th>
            <td>:</td>
            <td>
                <input type="text"
                    name="customer_po_no"
                    id="customer_po_no"
                    class="form-control form-control-sm">
            </td>
        </tr>

    </table>

</div>

                   {{-- RIGHT --}}
                   <div class="col-lg-6">

                       <table class="table table-sm table-borderless align-middle">



                           <tr>
                               <th>Vessel Name</th>
                               <td>:</td>
                               <td>
                                   <input type="text" id="vessel_name" name="vessel_name"
                                       class="form-control form-control-sm" placeholder="Vessel Name">
                               </td>
                           </tr>

                           <tr>
                               <th>Container Type</th>
                               <td>:</td>
                               <td>
                                   <select name="container_type" id="container_type" class="form-select form-select-sm">

                                       <option value="">Select Container</option>
                                       <option>20' GP</option>
                                       <option>40' GP</option>
                                       <option selected>40' HC</option>
                                       <option>45' HC</option>

                                   </select>
                               </td>
                           </tr>

                           <tr>
                               <th>Container No</th>
                               <td>:</td>
                               <td>
                                   <input type="text" id="container_no" name="container_no"
                                       class="form-control form-control-sm" placeholder="Container Number">
                               </td>
                           </tr>

                           <tr>
                               <th>Seal No</th>
                               <td>:</td>
                               <td>
                                   <input type="text" name="seal_no" id="seal_no"
                                       class="form-control form-control-sm" placeholder="Seal Number">
                               </td>
                           </tr>

                           <tr>
                               <th>Port of Loading</th>
                               <td>:</td>
                               <td>
                                   <input type="text" id="port_loading" name="port_loading"
                                       class="form-control form-control-sm" placeholder="Port of Loading">
                               </td>
                           </tr>

                           <tr>
                               <th>Port of Discharge</th>
                               <td>:</td>
                               <td>
                                   <input type="text" id="port_discharge" name="port_discharge"
                                       class="form-control form-control-sm" placeholder="Port of Discharge">
                               </td>
                           </tr>

                           <tr>
                               <th>Commodity</th>
                               <td>:</td>
                               <td>
                                   <input type="text" id="commodity" name="commodity" value="Rattan Furnitures"
                                       class="form-control form-control-sm">
                               </td>
                           </tr>

                           <tr>
                               <th>Fumigation</th>
                               <td>:</td>
                               <td>
                                   <select name="fumigation" id="fumigation" class="form-select form-select-sm">

                                       <option value="">Select</option>
                                       <option selected>YES</option>
                                       <option>NO</option>

                                   </select>
                               </td>
                           </tr>

                           <tr>
                               <th>ETD</th>
                               <td>:</td>
                               <td>
                                   <input type="date" name="etd"  id="etd" class="form-control form-control-sm">
                               </td>
                           </tr>

                           <tr>
                               <th>ETA</th>
                               <td>:</td>
                               <td>
                                   <input type="date" name="eta"   id="eta" class="form-control form-control-sm">
                               </td>
                           </tr>

                       </table>

                   </div>

               </div>

           </div>

       </div>

   </div>
