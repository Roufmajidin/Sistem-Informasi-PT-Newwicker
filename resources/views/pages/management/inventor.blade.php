    @extends('master.master')
    @section('title', 'monitorings SPK')
    @section('content')
        @include('pages.management.style')
        <style>
            /* Tinggi select */
        .select2-container .select2-selection--single{
            height:38px !important;
            border:1px solid #ced4da !important;
            border-radius:.375rem !important;
        }

        /* Posisi teks */
        .select2-container--default .select2-selection--single .select2-selection__rendered{
            line-height:36px !important;
            padding-left:12px;
        }

        /* Posisi icon panah */
        .select2-container--default .select2-selection--single .select2-selection__arrow{
            height:36px !important;
            right:8px;
        }

        /* Lebar penuh */
        .select2-container{
            width:100% !important;
        }
            .harga-vivi-input{
    border:none;
    background:transparent;
    width:100%;
    outline:none;
    box-shadow:none;
    padding:0;
}

.harga-vivi-input:focus{
    border:none;
    outline:none;
    box-shadow:none;
    background:#fffbe6;
}
.harga-vivi-input{
    border:none;
    background:transparent;
    width:100%;
    padding:2px 4px;
}

.harga-vivi-input:hover{
    background:#f8f9fa;
}

.harga-vivi-input:focus{
    background:#fff3cd;
    border:1px solid #ffc107;
}
            .deadline-card {
                min-width: 220px;
            }

            .deadline-bar {
                height: 10px;
                background: #edf2f7;
                border-radius: 20px;
                overflow: hidden;
            }

            .deadline-fill {
                height: 100%;
                border-radius: 20px;
                transition: .5s ease;
            }

            .deadline-success {
                background: linear-gradient(90deg,
                        #16a34a,
                        #22c55e);
            }

            .deadline-info {
                background: linear-gradient(90deg,
                        #0891b2,
                        #06b6d4);
            }

            .deadline-warning {
                background: linear-gradient(90deg,
                        #f59e0b,
                        #fbbf24);
            }

            .deadline-danger {
                background: linear-gradient(90deg,
                        #dc2626,
                        #ef4444);
            }

            .deadline-secondary {
                background: linear-gradient(90deg,
                        #94a3b8,
                        #cbd5e1);
            }

            .deadline-footer {
                display: flex;
                justify-content: space-between;
                font-size: 11px;
                margin-top: 4px;
                color: #64748b;
            }

            .modal-full-custom {
                max-width: 96%;
            }

            .inventor-row {
                cursor: pointer;
                transition: .2s;
            }

            .inventor-row:hover {
                background: #f5f5f5;
            }

            .qty-warning {
                font-size: 11px;
            }

            .timeline-wrapper {
                max-height: 200px;
                overflow-y: auto;
            }

            .timeline-wrapper thead th {
                position: sticky;
                top: 0;
                z-index: 10;
                /* background: #f8f9fa; */
            }
        </style>
        @php
            $hideHarga = in_array(auth()->user()->email, [
                'john@gmail.com',
                'aji@gmail.com',
            ]);
        @endphp
        <div class="container-fluid py-4">
            <div class="card inventor-card">
                <div class="card-header bg-white">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        {{-- LEFT --}}
                        <h4 class="mb-0 fw-bold">
                            Monitoring SPK
                        </h4>
                        {{-- RIGHT --}}

                        <div class="d-flex gap-2">
                            @php
                        $suppliers = array_unique(array_column($spks, 'supplier'));
                        @endphp

                        <input
                            type="text"
                            id="searchSub"
                            list="supplierList"
                            class="form-control"
                            placeholder="Cari Supplier...">

                        <datalist id="supplierList">
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier }}">
                            @endforeach
                        </datalist>
                            <input type="text" id="searchSpk" class="form-control" placeholder="Cari No PO / No SPK..."
                                style="width:300px">
                            <select id="filterSpkType" class="form-control">
                                <option value="">Semua SPK</option>
                                <option value="NW">SPK Produksi</option>
                                <option value="NWS">SPK Sampel</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table inventor-table table-bordered align-middle">
                            <thead>
                                <tr>
                                    <th width="60">   No  </th>
                                    <th>  No SPK  </th>
                                    <th>   Supplier </th>
                                    <th>   Kategori</th>
                                    <th>  PO </th>
                                    <th>    Tgl Terima </th>
                                    <th>     Deadline </th>
                                    <th>  Tenggat </th>
                                    <th>Status</th>
                                    @if(!$hideHarga)

                                      <th>Vivi </th>
                                    <th>Mr. Stanley </th>
                                    <th>Didin </th>
                                      @endif

                                     <th width="120">Total Item
                                    </th>
                                    <th width="120">Action </th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($spks as $spk)
                                    <tr>
                                        <td>
                                            {{ $loop->iteration }}
                                        </td>
                                        {{-- CLICK FOR INPUT --}}
                                       <td
                                            class="{{ $hideHarga ? '' : 'inventor-row' }} spk-col"
                                            data-id="{{ $spk['id'] }}"
                                        >
                                            <div class="fw-bold text-primary">
                                                {{ $spk['no_spk'] }}
                                            </div>
                                        </td>
                                        <td class="inventor-row" data-id="{{ $spk['id'] }}">
                                            {{ $spk['supplier'] }}
                                        </td>
                                        <td class="inventor-row" data-id="{{ $spk['id'] }}">
                                            <span class="badge bg-info">
                                                {{ strtoupper($spk['kategori']) }}
                                            </span>
                                        </td>
                                        <td class="inventor-row po-col" data-id="{{ $spk['id'] }}">
                                            {{ $spk['no_po'] }}
                                        </td>
                                        <td class="inventor-row" data-id="{{ $spk['id'] }}">
                                            {{ $spk['tgl_terima'] }}
                                        </td>
                                        <td class="inventor-row" data-id="{{ $spk['id'] }}">
                                            {{ $spk['tgl_selesai'] }}
                                        </td>
                                        <td>
                                            <div class="deadline-card">

                                                <div class="deadline-bar">

                                                    <div class="deadline-fill deadline-{{ $spk['deadline_color'] }}"
                                                        style="width: {{ $spk['deadline_percent'] }}%">
                                                    </div>

                                                </div>

                                                <div class="deadline-footer">
                                                    <span>
                                                        {{ $spk['deadline_text'] }}
                                                    </span>

                                                    <span>
                                                        <!-- {{ $spk['deadline_percent'] }}% -->
                                                    </span>
                                                </div>

                                            </div>
                                        </td>
                                      <td>"{{ $spk['status'] }}"</td>
                                    @if(!$hideHarga)

                                    {{-- VIVI --}}
                                    <td class="text-center">

                                        @if(!empty($spk['signature']['checked_at']))

                                            <span class="badge bg-success">
                                                <i class="fa fa-check"></i>
                                                Signed
                                            </span>

                                            <br>

                                            <small class="text-muted">
                                                {{ $spk['signature']['checked_by'] }}
                                            </small>

                                        @elseif($spk['status'] == 'diajukan')

                                            <span class="badge bg-warning">
                                                Pending
                                            </span>

                                        @else

                                            <span class="text-muted">-</span>

                                        @endif

                                    </td>

                                    {{-- DIDIN --}}
                                    <td class="text-center">

                                        @if(!empty($spk['signature']['checked_at_2']))

                                            <span class="badge bg-success">
                                                <i class="fa fa-check"></i>
                                                Signed
                                            </span>

                                            <br>

                                            <small class="text-muted">
                                                {{ $spk['signature']['checked_by_2'] }}
                                            </small>

                                        @elseif($spk['status'] == 'diajukan')

                                            <span class="badge bg-warning">
                                                Pending
                                            </span>

                                        @else

                                            <span class="text-muted">-</span>

                                        @endif

                                    </td>

                                    {{-- MR STANLEY --}}
                                    <td class="text-center">

                                        @if(!empty($spk['signature']['approved_at']))

                                            <a href="{{ url('/spk/views/'.$spk['id']) }}">
                                                <span class="badge bg-success">
                                                    <i class="fa fa-check"></i>
                                                    Signed
                                                </span>
                                            </a>

                                            <br>

                                            <small class="text-muted">
                                                {{ $spk['signature']['approved_by'] }}
                                            </small>

                                        @elseif(
                                            $spk['status'] == 'diajukan'
                                            && auth()->id() == 191
                                        )

                                            <button
                                                type="button"
                                                class="btn btn-warning btn-sm btn-approve-spk"
                                                data-id="{{ $spk['id'] }}">
                                                Pending Approval
                                            </button>

                                        @elseif($spk['status'] == 'diajukan')

                                            <a href="{{ url('/spk/views/'.$spk['id']) }}">
                                                <span class="badge bg-warning">
                                                    Pending Mr. Stanley
                                                </span>
                                            </a>

                                        @else

                                            <span class="text-muted">-</span>

                                        @endif

                                    </td>
                                    @endif
                                        <td class="text-center inventor-row" data-id="{{ $spk['id'] }}">
                                            {{ count($spk['items']) }}
                                        </td>
                                        {{-- DETAIL BUTTON --}}
                                        <td>

                                        {{-- Selain Admin Produksi dan bukan John/Aji --}}
                                        @if(
                                            auth()->user()->role != 'admin produksi'
                                            && !in_array(auth()->user()->email, [
                                                'john@gmail.com',
                                                'aji@gmail.com',
                                            ])
                                        )

                                            @if($spk['status'] == 'draft')
                                                <button
                                                    type="button"
                                                    class="btn btn-success btn-sm btn-ajukan"
                                                    data-id="{{ $spk['id'] }}"
                                                    data-spk="{{ $spk['no_spk'] }}">
                                                    Ajukan
                                                </button>
                                            @endif

                                        @endif

                                        {{-- Detail boleh untuk semua selain Admin Produksi, termasuk John & Aji --}}
                                        @if(
                                            auth()->user()->role != 'admin produksi'
                                            || in_array(auth()->user()->email, [
                                                'john@gmail.com',
                                                'aji@gmail.com',
                                            ])
                                        )
                                            <button
                                                type="button"
                                                class="btn btn-primary btn-sm btn-detail"
                                                data-id="{{ $spk['id'] }}">
                                                Detail
                                            </button>
                                        @endif

                                    </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="10" class="text-center text-muted py-5">
                                            Tidak ada data SPK
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        {{-- ====================================================== --}}
        {{-- MODAL INPUT INVENTORY --}}
        {{-- ====================================================== --}}
        <div class="modal fade" id="inventorModal" tabindex="-1">
            <div class="modal-dialog modal-full-custom modal-dialog-scrollable">
                <div class="modal-content border-0 shadow-lg">
                    {{-- HEADER --}}
                    <div class="modal-header bg-dark text-white">
                        <h4 class="mb-0">
                            INPUT INVENTORY
                        </h4>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal">
                        </button>
                    </div>
                    {{-- BODY --}}
                    <div class="modal-body">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h5 class="mb-0 fw-bold" id="modalSpkTitleText">
                                SPK #
                            </h5>
                            <button type="button" class="btn btn-primary" id="btnAddRow">
                                + Tambah
                            </button>
                        </div>
                        <form id="inventorForm">
                            @csrf
                            <input type="hidden" name="spk_id" id="modal_spk_id">
                            <div class="table-responsive">
                                <table class="table table-bordered align-middle">
                                    <thead class="table-dark">
                                        <tr>
                                            <th width="320">
                                                Item
                                            </th>
                                            <th width="140">
                                                Type
                                            </th>
                                            <th width="120">
                                                Qty
                                            </th>
                                            <th width="220">
                                                Supplier
                                            </th>
                                            <th width="160">
                                                Date
                                            </th>
                                            <th width="140">
                                                Time
                                            </th>
                                            <th>
                                                Remark
                                            </th>
                                            <th width="70">
                                                #
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody id="inventorTableBody">
                                    </tbody>
                                </table>
                            </div>
                            <div class="text-end mt-4">
                                <button type="submit" class="btn btn-success">
                                    Simpan Inventory
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        {{-- ====================================================== --}}
        {{-- MODAL DETAIL inventor --}}
        {{-- ====================================================== --}}
        <div class="modal fade" id="inventorDetailModal" tabindex="-1">
            <div class="modal-dialog modal-full-custom modal-dialog-scrollable">
                <div class="modal-content border-0 shadow-lg">
                    <div class="modal-header bg-dark text-white">
                        <h4 class="mb-0">
                            DETAIL INVENTORY
                        </h4>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal">
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            {{-- LEFT --}}
                            <div class="col-md-4">
                                <div class="card mb-3 border-0 shadow-sm">
                                    <div class="card-header bg-primary text-white">
                                        INFORMASI SPK
                                    </div>
                                    <div class="card-body" id="spkInfoArea">
                                    </div>
                                </div>
                                <div class="card border-0 shadow-sm">
                                    <div class="card-header bg-secondary text-white">
                                        <p style="color:black"> LIST ITEM</p>
                                    </div>
                                    <div class="card-body">
                                        <div id="itemArea">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            {{-- RIGHT --}}
                            <div class="col-md-8">
                                {{-- TIMELINE --}}
                                <div class="card mb-4 border-0 shadow-sm">
                                    <div class="card-header bg-warning fw-bold">
                                        INVENTORY TIMELINE
                                    </div>
                                    <div class="card-body p-0">
                                        <div class="table-responsive timeline-wrapper">
                                            <table class="table table-bordered mb-0">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>No</th>
                                                        <th>Tanggal</th>
                                                        <th>Items</th>
                                                        <th>Supplier</th>
                                                        <th>Type</th>
                                                        <th>Qty</th>
                                                        <th>Remark</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="timelineArea">
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <!-- bahan baku  -->
                                @if(!$hideHarga)
                                <div class="card border-0 shadow-sm">
                                    <div class="card-header bg-success text-white fw-bold">
                                        LIST BAHAN BAKU PENGAMBILAN
                                    </div>
                                    <div class="card-body p-0">
                                        <div class="table-responsive">
                                            <table class="table table-bordered mb-0">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>No</th>
                                                        <th>Tanggal</th>
                                                        <th>Tipe</th>
                                                        <th>Bahan</th>
                                                        <th>potong bahan</th>
                                                        <th>harga inventory</th>
                                                        <th>harga (adjusment)</th>
                                                        <th>Total</th>
                                                        <th>Keterangan</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="bahanbakuArea">
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                @endif
                                {{-- PAYMENT --}}
                                @if(!$hideHarga)
                                <div class="card border-0 shadow-sm">
                                    <div class="card-header bg-success text-white fw-bold">
                                        LIST PAYMENT
                                    </div>
                                    <div class="card-body p-0">
                                        <div class="table-responsive">
                                            <table class="table table-bordered mb-0">
                                                <thead class="table-light">
                                                    <tr>
                                                        <th>No</th>
                                                        <th>Tanggal</th>
                                                        <th>Type</th>
                                                        <th>Nilai</th>
                                                        <th>
                                                            Saldo
                                                        </th>
                                                        <th>Keterangan</th>
                                                        <th>stst request</th>
                                                        <th>Adjustment Finance</th>
                                                    </tr>
                                                </thead>
                                                <tbody id="paymentArea">
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endsection
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


    @push('scripts')
        <script>

            const hideHarga = @json($hideHarga);
            /*
                                |--------------------------------------------------------------------------
                                | OPEN INPUT MODAL
                                |--------------------------------------------------------------------------
                                */
            $(document).on('click', '.inventor-row', function() {
                let spkId = $(this).data('id');
                $('#modal_spk_id')
                    .val(spkId);
                $.get(`/inventor/spk/${spkId}`, function(res) {
                    window.currentItems =
                        res.items;
                    window.currentSupplier =
                        res.supplier;
                    $('#modalSpkTitleText')
                        .html(
                            'SPK # ' +
                            (res.spk_no ?? '-')
                        );
                    $('#inventorTableBody')
                        .html('');
                    /*
                    |--------------------------------------------------------------------------
                    | EXISTING DATA
                    |--------------------------------------------------------------------------
                    */
                    if (
                        Array.isArray(res.timelines) &&
                        res.timelines.length > 0
                    ) {
                        res.timelines.forEach(function(row) {
                            addInventorRow({
                                id: row.id,
                                detail_po_id: row.detail_po_id,
                                qty: row.qty,
                                type: row.type,
                                remark: row.remark,
                                date: row.date,
                                time: row.time,
                            });
                        });
                    } else {
                        addInventorRow();
                    }
                    validateQty();
                    $('#inventorModal')
                        .modal('show');
                });
                $('#inventorTableBody tr:last .item-select').select2({
                    width: '100%',
                    placeholder: 'Cari Item...',
                    dropdownParent: $('#inventorModal')
                });
            });

            /*
            |--------------------------------------------------------------------------
            | ADD ROW
            |--------------------------------------------------------------------------
            */
            function addInventorRow(data = null) {
                let itemOptions = '';
                currentItems.forEach(item => {
                    let selected = '';
                    if (
                        data &&
                        data.detail_po_id == item.detail_po_id
                    ) {
                        selected = 'selected';
                    }
                    itemOptions += `
                    <option
                        value="${item.detail_po_id}"
                        data-maxqty="${item.qty}"
                        ${selected}>
                        ${item.nama} (${item.qty})
                    </option>
                `;
                });
                let html = `
                <tr>
                    {{-- ITEM --}}
                    <td>
                        <select
                            name="detail_po_id[]"
                            class="form-control form-select">
                            ${itemOptions}
                        </select>
                    </td>
                    {{-- TYPE --}}
                    <td>
                        <select
                            name="type[]"
                            class="form-control form-select">
                            <option
                                value="in"
                                ${data?.type == 'in' ? 'selected' : ''}>
                                IN
                            </option>
                            <option
                                value="kirim_rangka"
                                ${data?.type == 'kirim_rangka' ? 'selected' : ''}>
                                KIRIM RANGKA
                            </option>
                            <option
                                value="return"
                                ${data?.type == 'return' ? 'selected' : ''}>
                                RETURN
                            </option>
                        </select>
                    </td>
                    {{-- QTY --}}
                    <td>
                        <input
                            type="number"
                            name="qty[]"
                            class="form-control qty-input"
                            value="${data?.qty ?? ''}">
                        <small class="text-danger qty-warning d-none">
                            Qty melebihi batas
                        </small>
                    </td>
                    {{-- SUPPLIER --}}
                    <td>
                        <select
                            name="sup_id[]"
                            class="form-control form-select">
                            <option value="${currentSupplier.id}">
                                ${currentSupplier.name}
                            </option>
                        </select>
                    </td>
                    {{-- DATE --}}
                    <td>
                        <input
                            type="date"
                            name="date[]"
                            class="form-control"
                            value="${data?.date ?? ''}">
                    </td>
                    {{-- TIME --}}
                    <td>
                        <input
                            type="time"
                            name="time[]"
                            class="form-control"
                            value="${data?.time ?? ''}">
                    </td>
                    {{-- REMARK --}}
                    <td>
                        <input
                            type="text"
                            name="remark[]"
                            class="form-control"
                            value="${data?.remark ?? ''}">
                    </td>
                    {{-- DELETE --}}
                <td class="text-center">
        <button
            type="button"
            class="btn btn-danger btn-sm btn-remove-row"
            data-id="${data?.id ?? ''}">
            ×
        </button>
    </td>
                </tr>
            `;
                $('#inventorTableBody')
                    .append(html);
            }
            /*
            |--------------------------------------------------------------------------
            | VALIDATE QTY
            |--------------------------------------------------------------------------
            */
            function validateQty() {
                let totals = {};
                $('#inventorTableBody tr').each(function() {
                    let row = $(this);
                    let itemId = row.find('select[name="detail_po_id[]"]').val();
                    let qty = parseInt(
                        row.find('.qty-input').val()
                    ) || 0;
                    if (!totals[itemId]) {
                        totals[itemId] = 0;
                    }
                    totals[itemId] += qty;
                });
                let hasError = false;
                $('#inventorTableBody tr').each(function() {
                    let row = $(this);
                    let select = row.find('select[name="detail_po_id[]"]');
                    let selectedOption =
                        select.find('option:selected');
                    let itemId =
                        select.val();
                    let maxQty =
                        parseInt(
                            selectedOption.data('maxqty')
                        ) || 0;
                    let totalQty =
                        totals[itemId] || 0;
                    let input =
                        row.find('.qty-input');
                    let warning =
                        row.find('.qty-warning');
                    if (totalQty > maxQty) {
                        hasError = true;
                        input.addClass('is-invalid');
                        warning
                            .removeClass('d-none')
                            .html(
                                `Total qty ${totalQty} melebihi batas ${maxQty}`
                            );
                    } else {
                        input.removeClass('is-invalid');
                        warning.addClass('d-none');
                    }
                });
                $('button[type="submit"]')
                    .prop('disabled', hasError);
            }
            /*
            |--------------------------------------------------------------------------
            | VALIDATE EVENT
            |--------------------------------------------------------------------------
            */
            $(document).on(
                'keyup change',
                '.qty-input, select[name="detail_po_id[]"]',
                function() {
                    validateQty();
                }
            );
            /*
            |--------------------------------------------------------------------------
            | ADD ROW BUTTON
            |--------------------------------------------------------------------------
            */
            $(document).on('click', '#btnAddRow', function() {
                addInventorRow();
            });
            /*
            |--------------------------------------------------------------------------
            | REMOVE ROW
            |--------------------------------------------------------------------------
            */
            $(document).on('click', '.btn-remove-row', function() {
                let btn = $(this);
                let row = btn.closest('tr');
                let timelineId = btn.data('id');
                // alert(timelineId);
                /*
                |--------------------------------------------------------------------------
                | DELETE DATABASE
                |--------------------------------------------------------------------------
                */
                if (timelineId) {
                    Swal.fire({
                        title: 'Hapus data?',
                        text: 'Data inventory akan dihapus',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Ya Hapus'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: `/inventor/delete/${timelineId}`,
                                type: 'DELETE',
                                data: {
                                    _token: $('meta[name="csrf-token"]').attr('content')
                                },
                                success: function(res) {
                                    row.remove();
                                    validateQty();
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Berhasil',
                                        text: res.message
                                    });
                                },
                                error: function() {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Oops',
                                        text: 'Gagal hapus data'
                                    });
                                }
                            });
                        }
                    });
                } else {
                    /*
                    |--------------------------------------------------------------------------
                    | DELETE ROW BARU (BELUM TERSIMPAN)
                    |--------------------------------------------------------------------------
                    */
                    row.remove();
                    validateQty();
                }
            });
            /*
            |--------------------------------------------------------------------------
            | SUBMIT
            |--------------------------------------------------------------------------
            */
            $('#inventorForm').submit(function(e) {
                e.preventDefault();
                $.ajax({
                    url: "{{ route('inventor.store') }}",
                    type: "POST",
                    data: $(this).serialize(),
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(res) {
                        $('#inventorModal')
                            .modal('hide');
                        Swal.fire({
                            icon: 'success',
                            title: 'Success',
                            text: res.message
                        });
                    },
                    error: function(xhr) {
                        console.log(xhr);
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops',
                            text: 'Gagal simpan data'
                        });
                    }
                });
            });
            /*
            |--------------------------------------------------------------------------
            | DETAIL BUTTON
            |--------------------------------------------------------------------------
            */
            $(document).on('click', '.btn-detail', function(e) {
                e.stopPropagation();
                let spkId = $(this).data('id');
                $.get(`/inventor/spk/${spkId}`, function(res) {
                    /*
                    |--------------------------------------------------------------------------
                    | INFO
                    |--------------------------------------------------------------------------
                    */
                let bahanHtml = '';

                if (Array.isArray(res.bahan_baku) && res.bahan_baku.length > 0) {

                    res.bahan_baku.forEach((row, i) => {

                        let qty = parseFloat(row.qty || 0);
                        let hargaInventory = parseFloat(row.harga || 0);
                        let hargaVivi = parseFloat(row.harga_vivi || 0);

                        let hargaViviCol = '';

                        if (res.can_edit_harga) {

                            hargaViviCol = `
                                <input
                                    type="number"
                                    class="form-control form-control-sm border-0 shadow-none harga-vivi-input"
                                    data-id="${row.id}"
                                    value="${row.harga_vivi ?? ''}">
                            `;

                        } else {

                            hargaViviCol = row.harga_vivi
                                ? 'Rp ' + hargaVivi.toLocaleString('id-ID')
                                : '-';
                        }

                        // total pakai harga vivi jika ada
                        let total =
                            hargaVivi > 0
                                ? hargaVivi * qty
                                : hargaInventory * qty;

                      bahanHtml += `
<tr>
    <td>${i + 1}</td>

    <td>${row.tanggal ?? '-'}</td>

    <td>
        <span class="badge bg-${
            row.tipe == 'in' ? 'success' : 'danger'
        }">
            ${row.tipe}
        </span>
    </td>

    <td>
        <div class="fw-bold">${row.nama_barang}</div>
        <small class="text-muted">${row.kode_barang}</small>
    </td>

    <td>${qty} ${row.satuan ?? ''}</td>

    ${
        !hideHarga
        ? `
            <td>
                ${
                    hargaInventory
                        ? 'Rp ' + hargaInventory.toLocaleString('id-ID')
                        : '-'
                }
            </td>

            <td>
                ${hargaViviCol}
            </td>

            <td class="fw-bold text-success">
                Rp ${total.toLocaleString('id-ID')}
            </td>
        `
        : ''
    }

    <td>${row.keterangan ?? '-'}</td>
</tr>
`;
                    });

                } else {

                    bahanHtml = `
                    <tr>
                        <td colspan="9"
                            class="text-center text-muted py-3">
                            Tidak ada pengambilan bahan baku
                        </td>
                    </tr>
                    `;
                }

                $('#bahanbakuArea').html(bahanHtml);
                    let infoHtml = `
                    <table class="table table-sm">
                        <tr>
                            <td width="120">No SPK</td>
                            <td>${res.spk_no}</td>
                        </tr>
                        <tr>
                            <td>Supplier</td>
                            <td>${res.supplier.name}</td>
                        </tr>
                        <tr>
                            <td>Kategori</td>
                            <td>${res.kategori}</td>
                        </tr>
                        <tr>
                            <td>Status</td>
                            <td>${res.status}</td>
                        </tr>
                    </table>
                `;
                    $('#spkInfoArea')
                        .html(infoHtml);
                    /*
                    |--------------------------------------------------------------------------
                    | ITEMS
                    |--------------------------------------------------------------------------
                    */
                    let itemHtml = `
    <table class="table table-bordered table-sm">
        <thead>
            <tr>
                <th>Item</th>
                <th width="70">
                    Qty
                </th>
                <th width="80">
                    Passed
                </th>
                <th width="80">
                    Rejected
                </th>
            </tr>
        </thead>
        <tbody>
    `;
                    const isCushion =
                        String(res.kategori || '')
                        .toLowerCase() === 'cushion';
                    res.items.forEach(item => {
                        itemHtml += `
            <tr>
                <td>
                    <div class="fw-bold">
                        ${item.nama}
                    </div>
                    <small class="text-muted">
                        ${item.kode ?? '-'}
                    </small>
                </td>
                <td class="text-center">
                    ${item.qty}
                </td>
                ${
                    !isCushion
                    ? `
                                            <td class="text-center text-success fw-bold">
                                                ${item.passed}
                                            </td>
                                            <td class="text-center text-danger fw-bold">
                                                ${item.rejected}
                                            </td>
                                            `
                    : ''
                }
            </tr>
        `;
                    });
                    itemHtml += `
        </tbody>
    </table>
    `;
                    $('#itemArea')
                        .html(itemHtml);
                    /*
                    |--------------------------------------------------------------------------
                    | TIMELINE
                    |--------------------------------------------------------------------------
                    */
                    let timelineHtml = '';
                    if (res.timelines.length > 0) {
                        res.timelines.forEach((row, i) => {
                            timelineHtml += `
    <tr>
        <td>
            ${i + 1}
        </td>
        <td>
            ${row.date}
        </td>
        <td>
            <div class="fw-bold">
                ${row.item_name ?? '-'}
            </div>
            <small class="text-muted">
                ${row.item_code ?? '-'}
            </small>
        </td>
        <td>
            ${res.supplier.name}
        </td>
        <td>
            <span class="
                badge
                bg-${
                    row.type == 'in'
                    ? 'success'
                    :
                    row.type == 'return'
                    ? 'danger'
                    :
                    'warning'
                }
            ">
                ${row.type}
            </span>
        </td>
        <td>
            ${row.qty}
        </td>
        <td>
            ${row.remark ?? '-'}
        </td>
    </tr>
    `;
                        });
                    } else {
                        timelineHtml = `
                        <tr>
                            <td colspan="6"
                                class="text-center text-muted py-4">
                                Tidak ada timeline
                            </td>
                        </tr>
                    `;
                    }
                    $('#timelineArea')
                        .html(timelineHtml);
                    /*
                    |--------------------------------------------------------------------------
                    | PAYMENT
                    |--------------------------------------------------------------------------
                    */
                    let paymentHtml = '';
                 let totalSpk = 0;

                res.items.forEach(item => {

                    totalSpk += parseFloat(
                        item.total || 0
                    );

                    if (
                        Array.isArray(
                            item.custom_columns
                        )
                    ) {

                        item.custom_columns.forEach(c => {

                            totalSpk += parseFloat(
                                c.total || 0
                            );

                        });

                    }

                });
                    let saldo = totalSpk;
                    // =========================
                    // BARIS TOTAL SPK
                    // =========================
                    paymentHtml += `
    <tr class="table-primary">
        <td></td>
        <td></td>
        <td class="fw-bold">
            TOTAL SPK
        </td>
        <td class="fw-bold">
            Rp
            ${totalSpk.toLocaleString()}
        </td>
        <td class="fw-bold text-success">
            Rp
            ${saldo.toLocaleString()}
        </td>
        <td></td>
    </tr>
    `;
                                // =========================
                // PAYMENT LIST
                // =========================
                if (
                    Array.isArray(res.payments) &&
                    res.payments.length > 0
                ) {

                    let totalPayment = 0;
                    let totalBahan = 0;
                    let totalReturnBahan = 0;
                    res.payments.forEach((pay, i) => {

                       let nilai = parseInt(pay.payment_request_amount || 0);

                        if (pay.note == 'bahan') {

                            totalBahan += nilai;
                            totalPayment += nilai;
                            saldo -= nilai;

                        }
                        else if (pay.note == 'return_bahan') {

                            totalReturnBahan += nilai;

                            // kembalikan saldo karena bahan direturn
                            saldo += nilai;

                            // jangan tambah total payment
                        }
                        else {

                            totalPayment += nilai;
                            saldo -= nilai;

                        }
                        let bahanBersih = totalBahan - totalReturnBahan;

                        if (bahanBersih < 0) {
                            bahanBersih = 0;
                        }
                        paymentHtml += `
                        <tr>

                            <td>
                                ${i + 1}
                            </td>

                            <td>
                                ${pay.date ?? '-'}
                            </td>

                            <td>
                                ${pay.note ?? '-'}
                            </td>

                            <td>
                                Rp ${Number(
                                    pay.amount || 0
                                ).toLocaleString('id-ID')}
                            </td>

                            <td class="
                                fw-bold
                                ${
                                    saldo <= 0
                                        ? 'text-danger'
                                        : 'text-success'
                                }
                            ">
                                Rp ${saldo.toLocaleString('id-ID')}
                            </td>

                            <td>
                                ${pay.note_tambahan ?? '-'}
                            </td>
                    <td>
                    ${
                        pay.is_request
                        ? `
                            <span class="badge bg-success">
                                ✓ Requested
                            </span>
                        `
                        : `
                            <span class="badge bg-warning" >
                                Belum Request
                            </span>
                        `
                    }
                    </td>
            <td>

            ${
                pay.adjustment > 0
                ? `
                    <div>
                        Paid :
                        <b>
                            Rp ${Number(
                                pay.payment_request_amount
                            ).toLocaleString('id-ID')}
                        </b>
                    </div>

                    <small class="text-danger">
                        Sisa :
                        Rp ${Number(
                            pay.remaining_amount
                        ).toLocaleString('id-ID')}
                    </small>
                `
                : pay.finance_approved
                ? `
                    <span class="badge bg-success">
                        Full Payment
                    </span>
                `
                : pay.is_request
                ? `
                    <span class="badge bg-warning">
                        Menunggu Finance
                    </span>
                `
                : `
                    <span class="badge bg-secondary">
                        Belum Request
                    </span>
                `
            }

            </td>

                        </tr>
                        `;
                    });

                    // =====================
                    // TOTAL PAYMENT
                    // =====================

                    paymentHtml += `
                    <tr class="table-success">

                        <td colspan="3"
                            class="text-end fw-bold">

                            TOTAL PAYMENT

                        </td>

                        <td class="fw-bold">

                            Rp ${totalPayment.toLocaleString('id-ID')}

                        </td>

                        <td class="fw-bold
                            ${
                                saldo <= 0
                                    ? 'text-danger'
                                    : 'text-success'
                            }">

                            Rp ${saldo.toLocaleString('id-ID')}

                        </td>

                        <td></td>

                        <td></td>

                    </tr>
                    `;

                } else {

                    paymentHtml += `
                    <tr>

                        <td colspan="7"
                            class="text-center text-muted py-4">

                            Tidak ada payment

                        </td>

                    </tr>
                    `;
                }
                    $('#paymentArea')
                        .html(paymentHtml);
                    $('#inventorDetailModal')
                        .modal('show');
                });
            });
            // NWS filter
            $(document).on('change', '#filterSpkType', function() {
                let value = $(this).val().trim().toUpperCase();
                $('table tbody tr').show();
                $('table tbody tr').each(function() {
                    let text = $(this)
                        .find('td:eq(1)')
                        .text()
                        .trim()
                        .toUpperCase();
                    // ambil bagian setelah /
                    let parts = text.split('/');
                    // contoh:
                    // 26-0010/NWS 26 - 31/05/2026
                    // hasil => NWS
                    let prefix = '';
                    if (parts.length > 1) {
                        prefix = parts[1].trim().split(' ')[0];
                    }
                    // semua
                    if (value === '') {
                        $(this).show();
                        return;
                    }
                    // filter
                    if (prefix === value) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            });
        </script>
        <script>
                $(document).on('keyup', '#searchSpk', function() {
                    let keyword = $(this).val().toLowerCase();
                    $('.inventor-table tbody tr').each(function() {
                        let rowText = $(this)
                            .text()
                            .toLowerCase();
                        $(this).toggle(
                            rowText.indexOf(keyword) > -1
                        );
                    });
                });
            </script>
            <script>
            $(document).on('click','.btn-ajukan',function(){

        let id = $(this).data('id');
        let spk = $(this).data('spk');

        Swal.fire({
            title: 'Ajukan SPK?',
            html: `
                SPK No <b>${spk}</b>
                akan diajukan?
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Ya',
            cancelButtonText: 'Cancel'
        }).then((result)=>{

            if(result.isConfirmed){

                $.post(
                    `/spk/${id}/submit-signature`,
                    {
                        _token:
                            $('meta[name="csrf-token"]')
                            .attr('content')
                    },
                    function(res){

                        Swal.fire({
                            icon:'success',
                            title:'Berhasil',
                            text:res.message
                        }).then(()=>{
                            location.reload();
                        });

                    }
                );

            }

        });

    });
    // update vivi
   $(document).on('keypress', '.harga-vivi-input', function(e){

    if(e.which == 13){

        e.preventDefault();

        let input = $(this);

        $.ajax({
            url:'/inventor/update-harga-vivi',
            type:'POST',
            data:{
                _token:$('meta[name="csrf-token"]').attr('content'),
                id:input.data('id'),
                harga:input.val()
            },
            success:function(){

                Swal.fire({
                    icon:'success',
                    title:'Tersimpan',
                    timer:1000,
                    showConfirmButton:false
                });

            }
        });
    }
});
// filter
$(document).on('change', '#subSelect', function () {

    let value = $(this).val().toLowerCase();

    $('.inventor-table tbody tr').each(function () {

        let supplier = $(this)
            .find('td:eq(2)')   // kolom Supplier
            .text()
            .trim()
            .toLowerCase();

        if (value === '' || supplier.includes(value)) {
            $(this).show();
        } else {
            $(this).hide();
        }

    });

});
$('#searchSub').on('input', function () {

    let keyword = $(this).val().toLowerCase();

    $('.inventor-table tbody tr').each(function () {

        let supplier = $(this)
            .find('td:eq(2)')
            .text()
            .trim()
            .toLowerCase();

        $(this).toggle(
            keyword === '' || supplier.includes(keyword)
        );

    });

});
const originalOptions = $('#subSelect').html();

$('#searchSub').on('keyup', function () {

    let keyword = $(this).val().toLowerCase();

    $('#subSelect').html(originalOptions);

    $('#subSelect option').each(function () {

        let text = $(this).text().toLowerCase();

        if (!text.includes(keyword) && $(this).val() != '') {
            $(this).remove();
        }

    });

});
// pending
$(document).on('click','.btn-approve-spk',function(){

    let id = $(this).data('id');

    window.location.href = '/spk/views/' + id;

});
function formatRupiah(angka)
{
    if(!angka) return '-';

    return 'Rp ' + Number(angka)
        .toLocaleString('id-ID');
}
        </script>
    @endpush
