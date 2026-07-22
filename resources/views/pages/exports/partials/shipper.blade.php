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

                       <table class="table table-borderless table-sm mb-0">

                           {{-- SHIPPER --}}
                           <tr>
                               <th colspan="2" class="bg-light">
                                   SHIPPER :
                               </th>
                           </tr>

                           <tr>
                               <td colspan="2">
                                   <strong>PT. NEWWICKER INDONESIA</strong>
                               </td>
                           </tr>

                           <tr>
                               <td colspan="2">
                                   JL. KISABALANANG BLOK. SIPANCING RT.005 RW.002,
                               </td>
                           </tr>

                           <tr>
                               <td colspan="2">
                                   DESA MEGU CILIK, KEC. WERU, CIREBON - INDONESIA
                               </td>
                           </tr>

                           <tr>
                               <td colspan="2">
                                   PHONE : 0231-325880 - export@newwicker.com
                               </td>
                           </tr>

                           {{-- BUYER --}}
                           <tr>
                               <th colspan="2" class="bg-light pt-3">
                                   BUYER :
                               </th>
                           </tr>

                           <tr>
                               <td colspan="2">
                                <input
                                    id="buyer_name"
                                    class="form-control form-control-sm"
                                    name="buyer_name">
                               </td>
                           </tr>

                           <tr>
                               <td colspan="2">
                                   <textarea name="buyer_address" rows="4" class="form-control form-control-sm" placeholder="Buyer Address"></textarea>
                               </td>
                           </tr>

                       </table>

                   </div>

                   {{-- RIGHT --}}
                   <div class="col-lg-6">

                       <table class="table table-sm table-borderless align-middle">

                           <tr>
                               <th width="180">Date</th>
                               <td width="15">:</td>
                               <td>
                                   <input type="date" name="date" class="form-control form-control-sm">
                               </td>
                           </tr>

                           <tr>
                           <tr>
                               <th>Sales Order No</th>
                               <td>:</td>
                               <td style="position:relative">

                                   <input type="text" class="form-control form-control-sm" id="sales_order"
                                       autocomplete="off" placeholder="Search Sales Order...">

                                   <input type="hidden" id="po_id" name="po_id">

                                   <div id="poResult" class="list-group shadow-sm"
                                       style="
                                        display:none;
                                        position:absolute;
                                        z-index:9999;
                                        width:100%;
                                        max-height:250px;
                                        overflow:auto;
                                    ">
                                   </div>

                               </td>
                           </tr>

                           <tr>
                               <th>Invoice No</th>
                               <td>:</td>
                               <td>
                                   <input type="text" name="invoice_no" class="form-control form-control-sm"
                                       placeholder="Invoice No">
                               </td>
                           </tr>

                           <tr>
                               <th>Customer Code</th>
                               <td>:</td>
                               <td>
                                   <input type="text" name="customer_code" class="form-control form-control-sm"
                                       placeholder="Customer Code">
                               </td>
                           </tr>

                           <tr>
                               <th>Customer PO No</th>
                               <td>:</td>
                               <td>
                                   <input type="text" name="customer_po_no" class="form-control form-control-sm"
                                       placeholder="Customer PO No">
                               </td>
                           </tr>

                           <tr>
                               <th>Vessel Name</th>
                               <td>:</td>
                               <td>
                                   <input type="text" name="vessel_name" class="form-control form-control-sm"
                                       placeholder="Vessel Name">
                               </td>
                           </tr>

                           <tr>
                               <th>Container Type</th>
                               <td>:</td>
                               <td>
                                   <select name="container_type" class="form-select form-select-sm">

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
                                   <input type="text" name="container_no" class="form-control form-control-sm"
                                       placeholder="Container Number">
                               </td>
                           </tr>

                           <tr>
                               <th>Seal No</th>
                               <td>:</td>
                               <td>
                                   <input type="text" name="seal_no" class="form-control form-control-sm"
                                       placeholder="Seal Number">
                               </td>
                           </tr>

                           <tr>
                               <th>Port of Loading</th>
                               <td>:</td>
                               <td>
                                   <input type="text" name="port_loading" class="form-control form-control-sm"
                                       placeholder="Port of Loading">
                               </td>
                           </tr>

                           <tr>
                               <th>Port of Discharge</th>
                               <td>:</td>
                               <td>
                                   <input type="text" name="port_discharge" class="form-control form-control-sm"
                                       placeholder="Port of Discharge">
                               </td>
                           </tr>

                           <tr>
                               <th>Commodity</th>
                               <td>:</td>
                               <td>
                                   <input type="text" name="commodity" value="Rattan Furnitures"
                                       class="form-control form-control-sm">
                               </td>
                           </tr>

                           <tr>
                               <th>Fumigation</th>
                               <td>:</td>
                               <td>
                                   <select name="fumigation" class="form-select form-select-sm">

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
                                   <input type="date" name="etd" class="form-control form-control-sm">
                               </td>
                           </tr>

                           <tr>
                               <th>ETA</th>
                               <td>:</td>
                               <td>
                                   <input type="date" name="eta" class="form-control form-control-sm">
                               </td>
                           </tr>

                       </table>

                   </div>

               </div>

           </div>

       </div>

   </div>
