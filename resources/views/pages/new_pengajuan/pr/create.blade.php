<div class="card">
    <div class="card-header d-flex justify-content-between">
        <h5>Create Purchase Request</h5>

        <button type="button"
                class="btn btn-primary"
                id="btnAddRow">
            + Add Row
        </button>
    </div>

    <div class="card-body">

        <div class="row mb-3">
            <div class="col-md-3">
                <label>Tanggal</label>
                <input type="date"
                       class="form-control"
                       name="request_date">
            </div>

            <div class="col-md-3">
                <label>Department</label>
                <input type="text"
                       class="form-control"
                       name="department">
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered" id="prTable">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Kebutuhan</th>
                        <th>Vendor</th>
                        <th>Payment</th>
                        <th>Description</th>
                        <th>Keterangan</th>
                        <th>Qty</th>
                        <th>Sat</th>
                        <th>Unit Price</th>
                        <th>Total</th>
                        <th width="50">#</th>
                    </tr>
                </thead>

                <tbody>
                    <tr>
                        <td class="row-no">1</td>

                        <td>
                            <input type="text"
                                   name="items[0][kebutuhan]"
                                   class="form-control">
                        </td>

                        <td>
                            <input type="text"
                                   name="items[0][vendor]"
                                   class="form-control">
                        </td>

                        <td>
                            <input type="text"
                                   name="items[0][payment]"
                                   class="form-control">
                        </td>

                        <td>
                            <input type="text"
                                   name="items[0][description]"
                                   class="form-control">
                        </td>

                        <td>
                            <input type="text"
                                   name="items[0][keterangan]"
                                   class="form-control">
                        </td>

                        <td>
                            <input type="number"
                                   class="form-control qty">
                        </td>

                        <td>
                            <input type="text"
                                   class="form-control">
                        </td>

                        <td>
                            <input type="number"
                                   class="form-control price">
                        </td>

                        <td>
                            <input type="text"
                                   class="form-control total"
                                   readonly>
                        </td>

                        <td>
                            <button type="button"
                                    class="btn btn-danger btn-sm remove-row">
                                ×
                            </button>
                        </td>
                    </tr>
                </tbody>

                <tfoot>
                    <tr>
                        <th colspan="9" class="text-end">
                            GRAND TOTAL
                        </th>
                        <th id="grandTotal">0</th>
                        <th></th>
                    </tr>
                </tfoot>
            </table>
        </div>

    </div>
</div>
