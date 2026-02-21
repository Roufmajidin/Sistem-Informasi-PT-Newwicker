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
            <div class="row" id="default-table">
                <div class="col-sm-12">
                    <div class="box">
                        <div class="freeze-wrapper">
                            <table class="table table-striped table-bordered">
                                <thead id="detail-table-head">
                                    <tr class="spk-header">
                                        <th>Tanggal</th>
                                        <th>Nama Barang</th>
                                        <th>Buyer</th>
                                        <th>No PO</th>
                                        <th>Qty PO</th>
                                        <th>Qty act</th>
                                        <th>list sub</th>
                                        <th>Qc report</th>
                                        <th width="120">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($result as $row)
                                    @php

                                    $dp = $row['detail_po'];
                                    @endphp

                                    <tr>
                                        <td></td>

                                        {{-- NAMA BARANG --}}
                                        <td>
                                            <div style="display:flex; gap:10px;">
                                                <img src="{{ $row['photo'] }}"
                                                    width="60" height="60"
                                                    style="object-fit:cover"
                                                    loading="lazy"
                                                    onerror="this.style.display='none'">


                                                <div>
                                                    <b>{{ data_get($dp->detail,'article_nr_') }}</b><br>
                                                    <small>{{ data_get($dp->detail,'description') }}</small><br>
                                                    <small class="text-muted">
                                                        Qty PO: {{ data_get($dp->detail,'qty') }}
                                                    </small>
                                                </div>
                                            </div>
                                        </td>

                                        <td>{{ $dp->po->company_name }}</td>
                                        <td>{{ $dp->po->order_no }}</td>
                                        <td>{{ data_get($dp->detail,'qty') }}</td>
                                        <td>
                                            <p
                                                class="btn-qty-in text-primary"
                                                data-item="{{ data_get($dp->detail,'description') }}"
                                                data-qty="{{ data_get($dp->detail,'qty') }}"
                                                data-detail="{{ $dp->id}}"
                                                data-po="{{ $dp->po->id }}"

                                                data-spk='@json($row["spk"] ?? [])'>

                                               isi
                                            </p>

                                        </td>

                                        {{-- LIST SUB --}}
                                        <td>
                                            @forelse($row['spk'] as $sub => $spks)
                                            <div>
                                                <span class="badge bg-info">{{ strtoupper($sub) }}</span>
                                                @foreach($spks as $spk)
                                                <div style="margin-left:8px">
                                                    <a href="/spk/edit/{{ $spk['spk_id'] }}">
                                                        {{ $spk['no_spk'] }}
                                                    </a>
                                                    <small>({{ $spk['sup'] }} - {{ $spk['qty'] }})</small>
                                                </div>
                                                @endforeach
                                            </div>
                                            @empty
                                            <span class="text-muted">Belum ada SPK</span>
                                            @endforelse
                                        </td>


                                        </td>

                                        <td></td>


                                    </tr>


                                    @endforeach
                                </tbody>

                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>



    </div>
    </div>

    <div class="modal" id="qtyModal" tabindex="-1" style="margin-left: 100px;margin-right: 40px;">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="qtyModalTitle"></h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

         <div class="modal-body">
    <input type="hidden" id="detailId">

    <div class="row">

        <!-- ================= LEFT : INPUT ================= -->
        <div class="col-md-8 border-end">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="text-primary m-0">📥 Input IN / OUT</h6>
                <div class="w-auto">
                    <select id="kategoriFilter" class="form-select form-select-sm">
                        <option value="">-- Semua Kategori --</option>
                        <!-- opsi kategori akan diisi via JS -->
                    </select>
                </div>
            </div>

            <div class="table-responsive" style="max-height:400px">
                <table class="table table-bordered table-sm align-middle" id="editTable">
                    <thead class="table-light sticky-top">
                        <tr>
                            <th width="80">IN</th>
                            <th width="80">OUT</th>
                            <th width="120">SUB</th>
                            <th width="120">SPK</th>
                            <th width="130">Tanggal</th>
                            <th width="200">Remark</th>
                            <th width="40"></th>

        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>

            <button class="btn btn-sm btn-outline-primary mt-2" id="addRowBtn">
                ➕ Add Row
            </button>
        </div>

        <!-- ================= RIGHT : KESIMPULAN ================= -->
        <div class="col-md-4 ps-3">
            <h6 class="text-success mb-2">📊 Kesimpulan</h6>
            <div id="summaryContainer" class="" style="max-height:400px; overflow-y:auto;">
                <!-- Kesimpulan kategori akan diisi via JS -->
            </div>
        </div>

    </div>

    <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button class="btn btn-primary" id="saveBtn">Simpan</button>
    </div>
</div>

        @include('pages.spk.produksi.script ')
        <style>
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
