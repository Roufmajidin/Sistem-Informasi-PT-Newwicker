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

@endsection

@push('scripts')
<!-- jQuery (WAJIB PERTAMA) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Bootstrap JS (SETELAH jQuery) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@3.4.1/dist/js/bootstrap.min.js"></script>
<script>
    document.getElementById('btn-show-form').addEventListener('click', function () {
        const form = document.getElementById('qc-form-wrapper');
        form.style.display = 'block';
        this.style.display = 'none'; // tombol hilang setelah diklik
    });
</script>

<script>
    // Parsing textarea kiri (order info)
    document.getElementById('order-textarea').addEventListener('input', function() {
        const text = this.value;
        const lines = text.split(/\r?\n/).filter(l => l.trim() !== '');
        const obj = {};

        lines.forEach(line => {
            const parts = line.split(':');
            if (parts.length >= 2) {
                const key = parts[0].trim().replace(/\s+/g, '_');
                const value = parts.slice(1).join(':').trim();
                obj[key] = value;
            }
        });

        document.getElementById('json-output').textContent = JSON.stringify(obj, null, 2);
    });

    // Parsing textarea kanan (Excel → JSON)
    document.getElementById('order-textarea2').addEventListener('input', function() {
        const text = this.value;

        if (text.trim() === '') {
            document.getElementById('json-output2').textContent = '';
            document.getElementById('parsed_excel_json').value = '';
            return;
        }

        fetch("{{ route('excel.paste') }}", {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": document.querySelector('input[name=_token]').value,
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    excel_data: text
                })
            })
            .then(res => res.json())
            .then(data => {
                // tampilkan JSON di pre
                document.getElementById('json-output2').textContent = JSON.stringify(data, null, 2);

                // simpan JSON di hidden input
                document.getElementById('parsed_excel_json').value = JSON.stringify(data);
            })
            .catch(err => {
                console.error(err);
                document.getElementById('json-output2').textContent = 'Gagal memanggil API';
            });
    });

    // Submit form → kirim kedua data
    document.getElementById('paste-form').addEventListener('submit', function(e) {
        e.preventDefault();

        // Ambil order info dari textarea kiri
        const orderText = document.getElementById('order-textarea').value;
        const lines = orderText.split(/\r?\n/).filter(l => l.trim() !== '');
        const orderJson = {};
        lines.forEach(line => {
            const parts = line.split(':');
            if (parts.length >= 2) {
                const key = parts[0].trim().replace(/\s+/g, '_');
                const value = parts.slice(1).join(':').trim();
                orderJson[key] = value;
            }
        });

        // Ambil Excel JSON dari textarea kanan
        const excelJsonStr = document.getElementById('parsed_excel_json').value;
        let excelJson = {};
        try {
            excelJson = JSON.parse(excelJsonStr);
        } catch (e) {
            alert('JSON Excel tidak valid!');
            return;
        }

        // Kirim ke controller
        fetch("{{ route('qc.save') }}", {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": document.querySelector('input[name=_token]').value,
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({
                    order_info: orderJson,
                    parsed_excel_json: excelJson
                })
            })
            .then(res => res.json())
            .then(data => {
                // Tampilkan JSON response di textarea kanan
                document.getElementById('json-output2').textContent = JSON.stringify(data, null, 2);
                // alert('Data berhasil disimpan!');
                loadPoTable();

            })
            .catch(err => {
                console.error(err);
                alert('Gagal menyimpan data');
            });
    });
</script>
<script>
    document.getElementById('import-form').addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);

        fetch("{{ route('pfi.import.preview') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name=_token]').value
                },
                body: formData
            })
            .then(res => res.json())
            .then(res => {

                // HIDE default table
                document.getElementById('default-table').classList.add('d-none');

                // SHOW preview table
                document.getElementById('preview-table').classList.remove('d-none');

                const tbody = document.getElementById('preview-body');
                tbody.innerHTML = '';

                res.Items.forEach(item => {
                    tbody.innerHTML += `
                <tr>
                    <td>${item["No."]}</td>
                    <td>${item.Description}</td>
                    <td>${item["Article Nr."]}</td>
                    <td>${item.Item.W} × ${item.Item.D} × ${item.Item.H}</td>
                    <td>${item.Packing.W} × ${item.Packing.D} × ${item.Packing.H}</td>
                    <td>${item.QTY ?? '-'}</td>
                    <td>${item.CBM ?? '-'}</td>
                    <td>${item["FOB JAKARTA IN USD"] ?? '-'}</td>
                </tr>
            `;
                });

                console.log('Company Profile:', res.CompanyProfile);
            })
            .catch(err => {
                alert('Gagal preview file');
                console.error(err);
            });
    });
</script>
<script>
document.getElementById('search-qc').addEventListener('input', function () {
    let val = this.value;

    // ubah "25-" -> "25 - "
    val = val.replace(/(\d)-$/g, '$1 - ');

    this.value = val;
});
</script>
<script>
    function loadPoTable(keyword = '') {
        fetch(`{{ route('qc.ajax.po') }}?q=${encodeURIComponent(keyword)}`)
            .then(res => res.json())
            .then(data => {
                const tbody = document.getElementById('po-table-body');
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
                                   class="btn btn-xs success btn-view"
                                   title="Ke detail QC">
                                    View
                                </a>
                            </td>
                        </tr>
                    `;
                });
            })
            .catch(err => console.error(err));
    }

    // load awal
    document.addEventListener('DOMContentLoaded', () => {
        loadPoTable();

        document.getElementById('search-qc')
            .addEventListener('keyup', function () {
                loadPoTable(this.value);
            });
    });
</script>


@endpush
