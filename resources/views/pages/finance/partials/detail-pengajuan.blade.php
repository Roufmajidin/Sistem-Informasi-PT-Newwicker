{{-- 
    resources/views/pages/finance/detail-pengajuan.blade.php

    PARTIAL:
    - Tidak menggunakan @extends
    - Tidak menggunakan @section
    - Tidak ada JavaScript di sini
    - File dimuat melalui fetch() + innerHTML
--}}

<div
    class="finance-detail-container"
    data-pengajuan-id="{{ $pengajuan->id }}"
>

    {{-- =========================================================
         HEADER
    ========================================================== --}}
    <div class="finance-detail-top">

        <div>
            <div class="finance-detail-heading">
                <i class="fas fa-file-invoice-dollar"></i>
                Detail Pengajuan Finance
            </div>

            <div class="finance-detail-id">
                ID Pengajuan:
                <strong>#{{ $pengajuan->id }}</strong>
            </div>
        </div>

        <div>

            @php
                $status = strtolower(
                    trim((string) ($pengajuan->status ?? ''))
                );
            @endphp

            @if($status === 'approved')

                <span class="finance-status approved">
                    <i class="fas fa-check-circle"></i>
                    APPROVED
                </span>

            @elseif($status === 'pending')

                <span class="finance-status pending">
                    <i class="fas fa-clock"></i>
                    PENDING
                </span>

            @elseif($status === 'rejected')

                <span class="finance-status rejected">
                    <i class="fas fa-times-circle"></i>
                    REJECTED
                </span>

            @else

                <span class="finance-status">
                    {{ strtoupper($pengajuan->status ?? '-') }}
                </span>

            @endif

        </div>

    </div>


    {{-- =========================================================
         EDIT INFORMATION
    ========================================================== --}}
    <div class="finance-edit-hint">

        <i class="fas fa-info-circle"></i>

        Klik dan ubah field yang diperlukan.
        Setelah ada perubahan, tombol
        <strong>Save</strong>
        akan muncul di sebelah kanan baris.

    </div>


    {{-- =========================================================
         DETAIL TABLE
    ========================================================== --}}
    <div class="finance-detail-table-wrapper">

        <table class="finance-detail-table">

            <thead>

                <tr>

                    <th class="col-no">
                        NO
                    </th>

                    <th class="col-date">
                        DATE
                    </th>

                    <th class="col-po">
                        NO. PO
                    </th>

                    <th class="col-invoice">
                        NO. INVOICE
                    </th>

                    <th class="col-type">
                        TYPE BIAYA
                    </th>

                    <th class="col-name">
                        NAMA BARANG
                    </th>

                    <th class="col-qty">
                        QTY
                    </th>

                    <th class="col-price">
                        HARGA SATUAN
                    </th>

                    <th class="col-total">
                        TOTAL HARGA
                    </th>

                    <th class="col-action">
                        ACTION
                    </th>

                </tr>

            </thead>


            <tbody>

                @php
                    $grandTotal = 0;
                @endphp


                @forelse($pengajuan->details as $detail)

                    @php

                        $qty = (float) (
                            $detail->qty ?? 0
                        );

                        $hargaSatuan = (float) (
                            $detail->harga_satuan ?? 0
                        );

                        /*
                         * Jika total_harga tersedia,
                         * gunakan nilai tersebut.
                         *
                         * Jika tidak tersedia,
                         * hitung qty x harga.
                         */
                        $totalHarga = $detail->total_harga !== null
                            ? (float) $detail->total_harga
                            : ($qty * $hargaSatuan);

                        $grandTotal += $totalHarga;


                        /*
                         * Format tanggal untuk
                         * input type=date.
                         */
                        $displayDate = '';

                        if (!empty($detail->date)) {

                            try {

                                $displayDate =
                                    \Carbon\Carbon::parse(
                                        $detail->date
                                    )->format('Y-m-d');

                            } catch (\Throwable $e) {

                                $displayDate = '';

                            }

                        }

                    @endphp


                    <tr
                        class="finance-detail-row"
                        data-detail-id="{{ $detail->id }}"
                    >

                        {{-- =================================================
                             NO
                        ================================================== --}}
                        <td class="finance-no-cell">

                            {{ $detail->no ?? $loop->iteration }}

                        </td>


                        {{-- =================================================
                             DATE
                        ================================================== --}}
                        <td>

                            <input
                                type="date"
                                class="finance-edit-input"
                                data-field="date"
                                value="{{ $displayDate }}"
                            >

                        </td>


                        {{-- =================================================
                             NO PO
                        ================================================== --}}
                        <td>

                            <input
                                type="text"
                                class="finance-edit-input"
                                data-field="no_po"
                                value="{{ $detail->no_po ?? '' }}"
                                autocomplete="off"
                            >

                        </td>


                        {{-- =================================================
                             NO INVOICE
                        ================================================== --}}
                        <td>

                            <input
                                type="text"
                                class="finance-edit-input"
                                data-field="no_inv"
                                value="{{ $detail->no_inv ?? '' }}"
                                autocomplete="off"
                            >

                        </td>


                        {{-- =================================================
                             TYPE BIAYA
                        ================================================== --}}
                        <td>

                            <input
                                type="text"
                                class="finance-edit-input"
                                data-field="type_biaya"
                                value="{{ $detail->type_biaya ?? '' }}"
                                autocomplete="off"
                            >

                        </td>


                        {{-- =================================================
                             NAMA BARANG
                        ================================================== --}}
                        <td>

                            <input
                                type="text"
                                class="finance-edit-input"
                                data-field="nama_barang"
                                value="{{ $detail->nama_barang ?? '' }}"
                                autocomplete="off"
                            >

                        </td>


                        {{-- =================================================
                             QTY
                        ================================================== --}}
                        <td class="finance-number-cell">

                            <input
                                type="number"
                                step="any"
                                class="finance-edit-input finance-number-input"
                                data-field="qty"
                                value="{{ $qty }}"
                            >

                        </td>


                        {{-- =================================================
                             HARGA SATUAN
                        ================================================== --}}
                        <td class="finance-number-cell">

                            <input
                                type="number"
                                step="any"
                                class="finance-edit-input finance-number-input"
                                data-field="harga_satuan"
                                value="{{ $hargaSatuan }}"
                            >

                        </td>


                        {{-- =================================================
                             TOTAL HARGA
                        ================================================== --}}
                        <td
                            class="finance-total-cell"
                            data-field="total_harga"
                        >

                            Rp {{ number_format(
                                $totalHarga,
                                0,
                                ',',
                                '.'
                            ) }}

                        </td>


                        {{-- =================================================
                             ACTION
                        ================================================== --}}
                        <td class="finance-action-cell">

                            <button
                                type="button"
                                class="btn-save-finance-detail"
                                style="display:none;"
                            >

                                <i class="fas fa-save"></i>

                                <span>Save</span>

                            </button>

                        </td>

                    </tr>


                @empty

                    <tr>

                        <td
                            colspan="10"
                            class="finance-detail-empty"
                        >

                            <i class="fas fa-inbox"></i>

                            <div>
                                Tidak ada detail pengajuan.
                            </div>

                        </td>

                    </tr>

                @endforelse

            </tbody>


            {{-- =========================================================
                 GRAND TOTAL
            ========================================================== --}}
            @if($pengajuan->details->isNotEmpty())

                <tfoot>

                    <tr>

                        <td
                            colspan="9"
                            class="finance-grand-label"
                        >

                            GRAND TOTAL

                        </td>

                        <td
                            class="finance-grand-total"
                            data-grand-total="1"
                        >

                            Rp {{ number_format(
                                $grandTotal,
                                0,
                                ',',
                                '.'
                            ) }}

                        </td>

                    </tr>

                </tfoot>

            @endif

        </table>

    </div>


    {{-- =========================================================
         APPROVAL
    ========================================================== --}}
    <div class="finance-approval-section">

        <div class="finance-section-title">

            <i class="fas fa-user-check"></i>

            Approval

        </div>


        <div class="finance-approval-table-wrapper">

            <table class="finance-approval-table">

                <thead>

                    <tr>

                        <th>
                            STEP
                        </th>

                        <th>
                            APPROVER
                        </th>

                        <th>
                            STATUS
                        </th>

                        <th>
                            DATE
                        </th>

                    </tr>

                </thead>


                <tbody>

                    @forelse($pengajuan->approvalSteps as $step)

                        @php

                            $approvalStatus = strtolower(
                                trim(
                                    (string) (
                                        $step->status ?? ''
                                    )
                                )
                            );

                        @endphp


                        <tr>

                            {{-- STEP --}}
                            <td>

                                {{ $step->step_order ?? '-' }}

                            </td>


                            {{-- APPROVER --}}
                            <td>

                                {{ $step->user_name ?? '-' }}

                            </td>


                            {{-- STATUS --}}
                            <td>

                                @if($approvalStatus === 'approved')

                                    <span
                                        class="finance-approval-status approved"
                                    >

                                        <i class="fas fa-check-circle"></i>

                                        APPROVED

                                    </span>

                                @elseif($approvalStatus === 'rejected')

                                    <span
                                        class="finance-approval-status rejected"
                                    >

                                        <i class="fas fa-times-circle"></i>

                                        REJECTED

                                    </span>

                                @else

                                    <span
                                        class="finance-approval-status pending"
                                    >

                                        <i class="fas fa-clock"></i>

                                        {{ strtoupper(
                                            $step->status ?? 'PENDING'
                                        ) }}

                                    </span>

                                @endif

                            </td>


                            {{-- DATE --}}
                            <td>

                                @if(!empty($step->approved_at))

                                    {{ $step->approved_at }}

                                @elseif(!empty($step->updated_at))

                                    {{ $step->updated_at }}

                                @else

                                    -

                                @endif

                            </td>

                        </tr>


                    @empty

                        <tr>

                            <td
                                colspan="4"
                                class="finance-detail-empty"
                            >

                                Belum ada data approval.

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>


{{-- =============================================================
     CSS
============================================================= --}}
<style>

/* =============================================================
   MAIN
============================================================= */

.finance-detail-container {
    width: 100%;
    padding: 18px;
    box-sizing: border-box;
}


/* =============================================================
   HEADER
============================================================= */

.finance-detail-top {

    display: flex;

    align-items: flex-start;

    justify-content: space-between;

    gap: 15px;

    margin-bottom: 12px;
}

.finance-detail-heading {

    font-size: 17px;

    font-weight: 700;

    color: #263b4d;
}

.finance-detail-heading i {

    margin-right: 6px;
}

.finance-detail-id {

    margin-top: 4px;

    color: #6c757d;

    font-size: 12px;
}


/* =============================================================
   STATUS
============================================================= */

.finance-status {

    display: inline-flex;

    align-items: center;

    gap: 5px;

    padding: 5px 10px;

    border-radius: 5px;

    background: #e9ecef;

    color: #495057;

    font-size: 11px;

    font-weight: 700;

    white-space: nowrap;
}

.finance-status.approved {

    background: #d1e7dd;

    color: #0f5132;
}

.finance-status.pending {

    background: #fff3cd;

    color: #664d03;
}

.finance-status.rejected {

    background: #f8d7da;

    color: #842029;
}


/* =============================================================
   HINT
============================================================= */

.finance-edit-hint {

    margin-bottom: 10px;

    padding: 8px 10px;

    border: 1px solid #d9dee5;

    border-radius: 5px;

    background: #f8f9fa;

    color: #6c757d;

    font-size: 11px;
}

.finance-edit-hint i {

    margin-right: 5px;

    color: #557086;
}


/* =============================================================
   TABLE WRAPPER
============================================================= */

.finance-detail-table-wrapper {

    width: 100%;

    max-height: 52vh;

    overflow-x: auto;

    overflow-y: auto;

    border: 1px solid #8d98a3;

    background: #fff;
}


/* =============================================================
   TABLE
============================================================= */

.finance-detail-table {

    width: 100%;

    min-width: 1350px;

    border-collapse: separate;

    border-spacing: 0;
}


/* =============================================================
   HEADER TABLE
============================================================= */

.finance-detail-table thead th {

    position: sticky;

    top: 0;

    z-index: 20;

    height: 38px;

    padding: 7px 8px;

    background: #263b4d;

    color: #fff;

    border-right: 1px solid #6f7b87;

    border-bottom: 1px solid #6f7b87;

    font-size: 11px;

    font-weight: 700;

    white-space: nowrap;

    text-align: left;
}

.finance-detail-table thead th:last-child {

    border-right: none;
}


/* =============================================================
   BODY
============================================================= */

.finance-detail-table tbody td {

    padding: 6px 7px;

    border-right: 1px solid #adb5bd;

    border-bottom: 1px solid #adb5bd;

    background: #fff;

    font-size: 11px;

    vertical-align: middle;
}

.finance-detail-table tbody td:last-child {

    border-right: none;
}


/* =============================================================
   ROW
============================================================= */

.finance-detail-row {

    background: #fff;

    transition:
        background .12s ease,
        box-shadow .12s ease;
}

.finance-detail-row:hover td {

    background: #f7f9fb;
}

.finance-detail-row.active td {

    background: #eaf2f8;
}


/*
 * Row yang sudah berubah.
 */
.finance-detail-row.has-changes td {

    background: #fffdf0;
}

.finance-detail-row.has-changes.active td {

    background: #eaf2f8;
}


/* =============================================================
   NO
============================================================= */

.finance-no-cell {

    width: 45px;

    min-width: 45px;

    text-align: center;

    font-weight: 600;

    color: #495057;
}


/* =============================================================
   INPUT
============================================================= */

.finance-edit-input {

    width: 100%;

    min-width: 85px;

    box-sizing: border-box;

    padding: 5px 6px;

    border: 1px solid transparent;

    border-radius: 3px;

    background: transparent;

    color: #212529;

    font-family: inherit;

    font-size: 11px;

    outline: none;

    transition:
        border-color .12s ease,
        background .12s ease,
        box-shadow .12s ease;
}

.finance-edit-input:hover {

    border-color: #ced4da;

    background: #fff;
}

.finance-edit-input:focus {

    border-color: #557086;

    background: #fff;

    box-shadow:
        0 0 0 2px rgba(85,112,134,.12);
}


/* =============================================================
   NUMBER
============================================================= */

.finance-number-input {

    min-width: 95px;

    text-align: right;
}


/* =============================================================
   TOTAL
============================================================= */

.finance-total-cell {

    min-width: 135px;

    text-align: right;

    font-weight: 600;

    color: #263b4d;

    white-space: nowrap;
}


/* =============================================================
   ACTION
============================================================= */

.finance-action-cell {

    width: 85px;

    min-width: 85px;

    text-align: center;

    white-space: nowrap;
}

.btn-save-finance-detail {

    display: inline-flex;

    align-items: center;

    justify-content: center;

    gap: 5px;

    padding: 5px 9px;

    border: 1px solid #198754;

    border-radius: 4px;

    background: #198754;

    color: #fff;

    font-size: 10px;

    font-weight: 600;

    cursor: pointer;

    white-space: nowrap;

    transition:
        background .15s ease,
        border-color .15s ease,
        opacity .15s ease;
}

.btn-save-finance-detail:hover {

    background: #157347;

    border-color: #146c43;
}

.btn-save-finance-detail:disabled {

    opacity: .65;

    cursor: not-allowed;
}


/* =============================================================
   COLUMN WIDTH
============================================================= */

.finance-detail-table .col-no {

    width: 45px;
}

.finance-detail-table .col-date {

    width: 120px;
}

.finance-detail-table .col-po {

    width: 125px;
}

.finance-detail-table .col-invoice {

    width: 140px;
}

.finance-detail-table .col-type {

    width: 130px;
}

.finance-detail-table .col-name {

    min-width: 240px;
}

.finance-detail-table .col-qty {

    width: 100px;
}

.finance-detail-table .col-price {

    width: 140px;
}

.finance-detail-table .col-total {

    width: 150px;
}

.finance-detail-table .col-action {

    width: 85px;
}


/* =============================================================
   FOOTER
============================================================= */

.finance-detail-table tfoot td {

    position: sticky;

    bottom: 0;

    z-index: 15;

    padding: 9px 8px;

    background: #f1f3f5;

    border-top: 2px solid #6c757d;

    border-right: 1px solid #adb5bd;

    font-size: 11px;

    font-weight: 700;
}

.finance-detail-table tfoot td:last-child {

    border-right: none;
}

.finance-grand-label {

    text-align: right;

    color: #263b4d;
}

.finance-grand-total {

    text-align: right;

    color: #263b4d;

    white-space: nowrap;
}


/* =============================================================
   EMPTY
============================================================= */

.finance-detail-empty {

    padding: 40px 20px !important;

    text-align: center;

    color: #9aa5b1;

    background: #fff !important;
}

.finance-detail-empty i {

    display: block;

    margin-bottom: 8px;

    font-size: 28px;
}


/* =============================================================
   APPROVAL
============================================================= */

.finance-approval-section {

    margin-top: 20px;
}

.finance-section-title {

    margin-bottom: 8px;

    color: #263b4d;

    font-size: 13px;

    font-weight: 700;
}

.finance-section-title i {

    margin-right: 5px;
}

.finance-approval-table-wrapper {

    width: 100%;

    overflow-x: auto;

    border: 1px solid #8d98a3;
}

.finance-approval-table {

    width: 100%;

    border-collapse: collapse;
}

.finance-approval-table th,
.finance-approval-table td {

    padding: 7px 9px;

    border-right: 1px solid #adb5bd;

    border-bottom: 1px solid #adb5bd;

    font-size: 11px;

    vertical-align: middle;
}

.finance-approval-table th:last-child,
.finance-approval-table td:last-child {

    border-right: none;
}

.finance-approval-table thead th {

    background: #f1f3f5;

    color: #495057;

    font-weight: 700;

    white-space: nowrap;
}


/* =============================================================
   APPROVAL STATUS
============================================================= */

.finance-approval-status {

    display: inline-flex;

    align-items: center;

    gap: 4px;

    padding: 3px 7px;

    border-radius: 4px;

    font-size: 10px;

    font-weight: 700;

    white-space: nowrap;
}

.finance-approval-status.approved {

    background: #d1e7dd;

    color: #0f5132;
}

.finance-approval-status.pending {

    background: #fff3cd;

    color: #664d03;
}

.finance-approval-status.rejected {

    background: #f8d7da;

    color: #842029;
}


/* =============================================================
   RESPONSIVE
============================================================= */

@media(max-width: 768px) {

    .finance-detail-container {

        padding: 12px;
    }

    .finance-detail-top {

        flex-direction: column;
    }

    .finance-detail-table-wrapper {

        max-height: 55vh;
    }

}
<script>
document.addEventListener('input', function (event) {

    const input = event.target.closest('.finance-edit-input');

    if (!input) {
        return;
    }

    const row = input.closest('.finance-detail-row');

    if (!row) {
        return;
    }

    console.log('Finance field berubah:', input.dataset.field);

    // Tandai row berubah
    row.classList.add('has-changes');

    // Tampilkan tombol Save
    const saveButton = row.querySelector('.btn-save-finance-detail');

    if (saveButton) {
        saveButton.style.display = 'inline-flex';

        console.log('Tombol Save ditemukan');
    } else {
        console.warn('Tombol Save tidak ditemukan di row');
    }

    // Hitung total otomatis
    const field = input.dataset.field;

    if (field !== 'qty' && field !== 'harga_satuan') {
        return;
    }

    const qtyInput = row.querySelector('[data-field="qty"]');
    const priceInput = row.querySelector('[data-field="harga_satuan"]');
    const totalElement = row.querySelector('[data-field="total_harga"]');

    if (!qtyInput || !priceInput || !totalElement) {
        return;
    }

    const qty = parseFloat(qtyInput.value) || 0;
    const price = parseFloat(priceInput.value) || 0;

    const total = qty * price;

    totalElement.textContent =
        'Rp ' + new Intl.NumberFormat('id-ID').format(total);
});
</script>
</style>