{{-- script --}}
<script>
    // Format tanggal API tanpa menggeser hari akibat timezone.
    function formatPaymentDateDMY(value) {
        if (!value) return '';
        const match = String(value).match(/^(\d{4})-(\d{2})-(\d{2})/);
        return match ? `${match[3]}/${match[2]}/${match[1]}` : String(value);
    }

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
                    let requestDate = formatPaymentDateDMY(res.request_date);
                    let needDate = formatPaymentDateDMY(res.need_date);
                    const detailSpks = (res.items || []).map(x => x.spk_no || x.no_spk || '').join(' ');
                    const detailSpkMatch = detailSpks.match(/\/(NWS|NWD|NWR|NW)(?=\s|\/|-|$)/i);
                    const currentDraftCode = String(res.production_code || $('.draft-row.active-row').attr(
                        'data-production-code') || (detailSpkMatch ? detailSpkMatch[1] : '')).toUpperCase();
                    const currentDepartment = ['NWS', 'NWD', 'NWR'].includes(currentDraftCode) ?
                        'RND Engineering' : 'Purchasing';
                    let html = `
<div id="printArea">
     <div class="alert alert-info mb-3 no-print print-instructions">

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
                    Payment Request
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
                <input type="text" value="${currentDepartment}" style="
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
    class="no-print"
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
                            class="table table-bordered payment-print-table">
                            <thead>
                                <tr>
                                    <th>No</th>
                                    <th>PO</th>
                                    <th>TGL</th>
                                    <th>Supplier</th>
                                    <th>Payment</th>
                                    <th>Description</th>
                                    <th>Keterangan</th>
                                    <th>Qty</th>
                                    <th>Sat</th>
                                    <th>Unit Price</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th class="finance-adjustment-column">Recon Fin</th>
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
            <td>${formatPaymentDateDMY(item.tanggal)}</td>
            <td>${item.supplier ?? ''}</td>
            <td>Transfer</td>
            <td>
                <a href="/spk/views/${item.spk_id}" target="_blank">
                    ${item.spk_no ?? ''}
                </a>
            </td>
            <td>${item.payment_note ?? ''}</td>
            <td class="text-center">1</td>
            <td class="text-center">-</td>
            <td class="text-end">Rp ${Number(item.payment_amount || 0).toLocaleString('id-ID')}</td>
            <td class="text-end">Rp ${Number(item.payment_amount || 0).toLocaleString('id-ID')}</td>
            <td>Urgent</td>
         <td class="finance-adjustment-column"
             data-original-amount="${Number(item.payment_amount || 0)}"
             data-recon-amount="${Number(item.payment_request_amount ?? item.payment_amount ?? 0)}">
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
                    let remainingAmount = grandTotal - totalPaymentRequest;
                    html += `
                        <tr class="print-total-row" style="font-weight:bold;background:#fff3cd;">
                            <td colspan="10" style="text-align:right;">TOTAL PAYMENT REQUEST</td>
                            <td style="text-align:right;">Rp ${totalPaymentRequest.toLocaleString('id-ID')}</td>
                            <td colspan="2"></td>
                        </tr>
                      
                        </tbody></table></div></div>
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
                    // NW tetap lengkap; NWS (termasuk NWD/NWR) tanpa Vivi.
                    const rowCode = String($('.draft-row.active-row').attr('data-production-code') || '')
                        .toUpperCase();
                    const itemSpk = (res.items || []).map(item => item.spk_no || item.no_spk || '').join(' ');
                    const spkMatch = itemSpk.match(/\/(NWS|NWD|NWR|NW)(?=\s|\/|-|$)/i);
                    const detailCode = String(res.production_code || rowCode || (spkMatch ? spkMatch[1] : ''))
                        .toUpperCase();
                    const isNwsGroup = ['NWS', 'NWD', 'NWR'].includes(detailCode);
                    const visibleApprovals = (res.approvals || []).filter(function(approval) {
                        return !(isNwsGroup && (Number(approval.user_id) === 171 || /\bvivi\b/i.test(
                            String(approval.name || '')) || /head purchasing/i.test(String(
                            approval.role || ''))));
                    });
                    visibleApprovals.forEach(function(approval) {
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

                    // Tutup row/table signature sebelum tombol finance.
                    html += `</tr></table></div>`;

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

                    html += `</div>`; // #printArea
                    $('#draftDetailArea').html(html);

                    setTimeout(function() {
                        const wrapper = $('.draft-wrapper')[0];
                        if (wrapper) {
                            $(wrapper).stop(true).animate({
                                scrollLeft: wrapper.clientWidth
                            }, 500, function() {
                                $(document).trigger('paymentDetailSlideReady', [String(
                                    draftId)]);
                            });
                        } else {
                            $(document).trigger('paymentDetailSlideReady', [String(draftId)]);
                        }
                    }, 200);
                }
            ).fail(function(xhr) {
                $(document).trigger('paymentDetailLoadFailed', [String(draftId), xhr]);
            });
        }
    );
    // Loading khusus saat membuka detail melalui ?request= / ?no_req=.
    // Tidak memengaruhi klik Detail biasa atau AJAX lain.
    function magicDetailLoading(show, message) {
        let el = document.getElementById('magic-detail-loading');
        if (!el && show) {
            el = document.createElement('div');
            el.id = 'magic-detail-loading';
            el.setAttribute('role', 'status');
            el.setAttribute('aria-live', 'polite');
            el.style.cssText =
                'position:fixed;inset:0;z-index:99999;background:rgba(248,250,252,.94);display:flex;align-items:center;justify-content:center;backdrop-filter:blur(3px);';
            el.innerHTML =
                '<div style="background:#fff;border:1px solid #e5e7eb;border-radius:14px;padding:30px 38px;text-align:center;box-shadow:0 12px 40px rgba(16,24,40,.10);font-family:Arial,sans-serif;min-width:270px;">' +
                '<div style="width:42px;height:42px;border:4px solid #e5e7eb;border-top-color:#2563eb;border-radius:50%;animation:magicDetailSpin .75s linear infinite;margin:0 auto 17px;"></div>' +
                '<div style="font-size:15px;font-weight:700;color:#1f2937;">Menyiapkan Payment Request</div>' +
                '<div id="magic-detail-loading-text" style="font-size:12px;color:#667085;margin-top:8px;">Memuat detail pengajuan...</div></div>';
            if (!document.getElementById('magic-detail-loading-style')) {
                const style = document.createElement('style');
                style.id = 'magic-detail-loading-style';
                style.textContent = '@keyframes magicDetailSpin { to { transform:rotate(360deg); } }';
                document.head.appendChild(style);
            }
            document.body.appendChild(el);
        }
        if (el) {
            if (show) {
                el.style.display = 'flex';
                const msg = document.getElementById('magic-detail-loading-text');
                if (msg && message) msg.textContent = message;
            } else {
                el.remove();
            }
        }
    }

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

        magicDetailLoading(true, 'Mencari draft ' + requestNo + '...');
        // Batas aman: overlay tidak akan menutup halaman selamanya.
        const loadingFallback = setTimeout(function() {
            $(document).off('.magicAuto');
            magicDetailLoading(false);
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'warning',
                    title: 'Detail belum siap',
                    text: 'Proses memuat terlalu lama. Silakan coba lagi melalui daftar Payment Request.'
                });
            }
        }, 15000);

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

                // Saved draft selalu dibuka di tab Payment Request.
                // Jangan klik Draft NW/NWS: tab tersebut khusus checklist yang belum disimpan.

                /*
                | Tandai row
                */
                $('.draft-row')
                    .removeClass('active-row');

                $target
                    .closest('tr')
                    .addClass('active-row');

                /*
                | Loading selesai setelah AJAX berhasil, HTML terpasang,
                | DAN animasi slider sudah selesai.
                */
                const draftId = String($target.attr('data-id'));
                const cleanup = function() {
                    $(document).off('paymentDetailSlideReady.magicAuto');
                    $(document).off('paymentDetailLoadFailed.magicAuto');
                    clearTimeout(loadingFallback);
                };
                $(document).on('paymentDetailSlideReady.magicAuto', function(event, readyId) {
                    if (String(readyId) !== draftId) return;
                    cleanup();
                    magicDetailLoading(false);
                });
                $(document).on('paymentDetailLoadFailed.magicAuto', function(event, failedId) {
                    if (String(failedId) !== draftId) return;
                    cleanup();
                    magicDetailLoading(false);
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Gagal memuat detail',
                            text: 'Silakan kembali ke daftar Payment Request dan coba lagi.'
                        });
                    } else {
                        alert('Gagal memuat detail. Silakan coba lagi.');
                    }
                });

                magicDetailLoading(true, 'Memuat data dan menyiapkan detail...');
                $target.trigger('click');

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
                clearTimeout(loadingFallback);
                magicDetailLoading(false);
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Draft tidak ditemukan',
                        text: 'Nomor draft pada link tidak tersedia di daftar Payment Request.'
                    });
                }

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
                                text: res.message ||
                                    'Gagal menambahkan payment ke Draft Finance.'
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
                            text: res.message ||
                                'Payment berhasil ditambahkan ke Draft Finance.',
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
                            text: xhr.responseJSON?.message ||
                                'Terjadi kesalahan saat menambahkan payment ke Draft Finance.'
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
    $(document).on('click', '#btn-print', function () {
        const original = document.getElementById('printArea');
        if (!original) return;

        const printWindow = window.open('', '_blank', 'width=1200,height=900');
        if (!printWindow) {
            if (typeof Swal !== 'undefined') Swal.fire('Pop-up diblokir', 'Izinkan pop-up untuk mencetak.', 'warning');
            return;
        }

        // Clone agar tombol, input, dan approval interaktif pada layar tidak berubah.
        const copy = original.cloneNode(true);
        copy.querySelectorAll('.no-print, .print-instructions, #btn-print, .btn-approve, .btn-add-finance-draft, .btn-add-draft-finance').forEach(el => el.remove());
        // CEO tidak dicetak, tetapi tetap ada pada halaman approval.
        copy.querySelectorAll('.signature-section td').forEach(td => {
            const label = td.querySelector('div')?.textContent?.trim() || '';
            const text = td.textContent || '';
            if (/^CEO$/i.test(label) || /\bCEO\b/i.test(text)) td.remove();
        });
        // Recon Fin hanya dicetak untuk baris dengan nominal recon berbeda.
        // Jika semua nominal sama, hilangkan kolom sepenuhnya.
        const reconCells = Array.from(copy.querySelectorAll('.payment-print-table tbody td.finance-adjustment-column'));
        const hasChangedRecon = reconCells.some(td => {
            const original = Number(td.dataset.originalAmount || 0);
            const input = td.querySelector('.finance-adjustment');
            const recon = Number(input ? input.value : td.dataset.reconAmount);
            return Number.isFinite(recon) && Math.abs(original - recon) > 0.005;
        });
        reconCells.forEach(td => {
            const original = Number(td.dataset.originalAmount || 0);
            const input = td.querySelector('.finance-adjustment');
            const recon = Number(input ? input.value : td.dataset.reconAmount);
            if (!hasChangedRecon || !Number.isFinite(recon) || Math.abs(original - recon) <= 0.005) {
                td.remove();
            } else {
                td.textContent = 'Rp ' + recon.toLocaleString('id-ID');
                td.classList.add('recon-changed');
            }
        });
        // Untuk recon sebagian baris, pertahankan sel kosong agar tabel tetap sejajar.
        if (hasChangedRecon) {
            const bodyRows = copy.querySelectorAll('.payment-print-table tbody tr:not(.print-total-row)');
            bodyRows.forEach(tr => {
                if (!tr.querySelector('td.finance-adjustment-column')) {
                    const empty = document.createElement('td');
                    empty.className = 'finance-adjustment-column';
                    tr.appendChild(empty);
                }
            });
        } else {
            copy.querySelectorAll('.payment-print-table th.finance-adjustment-column').forEach(th => th.remove());
        }
        const totalRow = copy.querySelector('.payment-print-table .print-total-row');
        if (totalRow) {
            const lastCell = totalRow.lastElementChild;
            if (lastCell) lastCell.colSpan = hasChangedRecon ? 2 : 1;
        }
        // Ubah semua input menjadi teks hanya pada salinan print.
        copy.querySelectorAll('input').forEach(input => {
            const span = document.createElement('span');
            span.textContent = input.value || '';
            input.replaceWith(span);
        });
        copy.querySelectorAll('a').forEach(link => {
            const span = document.createElement('span');
            span.textContent = link.textContent;
            link.replaceWith(span);
        });
        // Timestamp print tetap memakai waktu lokal browser.
        const stamp = new Date().toLocaleString('en-US', {
            month:'numeric', day:'numeric', year:'numeric',
            hour:'numeric', minute:'2-digit', hour12:true
        }) + ' || Printed on ERP';
        const header = document.createElement('div');
        header.className = 'erp-print-header';
        header.textContent = stamp;
        copy.prepend(header);

        printWindow.document.open();
        printWindow.document.write(`<!DOCTYPE html><html><head><meta charset="utf-8"><title>Payment Request</title>
        <style>
          @page { size:A4 landscape; margin:0; }
          * { box-sizing:border-box; }
          body { margin:0; padding:12mm 11mm; font-family:Arial,sans-serif; font-size:10px; color:#111; }
          .erp-print-header { text-align:right; font-size:9px; margin-bottom:8px; }
          #printArea, #printArea > div { width:100%; max-width:100%; }
          table { width:100%; border-collapse:collapse; }
          th,td { border:1px solid #333; padding:4px; vertical-align:middle; }
          .payment-print-table { table-layout:fixed; font-size:8px; }
          .payment-print-table th,.payment-print-table td { padding:4px 3px; font-size:8px; overflow-wrap:anywhere; word-break:normal; }
          .payment-print-table th:nth-child(1) {width:3%;}
          .payment-print-table th:nth-child(2) {width:10%;}
          .payment-print-table th:nth-child(3) {width:7%;}
          .payment-print-table th:nth-child(4) {width:10%;}
          .payment-print-table th:nth-child(5) {width:7%;}
          .payment-print-table th:nth-child(6) {width:16%;}
          .payment-print-table th:nth-child(7) {width:10%;}
          .payment-print-table th:nth-child(8) {width:4%;}
          .payment-print-table th:nth-child(9) {width:4%;}
          .payment-print-table th:nth-child(10) {width:8%;}
          .payment-print-table th:nth-child(11) {width:8%;}
          .payment-print-table th:nth-child(12) {width:6%;}
          .payment-print-table th:nth-child(13) {width:7%;}
          .payment-print-table .recon-changed {font-weight:700;}
          .payment-print-table td:nth-child(10),.payment-print-table td:nth-child(11) { text-align:right; }
          .print-total-row td {font-weight:bold;}
          .signature-section {margin-top:24px !important;break-inside:avoid;page-break-inside:avoid;}
          .signature-section table {table-layout:fixed;}
          .signature-section td {text-align:center;vertical-align:top;font-size:9px;}
          .signature-section img {max-width:90px;max-height:45px;object-fit:contain;}
          tr {break-inside:avoid;page-break-inside:avoid;}
          .no-print,.print-instructions,#btn-print,.btn-approve,.btn-add-finance-draft,.btn-add-draft-finance {display:none !important;}
          .card {border:0 !important;} .card-header {padding:5px 0 !important;}
          .card-body {padding:0 !important;} h5 {margin:4px 0 7px;}
        </style></head><body>${copy.outerHTML}</body></html>`);
        printWindow.document.close();
        // Tunggu gambar/logo agar tidak hilang pada print.
        const images = Array.from(printWindow.document.images);
        Promise.all(images.map(img => img.complete ? Promise.resolve() : new Promise(resolve => {
            img.onload = resolve; img.onerror = resolve;
        }))).then(() => {
            printWindow.focus();
            printWindow.print();
            printWindow.onafterprint = () => printWindow.close();
        });
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


                // Hanya filter tampilan pilihan approver untuk kelompok NWS.
                const magicCode = String(button.attr('data-production-code') || '').toUpperCase();
                if (['NWS', 'NWD', 'NWR'].includes(magicCode)) {
                    currentApprovals = currentApprovals.filter(function(approval) {
                        return Number(approval.user_id) !== 171 && !/\bvivi\b/i.test(String(approval
                            .name || approval.user_name || '')) && !/head purchasing/i.test(
                            String(approval.role || ''));
                    });
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


<script>
    /*
|--------------------------------------------------------------------------
| LOADING DOWNLOAD EXCEL 2-UP PER SUBKON
|--------------------------------------------------------------------------
| Menampilkan status saat server menyiapkan Excel dan persentase nyata
| ketika browser mulai menerima file.
*/
    (function() {
        if (window.__paymentRequestExcelLoadingBound) return;
        window.__paymentRequestExcelLoadingBound = true;

        function getFilename(disposition) {
            if (!disposition) return 'payment-request.xlsx';

            var utf8Match = disposition.match(/filename\*=UTF-8''([^;]+)/i);
            if (utf8Match && utf8Match[1]) {
                try {
                    return decodeURIComponent(utf8Match[1].replace(/["']/g, ''));
                } catch (e) {}
            }

            var match = disposition.match(/filename="?([^";]+)"?/i);
            return match && match[1] ? match[1] : 'payment-request.xlsx';
        }

        function showProgress(percent, message, indeterminate) {
            var bar = document.getElementById('excelDownloadProgressBar');
            var label = document.getElementById('excelDownloadProgressPercent');
            var status = document.getElementById('excelDownloadProgressStatus');

            if (bar) {
                bar.style.width = percent + '%';
                bar.setAttribute('aria-valuenow', percent);
                bar.classList.toggle('progress-bar-striped', !!indeterminate);
                bar.classList.toggle('progress-bar-animated', !!indeterminate);
            }
            if (label) label.textContent = indeterminate ? 'Memproses…' : percent + '%';
            if (status) status.textContent = message;
        }

        document.addEventListener('click', function(event) {
            var link = event.target.closest('a[href*="/export-excel-2up"]');
            if (!link) return;

            // Jangan mengambil alih link yang dibuka di tab baru atau dengan modifier.
            if (event.ctrlKey || event.metaKey || event.shiftKey || event.altKey ||
                (link.target && link.target !== '_self')) return;

            event.preventDefault();
            if (link.dataset.excelLoading === '1') return;

            link.dataset.excelLoading = '1';
            var originalHtml = link.innerHTML;
            var originalTitle = link.getAttribute('title');

            link.style.pointerEvents = 'none';
            link.style.opacity = '0.65';
            link.innerHTML = '<i class="fa fa-spinner fa-spin"></i>';
            link.setAttribute('aria-busy', 'true');

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Menyiapkan Excel',
                    html: '<div id="excelDownloadProgressStatus" style="margin-bottom:12px;">' +
                        'Server sedang membuat file Excel…</div>' +
                        '<div class="progress" style="height:18px;border-radius:9px;overflow:hidden;">' +
                        '<div id="excelDownloadProgressBar" class="progress-bar progress-bar-striped progress-bar-animated" ' +
                        'role="progressbar" style="width:3%;transition:width .2s;" aria-valuemin="0" aria-valuemax="100" aria-valuenow="3"></div>' +
                        '</div>' +
                        '<div id="excelDownloadProgressPercent" style="margin-top:8px;font-weight:600;">Memproses…</div>',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: function() {
                        Swal.showLoading();
                    }
                });
            }

            var xhr = new XMLHttpRequest();
            xhr.open('GET', link.href, true);
            xhr.responseType = 'blob';
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');

            // Progress download aktual; sebelum respons mulai, tampilkan status proses.
            xhr.onprogress = function(e) {
                if (e.lengthComputable && e.total > 0) {
                    var percent = Math.min(100, Math.round((e.loaded / e.total) * 100));
                    showProgress(percent, 'Mengunduh file Excel…', false);
                } else {
                    showProgress(3, 'Server sedang menyiapkan / mengirim file Excel…', true);
                }
            };

            xhr.onload = function() {
                var contentType = (xhr.getResponseHeader('Content-Type') || '').toLowerCase();

                if (xhr.status >= 200 && xhr.status < 300 &&
                    xhr.response && xhr.response.size > 0 &&
                    !contentType.includes('text/html') &&
                    !contentType.includes('application/json')) {

                    showProgress(100, 'Excel selesai. Menyiapkan unduhan…', false);

                    var filename = getFilename(xhr.getResponseHeader('Content-Disposition'));
                    var blobUrl = window.URL.createObjectURL(xhr.response);
                    var downloadLink = document.createElement('a');
                    downloadLink.href = blobUrl;
                    downloadLink.download = filename;
                    document.body.appendChild(downloadLink);
                    downloadLink.click();
                    downloadLink.remove();

                    setTimeout(function() {
                        window.URL.revokeObjectURL(blobUrl);
                    }, 30000);

                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Excel siap',
                            text: 'File berhasil dibuat dan unduhan sudah dimulai.',
                            timer: 1400,
                            showConfirmButton: false
                        });
                    }
                } else {
                    var message = 'Gagal mengunduh Excel. Silakan coba kembali.';
                    if (xhr.status === 401 || xhr.status === 403) {
                        message = 'Sesi atau izin akses tidak valid. Silakan login ulang.';
                    }
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Download gagal',
                            text: message
                        });
                    } else {
                        alert(message);
                    }
                }
            };

            xhr.onerror = function() {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Koneksi bermasalah',
                        text: 'File Excel tidak berhasil diunduh. Periksa koneksi lalu coba kembali.'
                    });
                }
            };

            xhr.onloadend = function() {
                link.dataset.excelLoading = '0';
                link.style.pointerEvents = '';
                link.style.opacity = '';
                link.innerHTML = originalHtml;
                link.removeAttribute('aria-busy');
                if (originalTitle) link.setAttribute('title', originalTitle);
            };

            xhr.send();
        });
    })();
</script>
