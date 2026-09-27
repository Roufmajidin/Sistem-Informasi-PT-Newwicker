<div>

    {{-- =========================================================
        DRAFT PAYMENT
    ========================================================== --}}

    <h5 class="mb-1">Draft Payment</h5>

    <p class="text-muted mb-3">
        Daftar payment yang masih dalam status draft.
    </p>

    <div class="finance-table-wrapper">

        <table class="table table-hover finance-table">

            <thead>
                <tr>
                    <th class="text-center">NO</th>
                    <th>REQUEST NO</th>
                    <th>TANGGAL</th>
                    <th>NO. PENGAJUAN</th>
                    <th>TYPE PEMBAYARAN</th>
                    <th>PEMBUAT</th>
                    <th>STATUS</th>
                    <th class="text-center">ACTION</th>
                </tr>
            </thead>

            <tbody>

                @forelse($pengajuans as $pengajuan)

                    <tr>

                        <td class="text-center">
                            {{ $loop->iteration }}
                        </td>

                        <td>
                            <strong>
                                {{ $pengajuan->meta->nomor ?? '-' }}
                            </strong>
                        </td>

                        <td>

                            @if($pengajuan->meta?->tanggal)

                                {{ \Carbon\Carbon::parse(
                                    $pengajuan->meta->tanggal
                                )->format('d/m/Y') }}

                            @else

                                -

                            @endif

                        </td>

                        <td>
                            #{{ $pengajuan->id }}
                        </td>

                        <td>
                            {{ $pengajuan->meta->type_pembayaran ?? '-' }}
                        </td>

                        <td>
                            {{ $pengajuan->user->name ?? '-' }}
                        </td>

                        <td>

                            @if($pengajuan->status === 'pending')

                                <span class="finance-status pending">
                                    Draft
                                </span>

                            @elseif($pengajuan->status === 'approved')

                                <span class="finance-status approved">
                                    Approved
                                </span>

                            @elseif($pengajuan->status === 'rejected')

                                <span class="finance-status rejected">
                                    Rejected
                                </span>

                            @else

                                <span class="finance-status">
                                    {{ ucfirst($pengajuan->status ?? '-') }}
                                </span>

                            @endif

                        </td>

                        {{-- ACTION --}}
                        <td class="text-center">

                            <button
                                type="button"
                                class="btn btn-sm btn-outline-primary btn-detail-finance"
                                data-id="{{ $pengajuan->id }}"
                                title="See Details">

                                <i class="fas fa-eye"></i>
                                Detail

                            </button>

                        </td>

                    </tr>

                @empty

                    <tr>

                        <td
                            colspan="8"
                            class="text-center text-muted py-4">

                            Belum ada Draft Payment

                        </td>

                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

</div>


{{-- =========================================================
    FINANCE DETAIL MODAL
========================================================= --}}

<div
    id="financeDetailModal"
    class="finance-detail-modal"
>

    <div class="finance-detail-modal-dialog">

        <div class="finance-detail-modal-header">

            <div class="finance-detail-modal-title">

                <i class="fas fa-file-invoice"></i>

                Detail Pengajuan Finance

            </div>

            <button
                type="button"
                class="finance-detail-modal-close"
                title="Close"
            >
                &times;
            </button>

        </div>

        <div
            id="financeDetailModalContent"
            class="finance-detail-modal-body"
        >

            <div class="finance-detail-loading">

                <div
                    class="spinner-border text-secondary"
                    role="status">
                </div>

                <div class="mt-3">
                    Loading...
                </div>

            </div>

        </div>

    </div>

</div>


<style>

/* =========================================================
   DRAFT PAYMENT TABLE
========================================================= */

.finance-table-wrapper {

    width: 100%;

    overflow-x: hidden;

    border: 1px solid #e1e5ea;

    border-radius: 6px;

    background: #fff;

}


.finance-table {

    width: 100%;

    margin-bottom: 0;

    font-size: 12px;

    table-layout: auto;

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


/* =========================================================
   STATUS
========================================================= */

.finance-status {

    display: inline-flex;

    align-items: center;

    justify-content: center;

    padding: 4px 9px;

    border-radius: 4px;

    background: #eef1f4;

    color: #495057;

    font-size: 11px;

    font-weight: 600;

    line-height: 1;

}


.finance-status.pending {

    background: #fff3cd;

    color: #856404;

}


.finance-status.approved {

    background: #d1e7dd;

    color: #0f5132;

}


.finance-status.rejected {

    background: #f8d7da;

    color: #842029;

}


/* =========================================================
   DETAIL BUTTON
========================================================= */

.btn-detail-finance {

    font-size: 11px;

    padding: 4px 10px;

    white-space: nowrap;

}


/* =========================================================
   MODAL
========================================================= */

.finance-detail-modal {

    position: fixed;

    inset: 0;

    z-index: 9999;

    display: flex;

    align-items: center;

    justify-content: center;

    padding: 25px;

    background: rgba(15, 23, 42, .60);

    opacity: 0;

    visibility: hidden;

    transition:
        opacity .15s ease,
        visibility .15s ease;

}


.finance-detail-modal.show {

    opacity: 1;

    visibility: visible;

}


.finance-detail-modal-dialog {

    width: 95vw;

    max-width: 1500px;

    max-height: 92vh;

    display: flex;

    flex-direction: column;

    background: #fff;

    border-radius: 8px;

    box-shadow:
        0 20px 60px rgba(0, 0, 0, .25);

    overflow: hidden;

    transform: translateY(-10px);

    transition: transform .15s ease;

}


.finance-detail-modal.show
.finance-detail-modal-dialog {

    transform: translateY(0);

}


/* =========================================================
   MODAL HEADER
========================================================= */

.finance-detail-modal-header {

    display: flex;

    align-items: center;

    justify-content: space-between;

    padding: 14px 18px;

    background: #263b4d;

    color: #fff;

}


.finance-detail-modal-title {

    display: flex;

    align-items: center;

    gap: 8px;

    font-size: 14px;

    font-weight: 600;

}


.finance-detail-modal-close {

    width: 32px;

    height: 32px;

    padding: 0;

    border: none;

    background: transparent;

    color: #fff;

    font-size: 25px;

    line-height: 1;

    cursor: pointer;

    border-radius: 4px;

}


.finance-detail-modal-close:hover {

    background: rgba(255,255,255,.12);

}


/* =========================================================
   MODAL BODY
========================================================= */

.finance-detail-modal-body {

    padding: 20px;

    overflow-y: auto;

    overflow-x: hidden;

    flex: 1;

}


.finance-detail-loading {

    min-height: 300px;

    display: flex;

    flex-direction: column;

    align-items: center;

    justify-content: center;

    color: #6c757d;

    font-size: 12px;

}


/* =========================================================
   MOBILE
========================================================= */

@media (max-width: 768px) {

    .finance-detail-modal {

        padding: 10px;

    }


    .finance-detail-modal-dialog {

        width: 100vw;

        max-height: 95vh;

    }


    .finance-detail-modal-body {

        padding: 12px;

    }


    .finance-table thead th,
    .finance-table tbody td {

        padding: 8px 7px;

    }


    .btn-detail-finance {

        font-size: 10px;

        padding: 3px 7px;

    }

}

</style>


<script>

/* =========================================================
   FINANCE DRAFT PAYMENT
   SEMUA EVENT MENGGUNAKAN DELEGATION
   AGAR TETAP BERFUNGSI PADA HTML HASIL FETCH()
========================================================= */


/* =========================================================
   OPEN DETAIL
========================================================= */

document.addEventListener('click', function (event) {

    const button =
        event.target.closest('.btn-detail-finance');

    if (!button) {
        return;
    }

    event.preventDefault();


    const id = button.dataset.id;

    if (!id) {
        console.error('Finance detail ID tidak ditemukan.');
        return;
    }


    const url =
        "{{ url('/finance/pengajuan-finance') }}/" + id;


    const modal =
        document.getElementById(
            'financeDetailModal'
        );


    const content =
        document.getElementById(
            'financeDetailModalContent'
        );


    if (!modal || !content) {

        console.error(
            'Modal Finance tidak ditemukan.'
        );

        return;
    }


    content.innerHTML = `

        <div class="finance-detail-loading">

            <div
                class="spinner-border text-secondary"
                role="status">
            </div>

            <div class="mt-3">
                Loading detail pengajuan...
            </div>

        </div>

    `;


    modal.classList.add('show');

    document.body.style.overflow = 'hidden';


    fetch(url, {

        method: 'GET',

        headers: {

            'X-Requested-With':
                'XMLHttpRequest',

            'Accept':
                'text/html'

        }

    })

    .then(function (response) {

        if (!response.ok) {

            throw new Error(
                'Gagal mengambil detail pengajuan.'
            );

        }

        return response.text();

    })

    .then(function (html) {

        content.innerHTML = html;

    })

    .catch(function (error) {

        console.error(error);


        content.innerHTML = `

            <div
                class="text-center text-danger"
                style="padding:80px 20px;">

                <i
                    class="fas fa-exclamation-triangle"
                    style="font-size:30px;">
                </i>

                <div class="mt-3">
                    Gagal memuat detail pengajuan.
                </div>

            </div>

        `;

    });

});


/* =========================================================
   CLOSE MODAL
========================================================= */

document.addEventListener('click', function (event) {

    const closeButton =
        event.target.closest(
            '.finance-detail-modal-close'
        );


    if (closeButton) {

        closeFinanceDetailModal();

        return;

    }


    if (
        event.target.id ===
        'financeDetailModal'
    ) {

        closeFinanceDetailModal();

    }

});


document.addEventListener('keydown', function (event) {

    if (event.key === 'Escape') {

        closeFinanceDetailModal();

    }

});


function closeFinanceDetailModal()
{

    const modal =
        document.getElementById(
            'financeDetailModal'
        );


    if (modal) {

        modal.classList.remove('show');

    }


    document.body.style.overflow = '';

}


/* =========================================================
   DETEKSI PERUBAHAN FIELD DETAIL
========================================================= */

document.addEventListener('input', function (event) {

    const input =
        event.target.closest(
            '.finance-edit-input'
        );


    /*
     * Kalau bukan input detail finance,
     * jangan lakukan apa-apa.
     */
    if (!input) {

        return;

    }


    const row =
        input.closest(
            '.finance-detail-row'
        );


    if (!row) {

        console.warn(
            'finance-detail-row tidak ditemukan.'
        );

        return;

    }


    /*
     * Tandai row berubah
     */
    row.classList.add(
        'has-changes'
    );


    /*
     * Cari tombol Save pada row yang sama
     */
    const saveButton =
        row.querySelector(
            '.btn-save-finance-detail'
        );


    if (saveButton) {

        saveButton.style.display =
            'inline-flex';

        saveButton.removeAttribute(
            'disabled'
        );

    } else {

        console.warn(
            'Tombol Save tidak ditemukan.',
            row
        );

    }


    /*
     * Hitung TOTAL HARGA secara live
     */
    const field =
        input.dataset.field;


    if (
        field !== 'qty' &&
        field !== 'harga_satuan'
    ) {

        return;

    }


    const qtyInput =
        row.querySelector(
            '[data-field="qty"]'
        );


    const priceInput =
        row.querySelector(
            '[data-field="harga_satuan"]'
        );


    const totalElement =
        row.querySelector(
            '[data-field="total_harga"]'
        );


    if (
        !qtyInput ||
        !priceInput ||
        !totalElement
    ) {

        return;

    }


    const qty =
        parseFloat(
            String(
                qtyInput.value || '0'
            ).replace(/,/g, '')
        ) || 0;


    const price =
        parseFloat(
            String(
                priceInput.value || '0'
            ).replace(/,/g, '')
        ) || 0;


    const total =
        qty * price;


    totalElement.textContent =
        'Rp ' +
        new Intl.NumberFormat(
            'id-ID'
        ).format(total);


    /*
     * Update Grand Total juga
     */
    updateFinanceGrandTotal();

});


/* =========================================================
   CHANGE EVENT
   DIPAKAI SEBAGAI BACKUP UNTUK DATE / SELECT DLL
========================================================= */

document.addEventListener('change', function (event) {

    const input =
        event.target.closest(
            '.finance-edit-input'
        );


    if (!input) {

        return;

    }


    const row =
        input.closest(
            '.finance-detail-row'
        );


    if (!row) {

        return;

    }


    row.classList.add(
        'has-changes'
    );


    const saveButton =
        row.querySelector(
            '.btn-save-finance-detail'
        );


    if (saveButton) {

        saveButton.style.display =
            'inline-flex';

    }

});


/* =========================================================
   CLICK SAVE
========================================================= */

document.addEventListener('click', function (event) {

    const button =
        event.target.closest(
            '.btn-save-finance-detail'
        );


    if (!button) {

        return;

    }


    event.preventDefault();


    const row =
        button.closest(
            '.finance-detail-row'
        );


    if (!row) {

        return;

    }


    saveFinanceDetailRow(row);

});


/* =========================================================
   SAVE DETAIL
========================================================= */

function saveFinanceDetailRow(row)
{

    const detailId =
        row.dataset.detailId;


    if (!detailId) {

        showFinanceToast(
            'ID detail tidak ditemukan.',
            'error'
        );

        return;

    }


    const inputs =
        row.querySelectorAll(
            '.finance-edit-input'
        );


    const data = {};


    inputs.forEach(function (input) {

        const field =
            input.dataset.field;


        if (!field) {
            return;
        }


        data[field] =
            input.value;

    });


    /*
     * Pastikan qty dan harga dikirim sebagai angka
     */
    if (
        Object.prototype.hasOwnProperty.call(
            data,
            'qty'
        )
    ) {

        data.qty =
            parseFloat(
                String(data.qty || '0')
                    .replace(/,/g, '')
            ) || 0;

    }


    if (
        Object.prototype.hasOwnProperty.call(
            data,
            'harga_satuan'
        )
    ) {

        data.harga_satuan =
            parseFloat(
                String(data.harga_satuan || '0')
                    .replace(/,/g, '')
            ) || 0;

    }


    const saveButton =
        row.querySelector(
            '.btn-save-finance-detail'
        );


    const originalButtonHtml =
        saveButton
            ? saveButton.innerHTML
            : 'Save';


    if (saveButton) {

        saveButton.disabled = true;

        saveButton.innerHTML = `
            <i class="fas fa-spinner fa-spin"></i>
            <span>Saving...</span>
        `;

    }


    /*
     * CSRF
     */
    const csrfMeta =
        document.querySelector(
            'meta[name="csrf-token"]'
        );


    const csrfToken =
        csrfMeta
            ? csrfMeta.getAttribute('content')
            : "{{ csrf_token() }}";


    const url =
        "{{ url('/finance/pengajuan-finance/detail') }}/"
        + detailId;


    fetch(url, {

        method: 'PUT',

        headers: {

            'Content-Type':
                'application/json',

            'Accept':
                'application/json',

            'X-CSRF-TOKEN':
                csrfToken,

            'X-Requested-With':
                'XMLHttpRequest'

        },

        body:
            JSON.stringify(data)

    })

    .then(async function (response) {

        const contentType =
            response.headers.get(
                'content-type'
            ) || '';


        let result;


        if (
            contentType.includes(
                'application/json'
            )
        ) {

            result =
                await response.json();

        } else {

            const text =
                await response.text();


            throw new Error(
                text ||
                'Server tidak mengembalikan JSON.'
            );

        }


        if (!response.ok) {

            throw new Error(
                result.message ||
                'Gagal menyimpan detail.'
            );

        }


        if (
            result.success === false
        ) {

            throw new Error(
                result.message ||
                'Gagal menyimpan detail.'
            );

        }


        return result;

    })

    .then(function (result) {

        /*
         * Update total dari response server
         */
        if (
            result.total_harga !== undefined
        ) {

            const totalElement =
                row.querySelector(
                    '[data-field="total_harga"]'
                );


            if (totalElement) {

                totalElement.textContent =
                    'Rp ' +
                    new Intl.NumberFormat(
                        'id-ID'
                    ).format(
                        Number(
                            result.total_harga
                        ) || 0
                    );

            }

        }


        /*
         * Hapus status perubahan
         */
        row.classList.remove(
            'has-changes'
        );


        /*
         * Sembunyikan tombol Save
         */
        if (saveButton) {

            saveButton.style.display =
                'none';

            saveButton.disabled =
                false;

            saveButton.innerHTML =
                originalButtonHtml;

        }


        /*
         * Refresh grand total
         */
        updateFinanceGrandTotal();


        /*
         * Toast sukses
         */
        showFinanceToast(
            result.message ||
            'Detail berhasil disimpan.',
            'success'
        );

    })

    .catch(function (error) {

        console.error(
            'Finance Save Error:',
            error
        );


        if (saveButton) {

            saveButton.disabled =
                false;

            saveButton.style.display =
                'inline-flex';

            saveButton.innerHTML =
                originalButtonHtml;

        }


        showFinanceToast(
            error.message ||
            'Gagal menyimpan detail.',
            'error'
        );

    });

}


/* =========================================================
   GRAND TOTAL
========================================================= */

function updateFinanceGrandTotal()
{

    const grandTotalElement =
        document.querySelector(
            '[data-grand-total="1"]'
        );


    if (!grandTotalElement) {

        return;

    }


    const rows =
        document.querySelectorAll(
            '#financeDetailModal .finance-detail-row'
        );


    let grandTotal = 0;


    rows.forEach(function (row) {

        const qtyInput =
            row.querySelector(
                '[data-field="qty"]'
            );


        const priceInput =
            row.querySelector(
                '[data-field="harga_satuan"]'
            );


        if (
            !qtyInput ||
            !priceInput
        ) {

            return;

        }


        const qty =
            parseFloat(
                String(
                    qtyInput.value || '0'
                ).replace(/,/g, '')
            ) || 0;


        const price =
            parseFloat(
                String(
                    priceInput.value || '0'
                ).replace(/,/g, '')
            ) || 0;


        grandTotal +=
            qty * price;

    });


    grandTotalElement.textContent =
        'Rp ' +
        new Intl.NumberFormat(
            'id-ID'
        ).format(
            grandTotal
        );

}


/* =========================================================
   TOAST
========================================================= */

function showFinanceToast(
    message,
    type = 'success'
) {

    /*
     * Hapus toast lama
     */
    const oldToast =
        document.getElementById(
            'financeEditToast'
        );


    if (oldToast) {

        oldToast.remove();

    }


    const toast =
        document.createElement(
            'div'
        );


    toast.id =
        'financeEditToast';


    const isSuccess =
        type === 'success';


    toast.style.position =
        'fixed';


    toast.style.right =
        '25px';


    toast.style.bottom =
        '25px';


    toast.style.zIndex =
        '999999';


    toast.style.minWidth =
        '280px';


    toast.style.maxWidth =
        '420px';


    toast.style.padding =
        '12px 16px';


    toast.style.borderRadius =
        '6px';


    toast.style.color =
        '#fff';


    toast.style.fontSize =
        '12px';


    toast.style.fontWeight =
        '500';


    toast.style.boxShadow =
        '0 8px 25px rgba(0,0,0,.18)';


    toast.style.background =
        isSuccess
            ? '#198754'
            : '#dc3545';


    toast.innerHTML = `

        <div
            style="
                display:flex;
                align-items:center;
                gap:9px;
            "
        >

            <i
                class="fas ${
                    isSuccess
                        ? 'fa-check-circle'
                        : 'fa-times-circle'
                }"
            ></i>

            <span>
                ${escapeFinanceHtml(message)}
            </span>

        </div>

    `;


    document.body.appendChild(
        toast
    );


    setTimeout(function () {

        toast.style.opacity =
            '0';

        toast.style.transition =
            'opacity .2s ease';


        setTimeout(function () {

            toast.remove();

        }, 200);

    }, 3000);

}


/* =========================================================
   ESCAPE HTML TOAST
========================================================= */

function escapeFinanceHtml(value)
{

    const div =
        document.createElement(
            'div'
        );


    div.textContent =
        value ?? '';


    return div.innerHTML;

}

</script>