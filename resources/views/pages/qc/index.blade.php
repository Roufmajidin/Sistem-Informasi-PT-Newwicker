@extends('master.master')
@section('title', "Index QC")

@section('content')
<div class="padding">
    <div class="box">
        <div class="box-header">
            <h2>QC Dashboard</h2>
            <small>___</small>
        </div>

        <div class="box-body">
            {{-- FORM IMPORT --}}
      <div class="box-header d-flex justify-content-between align-items-center">
    <button id="btn-show-form" class="btn btn-success">
        <i class="fa fa-plus"></i> Input Data QC
    </button>

    <input type="text"
        id="search-qc"
        class="form-control"
        style="width:300px"
        placeholder="Search PO / Item / Vendor">
</div>

    {{-- FORM IMPORT --}}
    <div id="qc-form-wrapper" style="display:none;">

        <form id="paste-form" method="POST" action="{{ route('qc.save') }}">
            @csrf

            <div class="row">

                <!-- Kiri -->
                <div class="col-md-4">
                    <h5 class="mb-2"><strong>Order Information</strong></h5>

                    <textarea id="order-textarea"
                        name="order_info"
                        rows="12"
                        class="form-control"
                        placeholder="Paste order info here..."></textarea>

                    <h5 class="mt-3">JSON Output</h5>
                    <pre id="json-output" class="p-2 border bg-light"></pre>
                </div>

                <!-- Kanan -->
                <div class="col-md-8">
                    <h5 class="mb-2"><strong>Paste Excel Table</strong></h5>

                    <textarea id="order-textarea2"
                        rows="12"
                        class="form-control"
                        placeholder="Paste data from Excel here..."
                        style="font-family: monospace;"></textarea>

                    <input type="hidden"
                        name="parsed_excel_json"
                        id="parsed_excel_json">

                    <button type="submit" class="btn btn-primary mt-3">
                        Simpan All Data
                    </button>

                    <h5 class="mt-3">JSON Output</h5>
                    <pre id="json-output2" class="p-2 border bg-light"></pre>
                </div>

            </div>
        </form>

    </div>
        </div>

        {{-- FILTER --}}
        <div class="btn-group p-2" data-toggle="buttons">
            <label class="btn btn-sm white">
                <input type="radio" name="options"> All
            </label>
            <label class="btn btn-sm white">
                <input type="radio" name="options"> In-Progress
            </label>
        </div>

        {{-- ================= DEFAULT TABLE ================= --}}
        <div class="row" id="default-table">
            <div class="col-sm-12">
                <div class="box">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>Order No</th>
                                    <th>Kode PO</th>
                                    <th>Company Name</th>
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
                </div>
            </div>
        </div>

        {{-- ================= PREVIEW TABLE ================= --}}
        <div class="row d-none" id="preview-table">
            <div class="col-sm-12">
                <div class="box">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>Description</th>
                                    <th>Article</th>
                                    <th>Item (W×D×H)</th>
                                    <th>Packing (W×D×H)</th>
                                    <th>QTY</th>
                                    <th>CBM</th>
                                    <th>FOB</th>
                                </tr>
                            </thead>
                            <tbody id="preview-body">
                                {{-- diisi via JS --}}
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>


<pre id="result"></pre>
@push('scripts')
<script>

if (!window.qcPageInitialized) {

    window.qcPageInitialized = true;

    function initQcPage() {

        // ❗ CEGAH jalan di halaman lain
        if (!$('#po-table-body').length) return;

        console.log('INIT QC PAGE');

        /* ==============================
           SHOW FORM
        ============================== */
        $(document).off('click', '#btn-show-form')
        .on('click', '#btn-show-form', function () {
            $('#qc-form-wrapper').show();
            $(this).hide();
        });

        /* ==============================
           FORMAT SEARCH
        ============================== */
        $(document).off('input', '#search-qc')
        .on('input', '#search-qc', function () {
            let val = this.value;
            val = val.replace(/(\d)-$/g, '$1 - ');
            this.value = val;
        });

        /* ==============================
           LOAD TABLE (GLOBAL SAFE)
        ============================== */
        window.loadPoTable = function(keyword = '') {

            if (!$('#po-table-body').length) return;

            fetch(`{{ route('qc.ajax.po') }}?q=${encodeURIComponent(keyword)}`)
                .then(res => res.json())
                .then(data => {

                    const tbody = document.getElementById('po-table-body');
                    if (!tbody) return;

                    tbody.innerHTML = '';

                    if (!data.length) {
                        tbody.innerHTML = `
                            <tr>
                                <td colspan="4" class="text-center text-muted">
                                    Belum ada data
                                </td>
                            </tr>
                        `;
                        return;
                    }

                    data.forEach(po => {
                        tbody.innerHTML += `
                            <tr>
                                <td>${po.order_no}</td>
                                <td>${po.po_no ?? '-'}</td>
                                <td>${po.company_name}</td>
                                <td>
                                    <a href="/qc/${po.id}"
                                       class="btn btn-xs success btn-view">
                                        View
                                    </a>
                                </td>
                            </tr>
                        `;
                    });
                })
                .catch(err => console.error(err));
        };

        /* ==============================
           SEARCH
        ============================== */
        $(document).off('keyup', '#search-qc')
        .on('keyup', '#search-qc', function () {
            loadPoTable(this.value);
        });

        /* ==============================
           TEXTAREA PARSER
        ============================== */
        $(document).off('input', '#order-textarea')
        .on('input', '#order-textarea', function () {

            const lines = this.value.split(/\r?\n/).filter(l => l.trim());
            const obj = {};

            lines.forEach(line => {
                const parts = line.split(':');
                if (parts.length >= 2) {
                    obj[parts[0].trim().replace(/\s+/g, '_')] =
                        parts.slice(1).join(':').trim();
                }
            });

            $('#json-output').text(JSON.stringify(obj, null, 2));
        });

        /* ==============================
           TEXTAREA EXCEL
        ============================== */
        $(document).off('input', '#order-textarea2')
        .on('input', '#order-textarea2', function () {

            const text = this.value;

            if (!text.trim()) {
                $('#json-output2').text('');
                $('#parsed_excel_json').val('');
                return;
            }

            fetch("{{ route('excel.paste') }}", {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": $('input[name=_token]').val(),
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({ excel_data: text })
            })
            .then(res => res.json())
            .then(data => {
                $('#json-output2').text(JSON.stringify(data, null, 2));
                $('#parsed_excel_json').val(JSON.stringify(data));
            })
            .catch(err => console.error(err));
        });

        /* ==============================
           SUBMIT FORM
        ============================== */
        $(document).off('submit', '#paste-form')
        .on('submit', '#paste-form', function (e) {

            e.preventDefault();

            let orderJson = {};

            $('#order-textarea').val()
            .split(/\r?\n/)
            .filter(l => l.trim())
            .forEach(line => {
                const parts = line.split(':');
                if (parts.length >= 2) {
                    orderJson[parts[0].trim().replace(/\s+/g, '_')] =
                        parts.slice(1).join(':').trim();
                }
            });

            let excelJson;

            try {
                excelJson = JSON.parse($('#parsed_excel_json').val());
            } catch {
                alert('JSON Excel tidak valid!');
                return;
            }

            fetch("{{ route('qc.save') }}", {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": $('input[name=_token]').val(),
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    order_info: orderJson,
                    parsed_excel_json: excelJson
                })
            })
            .then(res => res.json())
            .then(() => {
                loadPoTable();
            })
            .catch(err => {
                console.error(err);
                alert('Gagal menyimpan');
            });
        });

        /* ==============================
           LOAD AWAL
        ============================== */
        loadPoTable();
    }

    /* ==============================
       NORMAL LOAD
    ============================== */
    $(document).ready(function () {
        initQcPage();
    });

    /* ==============================
       PJAX LOAD (FIX DELAY)
    ============================== */
    $(document).on('pjax:end', function () {
        setTimeout(() => {
            initQcPage();
        }, 50);
    });

}
</script>


@endpush

@endsection
