@extends('master.master')
{{-- laporan admin barang masuk  --}}
@section('title', 'Monitoring barang jadi - siti')

@section('content')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <div class="table-responsive table-sticky mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3 ml-4 mt-4">
        @section('btn')
            <div>
                <h6 class="mb-0 fw-bold">Monitoring Barang Jadi</h6>
                <small class="text-muted">History Barang Jadi</small>
            </div>
        @endsection


        <div class="d-flex gap-2" style="min-width:100px">
            <div class="toolbar">

                <div class="toolbar-left">

                    <div class="search-box">
                        <i class="fa fa-search"></i>
                        <input type="text" id="searchTable" class="form-control"
                            placeholder="Cari PO, Article, Supplier, SPK, Deskripsi...">
                    </div>

                    <select id="sortBy" class="form-control sort-select">
                        <option value="">Sort By</option>
                        <option value="1">No PO</option>
                        <option value="4">Nama Supplier</option>
                    </select>

                </div>

                <div class="toolbar-right">

                    <div class="date-group">
                        <label>From</label>
                        <input type="date" id="date_from" class="form-control" value="{{ request('from') }}">
                    </div>

                    <div class="date-group">
                        <label>To</label>
                        <input type="date" id="date_to" class="form-control" value="{{ request('to') }}">
                    </div>

                    <button class="btn btn-primary" id="btn-filter">
                        <i class="fa fa-search"></i>
                        Filter
                    </button>

                    <button class="btn btn-outline-secondary" id="btn-reset">
                        <i class="fa fa-rotate-left"></i>
                        Reset
                    </button>

                    <button class="btn btn-success" id="btn-export" disabled>
                        <i class="fa fa-file-excel"></i>
                        Download Rekap
                    </button>

                </div>

            </div>
        </div>
    </div>
</div>

<table class="table table-bordered table-hover" id="tblBarangJadi">
    <thead>
        <tr>
            <th class="sortable" data-column="0">
                Tanggal <i class="bi bi-arrow-down-up sort-icon"></i>
            </th>
            <th>PO</th>
            <th>Article Nr</th>
            <th>Desc</th>
            <th>Sub</th>
            <th>No SPK</th>
            <th>Qty SPK</th>
            <th>Qty masuk</th>
            <th>Type</th>
            <th>remark</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($timelines as $item)
            @php
                $spkItem = collect($item->spk->data['items'] ?? [])->firstWhere('detail_po_id', $item->detail_po_id);
            @endphp

            <tr>
                <td class="ellipsis w-120">{{ $item->date }}</td>
                <td class="ellipsis w-120">{{ $item->po->order_no ?? '-' }}</td>
                <td class="ellipsis w-120">{{ $item->detailPo->detail['article_nr_'] ?? '-' }}</td>
                <td class="ellipsis w-120">{{ $item->detailPo->detail['description'] ?? '-' }}</td>
                <td class="ellipsis w-120">{{ $item->spk->data['sup'] ?? '-' }}</td>
                <td class="ellipsis w-220">{{ $item->spk->data['no_spk'] ?? '-' }}</td>

                {{-- Qty dari SPK --}}
                <td class="ellipsis w-120" class="text-end">
                    {{ $spkItem['qty'] ?? 0 }}
                </td>

                {{-- Qty pada ProductionTimeline --}}
                <td class="ellipsis w-120" class="text-end">
                    {{ $item->qty }}
                </td>

                {{-- Type pada ProductionTimeline --}}
                <td class="ellipsis w-120">
                    {{ $item->type }}
                </td>
                <td class="ellipsis w-220">{{ $item->remark ?? '-' }}</td>

            </tr>
        @endforeach
    </tbody>
</table>
</div>
{{-- modals --}}
<div class="modal fade" id="modalRekap" tabindex="-1">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">

            <div class="modal-header bg-primary text-white">
                <h4 class="modal-title">
                    Rekapitulasi Barang Jadi
                </h4>

                <button class="close"
                        data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>

            <div class="modal-body p-2">

                <div class="table-wrapper-modal">

                    <table class="table table-bordered table-hover mb-0">

                        <thead>

                            <tr>

                                <th>Description</th>
                                <th>No SPK</th>
                                <th>Sub</th>
                                <th>Kategori</th>
                                <th>Qty Masuk</th>
                                <th>Qty PASS</th>
                                <th>Selisih</th>

                            </tr>

                        </thead>

                        <tbody id="tbodyRekap">

                        </tbody>

                    </table>

                </div>

            </div>

        </div>
    </div>
</div>
<style>
    #modalRekap .modal-dialog{

    max-width:1200px;

}

#modalRekap .modal-body{

    padding:10px;

}

.table-wrapper-modal{

    max-height:70vh;
    overflow-y:auto;
    overflow-x:auto;

}

.table-wrapper-modal table{

    margin-bottom:0;

}

.table-wrapper-modal thead th{

    position:sticky;

    top:0;

    z-index:100;

    background:#1E2B45;

    color:white;

    white-space:nowrap;

}

.table-wrapper-modal tbody td{

    vertical-align:middle;

}
    #modalRekap table th {

        white-space: nowrap;
        text-align: center;
        vertical-align: middle;

    }

    #modalRekap table td {

        vertical-align: middle;

    }

    #modalRekap .modal-dialog {

        max-width: 1300px;

    }

    .toolbar {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        gap: 20px;
        margin-bottom: 15px;
        flex-wrap: wrap;
    }

    .toolbar-left {
        display: flex;
        align-items: center;
        gap: 12px;
        flex: 1;
    }

    .toolbar-right {
        display: flex;
        align-items: flex-end;
        gap: 10px;
        flex-wrap: wrap;
    }

    .search-box {
        position: relative;
        flex: 1;
        min-width: 320px;
    }

    .search-box i {
        position: absolute;
        left: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #999;
    }

    .search-box input {
        padding-left: 35px;
        height: 38px;
    }

    .sort-select {
        width: 180px;
        height: 38px;
    }

    .date-group {
        display: flex;
        flex-direction: column;
    }

    .date-group label {
        font-size: 11px;
        font-weight: 600;
        color: #666;
        margin-bottom: 3px;
    }

    .date-group input {
        width: 150px;
        height: 38px;
    }

    .toolbar .btn {
        height: 38px;
        padding: 0 16px;
        white-space: nowrap;
    }

    .table-sticky {
        max-height: 80vh;
        /* tinggi area scroll */
        overflow: auto;
    }

    .table-sticky thead th {
        position: sticky;
        top: 0;
        z-index: 100;
        /* background: #fff;      atau warna header Anda */
        box-shadow: 0 2px 2px rgba(0, 0, 0, .08);
    }

    .ellipsis {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .w-120 {
        width: 120px;
        max-width: 120px;
    }

    .w-110 {
        width: 110px;
        max-width: 110px;
    }

    .w-220 {
        width: 220px;
        max-width: 220px;
    }

    .sortable {
        cursor: pointer;
        user-select: none;
    }

    .sort-icon {
        font-size: 12px;
        margin-left: 4px;
        opacity: .6;
    }

    .sortable:hover .sort-icon {
        opacity: 1;
    }
</style>
<script>
    const tbody = document.querySelector("#tblBarangJadi tbody");
    const headers = document.querySelectorAll("#tblBarangJadi thead th.sortable");

    let sortDirection = {};

    headers.forEach(header => {

        header.addEventListener("click", function() {

            const column = parseInt(this.dataset.column);

            sortDirection[column] = !sortDirection[column];

            const rows = Array.from(tbody.querySelectorAll("tr"));

            rows.sort((a, b) => {

                if (column === 0) { // Kolom Tanggal
                    const dateA = parseTanggal(a.cells[0].innerText.trim());
                    const dateB = parseTanggal(b.cells[0].innerText.trim());

                    return sortDirection[column] ?
                        dateA - dateB // Terlama → Terbaru
                        :
                        dateB - dateA; // Terbaru → Terlama
                }

                // sort biasa
                let A = a.cells[column].innerText.trim().toLowerCase();
                let B = b.cells[column].innerText.trim().toLowerCase();

                return sortDirection[column] ?
                    A.localeCompare(B) :
                    B.localeCompare(A);

            });
            rows.forEach(r => tbody.appendChild(r));

            // Reset semua icon
            headers.forEach(h => {
                h.querySelector("i").className = "bi bi-arrow-down-up sort-icon";
            });

            // Ganti icon sesuai arah sort
            this.querySelector("i").className =
                sortDirection[column] ?
                "bi bi-sort-down sort-icon" :
                "bi bi-sort-up sort-icon";

        });

    });

    function parseTanggal(text) {
        // dd-mm-yyyy
        if (/^\d{2}-\d{2}-\d{4}$/.test(text)) {
            const [d, m, y] = text.split("-");
            return new Date(y, m - 1, d);
        }

        // yyyy-mm-dd
        if (/^\d{4}-\d{2}-\d{2}$/.test(text)) {
            return new Date(text);
        }

        // dd-MMM-yyyy
        return new Date(text);
    }
</script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const rows = document.querySelectorAll('#tblBarangJadi tbody tr');

        rows.forEach(row => {
            row.addEventListener('click', function() {
                // Hapus highlight dari semua baris
                rows.forEach(r => r.classList.remove('table-active'));

                // Highlight baris yang dipilih
                this.classList.add('table-active');
            });
        });
    });
    document.addEventListener('DOMContentLoaded', function() {

        const table = document.getElementById('tblBarangJadi');
        const tbody = table.querySelector('tbody');
        const rows = Array.from(tbody.querySelectorAll('tr'));

        // Highlight row
        rows.forEach(row => {
            row.addEventListener('click', function() {
                rows.forEach(r => r.classList.remove('table-active'));
                this.classList.add('table-active');
            });
        });

        // SEARCH
        document.getElementById('searchTable').addEventListener('keyup', function() {

            let keyword = this.value.toLowerCase();

            tbody.querySelectorAll('tr').forEach(row => {

                // ambil semua text kecuali kolom Qty SPK dan Qty Masuk
                let text = '';

                row.querySelectorAll('td').forEach((td, index) => {
                    if (index != 6 && index != 7) { // skip Qty SPK & Qty Masuk
                        text += td.innerText.toLowerCase() + ' ';
                    }
                });

                row.style.display = text.includes(keyword) ? '' : 'none';

            });

        });

        // SORT
        document.getElementById('sortBy').addEventListener('change', function() {

            let column = parseInt(this.value);

            if (isNaN(column)) return;

            let sorted = [...rows].sort((a, b) => {

                let textA = a.cells[column].innerText.trim().toLowerCase();
                let textB = b.cells[column].innerText.trim().toLowerCase();

                return textA.localeCompare(textB);
            });

            sorted.forEach(r => tbody.appendChild(r));

        });

    });
    document.getElementById('btn-filter').addEventListener('click', function() {

        const from = document.getElementById('date_from').value;
        const to = document.getElementById('date_to').value;

        const params = new URLSearchParams();

        if (from) params.append('from', from);
        if (to) params.append('to', to);

        window.location = "{{ route('barang.jadi') }}?" + params.toString();
    });
    document.addEventListener('DOMContentLoaded', function() {

        const from = document.getElementById('date_from');
        const to = document.getElementById('date_to');
        const exportBtn = document.getElementById('btn-export');

        function toggleExport() {
            exportBtn.disabled = !(from.value && to.value);
        }

        // Saat halaman pertama kali dibuka
        toggleExport();

        // Saat user mengubah tanggal
        from.addEventListener('change', toggleExport);
        to.addEventListener('change', toggleExport);

    });
    // btn 
    document.getElementById('btn-export').addEventListener('click', function() {

        const from = document.getElementById('date_from').value;
        const to = document.getElementById('date_to').value;

        window.location =
            "{{ route('barang.jadi.export') }}" +
            "?from=" + encodeURIComponent(from) +
            "&to=" + encodeURIComponent(to);

    });



    function loadRekap(from,to){

    $.get(
        "{{ route('barang.jadi.rekap') }}",
        {
            from:from,
            to:to
        },
        function(rows){

            let html='';

            rows.forEach(function(r){

                let badge='';

                if(r.selisih==0){

                    badge='<span class="badge badge-success">MATCH</span>';

                }else{

                    badge='<span class="badge badge-danger">'
                            +r.selisih+
                          '</span>';

                }

                html+=`

                <tr>

                    <td>${r.description}</td>

                    <td>${r.spk}</td>

                    <td>${r.sub}</td>
                    <td>${r.kategori}</td>

                    <td class="text-center">
                        ${r.qty_in}
                    </td>

                    <td class="text-center">
                        ${r.qty_pass}
                    </td>

                    <td class="text-center">
                        ${badge}
                    </td>

                </tr>

                `;

            });

            $('#tbodyRekap').html(html);

            $('#modalRekap').modal('show');

        }
    );

}
$('#btn-reset').click(function () {

    $('#date_from').val('');
    $('#date_to').val('');

    $('#modalRekap').modal('hide');
    $('#tbodyRekap').empty();

    window.location = "{{ route('barang.jadi') }}";
});
</script>
@if(request()->filled('from') && request()->filled('to'))

<script>

$(function () {

    loadRekap(
        "{{ request('from') }}",
        "{{ request('to') }}"
    );

});

</script>

@endif
@endsection
