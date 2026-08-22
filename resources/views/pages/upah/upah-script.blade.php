<script>
$(document).ready(function() {


    let articleTimer = null;
    let pekerjaanTimer = null;

    // Mencegah satu transaksi terkirim lebih dari sekali
    let isSubmittingUpah = false;


    /*
    |--------------------------------------------------------------------------
    | OPEN MODAL
    |--------------------------------------------------------------------------
    */

    $('#btnAddUpahTransaksi').on(
        'click',
        function() {

            $('#formInsertUpah')[0].reset();

            showNormalUpah();


            $('#insert_tanggal')
                .val(
                    '{{ date('Y-m-d') }}'
                );


            $('#insert_qty')
                .val(1);


            $('#insert_harga')
                .val(0);

            $('#insert_pekerjaan')
                .removeClass('d-none')
                .val('');

            $('#insert_pekerjaan_new')
                .addClass('d-none')
                .val('');


            $('#insert_total')
                .val(0);


            $('#articleSearchResult')
                .empty()
                .removeClass('show');


            $('#articleNotFound')
                .removeClass('show');


            $('#formUpahError')
                .addClass('d-none')
                .empty();


            $('#modalInsertUpah')
                .modal('show');


            setTimeout(function() {

                $('#insert_article')
                    .focus();

            }, 300);

        }
    );


    /*
    |--------------------------------------------------------------------------
    | ARTICLE SEARCH
    |--------------------------------------------------------------------------
    */

    $('#insert_article').on(
        'input',
        function() {

            const input =
                $(this);

            const keyword =
                input.val().trim();


            clearTimeout(articleTimer);


            $('#articleSearchResult')
                .empty()
                .removeClass('show');


            $('#articleNotFound')
                .removeClass('show');


            /*
            |--------------------------------------------------------------------------
            | USER GANTI ARTICLE
            |--------------------------------------------------------------------------
            */

            $('#insert_description')
                .val('');

            $('#insert_pekerjaan')
                .removeClass('d-none')
                .val('');

            $('#insert_pekerjaan_new')
                .addClass('d-none')
                .val('');

            $('#insert_harga').val(0);

            $('#articleSearchResult')
                .empty()
                .removeClass('show');

            $('#articleNotFound')
                .removeClass('show');

            calculateTotal();

            if (keyword.length < 2) {

                return;

            }


            articleTimer =
                setTimeout(function() {

                    $.ajax({

                        url: "{{ route('upah.transaksi.search.article') }}",

                        type: 'GET',

                        data: {
                            q: keyword
                        },


                        success: function(response) {

                            if (
                                !Array.isArray(response)
                            ) {

                                response = [];

                            }


                            if (
                                response.length === 0
                            ) {

                                $('#articleNotFound')
                                    .addClass('show');

                                return;

                            }


                            let html = '';


                            response.forEach(
                                function(item) {

                                    html += `

                                    <div
                                        class="article-result-item"
                                        data-article="${escapeHtml(item.article)}"
                                        data-description="${escapeHtml(item.description || '')}"
                                        data-harga="${item.harga || 0}"
                                        data-jenis="${escapeHtml(item.jenis || '')}"
                                        data-exists="${
                                            (item.exists_in_upah === true ||
                                                item.exists_in_upah === 1 ||
                                                item.exists_in_upah === '1' ||
                                                (item.jenis !== null && item.jenis !== undefined && String(item.jenis).trim() !== '') ||
                                                (item.harga !== null && item.harga !== undefined))
                                                ? '1'
                                                : '0'
                                        }"
                                    >

                                        <div class="article-result-code">

                                            ${escapeHtml(item.article)}

                                        </div>

                                        <div class="article-result-description">

                                            ${escapeHtml(item.description || '-')}

                                        </div>

                                        <div class="article-result-type">

                                            ${escapeHtml(item.jenis || '-')}

                                        </div>

                                    </div>

                                `;

                                }
                            );


                            $('#articleSearchResult')
                                .html(html)
                                .addClass('show');

                        },


                        error: function(xhr) {

                            console.error(
                                'Search article error:',
                                xhr
                            );

                        }

                    });

                }, 300);

        }
    );

    function setManualPekerjaanMode(article) {

        $('#modalInsertUpah .modal-title').html(`
            <i class="fas fa-plus-circle mr-1"></i>
            Anda sedang menambahkan upah <strong>${escapeHtml(article || '')}</strong>
            <small class="d-block text-danger mt-1">
                Pekerjaan belum tersedia untuk article ini — silakan isi jenis pekerjaan dan harga baru.
            </small>
        `);

        $('#insert_pekerjaan')
            .addClass('d-none')
            .val('');

        $('#insert_pekerjaan_new')
            .removeClass('d-none')
            .val('')
            .focus();

        $('#insert_harga')
            .val(0)
            .prop('readonly', false);

        if (article) {
            loadPoByArticle(
                article,
                $('#insert_description').val().trim()
            );
        }

        calculateTotal();
    }


    function loadPekerjaanByArticle(article) {

        const select = $('#insert_pekerjaan');

        select.empty().append(
            '<option value="">Memuat pekerjaan...</option>'
        );

        $('#insert_harga').val(0);
        calculateTotal();

        if (!article) {
            select.empty().append(
                '<option value="">Pilih pekerjaan...</option>'
            );
            return;
        }

        $.ajax({
            url: "{{ route('upah.transaksi.search.pekerjaan') }}",
            type: 'GET',

            data: {
                article: article,
                q: ''
            },

            success: function(response) {

                if (!Array.isArray(response)) {
                    response = [];
                }

                select.empty();

                if (!response.length) {

                    /*
                    |--------------------------------------------------------------------------
                    | ARTICLE ADA, TETAPI BELUM MEMILIKI MASTER UPAH
                    |--------------------------------------------------------------------------
                    */
                    setManualPekerjaanMode(article);

                    return;
                }

                select.append(
                    '<option value="">Pilih pekerjaan...</option>'
                );

                response.forEach(function(item) {

                    const jenis = item.jenis || '';
                    const harga = parseFloat(item.harga) || 0;

                    select.append(
                        $('<option>', {
                            value: jenis,
                            text: jenis + ' — Rp ' + formatRupiah(harga)
                        }).attr(
                            'data-harga',
                            harga
                        )
                    );

                });

                $('#insert_harga').val(0);
                calculateTotal();
            },

            error: function(xhr) {

                console.error(
                    'Load pekerjaan error:',
                    xhr
                );

                select.empty().append(
                    '<option value="">Gagal memuat pekerjaan</option>'
                );

                $('#insert_harga').val(0);
                calculateTotal();
            }
        });
    }
    $(document).on(
        'change',
        '#insert_pekerjaan',
        function() {

            const selected =
                $(this).find('option:selected');

            const harga =
                parseFloat(
                    selected.attr('data-harga')
                ) || 0;

            $('#insert_harga')
                .val(harga);

            calculateTotal();
        }
    );
    /*
            |--------------------------------------------------------------------------
            | SELECT ARTICLE
            |--------------------------------------------------------------------------
            */

    $(document).on(
        'click',
        '#articleSearchResult .article-result-item',
        function() {

            const item = $(this);

            const article =
                item.attr('data-article') || '';

            const description =
                item.attr('data-description') || '';

            const exists =
                item.attr('data-exists') === '1';

            /*
            |--------------------------------------------------------------------------
            | SIMPAN STATUS ARTICLE
            |--------------------------------------------------------------------------
            */

            $('#insert_article')
                .data('exists-in-upah', exists);

            /*
            |--------------------------------------------------------------------------
            | ISI ARTICLE
            |--------------------------------------------------------------------------
            */

            $('#insert_article')
                .val(article);

            $('#insert_description')
                .val(description);

            /*
            |--------------------------------------------------------------------------
            | TUTUP SEARCH
            |--------------------------------------------------------------------------
            */

            $('#articleSearchResult')
                .empty()
                .removeClass('show');

            /*
            |--------------------------------------------------------------------------
            | ARTICLE BELUM ADA
            |--------------------------------------------------------------------------
            */

            if (!exists) {
                setManualPekerjaanMode(article);
                return;
            }

            /*
            |--------------------------------------------------------------------------
            | ARTICLE SUDAH ADA
            |--------------------------------------------------------------------------
            */

            $('#modalInsertUpah .modal-title')
                .html(`
            <i class="fas fa-money-bill-wave mr-1"></i>
            Tambah Transaksi Upah
        `);

            /*
            | Article SUDAH ADA
            | Kembalikan pekerjaan ke dropdown master.
            */
            $('#insert_pekerjaan_new')
                .addClass('d-none')
                .val('');

            $('#insert_pekerjaan')
                .removeClass('d-none');

            /*
            | Load pekerjaan existing
            */
            loadPekerjaanByArticle(article);

            /*
            | Load No PO untuk article yang dipilih.
            */
            loadPoByArticle(
                article,
                $('#insert_description').val().trim()
            );

            $('#insert_harga')
                .val(0);

            calculateTotal();
        }
    );
    /*
    |--------------------------------------------------------------------------
    | PEKERJAAN SEARCH
    |--------------------------------------------------------------------------
    | Mencari jenis pekerjaan berdasarkan ARTICLE yang sudah dipilih.
    */

    $('#insert_pekerjaan').on(
        'input',
        function() {

            const input = $(this);

            const keyword =
                input.val().trim();

            const article =
                $('#insert_article')
                .val()
                .trim();


            clearTimeout(pekerjaanTimer);


            $('#pekerjaanSearchResult')
                .empty()
                .removeClass('show');


            /*
            |--------------------------------------------------------------------------
            | ARTICLE WAJIB SUDAH DIPILIH
            |--------------------------------------------------------------------------
            */

            if (!article) {

                return;

            }


            /*
            |--------------------------------------------------------------------------
            | SEARCH MINIMAL 1 KARAKTER
            |--------------------------------------------------------------------------
            */

            if (keyword.length < 1) {

                return;

            }


            pekerjaanTimer = setTimeout(
                function() {

                    $.ajax({

                        url: "{{ route('upah.transaksi.search.pekerjaan') }}",

                        type: 'GET',

                        data: {
                            article: article,
                            q: keyword
                        },


                        success: function(response) {

                            if (
                                !Array.isArray(response)
                            ) {

                                response = [];

                            }


                            if (
                                response.length === 0
                            ) {

                                $('#pekerjaanSearchResult')
                                    .html(`
                                    <div class="article-result-item">

                                        <div class="article-result-description">

                                            Jenis pekerjaan
                                            tidak ditemukan
                                            untuk article ini.

                                        </div>

                                    </div>
                                `)
                                    .addClass('show');

                                return;

                            }


                            let html = '';


                            response.forEach(
                                function(item) {

                                    html += `

                                    <div
                                        class="article-result-item pekerjaan-result-item"
                                        data-jenis="${escapeHtml(item.jenis || '')}"
                                        data-harga="${item.harga || 0}"
                                    >

                                        <div class="article-result-code">

                                            ${escapeHtml(item.jenis || '')}

                                        </div>

                                        <div class="article-result-description">

                                            Rp ${formatRupiah(item.harga || 0)}

                                        </div>

                                    </div>

                                `;

                                }
                            );


                            $('#pekerjaanSearchResult')
                                .html(html)
                                .addClass('show');

                        },


                        error: function(xhr) {

                            console.error(
                                'Search pekerjaan error:',
                                xhr
                            );

                        }

                    });

                },
                300
            );

        }
    );


    /*
    |--------------------------------------------------------------------------
    | SELECT PEKERJAAN
    |--------------------------------------------------------------------------
    */

    $(document).on(
        'click',
        '.pekerjaan-result-item',
        function() {

            const item = $(this);


            const jenis =
                item.attr('data-jenis') || '';


            const harga =
                parseFloat(
                    item.attr('data-harga')
                ) || 0;


            $('#insert_pekerjaan')
                .val(jenis);


            $('#insert_harga')
                .val(harga);


            $('#pekerjaanSearchResult')
                .empty()
                .removeClass('show');


            calculateTotal();

        }
    );


    /*
    |--------------------------------------------------------------------------
    | FORMAT RUPIAH
    |--------------------------------------------------------------------------
    */

    function formatRupiah(value) {

        return new Intl.NumberFormat(
            'id-ID'
        ).format(
            parseFloat(value) || 0
        );

    }


    /*
    |--------------------------------------------------------------------------
    | TOTAL
    |--------------------------------------------------------------------------
    */

    function calculateTotal() {

        const qty =
            parseFloat(
                $('#insert_qty').val()
            ) || 0;


        const harga =
            parseFloat(
                $('#insert_harga').val()
            ) || 0;


        const total =
            qty * harga;


        $('#insert_total')
            .val(
                new Intl.NumberFormat(
                    'id-ID'
                ).format(total)
            );

    }


    $('#insert_qty, #insert_harga')
        .on(
            'input',
            calculateTotal
        );


    /*
    |--------------------------------------------------------------------------
    | SUBMIT
    |--------------------------------------------------------------------------
    */

    $('#formInsertUpah')
        .off('submit.upahTransaction')
        .on(
            'submit.upahTransaction',
            function(e) {

            e.preventDefault();

            // Guard kedua: walaupun tombol diklik berkali-kali,
            // hanya request pertama yang boleh berjalan.
            if (isSubmittingUpah) {
                return;
            }

            isSubmittingUpah = true;

            const button =
                $('#btnSaveUpahTransaksi');


            button
                .prop('disabled', true)
                .html(
                    '<i class="fas fa-spinner fa-spin mr-1"></i>' +
                    ' Menyimpan...'
                );


            /*
            |--------------------------------------------------------------------------
            | TOTAL RAW
            |--------------------------------------------------------------------------
            */

            const qty =
                parseFloat(
                    $('#insert_qty').val()
                ) || 0;


            const harga =
                parseFloat(
                    $('#insert_harga').val()
                ) || 0;


            const total =
                qty * harga;

            /*
            | Pekerjaan berasal dari dropdown jika article existing,
            | atau dari input manual jika article NOT YET.
            */
            const pekerjaan =
                $('#insert_pekerjaan_new').hasClass('d-none')
                    ? $('#insert_pekerjaan').val()
                    : $('#insert_pekerjaan_new').val().trim();


            const formData = {

                _token: "{{ csrf_token() }}",

                article: $('#insert_article').val(),

                description: $('#insert_description').val(),

                tanggal: $('#insert_tanggal').val(),

                pekerjaan: pekerjaan,
                create_master_upah: !$('#insert_pekerjaan_new').hasClass('d-none') ? 1 : 0,

                person: $('#insert_person').val(),

                qty: qty,

                harga: harga,

                total: total,

                no_po: $('#insert_no_po').val(),

                no_spk: $('#insert_no_spk').val()

            };


            $.ajax({

                url: "{{ route('upah.transaksi.store') }}",

                type: 'POST',

                data: formData,


                success: function(response) {

                    if (
                        response.success
                    ) {

                        $('#modalInsertUpah')
                            .modal('hide');


                        location.reload();

                        return;

                    }


                    $('#formUpahError')
                        .removeClass('d-none')
                        .text(
                            response.message ||
                            'Gagal menyimpan data.'
                        );

                    isSubmittingUpah = false;

                },


                error: function(xhr) {

                    let message =
                        'Gagal menyimpan data.';


                    if (
                        xhr.responseJSON &&
                        xhr.responseJSON.errors
                    ) {

                        message =
                            Object.values(
                                xhr.responseJSON.errors
                            )
                            .flat()
                            .join('\n');

                    } else if (
                        xhr.responseJSON &&
                        xhr.responseJSON.message
                    ) {

                        message =
                            xhr.responseJSON.message;

                    }


                    $('#formUpahError')
                        .removeClass('d-none')
                        .text(message);

                    isSubmittingUpah = false;

                },


                complete: function() {

                    // Jika berhasil, halaman akan reload sehingga lock
                    // sengaja tidak dibuka kembali.
                    if (!isSubmittingUpah) {
                        button
                            .prop('disabled', false)
                            .html(
                                '<i class="fas fa-save mr-1"></i>' +
                                ' Simpan'
                            );
                    }

                }

            });

        }
    );



    /*
    |--------------------------------------------------------------------------
    | MASS INPUT
    |--------------------------------------------------------------------------
    */

    let massRowCounter = 0;
    let massArticleTimers = {};
    let massPekerjaanTimers = {};


    function createMassRow() {

        massRowCounter++;

        const rowId = massRowCounter;
        const today = '{{ date('Y-m-d') }}';

        $('#massUpahBodyRows').append(`
        <tr class="mass-upah-row" data-row="${rowId}">

            <td class="text-center mass-row-number">${rowId}</td>

            <td>
                <div class="mass-search-wrapper">
                    <input type="text"
                            class="form-control mass-article"
                            autocomplete="off"
                            placeholder="Article">

                    <div class="mass-search-result mass-article-result"></div>
                </div>
            </td>

            <td>
                <textarea class="form-control mass-description"
                            rows="1"
                            placeholder="Description"></textarea>
            </td>

            <td>
                <input type="date"
                        class="form-control mass-tanggal"
                        value="${today}">
            </td>

            <td>
                <div class="mass-search-wrapper">
                    <input type="text"
                            class="form-control mass-pekerjaan"
                            autocomplete="off"
                            placeholder="Pekerjaan">

                    <div class="mass-search-result mass-pekerjaan-result"></div>
                </div>
            </td>

            <td>
                <input type="text"
                        class="form-control mass-person"
                        placeholder="Person">
            </td>

            <td>
                <input type="number"
                        class="form-control mass-qty"
                        value="1"
                        min="0"
                        step="0.01">
            </td>

            <td>
                <input type="number"
                        class="form-control mass-harga"
                        value="0"
                        min="0"
                        step="0.01">
            </td>

            <td>
                <input type="text"
                        class="form-control mass-total mass-total-input"
                        value="0"
                        readonly>
            </td>

            <td>
                <input type="text"
                        class="form-control mass-no-po"
                        placeholder="No PO">
            </td>

            <td>
                <input type="text"
                        class="form-control mass-no-spk"
                        placeholder="No SPK">
            </td>

            <td class="text-center">
                <button type="button"
                        class="btn btn-sm btn-outline-danger btn-remove-mass-row"
                        title="Hapus row">
                    <i class="fas fa-times"></i>
                </button>
            </td>

        </tr>
    `);
    }


    function calculateMassRow(row) {

        const qty = parseFloat(row.find('.mass-qty').val()) || 0;
        const harga = parseFloat(row.find('.mass-harga').val()) || 0;

        row.find('.mass-total').val(
            new Intl.NumberFormat('id-ID').format(qty * harga)
        );
    }


    function renumberMassRows() {

        $('#massUpahBodyRows .mass-upah-row').each(function(index) {
            $(this).find('.mass-row-number').text(index + 1);
        });
    }


    function resetMassUpah() {

        massRowCounter = 0;
        massArticleTimers = {};
        massPekerjaanTimers = {};

        $('#massUpahBodyRows').empty();

        $('#massUpahError')
            .addClass('d-none')
            .empty();

        createMassRow();
    }


    function showMassUpah() {

        resetMassUpah();

        $('#modalInsertUpah').addClass('mass-mode');
        $('#normalUpahBody').addClass('d-none');
        $('#massUpahBody').removeClass('d-none');

        $('#btnSaveUpahTransaksi').addClass('d-none');
        $('#btnSaveMassUpah').removeClass('d-none');
        $('#btnToggleMassUpah').addClass('d-none');
    }


    function showNormalUpah() {

        $('#modalInsertUpah').removeClass('mass-mode');
        $('#massUpahBody').addClass('d-none');
        $('#normalUpahBody').removeClass('d-none');

        $('#btnSaveMassUpah').addClass('d-none');
        $('#btnSaveUpahTransaksi').removeClass('d-none');
        $('#btnToggleMassUpah').removeClass('d-none');
    }


    $('#btnToggleMassUpah').on('click', function() {
        showMassUpah();
    });


    $('#btnBackNormalUpah').on('click', function() {
        showNormalUpah();
    });


    $(document).on('click', '#btnAddMassRow', function() {
        createMassRow();
    });


    /*
    |--------------------------------------------------------------------------
    | MASS ARTICLE SEARCH
    |--------------------------------------------------------------------------
    */

    $(document).on('input', '.mass-article', function() {

        const input = $(this);
        const row = input.closest('.mass-upah-row');
        const rowId = row.data('row');
        const keyword = input.val().trim();

        clearTimeout(massArticleTimers[rowId]);

        row.find('.mass-article-result').empty().removeClass('show');
        row.find('.mass-description').val('');
        row.find('.mass-pekerjaan').val('');
        row.find('.mass-pekerjaan-result').empty().removeClass('show');
        row.find('.mass-harga').val(0);

        calculateMassRow(row);

        if (keyword.length < 2) return;

        massArticleTimers[rowId] = setTimeout(function() {

            $.ajax({
                url: "{{ route('upah.transaksi.search.article') }}",
                type: 'GET',
                data: {
                    q: keyword
                },

                success: function(response) {

                    if (!Array.isArray(response)) response = [];

                    if (!response.length) {
                        row.find('.mass-article-result')
                            .html(`
                            <div class="mass-search-item">
                                <div class="mass-search-desc">
                                    Article tidak ditemukan.
                                </div>
                            </div>
                        `)
                            .addClass('show');
                        return;
                    }

                    let html = '';

                    response.forEach(function(item) {

                        const isExisting =
                            item.exists_in_upah === true;

                        html += `
    <div
        class="article-result-item
            ${isExisting ? '' : 'article-not-in-db'}"

        data-article="${escapeHtml(item.article)}"

        data-description="${escapeHtml(
            item.description || ''
        )}"

        data-harga="${item.harga || 0}"

        data-jenis="${escapeHtml(
            item.jenis || ''
        )}"

        data-exists="${isExisting ? '1' : '0'}"
    >

        <div class="article-result-code">
            ${escapeHtml(item.article)}
        </div>

        <div class="article-result-description">
            ${escapeHtml(
                item.description || '-'
            )}
        </div>

        ${
            isExisting
                ? `
                    <div class="article-result-type">
                        ${escapeHtml(
                            item.jenis || '-'
                        )}
                    </div>
                `
                : `
                    <div class="article-result-type text-warning">
                        <i class="fas fa-exclamation-circle mr-1"></i>
                        NOT YET IN DATABASE
                    </div>
                `
        }

    </div>
`;
                    });
                    row.find('.mass-article-result')
                        .html(html)
                        .addClass('show');
                },

                error: function(xhr) {
                    console.error('Mass article search error:', xhr);
                }
            });

        }, 300);
    });


    $(document).on('click', '.mass-article-result-item', function(e) {

        e.stopPropagation();

        const item = $(this);
        const row = item.closest('.mass-upah-row');

        row.find('.mass-article')
            .val(item.attr('data-article') || '');

        row.find('.mass-description')
            .val(item.attr('data-description') || '');

        row.find('.mass-pekerjaan')
            .val('');

        row.find('.mass-pekerjaan-result')
            .empty()
            .removeClass('show');

        row.find('.mass-harga')
            .val(0);

        row.find('.mass-article-result')
            .empty()
            .removeClass('show');

        calculateMassRow(row);
    });


    /*
    |--------------------------------------------------------------------------
    | MASS PEKERJAAN SEARCH
    |--------------------------------------------------------------------------
    */

    $(document).on('input', '.mass-pekerjaan', function() {

        const input = $(this);
        const row = input.closest('.mass-upah-row');
        const rowId = row.data('row');
        const keyword = input.val().trim();

        const article =
            row.find('.mass-article').val().trim();

        clearTimeout(massPekerjaanTimers[rowId]);

        row.find('.mass-pekerjaan-result')
            .empty()
            .removeClass('show');

        if (!article || !keyword) return;

        massPekerjaanTimers[rowId] = setTimeout(function() {

            $.ajax({
                url: "{{ route('upah.transaksi.search.pekerjaan') }}",
                type: 'GET',
                data: {
                    article: article,
                    q: keyword
                },

                success: function(response) {

                    if (!Array.isArray(response)) response = [];

                    if (!response.length) {
                        row.find('.mass-pekerjaan-result')
                            .html(`
                            <div class="mass-search-item">
                                <div class="mass-search-desc">
                                    Jenis pekerjaan tidak ditemukan.
                                </div>
                            </div>
                        `)
                            .addClass('show');
                        return;
                    }

                    let html = '';

                    response.forEach(function(item) {
                        html += `
                        <div class="mass-search-item mass-pekerjaan-result-item"
                                data-jenis="${escapeHtml(item.jenis || '')}"
                                data-harga="${item.harga || 0}">
                            <div class="mass-search-code">
                                ${escapeHtml(item.jenis || '')}
                            </div>
                            <div class="mass-search-desc">
                                Rp ${formatRupiah(item.harga || 0)}
                            </div>
                        </div>
                    `;
                    });

                    row.find('.mass-pekerjaan-result')
                        .html(html)
                        .addClass('show');
                },

                error: function(xhr) {
                    console.error('Mass pekerjaan search error:', xhr);
                }
            });

        }, 300);
    });


    $(document).on('click', '.mass-pekerjaan-result-item', function(e) {

        e.stopPropagation();

        const item = $(this);
        const row = item.closest('.mass-upah-row');

        row.find('.mass-pekerjaan')
            .val(item.attr('data-jenis') || '');

        row.find('.mass-harga')
            .val(parseFloat(item.attr('data-harga')) || 0);

        row.find('.mass-pekerjaan-result')
            .empty()
            .removeClass('show');

        calculateMassRow(row);
    });


    $(document).on('input', '.mass-qty, .mass-harga', function() {
        calculateMassRow($(this).closest('.mass-upah-row'));
    });


    $(document).on('click', '.btn-remove-mass-row', function() {

        const rows = $('#massUpahBodyRows .mass-upah-row');

        if (rows.length <= 1) {
            rows.first().find('input, textarea').val('');
            rows.first().find('.mass-tanggal').val('{{ date('Y-m-d') }}');
            rows.first().find('.mass-qty').val(1);
            rows.first().find('.mass-harga').val(0);
            calculateMassRow(rows.first());
            return;
        }

        $(this).closest('.mass-upah-row').remove();
        renumberMassRows();
    });


    /*
    |--------------------------------------------------------------------------
    | MASS SAVE
    |--------------------------------------------------------------------------
    */

    $('#btnSaveMassUpah').on('click', function() {

        const button = $(this);
        const rows = [];
        let invalid = false;

        $('#massUpahBodyRows .mass-upah-row').each(function() {

            const row = $(this);

            const qty =
                parseFloat(row.find('.mass-qty').val()) || 0;

            const harga =
                parseFloat(row.find('.mass-harga').val()) || 0;

            const item = {
                article: row.find('.mass-article').val().trim(),
                description: row.find('.mass-description').val().trim(),
                tanggal: row.find('.mass-tanggal').val(),
                pekerjaan: row.find('.mass-pekerjaan').val().trim(),
                person: row.find('.mass-person').val().trim(),
                qty: qty,
                harga: harga,
                total: qty * harga,
                no_po: row.find('.mass-no-po').val().trim(),
                no_spk: row.find('.mass-no-spk').val().trim()
            };

            if (
                !item.article ||
                !item.tanggal ||
                !item.pekerjaan ||
                qty <= 0 ||
                harga < 0
            ) {
                invalid = true;
            }

            rows.push(item);
        });

        if (invalid) {
            $('#massUpahError')
                .removeClass('d-none')
                .text(
                    'Mohon lengkapi Article, Tanggal, Pekerjaan, Qty, dan Harga pada semua baris.'
                );
            return;
        }

        if (!rows.length) return;

        $('#massUpahError')
            .addClass('d-none')
            .empty();

        button
            .prop('disabled', true)
            .html(
                '<i class="fas fa-spinner fa-spin mr-1"></i> Menyimpan...'
            );

        $.ajax({

            url: "{{ route('upah.transaksi.mass.store') }}",
            type: 'POST',

            data: {
                _token: "{{ csrf_token() }}",
                rows: rows
            },

            success: function(response) {

                if (response.success) {
                    $('#modalInsertUpah').modal('hide');
                    location.reload();
                    return;
                }

                $('#massUpahError')
                    .removeClass('d-none')
                    .text(
                        response.message ||
                        'Gagal menyimpan data.'
                    );
            },

            error: function(xhr) {

                let message = 'Gagal menyimpan data mass.';

                if (
                    xhr.responseJSON &&
                    xhr.responseJSON.errors
                ) {
                    message =
                        Object.values(xhr.responseJSON.errors)
                        .flat()
                        .join('\n');
                } else if (
                    xhr.responseJSON &&
                    xhr.responseJSON.message
                ) {
                    message = xhr.responseJSON.message;
                }

                $('#massUpahError')
                    .removeClass('d-none')
                    .text(message);
            },

            complete: function() {
                button
                    .prop('disabled', false)
                    .html(
                        '<i class="fas fa-save mr-1"></i> Simpan Semua'
                    );
            }
        });
    });


    /*
    |--------------------------------------------------------------------------
    | RESET MASS WHEN MODAL CLOSES
    |--------------------------------------------------------------------------
    */

    $('#modalInsertUpah').on('hidden.bs.modal', function() {
        showNormalUpah();
    });


    /*
    |--------------------------------------------------------------------------
    | CLOSE SEARCH
    |--------------------------------------------------------------------------
    */

    $(document).on(
        'click',
        function(e) {

            if (
                !$(e.target)
                .closest('.article-search-wrapper')
                .length
            ) {

                $('#articleSearchResult')
                    .empty()
                    .removeClass('show');

                $('#pekerjaanSearchResult')
                    .empty()
                    .removeClass('show');

            }

        }
    );


    /*
    |--------------------------------------------------------------------------
    | ESCAPE HTML
    |--------------------------------------------------------------------------
    */

    function escapeHtml(value) {

        if (
            value === null ||
            value === undefined
        ) {

            return '';

        }


        return String(value)

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
/*
|--------------------------------------------------------------------------
| FILTER + GROUP TABLE
|--------------------------------------------------------------------------
| Group berdasarkan:
| ARTICLE + PEKERJAAN
|
| Qty  = SUM
| Total = SUM
|--------------------------------------------------------------------------
*/


function filterUpahTable() {

    const dateFrom =
        $('#filterDateFrom').val() || '';

    const dateTo =
        $('#filterDateTo').val() || '';

    const keyword =
        $('#searchUpahTable')
            .val()
            .trim()
            .toLowerCase();

    const rows =
        $('#upahTable tbody tr.upah-data-row');

    let visibleCount = 0;


    rows.each(function () {

        const tr = $(this);


        /*
        |--------------------------------------------------------------------------
        | SEARCH FULL TR
        |--------------------------------------------------------------------------
        */

        const rowText =
            tr.text()
                .replace(/\s+/g, ' ')
                .trim()
                .toLowerCase();

        const matchSearch =
            !keyword ||
            rowText.includes(keyword);


        /*
        |--------------------------------------------------------------------------
        | TANGGAL
        |--------------------------------------------------------------------------
        |
        | Struktur table:
        |
        | 0 = NO
        | 1 = AKSI
        | 2 = ARTICLE
        | 3 = DESCRIPTION
        | 4 = TANGGAL
        | 5 = PEKERJAAN
        | 6 = PERSON
        | 7 = QTY
        | 8 = HARGA
        | 9 = TOTAL
        | 10 = NO PO
        | 11 = NO SPK
        |
        */

        let matchDate = true;

        const tanggalText =
            tr.find('td')
                .eq(4)
                .text()
                .trim();


        if (dateFrom || dateTo) {

            if (!tanggalText) {

                matchDate = false;

            } else {

                const parts =
                    tanggalText.split('/');


                if (parts.length === 3) {

                    const rowDate =
                        parts[2] + '-' +
                        parts[1].padStart(2, '0') + '-' +
                        parts[0].padStart(2, '0');


                    if (
                        dateFrom &&
                        rowDate < dateFrom
                    ) {
                        matchDate = false;
                    }


                    if (
                        dateTo &&
                        rowDate > dateTo
                    ) {
                        matchDate = false;
                    }

                } else {

                    matchDate = false;

                }
            }
        }


        /*
        |--------------------------------------------------------------------------
        | SHOW / HIDE
        |--------------------------------------------------------------------------
        */

        if (
            matchSearch &&
            matchDate
        ) {

            tr.show();

            visibleCount++;

            tr.find('td')
                .eq(0)
                .text(visibleCount);

        } else {

            tr.hide();

        }

    });


    updateUpahFilterInfo();

}
/*
|--------------------------------------------------------------------------
| FORMAT QTY
|--------------------------------------------------------------------------
*/

function formatQty(value) {

    if (
        Number.isInteger(value)
    ) {

        return value.toString();

    }

    return value.toLocaleString(
        'id-ID', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 2
        }
    );

}


/*
|--------------------------------------------------------------------------
| FORMAT NUMBER
|--------------------------------------------------------------------------
*/

function formatNumber(value) {

    return new Intl.NumberFormat(
        'id-ID', {
            maximumFractionDigits: 0
        }
    ).format(value);

}


/*
|--------------------------------------------------------------------------
| INFO DI BAWAH TABLE
|--------------------------------------------------------------------------
*/

function updateUpahGroupInfo(
    filteredRows,
    groups
) {

    let totalRows = filteredRows.length;

    let duplicateRows = 0;

    let groupCount = 0;

    Object.keys(groups).forEach(function(key) {

        const count =
            groups[key].rows.length;

        if (count > 1) {

            duplicateRows += count;

        }

        groupCount++;

    });


    /*
    |--------------------------------------------------------------------------
    | Buat info di bawah table
    |--------------------------------------------------------------------------
    */

    let info = $('#upahGroupInfo');

    if (!info.length) {

        $('#upahTable')
            .closest('.upah-table-wrapper')
            .after(`
            <div
                id="upahGroupInfo"
                class="mt-2 px-2"
                style="font-size:12px;"
            ></div>
        `);

        info = $('#upahGroupInfo');

    }


    /*
    |--------------------------------------------------------------------------
    | Tidak ada filter
    |--------------------------------------------------------------------------
    */

    const hasFilter =
        $('#filterDateFrom').val() ||
        $('#filterDateTo').val() ||
        $('#searchUpahTable').val().trim();


    if (!hasFilter) {

        info
            .html('')
            .hide();

        return;

    }


    /*
    |--------------------------------------------------------------------------
    | DATA DUPLIKAT
    |--------------------------------------------------------------------------
    */

    if (duplicateRows > 1) {

        info
            .html(
                `<span class="text-muted">
                <i class="fas fa-layer-group mr-1"></i>
                ${duplicateRows} baris data yang sama
            </span>`
            )
            .show();

    } else {

        info
            .html('')
            .hide();

    }

}
/*
|--------------------------------------------------------------------------
| DATE FILTER
|--------------------------------------------------------------------------
*/

$('#filterDateFrom, #filterDateTo').on(
    'change',
    function() {

        filterUpahTable();

    }
);


/*
|--------------------------------------------------------------------------
| SEARCH BY TR
|--------------------------------------------------------------------------
*/

$('#searchUpahTable').on(
    'input',
    function() {

        const value =
            $(this).val().trim();

        $('#clearSearchUpah').toggle(
            value.length > 0
        );

        filterUpahTable();

    }
);


/*
|--------------------------------------------------------------------------
| CLEAR SEARCH
|--------------------------------------------------------------------------
*/

$('#clearSearchUpah').on(
    'click',
    function() {

        $('#searchUpahTable')
            .val('')
            .focus();

        $(this).hide();

        filterUpahTable();

    }
);


/*
|--------------------------------------------------------------------------
| RESET DATE
|--------------------------------------------------------------------------
*/

$('#btnResetDate').on(
    'click',
    function() {

        $('#filterDateFrom').val('');
        $('#filterDateTo').val('');

        filterUpahTable();

    }
);


/*
|--------------------------------------------------------------------------
| VALIDASI RANGE TANGGAL
|--------------------------------------------------------------------------
*/

$('#filterDateFrom, #filterDateTo').on(
    'change',
    function() {

        const dateFrom =
            $('#filterDateFrom').val();

        const dateTo =
            $('#filterDateTo').val();

        if (
            dateFrom &&
            dateTo &&
            dateFrom > dateTo
        ) {

            $('#upahAlert')
                .removeClass('d-none alert-success')
                .addClass('alert-warning')
                .html(
                    '<i class="fas fa-exclamation-triangle mr-1"></i>' +
                    'Tanggal Dari tidak boleh lebih besar dari Tanggal Sampai.'
                );

            return;

        }

        $('#upahAlert')
            .addClass('d-none')
            .removeClass('alert-warning alert-success')
            .empty();

        filterUpahTable();

    }
);


/*
|--------------------------------------------------------------------------
| FILTER INFO
|--------------------------------------------------------------------------
*/

function updateUpahFilterInfo() {

    const total =
        $('#upahTable tbody tr.upah-data-row').length;

    const visible =
        $('#upahTable tbody tr.upah-data-row:visible').length;

    const hasFilter =
        $('#filterDateFrom').val() ||
        $('#filterDateTo').val() ||
        $('#searchUpahTable').val().trim();

    if (hasFilter) {

        $('#upahAlert')
            .removeClass('d-none alert-warning')
            .addClass('alert-info')
            .html(
                '<i class="fas fa-filter mr-1"></i>' +
                'Menampilkan <strong>' +
                visible +
                '</strong> dari <strong>' +
                total +
                '</strong> transaksi.'
            );

    } else {

        $('#upahAlert')
            .addClass('d-none')
            .removeClass('alert-info')
            .empty();

    }

}

$('#btnExportUpah').off('click').on('click', async function(e) {

    e.preventDefault();

    const button = $(this);

    /*
    |--------------------------------------------------------------------------
    | AMBIL FILTER
    |--------------------------------------------------------------------------
    */

    const dateFrom =
        $('#filterDateFrom').val() || '';

    const dateTo =
        $('#filterDateTo').val() || '';

    const search =
        $('#searchUpahTable').val().trim() || '';


    /*
    |--------------------------------------------------------------------------
    | VALIDASI TANGGAL
    |--------------------------------------------------------------------------
    */

    if (
        dateFrom &&
        dateTo &&
        dateFrom > dateTo
    ) {

        alert(
            'Tanggal Dari tidak boleh lebih besar dari Tanggal Sampai.'
        );

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | LOADING
    |--------------------------------------------------------------------------
    */

    const originalHtml =
        button.html();

    button
        .prop('disabled', true)
        .html(
            '<i class="fas fa-spinner fa-spin mr-1"></i> Exporting...'
        );


    try {

        /*
        |--------------------------------------------------------------------------
        | URL
        |--------------------------------------------------------------------------
        */

        const url =
            new URL(
                "{{ route('upah.upah.transaksi.export') }}",
                window.location.origin
            );


        /*
        |--------------------------------------------------------------------------
        | PARAMETER
        |--------------------------------------------------------------------------
        */

        if (dateFrom) {

            url.searchParams.set(
                'date_from',
                dateFrom
            );

        }

        if (dateTo) {

            url.searchParams.set(
                'date_to',
                dateTo
            );

        }

        if (search) {

            url.searchParams.set(
                'search',
                search
            );

        }


        /*
        |--------------------------------------------------------------------------
        | FETCH
        |--------------------------------------------------------------------------
        */

        const response =
            await fetch(
                url.toString(), {
                    method: 'GET',

                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',

                        'Accept': 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                    },

                    credentials: 'same-origin'
                }
            );


        /*
        |--------------------------------------------------------------------------
        | JIKA ERROR
        |--------------------------------------------------------------------------
        */

        if (!response.ok) {

            let message =
                'Gagal melakukan export Excel.';


            const contentType =
                response.headers.get(
                    'content-type'
                ) || '';


            /*
            |--------------------------------------------------------------------------
            | ERROR JSON
            |--------------------------------------------------------------------------
            */

            if (
                contentType.includes(
                    'application/json'
                )
            ) {

                try {

                    const json =
                        await response.json();

                    message =
                        json.message ||
                        message;

                } catch (e) {
                    // ignore
                }

            } else {

                /*
                |--------------------------------------------------------------------------
                | ERROR TEXT
                |--------------------------------------------------------------------------
                */

                try {

                    const text =
                        await response.text();

                    if (text) {

                        console.error(
                            'Export server response:',
                            text
                        );

                    }

                } catch (e) {
                    // ignore
                }

            }


            throw new Error(message);
        }


        /*
        |--------------------------------------------------------------------------
        | AMBIL BLOB
        |--------------------------------------------------------------------------
        */

        const blob =
            await response.blob();


        /*
        |--------------------------------------------------------------------------
        | FILE NAME
        |--------------------------------------------------------------------------
        */

        let filename =
            'Rekap_Upah.xlsx';


        const disposition =
            response.headers.get(
                'Content-Disposition'
            );


        if (disposition) {

            const match =
                disposition.match(
                    /filename\*=UTF-8''([^;]+)|filename="?([^"]+)"?/i
                );


            if (match) {

                filename =
                    decodeURIComponent(
                        match[1] ||
                        match[2]
                    );

            }

        }


        /*
        |--------------------------------------------------------------------------
        | DOWNLOAD
        |--------------------------------------------------------------------------
        */

        const blobUrl =
            window.URL.createObjectURL(
                blob
            );


        const link =
            document.createElement('a');


        link.href =
            blobUrl;

        link.download =
            filename;


        document.body.appendChild(
            link
        );


        link.click();


        link.remove();


        /*
        |--------------------------------------------------------------------------
        | CLEAN
        |--------------------------------------------------------------------------
        */

        setTimeout(function() {

            window.URL.revokeObjectURL(
                blobUrl
            );

        }, 1000);


    } catch (error) {

        console.error(
            'EXPORT UPAH ERROR:',
            error
        );


        alert(
            error.message ||
            'Gagal melakukan export Excel.'
        );


    } finally {

        button
            .prop('disabled', false)
            .html(
                originalHtml
            );

    }

});
function loadPoByArticle(article, description = '') {

const select = $('#insert_no_po');

select.empty();

select.append(
    '<option value="">Memuat No PO...</option>'
);

if (!article) {

    select.empty().append(
        '<option value="">Pilih No PO...</option>'
    );

    return;
}

$.ajax({

    url: "{{ route('upah.transaksi.search.po') }}",

    type: 'GET',

    data: {
        article: article,
        description: description
    },

    success: function(response) {

        if (!Array.isArray(response)) {
            response = [];
        }

        select.empty();

        /*
        |--------------------------------------------------------------------------
        | TIDAK ADA PO
        |--------------------------------------------------------------------------
        */

        if (!response.length) {

            select.append(
                '<option value="">Tidak ada PO terkait</option>'
            );

            return;
        }

        /*
        |--------------------------------------------------------------------------
        | DEFAULT
        |--------------------------------------------------------------------------
        */

        select.append(
            '<option value="">Pilih No PO...</option>'
        );

        /*
        |--------------------------------------------------------------------------
        | LIST PO
        |--------------------------------------------------------------------------
        */

        response.forEach(function(item) {

            if (!item.no_po) {
                return;
            }

            select.append(
                $('<option>', {
                    value: item.no_po,
                    text: item.no_po
                })
            );

        });

    },

    error: function(xhr) {

        console.error(
            'Load PO error:',
            xhr
        );

        select.empty().append(
            '<option value="">Gagal memuat No PO</option>'
        );
    }

});
}
$(document).on('click', '.btn-delete-upah', function () {

    const button = $(this);

    const id = button.data('id');
    const article = button.data('article') || '';
    const description = button.data('description') || '';

    Swal.fire({

        title: 'Hapus transaksi?',

        icon: 'warning',

        html: `
            <div style="
                font-size:13px;
                line-height:1.6;
            ">
                <div style="
                    padding:10px;
                    background:#f8fafc;
                    border-radius:6px;
                    text-align:left;
                    margin-top:8px;
                ">
                    <strong>${$('<div>').text(article).html()}</strong>

                    <br>

                    <span style="color:#667085;">
                        ${$('<div>').text(description).html()}
                    </span>
                </div>

                <div style="
                    margin-top:10px;
                    color:#dc2626;
                    font-size:12px;
                ">
                    Data yang sudah dihapus tidak dapat dikembalikan.
                </div>
            </div>
        `,

        showCancelButton: true,

        confirmButtonText: 'Ya, Hapus',

        cancelButtonText: 'Batal',

        reverseButtons: true,

        confirmButtonColor: '#dc2626',

        cancelButtonColor: '#6b7280',

        focusCancel: true

    }).then(function (result) {

        if (!result.isConfirmed) {
            return;
        }

        deleteUpah(id, button);

    });

});
function deleteUpah(id, button) {

    const originalHtml = button.html();

    button.prop('disabled', true);

    button.html(
        '<i class="fas fa-spinner fa-spin"></i>'
    );

    $.ajax({

        url: "{{ url('/upah/transaksi') }}/" + id,

        type: 'DELETE',

        data: {
            _token: "{{ csrf_token() }}"
        },

        success: function (res) {

            if (!res.success) {

                button.prop('disabled', false);
                button.html(originalHtml);

                Swal.fire({
                    icon: 'error',
                    title: 'Gagal',
                    text: res.message || 'Data gagal dihapus.'
                });

                return;
            }

            Swal.fire({

                icon: 'success',

                title: 'Berhasil',

                text: res.message || 'Transaksi berhasil dihapus.',

                timer: 1200,

                showConfirmButton: false

            }).then(function () {

                /*
                 * Reload supaya pagination,
                 * nomor urut dan data tetap sinkron.
                 */
                window.location.reload();

            });

        },

        error: function (xhr) {

            button.prop('disabled', false);

            button.html(originalHtml);

            let message =
                'Terjadi kesalahan saat menghapus data.';

            if (
                xhr.responseJSON &&
                xhr.responseJSON.message
            ) {

                message =
                    xhr.responseJSON.message;

            }

            Swal.fire({

                icon: 'error',

                title: 'Gagal menghapus',

                text: message

            });

        }

    });

}

</script>
