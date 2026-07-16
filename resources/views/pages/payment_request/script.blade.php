
{{-- script --}}
<script>
  $(document).on(
    'click',
    '.btn-detail-draft',
    function () {

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
                function (res) {
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

        • Untuk kembali ke daftar Payment Request,
        <b>geser ke kiri</b>.

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

      res.items.forEach(function (item, index) {
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
res.approvals.forEach(function (approval) {
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
                $('#draftDetailArea').html(html);

setTimeout(function () {

    $('.draft-wrapper').animate({
        scrollLeft:
            $('.draft-wrapper')[0].scrollWidth
    }, 1000);

}, 200);
                }
            );
        }
    );
  $(window).on('load', function () {

    const noReq = new URLSearchParams(
        window.location.search
    ).get('no_req');

    if (!noReq) return;

    const draftTabBtn = $(
        '[data-bs-target="#draft-request-tab"]'
    );

    draftTabBtn.trigger('click');

    setTimeout(function () {

        const btn = $('.btn-detail-draft')
            .filter(function () {

                return String(
                    $(this).data('request')
                ).trim() === noReq.trim();

            });

        if (!btn.length) return;

        btn.trigger('click');

        setTimeout(function () {

            const wrapper =
                document.querySelector(
                    '.draft-wrapper'
                );

            if (wrapper) {

                wrapper.scrollTo({
                    left:
                        wrapper.scrollWidth,
                    behavior:
                        'smooth'
                });

            }

        }, 800);

    }, 800);

});
</script>
<script>
    // adjustment finance
    $(document).on(
    'keypress',
    '.finance-adjustment',
    function (e) {

        if (e.which != 13) {
            return;
        }

        let input = $(this);

        $.post(
            '/payment-request/finance-adjustment',
            {
                _token:
                    $('meta[name="csrf-token"]')
                    .attr('content'),

                spk_id:
                    input.data('spk'),

                payment_id:
                    input.data('payment'),

                adjustment:
                    input.val()
            },
            function () {

                Swal.fire({

                    icon:'success',

                    title:'Saved',

                    timer:1000,

                    showConfirmButton:false

                });

            }
        );

    }
);
    // approve
    $(document).on(
    'click',
    '.btn-approve',
    function () {

        let id =
            $(this).data('id');

        Swal.fire({

            title: 'Approve Request?',

            text:
                'Setelah di approve data tidak dapat dibatalkan.',

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

                url:
                    `/payment-request-approval/${id}/approve`,

                type: 'POST',

                data: {

                    _token:
                        $('meta[name="csrf-token"]')
                        .attr('content')

                },

                beforeSend: function () {

                    Swal.fire({

                        title: 'Processing...',

                        text:
                            'Mohon tunggu',

                        allowOutsideClick: false,

                        didOpen: () => {

                            Swal.showLoading();

                        }

                    });

                },

                success: function (res) {

                    Swal.fire({

                        icon: 'success',

                        title: 'Approved',

                        text:
                            'Approval berhasil disimpan',

                        timer: 1500,

                        showConfirmButton: false

                    }).then(() => {

                        location.reload();

                    });

                },

              error: function (xhr) {

                Swal.fire({

                    icon: 'warning',

                    title: 'Tidak Bisa Approve',

                    text:
                        xhr.responseJSON?.message ??
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
    function () {

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

        setTimeout(function(){
            printWindow.print();

            printWindow.close();

        },500);

    }
);

// hint
$(window).on('load', function () {

    const noReq = new URLSearchParams(
        window.location.search
    ).get('no_req');

    if (!noReq) return;

    setTimeout(function () {

        const btn = $('.btn-detail-draft')
            .filter(function () {

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
    .spk-wrapper > .nav-tabs {

        display: none !important;

    }
    @page {
        size: A4 landscape;
        margin: 10mm;
    }

    body {
        margin:0;
    }

    #btn-print {
        display:none !important;
    }

    .no-print {
        display:none !important;
    }

    table {
        page-break-inside:auto;
    }

    tr {
        page-break-inside:avoid;
        page-break-after:auto;
    }

    .signature-section {
        page-break-inside:avoid;
    }
}

</style>
