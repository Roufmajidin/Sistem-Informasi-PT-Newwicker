@extends('master.master')
@section('title', "All SPK")
@section('content')

<div class="padding">
    <div class="box">
        <div class="box-header">
            <h2>All Spk</h2>
            <small></small>
            <div class="row" id="default-table">
                <div class="col-sm-12">
                    <div class="box">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th style="width: 20px;">No</th>
                                        <th>Po</th>
                                        <th>Buyer Name</th>
                                        <th width="120">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="po-table-body">
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">
                                            Loading...
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="box" id="spkDetailBox" style="display:none">
                            <div class="box-header">
                                <h3>Detail SPK</h3>
                                <small id="detailPoTitle"></small>
                            </div>

                            <div class="box-body">
                                <table class="table table-sm table-bordered">
                                    <thead>
                                        <tr>
                                            <th>No. SPK</th>
                                            <th>kategori</th>
                                            <th>act</th>
                                        </tr>
                                    </thead>
                                    <tbody id="spk-detail-body"></tbody>
                                </table>


                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endsection
        @push('scripts')
        <!-- jQuery (WAJIB PERTAMA) -->
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@3.4.1/dist/js/bootstrap.min.js"></script>

        <script>
            console.log(window.history.length);
            // ===============================
            // GLOBAL VARIABLE (WAJIB DI LUAR)
            // ===============================
            let cacheData = [];

            // ===============================
            // DOWNLOAD SPK
            // ===============================
            $(document).on('click', '.btn-download-spk', function() {
                const spkId = $(this).data('id');
                window.open(`/spk/export/${spkId}`, '_blank');
            });

            // ===============================
            // LOAD DATA PO
            // ===============================
            function loadSpkTable() {
                $.get("{{ route('spk.all') }}", function(res) {

                    cacheData = res;

                    let html = '';
                    let no = 1;

                    res.forEach((po, index) => {

                        let poId = po.data_po?.id;
                        let poNo = po.data_po?.no_po ?? '-';
                        let buyerName = po.data_po?.company ?? '-';
                        // count spk
                        let spkIds = new Set();

                        (po.data_po?.items || []).forEach(item => {
                            let summary = item.summary || {};

                            Object.values(summary).forEach(suppliers => {
                                Object.values(suppliers).forEach(supplier => {
                                    (supplier.spks || []).forEach(spk => {
                                        spkIds.add(spk.spk_id);
                                    });
                                });
                            });
                        });

                        let spkCount = spkIds.size;
                        html += `
                    <tr>
                        <td>${no++}</td>
                        <td>${poNo}</td>
                        <td>${buyerName}</td>
                      <td>
    <button
        class="btn btn-sm btn-primary btn-view-spk"
        data-index="${index}">
        View SPK (${spkCount})
    </button>
    <a href="/spk/${poId}"
       class="btn btn-sm btn-info">
        Buat SPK
    </a>
</td>
                    </tr>

                    <!-- DETAIL ROW -->
                    <tr id="detail-${poId}" class="detail-row" style="display:none;">
                        <td colspan="4">
                            <div class="detail-content"></div>
                        </td>
                    </tr>
                `;
                    });

                    $('#po-table-body').html(html);
                });
            }

            // ===============================
            // CLICK VIEW SPK (EXPAND TABLE)
            // ===============================
            $(document).on('click', '.btn-view-spk', function() {

                let index = $(this).data('index');
                let po = cacheData[index];

                let poId = po.data_po.id;
                let items = po.data_po.items || [];

                let row = $('#detail-' + poId);

                // toggle
                if (row.is(':visible')) {
                    row.slideUp();
                    return;
                }

                // tutup semua
                $('.detail-row').slideUp();

                let html = `
            <table class="table table-sm table-bordered">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Article</th>
                        <th>Description</th>
                        <th>Qty</th>
                        <th>CBM</th>
                        <th>SPK</th>
                    </tr>
                </thead>
                <tbody>
        `;

                items.forEach((item, i) => {

                    let d = item.detail || {};
                    let summary = item.summary || {};

                    let spkHtml = '';

                    Object.keys(summary).forEach(kategori => {

                        Object.keys(summary[kategori]).forEach(supplier => {

                            let data = summary[kategori][supplier];

                            spkHtml += `<b>${kategori}</b><br>`;
                            spkHtml += `${supplier} (${data.total_qty})<br>`;

                            (data.spks || []).forEach(spk => {
                                spkHtml += `
                            - ${spk.no_spk} (${spk.qty})
                            <button class="btn btn-xs btn-success btn-download-spk"
                                data-id="${spk.spk_id}">
                                Download
                            </button>
                              <button class="btn btn-xs btn-warning btn-edit-spk"
        data-id="${spk.spk_id}">
        Edit
    </button>

                            <br>
                        `;
                            });

                            spkHtml += `<br>`;
                        });
                    });

                    html += `
                <tr>
                    <td>${i + 1}</td>
                    <td>${d.article_nr_ ?? '-'}</td>
                    <td>${d.description ?? '-'}</td>
                    <td>${d.qty ?? 0}</td>
                    <td>${parseFloat(d.total_cbm ?? 0).toFixed(3)}</td>
                    <td>${spkHtml || '-'}</td>
                </tr>
            `;
                });

                html += `</tbody></table>`;

                row.find('.detail-content').html(html);
                row.slideDown();
            });

            // ===============================
            // EDIT SPK
            // ===============================
            $(document).on('click', '.btn-edit-spk', function() {
                let spkId = $(this).data('id');
                window.location.href = `/spk/edit/${spkId}`;
            });

            $(document).on('click', '.spk-link', function() {
                let spkId = $(this).data('spk-id');
                window.location.href = `/spk/edit/${spkId}`;
            });

            // ===============================
            // INIT
            // ===============================
            $(document).ready(function() {
                loadSpkTable();
            });
        </script>
        <style>
            .selected-spk {
                background-color: #ffeeba !important;
                border-left: 5px solid #f39c12;
                font-weight: 600;
            }

            .spk-detail-row {
                background: #f9f9f9;
            }

            /* BARANG */
            .barang-row td {
                padding: 12px 10px;
                background: #f9fafb;
                border-top: 2px solid #dee2e6;
            }

            .barang-title {
                font-weight: 600;
            }

            .barang-desc {
                font-size: 12px;
                color: #666;
            }

            .barang-qty {
                font-size: 11px;
                color: #999;
            }

            /* KATEGORI */
            .kategori-row td {
                padding: 6px 10px;
                font-style: italic;
                color: #555;
                background: #f1f3f5;
            }

            /* SPK */
            .spk-row td {
                padding: 6px 10px;
                font-size: 13px;
            }

            /* TABLE UMUM */
            table td {
                vertical-align: top;
            }
        </style>
        @endpush
