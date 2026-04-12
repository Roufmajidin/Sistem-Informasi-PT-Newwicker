    @extends('master.master')
    @section('title', "Produksi")
    @section('content')
    @include('pages.spk.stylespk')

    <div class="padding">
        <div class="box">
            <div class="box-header d-flex justify-content-between align-items-center">
                <h2 class="mb-0">Monitoring Barang Produksi</h2>

                <input type="text"
                    id="search-qc"
                    class="form-control"
                    style="width:300px"
                    placeholder="Search PO / Item / Vendor">
            </div>
        </div>

        <div class="row">

            <!-- ================= LEFT TABLE ================= -->
            <div class="col-sm-6">
                <div class="box">
                    <div class="box-header">
                        <h4>List PO</h4>
                    </div>

                    <div class="freeze-wrapper">
                        <table class="table table-striped table-bordered table-sm">
                            <thead>
                                <tr class="spk-header">
                                    <th>#</th>
                                    <th>Buyer</th>
                                    <th>No PO</th>
                                    <th>act</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($detailPo as $item)
                                <tr>
                                    <td>{{ ($detailPo->currentPage() - 1) * $detailPo->perPage() + $loop->iteration }}</td>
                                    <td>{{ $item->company_name }}</td>
                                    <td>{{ $item->order_no }}</td>
                                  <td>
    <button class="btn btn-sm btn-info btn-view"
        data-items='@json($item->details)'
        data-id="{{ $item->id }}">
        Detail
    </button>
</td>

                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="mt-3">
    {{ $detailPo->links() }}
</div>
                    </div>
                </div>
            </div>

            <!-- ================= RIGHT TABLE ================= -->
            <div class="col-sm-6">
                <div class="box">
                    <div class="box-header">
                        <h4>Detail PO :</h4>
                    </div>

                    <div class="freeze-wrapper">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th># </th>
                                    <th>Name </th>
                                    <th>Total PO </th>
                                    <th>Rangka</th>
                                    <th>Anyam</th>
                                    <th>Unfinish</th>
                                    <th>Final</th>
                                </tr>
                            </thead>
                            <tbody id="detail-area">
                                <tr>
                                    <td class="text-center text-muted">
                                        Banana
                                    </td>
                                    <td class="text-center text-muted">
                                        10
                                    </td>
                                    <td class="text-center text-muted">
                                        9
                                    </td>
                                    <td class="text-center text-muted">
                                        8
                                    </td>
                                    <td class="text-center text-muted">
                                        8
                                    </td>
                                    <td class="text-center text-muted">
                                        -
                                    </td>
                                </tr>
                                </tr>
                            </tbody>
                        </table>
                    </div>


                </div>
            </div>

        </div>
    </div>

    <div class="modal fade" id="modalDetail">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">

                <div class="modal-header">
                    <h4 id="modal-title">Input Barang</h4>
                </div>

                <div class="modal-body">

                    <div class="row">

                        <!-- LEFT -->
                        <div class="col-md-6">

                            <form id="form-process">

                                <!-- ROW 1 -->
                                <div class="row mb-3">

                                    <div class="col-md-6">
                                        <label>Supplier</label>
                                        <select class="form-control mb-2" id="supplier">
                                            <option value="">-- pilih supplier --</option>
                                        </select>

                                        <label>Sub Barang</label>
                                        <select class="form-control" id="sub_barang">
                                            <option value="">-- jenis barang --</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label>Qty</label>
                                        <input type="number" class="form-control" id="qty">
                                    </div>

                                </div>

                                <!-- ROW 2 -->
                                <div class="row mb-3">

                                    <div class="col-md-4">
                                        <label>Tanggal</label>
                                        <input type="date" class="form-control" id="date">
                                    </div>

                                    <div class="col-md-4">
                                        <label>Jam</label>
                                        <input type="time" class="form-control" id="time">
                                    </div>

                                    <div class="col-md-3">
                                        <label>Jenis</label>
                                        <select class="form-control" id="type">
                                            <option value="">-- pilih --</option>
                                            <option value="masuk">Masuk</option>
                                            <option value="keluar">Keluar</option>
                                            <option value="service">Service</option>
                                        </select>
                                    </div>

                                </div>
                                <!-- 🔥 PROCESS -->
                                <div class="col-md-3 d-none" id="process-wrapper">
                                    <label>Process</label>
                                    <select class="form-control" id="process">
                                        <option value="">-- pilih process --</option>
                                        <option value="unfinish">Unfinish</option>
                                        <option value="final">Final</option>
                                        <option value="anyam">Anyam</option>
                                    </select>
                                </div>

                                <!-- REMARK -->
                                <div class="mb-3">
                                    <label>Remark</label>
                                    <input type="text" class="form-control" id="remark">
                                </div>

                            </form>
                        </div>

                        <!-- RIGHT -->
                        <div class="col-md-6">
                            <h5>List of in/out barang</h5>
                            <button class="btn btn-info btn-sm mb-2" id="toggle-graph">
                                Tampilkan Graph
                            </button>
                            <div id="graph-view" class="d-none p-3" style="background:#111; color:#fff; border-radius:8px;"></div>
                            <table class="table table-bordered">
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Jam</th>
                                    <th>Type</th>
                                    <th>Process</th>
                                    <th>Next</th>
                                    <th>Qty</th>
                                    <th>Supplier</th>
                                    <th>Remark</th>
                                </tr>
                                </thead>
                                <tbody id="table-out"></tbody>
                            </table>
                        </div>

                    </div>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-primary" id="save-process">Save</button>
                </div>

            </div>
        </div>
    </div>

    @include('pages.spk.produksi.script ')
    <style>
        .btn-detail-name {
            cursor: pointer;
            color: #0d6efd;
            text-decoration: underline;
        }

        #modalDetail .modal-dialog {
            max-width: 95%;
        }

        .freeze-wrapper {
            max-height: 600px;
            overflow: auto;
            position: relative;
            border: 1px solid #ddd;
        }

        /* ===== HEADER FREEZE ===== */
        #detail-table thead th {
            position: sticky;
            top: 0;
            background: #2b3c70ff;
            /* WARNA HEADER */
            color: white;
            z-index: 20;
            border-bottom: 2px solid #ccc;
        }

        /* kasih bayangan supaya keliatan pas scroll */
        #detail-table thead {
            box-shadow: 0 2px 6px rgba(0, 0, 0, .08);
        }

        /* ================= COLUMN WIDTH ================= */
        #detail-table th:nth-child(1),
        #detail-table td:nth-child(1) {
            min-width: 60px;
        }

        #detail-table th:nth-child(2),
        #detail-table td:nth-child(2) {
            min-width: 90px;
        }

        #detail-table th:nth-child(3),
        #detail-table td:nth-child(3) {
            min-width: 280px;
        }

        /* ================= FREEZE COL 1 ================= */
        /* #detail-table th:nth-child(1), */
        #detail-table td:nth-child(1) {
            position: sticky;
            left: 0;
            background: #fff;
            z-index: 8;
        }

        /* ================= FREEZE COL 2 ================= */
        /* #detail-table th:nth-child(2), */
        #detail-table td:nth-child(2) {
            position: sticky;
            left: 60px;
            background: #fff;
            z-index: 8;
        }

        /* ================= FREEZE COL 3 ================= */
        /* #detail-table th:nth-child(3), */
        #detail-table td:nth-child(3) {
            position: sticky;
            left: 150px;
            background: #fff;
            z-index: 8;
            box-shadow: 2px 0 6px rgba(0, 0, 0, .1);
        }

        /* header freeze priority */
        #detail-table thead th:nth-child(1),
        #detail-table thead th:nth-child(2),
        #detail-table thead th:nth-child(3) {
            z-index: 12;
        }

        #detail-table tbody tr:hover td {
            background: #f9f9f9;
        }
    </style>

    @endsection
