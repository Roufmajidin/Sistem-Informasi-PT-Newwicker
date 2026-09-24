{{-- script --}}
<script>
    $(document).on(
        'change',
        '.ainun-recon-check',
        function() {

            const checkbox = $(this);

            const id = checkbox.data('id');

            /*
            |--------------------------------------------------------------------------
            | STATUS CHECKBOX SEKARANG
            |--------------------------------------------------------------------------
            */

            const checked = checkbox.is(':checked');


            /*
            |--------------------------------------------------------------------------
            | JIKA DICENTANG
            |--------------------------------------------------------------------------
            */

            if (checked) {

                Swal.fire({

                    title: 'Selesai recons?',

                    text: 'Apply to kreditor database?',

                    icon: 'question',

                    showCancelButton: true,

                    confirmButtonText: 'Ya',

                    cancelButtonText: 'Cancel',

                    confirmButtonColor: '#198754',

                    cancelButtonColor: '#6c757d',

                    reverseButtons: true,

                }).then(function(result) {

                    /*
                    |--------------------------------------------------------------------------
                    | CANCEL
                    |--------------------------------------------------------------------------
                    */

                    if (!result.isConfirmed) {

                        checkbox.prop(
                            'checked',
                            false
                        );

                        return;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | APPLY
                    |--------------------------------------------------------------------------
                    */

                    $.ajax({

                        url: `/payment-request-saved/${id}/set-recon`,

                        type: 'POST',

                        data: {

                            _token: $('meta[name="csrf-token"]')
                                .attr('content'),

                        },


                        beforeSend: function() {

                            Swal.fire({

                                title: 'Processing...',

                                text: 'Applying to kreditor database',

                                allowOutsideClick: false,

                                allowEscapeKey: false,

                                didOpen: function() {

                                    Swal.showLoading();

                                }

                            });

                        },


                        success: function(res) {

                            if (!res.success) {

                                checkbox.prop(
                                    'checked',
                                    false
                                );

                                Swal.fire({

                                    icon: 'error',

                                    title: 'Gagal',

                                    text: res.message ??
                                        'Gagal menyimpan recon.'

                                });

                                return;
                            }


                            /*
                            |--------------------------------------------------------------------------
                            | SUCCESS
                            |--------------------------------------------------------------------------
                            */

                            Swal.fire({

                                icon: 'success',

                                title: 'Selesai',

                                text: 'Data berhasil di-apply ke kreditor database.',

                                timer: 1500,

                                showConfirmButton: false

                            });

                        },


                        error: function(xhr) {

                            /*
                            |--------------------------------------------------------------------------
                            | JIKA ERROR
                            |--------------------------------------------------------------------------
                            */

                            checkbox.prop(
                                'checked',
                                false
                            );


                            Swal.fire({

                                icon: 'error',

                                title: 'Gagal',

                                text: xhr.responseJSON?.message ??
                                    'Terjadi kesalahan saat menyimpan recon.'

                            });

                        }

                    });

                });

            }

        }
    );
    $(document).on(
        'click',
        '.btn-detail-draft',
        function() {

            let draftId = $(this).data('id');

            $('.draft-row').removeClass(
                'active-row'
            );

            $(this)
                .closest('tr')
                .addClass(
                    'active-row'
                );

            $.get(
                `/payment-request-saved/${draftId}/detail`,
                function(res) {
                    let requestDate = res.request_date ?? '';
                    let needDate = res.need_date ?? '';
                    let html = `
<div id="printArea">
     <div class="alert alert-info mb-3 no-print">

        <i class="fa fa-info-circle"></i>

        <b>Petunjuk :</b>

        • Scroll ke bawah hingga bagian <b>Signature</b>, lalu klik
        <b>Approve</b>.

        <br>

        • Klik <b>Description (SPK)</b> untuk melihat detail SPK terkait.

        <br>

        • Untuk kembali ke daftar Payment Request, klik tombol <b>← Back</b> di samping judul Payment Requests.

    </div>

                    <div style="background:white;
        padding:20px;
        font-family:Arial;
        font-size:11px;  ">
    {{-- HEADER --}}
    <table width="100%" style="   margin-bottom:10px;">
        <tr>
            {{-- LOGO --}}
            <td width="25%">
                                    <img src="{{ asset('/assets/images/NEWWICKER WHITE.png') }}" height="80">

            </td>
            {{-- TITLE --}}
            <td width="50%" align="center">
                <h2 style="margin:0;font-size:28px; ">
                    Purchase Request
                </h2>
            </td>
            {{-- NEED DATE --}}
            <td width="25%">
                <table width="100%" style="border-collapse:collapse;  ">
                    <tr>
                        <td style=" border:1px solid black; padding:4px; font-size:11px; ">     Need by Date :
                        </td>
                        <td style=" border:1px solid black;  padding:4px;font-size:11px;
                            ">
                            <input type="tet" id="need_date" value="${needDate}"
                                style="
                                    width:100%;
                                    border:none;
                                    outline:none;
                                    background:transparent;
                                    font-size:11px;
                                ">
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    {{-- INFO --}}
    <table width="100%" style="
            border-collapse:collapse;
            margin-bottom:10px;
        ">
        <tr>
            <td style="
                    border:1px solid black;
                    padding:4px;
                    width:180px;
                    font-weight:bold;
                ">
                Requisition Date :
            </td>
            <td style="
                    border:1px solid black;
                    padding:4px;
                ">
                <input type="text" id="request_date"value="${requestDate}" style="
                        width:100%;
                        border:none;
                        outline:none;
                        background:transparent;
                        font-size:11px;
                    ">
            </td>
            <td style="
                    border:1px solid black;
                    padding:4px;
                    width:180px;
                    font-weight:bold;
                ">
                Department :
            </td>
            <td style="
                    border:1px solid black;
                    padding:4px;
                ">
                <input type="text" value="Purchasing" style="
                        width:100%;
                        border:none;
                        outline:none;
                        background:transparent;
                        font-size:11px;
                    ">
            </td>
        </tr>
    </table>
    {{-- print BUTTON --}}
  <button
    id="btn-print"
    type="button"
    style="
        background:#111827;
        color:white;
        border:none;
        padding:8px 18px;
        border-radius:6px;
        font-size:12px;
        font-weight:bold;
        cursor:pointer;
    ">
    Print
</button>
                <div class="card">
                    <div class="card-header">
                        <h5>
                            ${res.request_no}
                        </h5>
                    </div>
                    <div class="card-body">
                        <table
                            class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>PO</th>
                                    <th>TGL</th>
                                    <th>Supplier</th>
                                    <th>Payment</th>
                                    <th>Description</th>
                                    <th>Keterangan</th>
                                    <th>Quantity</th>
                                    <th>sat</th>
                                    <th>Unit price</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>adjusment finance</th>
                            </tr>
                        </thead>
                        <tbody>
        `;
                    let grandTotal = 0;

                    let totalPaymentRequest = 0;

                    res.items.forEach(function(item, index) {
                        grandTotal += Number(
                            item.payment_amount || 0
                        );
                        totalPaymentRequest += Number(
                            item.payment_request_amount || 0
                        );
                        html += `
        <tr style="font-size:11px;">
            <td>${index + 1}</td>
            <td>${item.no_po ?? ''}</td>
            <td>${item.tanggal ?? ''}</td>
            <td>${item.supplier ?? ''}</td>
            <td>Transfer</td>
            <td>
                <a href="/spk/views/${item.spk_id}" target="_blank">
                    ${item.spk_no ?? ''}
                </a>
            </td>
            <td>${item.payment_note ?? ''}</td>
            <td></td>
            <td></td>
            <td></td>
            <td>
                Rp ${Number(item.payment_amount || 0).toLocaleString('id-ID')}
            </td>
            <td>Urgent</td>
         <td>
            ${
                res.is_finance
                ? `
                    <input
                        type="number"
                        class="form-control form-control-sm finance-adjustment"
                        data-spk="${item.spk_id}"
                        data-payment="${item.payment_id}"
                        value="${item.payment_request_amount}">
                `
                : `
                    ${Number(item.payment_request_amount)
                        .toLocaleString('id-ID')}
                `
            }
            </td>
        </tr>
    `;
                    });
                    let remainingAmount =
                        grandTotal - totalPaymentRequest;
                    html += `
<tr
    style="
        font-weight:bold;
        background:#fff3cd;
    ">
    <td colspan="10" align="right">
        TOTAL PAYMENT REQUEST
    </td>

    <td align="right">
        Rp ${totalPaymentRequest.toLocaleString('id-ID')}
    </td>

    <td></td>
</tr>

<tr
    style="
        font-weight:bold;
        background:#d1e7dd;
        font-size:13px;
    ">
    <td colspan="10" align="right">
        SISA PEMBAYARAN
    </td>

    <td align="right">
        Rp ${remainingAmount.toLocaleString('id-ID')}
    </td>

    <td></td>
</tr>

`;
                    html += `

<div
    class="signature-section"
    style="margin-top:60px;">

    <table
        width="100%"
        style="
            text-align:center;
            font-size:11px;
        ">

        <tr>

            <td width="12.5%">

                <div
                    style="
                        font-weight:bold;
                        margin-bottom:5px;
                    ">
                    Made By
                </div>

              <div style="height:70px;">
    <img src="{{ asset('signature/1.png') }}"
        style="max-height:50px; max-width:120px;">
</div>

<div style="font-weight:bold;">
    Nur Khasanah
</div>

            </td>

`;
                    res.approvals.forEach(function(approval) {
                        console.log(res.approvals);
                        html += `

        <td width="12.5%">

            <div
                style="
                    font-weight:bold;
                    margin-bottom:5px;
                ">
                ${approval.role}
            </div>

       <div
            style="
                height:100px;
                display:flex;
                flex-direction:column;
                align-items:center;
                justify-content:center;
            ">

            ${
                approval.status === 'Approved'
                ? `
                    <img
                        src="/assets/signature/${approval.user_id}.png"
                        style="
                            max-height:80px;
                            max-width:120px;
                        ">

                    <div
                        style="
                            margin-top:4px;
                            font-size:10px;
                            color:#198754;
                            font-weight:bold;
                            line-height:1.3;
                        ">
                        Approved on<br>
                        ${approval.approved_at ?? ''}
                    </div>
                `
                : ''
            }

        </div>

            <div style="font-weight:bold;">
                ${approval.name}
            </div>

            <div style="font-size:10px;">
                ${approval.role}
            </div>

            <div
                style="
                    font-size:10px;
                    color:${
                        approval.status === 'Approved'
                        ? 'green'
                        : 'red'
                    };
                ">
                ${approval.status}
            </div>

            ${
                approval.can_approve
                ? `
                    <button
                        class="btn btn-success btn-sm btn-approve"
                        data-id="${approval.id}"
                        style="margin-top:5px;">
                        Approve
                    </button>
                `
                : ''
            }

        </td>

    `;

                    });

                    // =========================================================
                    // ADD TO DRAFT FINANCE
                    // =========================================================
                    html += `
                        <div
                            class="no-print"
                            style="
                                margin-top:20px;
                                padding:15px;
                                border:1px solid #dbe3ec;
                                border-radius:8px;
                                background:#f8fafc;
                                text-align:right;
                            "
                        >
                            <button
                                type="button"
                                class="btn btn-primary btn-add-finance-draft"
                                data-id="${draftId}"
                            >
                                <i class="fa fa-plus-circle"></i>
                                Add to Draft Finance
                            </button>
                        </div>
                    `;

                    $('#draftDetailArea').html(html);

                    setTimeout(function() {

                        const wrapper = $('.draft-wrapper')[0];

                        if (wrapper) {
                            $(wrapper).animate({
                                scrollLeft: wrapper.clientWidth
                            }, 500);
                        }

                    }, 200);
                }
            );
        }
    );
    $(window).on('load', function() {

        const params = new URLSearchParams(window.location.search);

        // Ambil nomor request dari URL
        const requestNo =
            params.get('request') ||
            params.get('no_req');

        // Bukan halaman Magic Link
        if (!requestNo) {
            return;
        }

        console.log('Magic Link Request:', requestNo);

        /*
        |--------------------------------------------------------------------------
        | 1. Pastikan tab Draft Request aktif
        |--------------------------------------------------------------------------
        */

        const draftTab =
            document.querySelector(
                '[data-bs-target="#draft-request-tab"]'
            );

        if (draftTab) {
            draftTab.click();
        }

        /*
        |--------------------------------------------------------------------------
        | 2. Tunggu tabel draft selesai dirender
        |--------------------------------------------------------------------------
        */

        let attempts = 0;
        const maxAttempts = 30;

        const openDraftDetail = setInterval(function() {

            attempts++;

            const buttons =
                $('.btn-detail-draft');

            console.log(
                'Cari request:',
                requestNo,
                '| Button:',
                buttons.length,
                '| Attempt:',
                attempts
            );

            let target = null;

            buttons.each(function() {

                const btnRequest =
                    String(
                        $(this).attr('data-request') || ''
                    ).trim();

                if (
                    btnRequest.toLowerCase() ===
                    requestNo.trim().toLowerCase()
                ) {
                    target = this;
                    return false;
                }
            });

            /*
            |--------------------------------------------------------------------------
            | 3. Ketemu → buka Detail
            |--------------------------------------------------------------------------
            */

            if (target) {

                clearInterval(openDraftDetail);

                console.log(
                    'FOUND REQUEST:',
                    requestNo
                );

                const $target =
                    $(target);

                /*
                | Tandai row
                */
                $('.draft-row')
                    .removeClass('active-row');

                $target
                    .closest('tr')
                    .addClass('active-row');

                /*
                | Trigger handler Detail
                */
                $target.trigger('click');

                /*
                |--------------------------------------------------------------------------
                | 4. Setelah detail muncul → scroll ke detail
                |--------------------------------------------------------------------------
                */

                setTimeout(function() {

                    const detail =
                        document.getElementById(
                            'draftDetailArea'
                        );

                    if (detail) {

                        detail.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });

                    }

                }, 700);

                return;
            }

            /*
            |--------------------------------------------------------------------------
            | 5. Stop kalau sudah terlalu lama
            |--------------------------------------------------------------------------
            */

            if (attempts >= maxAttempts) {

                clearInterval(openDraftDetail);

                console.warn(
                    'Request tidak ditemukan:',
                    requestNo
                );

            }

        }, 300);

    });
</script>
<script>
    // =========================================================
    // ADD PAYMENT LIST TO DRAFT FINANCE
    // =========================================================
    $(document).on(
        'click',
        '.btn-add-finance-draft',
        function() {

            const button = $(this);
            const id = button.data('id');

            if (!id) {
                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: 'ID payment request tidak ditemukan.'
                });

                return;
            }

            Swal.fire({
                title: 'Add to Draft Finance?',
                text: 'Anda akan menambahkan list payment ini ke draft finance, sudah selesai recons?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Ya',
                cancelButtonText: 'Tidak',
                confirmButtonColor: '#198754',
                cancelButtonColor: '#6c757d',
                reverseButtons: true
            }).then(function(result) {

                if (!result.isConfirmed) {
                    return;
                }

                $.ajax({
                    url: `/payment-request-saved/${id}/add-to-finance`,
                    type: 'POST',
                    data: {
                        _token: $('meta[name="csrf-token"]').attr('content')
                    },
                    beforeSend: function() {

                        button
                            .prop('disabled', true)
                            .html('<i class="fa fa-spinner fa-spin"></i> Processing...');

                    },
                    success: function(res) {

                        if (!res.success) {
                            button
                                .prop('disabled', false)
                                .html('<i class="fa fa-plus-circle"></i> Add to Draft Finance');

                            Swal.fire({
                                icon: 'error',
                                title: 'Gagal',
                                text: res.message || 'Gagal menambahkan payment ke Draft Finance.'
                            });

                            return;
                        }

                        button
                            .prop('disabled', true)
                            .removeClass('btn-primary')
                            .addClass('btn-success')
                            .html('<i class="fa fa-check"></i> Added to Draft Finance');

                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil',
                            text: res.message || 'Payment berhasil ditambahkan ke Draft Finance.',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    },
                    error: function(xhr) {

                        button
                            .prop('disabled', false)
                            .html('<i class="fa fa-plus-circle"></i> Add to Draft Finance');

                        Swal.fire({
                            icon: 'error',
                            title: 'Server Error',
                            text: xhr.responseJSON?.message || 'Terjadi kesalahan saat menambahkan payment ke Draft Finance.'
                        });
                    }
                });
            });
        }
    );
</script>
<script>
    // adjustment finance
    $(document).on(
        'keypress',
        '.finance-adjustment',
        function(e) {

            if (e.which != 13) {
                return;
            }

            let input = $(this);

            $.post(
                '/payment-request/finance-adjustment', {
                    _token: $('meta[name="csrf-token"]')
                        .attr('content'),

                    spk_id: input.data('spk'),

                    payment_id: input.data('payment'),

                    adjustment: input.val()
                },
                function() {

                    Swal.fire({

                        icon: 'success',

                        title: 'Saved',

                        timer: 1000,

                        showConfirmButton: false

                    });

                }
            );

        }
    );
    // approve
    $(document).on(
        'click',
        '.btn-approve',
        function() {

            let id =
                $(this).data('id');

            Swal.fire({

                title: 'Approve Request?',

                text: 'Setelah di approve data tidak dapat dibatalkan.',

                icon: 'question',

                showCancelButton: true,

                confirmButtonColor: '#198754',

                cancelButtonColor: '#6c757d',

                confirmButtonText: 'Ya, Approve',

                cancelButtonText: 'Batal'

            }).then((result) => {

                if (!result.isConfirmed) {
                    return;
                }

                $.ajax({

                    url: `/payment-request-approval/${id}/approve`,

                    type: 'POST',

                    data: {

                        _token: $('meta[name="csrf-token"]')
                            .attr('content')

                    },

                    beforeSend: function() {

                        Swal.fire({

                            title: 'Processing...',

                            text: 'Mohon tunggu',

                            allowOutsideClick: false,

                            didOpen: () => {

                                Swal.showLoading();

                            }

                        });

                    },

                    success: function(res) {

                        Swal.fire({

                            icon: 'success',

                            title: 'Approved',

                            text: 'Approval berhasil disimpan',

                            timer: 1500,

                            showConfirmButton: false

                        }).then(() => {

                            location.reload();

                        });

                    },

                    error: function(xhr) {

                        Swal.fire({

                            icon: 'warning',

                            title: 'Tidak Bisa Approve',

                            text: xhr.responseJSON?.message ??
                                'Approval gagal'

                        });

                    }

                });

            });

        }
    );
    $(document).on(
        'click',
        '#btn-print',
        function() {

            let printContents =
                $('#printArea').html();

            let printWindow =
                window.open(
                    '',
                    '',
                    'width=1200,height=900'
                );

            printWindow.document.write(`
            <html>
            <head>

                <title>
                    Purchase Request
                </title>

                <style>

                    @page{
                        size:A4 landscape;
                        margin:10mm;
                    }

                    body{
                        font-family:Arial;
                        font-size:11px;
                    }

                    table{
                        width:100%;
                        border-collapse:collapse;
                    }

                    th,
                    td{
                        border:1px solid #000;
                        padding:4px;
                        font-size:11px;
                    }

                    .signature-section{
                        page-break-inside:avoid;
                    }
 #btn-print
{
 display:none !important;}
                </style>

            </head>

            <body>

                ${printContents}

            </body>

            </html>
        `);

            printWindow.document.close();

            printWindow.focus();

            setTimeout(function() {
                printWindow.print();

                printWindow.close();

            }, 500);

        }
    );

    // hint
    $(window).on('load', function() {

        const noReq = new URLSearchParams(
            window.location.search
        ).get('no_req');

        if (!noReq) return;

        setTimeout(function() {

            const btn = $('.btn-detail-draft')
                .filter(function() {

                    return (
                        String(
                            $(this).data('request')
                        ).trim() === noReq.trim()
                    );

                });

            if (btn.length) {

                btn.trigger('click');

            }

        }, 1000);

    });
</script>
<style>
    @media print {
        .card-title {

            display: none !important;

        }

        .nav,
        .nav-tabs,
        .nav-item,
        .spk-wrapper>.nav-tabs {

            display: none !important;

        }

        @page {
            size: A4 landscape;
            margin: 10mm;
        }

        body {
            margin: 0;
        }

        #btn-print {
            display: none !important;
        }

        .no-print {
            display: none !important;
        }

        table {
            page-break-inside: auto;
        }

        tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }

        .signature-section {
            page-break-inside: avoid;
        }
    }
</style>
<script>
    $(document).ready(function() {

        let currentMagicRequest = null;
        let currentApprovals = [];
        let selectedApproval = null;


        /*
        |--------------------------------------------------------------------------
        | OPEN MAGIC APPROVAL MODAL
        |--------------------------------------------------------------------------
        */

        $(document).on(
            'click',
            '.btn-magic-approval',
            function(e) {

                e.preventDefault();
                e.stopPropagation();

                const button = $(this);

                /*
                |--------------------------------------------------------------------------
                | REQUEST NUMBER
                |--------------------------------------------------------------------------
                */

                currentMagicRequest =
                    String(button.attr('data-request') || '').trim();

                $('#magicRequestNo')
                    .text(currentMagicRequest || '-');


                /*
                |--------------------------------------------------------------------------
                | RESET MODAL
                |--------------------------------------------------------------------------
                */

                currentApprovals = [];
                selectedApproval = null;

                $('#magicApproverList').html('');

                $('#magicGeneratedArea')
                    .hide();

                $('#magicGeneratedUrl')
                    .val('');

                $('#btnGenerateMagicLink')
                    .prop('disabled', true)
                    .html(
                        '<i class="fa fa-link"></i> Generate Link'
                    );


                /*
                |--------------------------------------------------------------------------
                | GET APPROVAL DATA
                |--------------------------------------------------------------------------
                */

                let rawApprovals =
                    button.attr('data-approvals') || '[]';

                try {

                    currentApprovals =
                        JSON.parse(rawApprovals);

                } catch (error) {

                    console.error(
                        'Gagal membaca data approvals:',
                        error
                    );

                    currentApprovals = [];
                }


                console.log(
                    'Magic Approval:',
                    currentMagicRequest,
                    currentApprovals
                );


                /*
                |--------------------------------------------------------------------------
                | RENDER APPROVER
                |--------------------------------------------------------------------------
                */

                renderMagicApprovers();


                /*
                |--------------------------------------------------------------------------
                | OPEN MODAL
                |--------------------------------------------------------------------------
                */

                const modalElement =
                    document.getElementById(
                        'magicApprovalModal'
                    );

                if (!modalElement) {

                    console.error(
                        'Element #magicApprovalModal tidak ditemukan.'
                    );

                    return;
                }

                const modal =
                    bootstrap.Modal.getOrCreateInstance ?
                    bootstrap.Modal.getOrCreateInstance(modalElement) :
                    new bootstrap.Modal(modalElement);

                modal.show();

            }
        );


        /*
        |--------------------------------------------------------------------------
        | RENDER APPROVERS
        |--------------------------------------------------------------------------
        */

        function renderMagicApprovers() {

            const container =
                $('#magicApproverList');

            container.empty();


            if (
                !Array.isArray(currentApprovals) ||
                currentApprovals.length === 0
            ) {

                container.html(`
                <div class="magic-empty-approver">
                    <div class="magic-empty-icon">
                        <i class="fa fa-users"></i>
                    </div>

                    <div class="magic-empty-title">
                        Tidak ada approver
                    </div>

                    <div class="magic-empty-text">
                        Draft ini belum memiliki data approval.
                    </div>
                </div>
            `);

                $('#btnGenerateMagicLink')
                    .prop('disabled', true);

                return;
            }


            /*
            |--------------------------------------------------------------------------
            | SORT BY STEP
            |--------------------------------------------------------------------------
            */

            currentApprovals.sort(function(a, b) {

                return Number(a.step || 0) -
                    Number(b.step || 0);

            });


            /*
            |--------------------------------------------------------------------------
            | RENDER
            |--------------------------------------------------------------------------
            */

            currentApprovals.forEach(function(
                approval,
                index
            ) {

                const approvalId =
                    approval.id ?? '';

                const userId =
                    approval.user_id ?? '';

                const role =
                    approval.role ||
                    'Approver';

                const userName =
                    approval.user_name ||
                    approval.name ||
                    '-';

                const email =
                    approval.email || '';

                const status =
                    String(
                        approval.status || 'Pending'
                    ).trim();

                const normalizedStatus =
                    status.toLowerCase();


                /*
                |--------------------------------------------------------------------------
                | STATUS
                |--------------------------------------------------------------------------
                */

                let statusClass =
                    'magic-status-pending';

                let statusText =
                    'Pending';

                let disabled = false;


                if (
                    normalizedStatus === 'approved'
                ) {

                    statusClass =
                        'magic-status-approved';

                    statusText =
                        'Approved';

                    disabled = true;

                } else if (
                    normalizedStatus === 'rejected'
                ) {

                    statusClass =
                        'magic-status-rejected';

                    statusText =
                        'Rejected';

                    disabled = true;

                }


                /*
                |--------------------------------------------------------------------------
                | USER ACCOUNT CHECK
                |--------------------------------------------------------------------------
                */

                if (!userId) {
                    disabled = true;
                }


                /*
                |--------------------------------------------------------------------------
                | HTML
                |--------------------------------------------------------------------------
                */

                const html = `

                <div
                    class="
                        magic-approver-item
                        ${disabled ? 'is-disabled' : ''}
                    "
                    data-approval-id="${escapeHtml(approvalId)}"
                    data-user-id="${escapeHtml(userId)}"
                    data-role="${escapeHtml(role)}"
                    data-name="${escapeHtml(userName)}"
                    data-status="${escapeHtml(status)}"
                >

                    <div class="magic-radio-wrap">

                        <input
                            type="radio"
                            name="magicApprover"
                            class="magic-approver-radio"
                            value="${escapeHtml(approvalId)}"
                            ${disabled ? 'disabled' : ''}
                        >

                    </div>


                    <div class="magic-avatar">

                        <i class="fa fa-user"></i>

                    </div>


                    <div class="magic-approver-info">

                        <div class="magic-approver-name">

                            ${escapeHtml(userName)}

                        </div>


                        <div class="magic-approver-role">

                            ${escapeHtml(role)}

                        </div>


                        ${
                            email
                            ? `
                                <div class="magic-approver-email">
                                    ${escapeHtml(email)}
                                </div>
                            `
                            : ''
                        }

                    </div>


                    <div class="magic-approver-right">

                        <span
                            class="
                                magic-status
                                ${statusClass}
                            "
                        >
                            ${escapeHtml(statusText)}
                        </span>

                    </div>

                </div>

            `;

                container.append(html);

            });


            /*
            |--------------------------------------------------------------------------
            | CHECK IF THERE IS SELECTABLE APPROVER
            |--------------------------------------------------------------------------
            */

            const selectable =
                container.find(
                    '.magic-approver-radio:not(:disabled)'
                );

            if (!selectable.length) {

                $('#btnGenerateMagicLink')
                    .prop('disabled', true);

            }

        }


        /*
        |--------------------------------------------------------------------------
        | SELECT APPROVER
        |--------------------------------------------------------------------------
        */

        $(document).on(
            'click',
            '.magic-approver-item:not(.is-disabled)',
            function(e) {

                /*
                |--------------------------------------------------------------------------
                | Jangan trigger dua kali ketika click radio
                |--------------------------------------------------------------------------
                */

                if (
                    $(e.target).is(
                        'input[type="radio"]'
                    )
                ) {
                    return;
                }


                const item =
                    $(this);

                const radio =
                    item.find(
                        '.magic-approver-radio'
                    );

                radio.prop(
                    'checked',
                    true
                );

                selectApprover(item);

            }
        );


        /*
        |--------------------------------------------------------------------------
        | RADIO CHANGE
        |--------------------------------------------------------------------------
        */

        $(document).on(
            'change',
            '.magic-approver-radio',
            function() {

                const item =
                    $(this).closest(
                        '.magic-approver-item'
                    );

                selectApprover(item);

            }
        );


        /*
        |--------------------------------------------------------------------------
        | SELECT APPROVER FUNCTION
        |--------------------------------------------------------------------------
        */

        function selectApprover(item) {

            $('.magic-approver-item')
                .removeClass(
                    'is-selected'
                );


            item.addClass(
                'is-selected'
            );


            selectedApproval = {

                id: item.data('approval-id'),

                user_id: item.data('user-id'),

                role: item.data('role'),

                name: item.data('name'),

                status: item.data('status')

            };


            console.log(
                'Selected approval:',
                selectedApproval
            );


            /*
            |--------------------------------------------------------------------------
            | ENABLE GENERATE
            |--------------------------------------------------------------------------
            */

            if (
                selectedApproval.user_id
            ) {

                $('#btnGenerateMagicLink')
                    .prop(
                        'disabled',
                        false
                    );

            } else {

                $('#btnGenerateMagicLink')
                    .prop(
                        'disabled',
                        true
                    );

            }

        }


        /*
        |--------------------------------------------------------------------------
        | GENERATE BUTTON
        |--------------------------------------------------------------------------
        */

        $(document).on(
            'click',
            '#btnGenerateMagicLink',
            function(e) {

                e.preventDefault();


                /*
                |--------------------------------------------------------------------------
                | VALIDATE
                |--------------------------------------------------------------------------
                */

                if (!currentMagicRequest) {

                    Swal.fire({

                        icon: 'warning',

                        title: 'Request tidak ditemukan',

                        text: 'Nomor request tidak tersedia.'

                    });

                    return;
                }


                if (
                    !selectedApproval ||
                    !selectedApproval.user_id
                ) {

                    Swal.fire({

                        icon: 'warning',

                        title: 'Pilih approver',

                        text: 'Silakan pilih approver terlebih dahulu.'

                    });

                    return;
                }


                /*
                |--------------------------------------------------------------------------
                | CONFIRM
                |--------------------------------------------------------------------------
                */

                Swal.fire({

                    title: 'Generate Magic Link?',

                    html: `

                    <div style="
                        font-size:13px;
                        line-height:1.7;
                    ">

                        Request:
                        <strong>
                            ${escapeHtml(
                                currentMagicRequest
                            )}
                        </strong>

                        <br>

                        Approver:
                        <strong>
                            ${escapeHtml(
                                selectedApproval.name
                            )}
                        </strong>

                        <br>

                        Role:
                        <strong>
                            ${escapeHtml(
                                selectedApproval.role
                            )}
                        </strong>

                    </div>

                `,

                    icon: 'question',

                    showCancelButton: true,

                    confirmButtonText: 'Generate Link',

                    cancelButtonText: 'Batal',

                    confirmButtonColor: '#0d6efd',

                    cancelButtonColor: '#6c757d',

                    reverseButtons: true

                }).then(function(result) {

                    if (!result.isConfirmed) {
                        return;
                    }


                    generateMagicLink();

                });

            }
        );


        /*
        |--------------------------------------------------------------------------
        | GENERATE MAGIC LINK AJAX
        |--------------------------------------------------------------------------
        */

        function generateMagicLink() {

            const button =
                $('#btnGenerateMagicLink');


            const originalHtml =
                button.html();


            button
                .prop(
                    'disabled',
                    true
                )
                .html(`
                <i class="fa fa-spinner fa-spin"></i>
                Generating...
            `);


            $.ajax({

                url: "{{ route('approval.magic.generate') }}",

                type: 'POST',

                data: {

                    _token: $('meta[name="csrf-token"]')
                        .attr('content'),

                    no_req: currentMagicRequest,

                    user_id: selectedApproval.user_id

                },


                success: function(res) {

                    console.log(
                        'Magic link response:',
                        res
                    );


                    if (!res.success) {

                        Swal.fire({

                            icon: 'error',

                            title: 'Gagal',

                            text: res.message ||
                                'Gagal membuat magic link.'

                        });

                        return;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | HIDE APPROVER LIST
                    |--------------------------------------------------------------------------
                    */

                    $('#magicApproverList')
                        .slideUp(
                            150
                        );


                    $('.magic-section-title')
                        .hide();


                    /*
                    |--------------------------------------------------------------------------
                    | SHOW GENERATED LINK
                    |--------------------------------------------------------------------------
                    */

                    $('#magicGeneratedUrl')
                        .val(
                            res.url || ''
                        );


                    $('#magicGeneratedArea')
                        .slideDown(
                            200
                        );


                    /*
                    |--------------------------------------------------------------------------
                    | SUCCESS
                    |--------------------------------------------------------------------------
                    */

                    Swal.fire({

                        icon: 'success',

                        title: 'Berhasil',

                        html: `
                        Magic link berhasil dibuat
                        untuk
                        <strong>
                            ${escapeHtml(
                                selectedApproval.name
                            )}
                        </strong>
                    `,

                        timer: 1400,

                        showConfirmButton: false

                    });

                },


                error: function(xhr) {

                    console.error(
                        'Magic link error:',
                        xhr
                    );


                    Swal.fire({

                        icon: 'error',

                        title: 'Server Error',

                        text: xhr.responseJSON?.message ||
                            'Terjadi kesalahan server.'

                    });

                },


                complete: function() {

                    button
                        .prop(
                            'disabled',
                            false
                        )
                        .html(
                            originalHtml
                        );

                }

            });

        }


        /*
        |--------------------------------------------------------------------------
        | COPY LINK
        |--------------------------------------------------------------------------
        */

        $(document).on(
            'click',
            '#btnCopyMagicLink',
            function() {

                const input =
                    document.getElementById(
                        'magicGeneratedUrl'
                    );


                if (!input) {
                    return;
                }


                const url =
                    input.value;


                if (!url) {
                    return;
                }


                /*
                |--------------------------------------------------------------------------
                | MODERN CLIPBOARD
                |--------------------------------------------------------------------------
                */

                if (
                    navigator.clipboard &&
                    window.isSecureContext
                ) {

                    navigator.clipboard
                        .writeText(url)
                        .then(function() {

                            showCopied();

                        })
                        .catch(function() {

                            fallbackCopy(input);

                        });

                } else {

                    fallbackCopy(input);

                }

            }
        );


        /*
        |--------------------------------------------------------------------------
        | FALLBACK COPY
        |--------------------------------------------------------------------------
        */

        function fallbackCopy(input) {

            input
                .focus();

            input
                .select();

            input
                .setSelectionRange(
                    0,
                    99999
                );


            try {

                document.execCommand(
                    'copy'
                );

                showCopied();

            } catch (error) {

                Swal.fire({

                    icon: 'warning',

                    title: 'Tidak bisa copy',

                    text: 'Silakan copy link secara manual.'

                });

            }

        }


        /*
        |--------------------------------------------------------------------------
        | COPIED MESSAGE
        |--------------------------------------------------------------------------
        */

        function showCopied() {

            Swal.fire({

                icon: 'success',

                title: 'Copied',

                text: 'Magic link berhasil disalin.',

                timer: 1000,

                showConfirmButton: false

            });

        }


        /*
        |--------------------------------------------------------------------------
        | RESET WHEN MODAL CLOSED
        |--------------------------------------------------------------------------
        */

        $('#magicApprovalModal')
            .on(
                'hidden.bs.modal',
                function() {

                    currentMagicRequest =
                        null;

                    currentApprovals = [];

                    selectedApproval =
                        null;

                    $('#magicApproverList')
                        .show()
                        .empty();

                    $('.magic-section-title')
                        .show();

                    $('#magicGeneratedArea')
                        .hide();

                    $('#magicGeneratedUrl')
                        .val('');

                    $('#btnGenerateMagicLink')
                        .prop(
                            'disabled',
                            true
                        );

                }
            );


        /*
        |--------------------------------------------------------------------------
        | HTML ESCAPE
        |--------------------------------------------------------------------------
        */

        function escapeHtml(value) {

            return String(
                    value ?? ''
                )
                .replace(
                    /&/g,
                    '&amp;'
                )
                .replace(
                    /</g,
                    '&lt;'
                )
                .replace(
                    />/g,
                    '&gt;'
                )
                .replace(
                    /"/g,
                    '&quot;'
                )
                .replace(
                    /'/g,
                    '&#039;'
                );

        }

    });
</script>
