<div>

    <h5 class="mb-1">Draft Payment</h5>

    <p class="text-muted mb-3">
        Daftar payment yang masih dalam status draft.
    </p>


    <div class="table-responsive finance-table-wrapper">

        <table class="table table-hover finance-table">

            <thead>
                <tr>
                    <th>No</th>
                    <th>DATE</th>
                    <th>NO PO</th>
                    <th>INV / NO. SPK</th>
                    <th>TYPE BIAYA</th>
                    <th>Nama Barang / Item / Jasa</th>
                    <th class="text-center">QTY</th>
                    <th class="text-end">Estimasi Harga Satuan</th>
                    <th class="text-end">Total Harga</th>
                </tr>
            </thead>

            <tbody>

                {{-- DATA PAYMENT NANTI DI SINI --}}

                <tr>
                    <td colspan="8" class="text-center text-muted py-4">
                        Belum ada Draft Payment
                    </td>
                </tr>

            </tbody>

        </table>

    </div>

</div>


<style>

.finance-table-wrapper {
    width: 100%;
    overflow-x: auto;
    border: 1px solid #e1e5ea;
    border-radius: 6px;
}

.finance-table {
    margin-bottom: 0;
    min-width: 1100px;
    font-size: 12px;
}

.finance-table thead th {
    background: #f5f7fa;
    color: #495057;
    font-size: 11px;
    font-weight: 600;
    white-space: nowrap;
    vertical-align: middle;
    border-bottom: 1px solid #dfe3e8;
    padding: 10px 12px;
}

.finance-table tbody td {
    padding: 10px 12px;
    vertical-align: middle;
    white-space: nowrap;
    border-color: #edf0f2;
}

.finance-table tbody tr:hover {
    background: #f8fafc;
}

.finance-table .text-end {
    text-align: right;
}

.finance-table .text-center {
    text-align: center;
}

</style>