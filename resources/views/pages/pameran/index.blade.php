@extends('master.master')
@section('title', "Pameran list")
@section('content')
<div class="padding">
    <div class="box">
        <div class="p-a white lt box-shadow">
            <div class="row">
                <div class="col-sm-6">
                    <h4 class="mb-0 _300">Pameran View</h4>
                    <small class="text-muted">PT. Newwicker Indonesia</small>
                </div>

                <div class="col-sm-6 text-sm-right">
                    <div class="m-y-sm">
                        <!-- Dropdown Tahun -->
                        <select name="exhibition_id" id="exhibition_year" class="form-control">
                            <option value="">-- Semua Tahun --</option>
                            @foreach($e as $exhibition)
                                <option value="{{ $exhibition->id }}" {{ request('exhibition_id') == $exhibition->id ? 'selected' : '' }}>
                                    {{ $exhibition->name }}
                                </option>
                            @endforeach
                        </select>

                        <!-- Form Import -->
                        <form id="importForm" enctype="multipart/form-data" class="mt-2">
                            @csrf
                            <input type="hidden" name="exhibition_id" id="exhibition_id_input" value="{{ request('exhibition_id') }}">
                            <div class="mb-3">
                                <label>Pilih File Excel</label>
                                <input type="file" name="file" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Import</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="col-12 mt-3">
            <div class="table-wrapper">
                <table border="1" cellspacing="0" cellpadding="5" class="table table-bordered text-center align-middle">
                    <thead class="table-light">
                        <tr>
                            <th rowspan="2">Nr.</th>
                            <th rowspan="2">Photo</th>
                            <th rowspan="2">Article Code</th>
                            <th rowspan="2">Name</th>
                            <th rowspan="2">Categories</th>

                            <!-- Item Dimension -->
                            <th colspan="3" style="background-color: blue;">Item Dimension</th>

                            <!-- Packing Dimension -->
                            <th colspan="3" style="background-color: blue;">Packing Dimension</th>

                            <!-- Size of Set -->
                            <th colspan="4">Size of Set</th>

                            <th rowspan="2">Composition</th>
                            <th rowspan="2">Finishing</th>
                            <th rowspan="2">QTY</th>
                            <th rowspan="2">CBM</th>

                            <!-- Loadability -->
                            <th colspan="3">Loadability</th>

                            <th rowspan="2">Rangka</th>
                            <th rowspan="2">Anyam</th>
                            <th rowspan="2">Finishing / Powder</th>
                            <th rowspan="2">Accessories / Final</th>
                            <th rowspan="2">Electricity</th>
                        </tr>
                        <tr>
                            <th>W</th><th>D</th><th>H</th>
                            <th>W</th><th>D</th><th>H</th>
                            <th>Set 2</th><th>Set 3</th><th>Set 4</th><th>Set 5</th>
                            <th>20'</th><th>40'</th><th>40HC</th>
                            <th rowspan="2">Remark</th>
                        </tr>
                    </thead>
                    <tbody id="pameranTableBody">
                        @include('pages.pameran._table', ['pm' => $pm])
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal hasil import -->
    <div class="modal fade" id="importResultModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Hasil Import</h5>
                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                </div>
                <div class="modal-body" id="resultTableBody"></div>
                <div class="text-right mb-2 mr-2">
                    <button class="btn btn-sm btn-success" id="btnBulkSave">Simpan Data Baru</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="importResult" class="mt-3"></div>

<div id="loadingIndicator" class="alert alert-warning text-center" style="display:none;">
    Memuat data, mohon tunggu...
</div>

<script>
    document.addEventListener("click", function(e) {
    if (e.target.closest("#refreshBtn")) {
        let exhibitionId = document.getElementById('exhibition_year').value;

        fetch(`{{ route('pameran.filter') }}?exhibition_id=${exhibitionId}`)
            .then(res => res.text())
            .then(html => {
                document.getElementById("pameranTableBody").innerHTML = html;
            })
            .catch(err => {
                alert("Gagal refresh tabel: " + err);
            });
    }
});
// 🔹 Update hidden input exhibition_id ketika select berubah
document.getElementById('exhibition_year').addEventListener('change', function() {
    document.getElementById('exhibition_id_input').value = this.value;

    let tbody = document.getElementById('pameranTableBody');
    let loading = document.getElementById('loadingIndicator');
    tbody.innerHTML = `<tr><td colspan="30">Memuat data...</td></tr>`;
    loading.style.display = 'block';

    fetch(`{{ route('pameran.filter') }}?exhibition_id=${this.value}`, {
        headers: {'X-Requested-With': 'XMLHttpRequest'}
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === 'success') {
            tbody.innerHTML = data.data;
        } else {
            tbody.innerHTML = `<tr><td colspan="30">Gagal memuat data</td></tr>`;
        }
        loading.style.display = 'none';
    })
    .catch(err => {
        console.error(err);
        tbody.innerHTML = `<tr><td colspan="30">Error memuat data</td></tr>`;
        loading.style.display = 'none';
    });
});

// 🔹 Import Form
document.getElementById('importForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const form = e.target;
    const formData = new FormData(form);

    fetch("{{ route('product_pameran.import') }}", {
        method: "POST",
        headers: {'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value},
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        let resultDiv = document.getElementById('importResult');
       if (data.status === 'success') {
        resultDiv.innerHTML = `
            <div class="alert alert-success d-flex align-items-center justify-content-between">
                <div>
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <span>${data.message}</span>
                </div>
                <button id="refreshBtn" class="btn btn-sm btn-light">
                    <i class="bi bi-arrow-repeat"></i> Refresh
                </button>
            </div>
        `;
        } else {
            resultDiv.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
        }
    })
    .catch(error => {
        document.getElementById('importResult').innerHTML = `<div class="alert alert-danger">Terjadi kesalahan: ${error}</div>`;
    });
});
</script>

<style>
thead th {
    background-color: #f2f2f2;
    color: #000;
    font-weight: bold;
    text-align: center;
    vertical-align: middle;
    border: 1px solid #000;
}
</style>
@endsection
