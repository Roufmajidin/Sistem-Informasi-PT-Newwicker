<script src="https://js.pusher.com/8.4.0/pusher.min.js"></script>
<script>
$(document).ready(function() {


    let articleTimer = null;
    let pekerjaanTimer = null;

    // Mencegah satu transaksi terkirim lebih dari sekali
    let isSubmittingUpah = false;
    let editingUpahId = null;


    /*
    |--------------------------------------------------------------------------
    | OPEN MODAL
    |--------------------------------------------------------------------------
    */

    $('#btnAddUpahTransaksi').on(
        'click',
        function() {

            editingUpahId = null;
            window.editingUpahId = null;

            if (typeof window.resetUpahQtyCheckState === 'function') {
                window.resetUpahQtyCheckState();
            } else {
                $('#insert_qty').removeClass('is-invalid');
                $('#insert_qty_limit_info').hide();
            }

            $('#modalInsertUpah .modal-title').html(`
                <i class="fas fa-money-bill-wave mr-1"></i>
                Tambah Transaksi Upah
            `);
            $('#btnSaveUpahTransaksi')
                .html('<i class="fas fa-save mr-1"></i> Simpan');

            $('#formInsertUpah')[0].reset();
            $('#insert_upah_id').val('');

            // Pastikan setiap membuka Single Add, No PO kembali ke SELECT.
            // Jika transaksi sebelumnya memakai input manual, helper akan
            // mengembalikannya menjadi select.
            ensureSingleNoPoSelect('Pilih No PO...');

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

        if (editingUpahId) {
            $('#modalInsertUpah .modal-title').html(`
                <i class="fas fa-edit mr-1"></i>
                Edit Transaksi Upah
                <small class="d-block text-warning mt-1">
                    Pekerjaan belum tersedia untuk article ini — silakan isi jenis pekerjaan dan harga baru.
                </small>
            `);
        } else {
            $('#modalInsertUpah .modal-title').html(`
                <i class="fas fa-plus-circle mr-1"></i>
                Anda sedang menambahkan upah <strong>${escapeHtml(article || '')}</strong>
                <small class="d-block text-danger mt-1">
                    Pekerjaan belum tersedia untuk article ini — silakan isi jenis pekerjaan dan harga baru.
                </small>
            `);
        }

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


    function loadPekerjaanByArticle(article, selectedPekerjaan = '', selectedHarga = null) {

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

                    if (selectedPekerjaan) {
                        $('#insert_pekerjaan_new').val(selectedPekerjaan);
                    }
                    if (selectedHarga !== null) {
                        $('#insert_harga').val(selectedHarga);
                        calculateTotal();
                    }

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

                if (selectedPekerjaan) {
                    const option = select.find('option').filter(function() {
                        return $(this).val() === selectedPekerjaan;
                    }).first();

                    if (option.length) {
                        select.val(selectedPekerjaan);
                        $('#insert_harga').val(
                            selectedHarga !== null ? selectedHarga : (parseFloat(option.attr('data-harga')) || 0)
                        );
                    } else {
                        setManualPekerjaanMode(article);
                        $('#insert_pekerjaan_new').val(selectedPekerjaan);
                        $('#insert_harga').val(selectedHarga !== null ? selectedHarga : 0);
                    }
                } else {
                    $('#insert_harga').val(0);
                }

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

                // Tetap cari No PO. Jika tidak ditemukan,
                // field otomatis berubah menjadi input manual.
                loadPoByArticle(
                    article,
                    $('#insert_description').val().trim()
                );

                return;
            }

            /*
            |--------------------------------------------------------------------------
            | ARTICLE SUDAH ADA
            |--------------------------------------------------------------------------
            */

            if (editingUpahId) {
                $('#modalInsertUpah .modal-title').html(`
                    <i class="fas fa-edit mr-1"></i>
                    Edit Transaksi Upah
                `);
            } else {
                $('#modalInsertUpah .modal-title').html(`
                    <i class="fas fa-money-bill-wave mr-1"></i>
                    Tambah Transaksi Upah
                `);
            }

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

                url: editingUpahId
                    ? "{{ url('/upah/transaksi') }}/" + editingUpahId
                    : "{{ route('upah.transaksi.store') }}",

                type: editingUpahId ? 'PUT' : 'POST',

                data: formData,


                success: function(response) {

                    if (
                        response.success
                    ) {

                        $('#modalInsertUpah')
                            .modal('hide');

                        editingUpahId = null;
                        window.editingUpahId = null;

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
                                (editingUpahId ? ' Update' : ' Simpan')
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


    function createMassRow(focusArticle = true) {

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
                <select class="form-control mass-pekerjaan">
                    <option value="">Pilih pekerjaan...</option>
                </select>
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
                <select class="form-control mass-no-po">
                    <option value="">Pilih No PO...</option>
                </select>
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

        const row = $('#massUpahBodyRows .mass-upah-row').last();

        calculateMassRow(row);

        if (focusArticle) {
            setTimeout(function() {
                row.find('.mass-article').trigger('focus');
            }, 30);
        }
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


    /*
    |--------------------------------------------------------------------------
    | MASS NO PO HELPER
    |--------------------------------------------------------------------------
    | Jika PO ditemukan -> gunakan SELECT.
    | Jika PO tidak ditemukan -> ubah menjadi INPUT agar user bisa
    | mengetik No PO manual. Value tetap dibaca oleh .val() saat save.
    |--------------------------------------------------------------------------
    */

    function resetMassNoPo(row, placeholder = 'Pilih No PO...') {

        let field = row.find('.mass-no-po');

        if (!field.length) return;

        if (!field.is('select')) {

            field.replaceWith(`
                <select class="form-control mass-no-po">
                    <option value="">${placeholder}</option>
                </select>
            `);

            return;
        }

        field
            .empty()
            .append(`<option value="">${placeholder}</option>`)
            .prop('disabled', false);
    }


    function makeMassNoPoManual(row, value = '') {

        const field = row.find('.mass-no-po');

        if (!field.length) return;

        if (field.is('input')) {

            field
                .val(value)
                .prop('disabled', false)
                .attr('placeholder', 'Ketik No PO');

            return;
        }

        field.replaceWith(`
            <input
                type="text"
                class="form-control mass-no-po"
                value="${String(value).replace(/"/g, '&quot;')}"
                placeholder="Ketik No PO"
                autocomplete="off"
            >
        `);
    }


    function resetMassUpah() {

        massRowCounter = 0;
        massArticleTimers = {};
        massPekerjaanTimers = {};

        $('#massUpahBodyRows').empty();

        $('#massUpahError')
            .addClass('d-none')
            .empty();

        createMassRow(false);
    }


    function showMassUpah() {

        resetMassUpah();

        $('#modalInsertUpah').addClass('mass-mode');
        $('#normalUpahBody').addClass('d-none');
        $('#massUpahBody').removeClass('d-none');

        $('#btnSaveUpahTransaksi').addClass('d-none');
        $('#btnSaveMassUpah').removeClass('d-none');
        $('#btnToggleMassUpah').addClass('d-none');

        $('#modalInsertUpah .modal-title').html(`
            <i class="fas fa-layer-group mr-1"></i>
            Mass Input Transaksi Upah
        `);

        setTimeout(function() {
            $('#massUpahBodyRows .mass-upah-row')
                .first()
                .find('.mass-article')
                .trigger('focus');
        }, 50);
    }


    function showNormalUpah() {

        $('#modalInsertUpah').removeClass('mass-mode');
        $('#massUpahBody').addClass('d-none');
        $('#normalUpahBody').removeClass('d-none');

        $('#btnSaveMassUpah').addClass('d-none');
        $('#btnSaveUpahTransaksi').removeClass('d-none');
        $('#btnToggleMassUpah').removeClass('d-none');
    }


    $('#btnToggleMassUpah').on('click', function(e) {
        e.preventDefault();
        showMassUpah();
    });


    $('#btnBackNormalUpah').on('click', function(e) {
        e.preventDefault();
        showNormalUpah();
    });


    $(document).on('click', '#btnAddMassRow', function(e) {

        e.preventDefault();
        e.stopPropagation();

        $('.mass-search-result')
            .empty()
            .removeClass('show');

        createMassRow(true);

        const wrapper = $('#modalInsertUpah .mass-table-wrapper');

        if (wrapper.length) {
            setTimeout(function() {
                wrapper.animate({
                    scrollTop: wrapper[0].scrollHeight
                }, 180);
            }, 50);
        }
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

        row.find('.mass-article-result')
            .empty()
            .removeClass('show');

        row.find('.mass-description').val('');
        row.find('.mass-pekerjaan')
            .empty()
            .append('<option value="">Pilih pekerjaan...</option>');
        row.find('.mass-harga').val(0);
        resetMassNoPo(row);

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

                    if (row.find('.mass-article').val().trim() !== keyword) {
                        return;
                    }

                    if (!Array.isArray(response)) {
                        response = [];
                    }

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
                                class="mass-search-item mass-article-result-item ${
                                    isExisting ? '' : 'article-not-in-db'
                                }"
                                data-article="${escapeHtml(item.article || '')}"
                                data-description="${escapeHtml(item.description || '')}"
                                data-harga="${item.harga || 0}"
                                data-jenis="${escapeHtml(item.jenis || '')}"
                                data-exists="${isExisting ? '1' : '0'}"
                            >
                                <div class="article-result-code">
                                    ${escapeHtml(item.article || '')}
                                </div>

                                <div class="article-result-description">
                                    ${escapeHtml(item.description || '-')}
                                </div>

                                ${
                                    isExisting
                                        ? `
                                            <div class="article-result-type">
                                                ${escapeHtml(item.jenis || '-')}
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


    /*
    |--------------------------------------------------------------------------
    | MASS ARTICLE SELECT + LOAD PEKERJAAN & NO PO
    |--------------------------------------------------------------------------
    */

    $(document).on('mousedown', '.mass-article-result-item', function(e) {

        e.preventDefault();
        e.stopPropagation();

        const item = $(this);
        const row = item.closest('.mass-upah-row');
        const article = item.attr('data-article') || '';
        const description = item.attr('data-description') || '';
        const rowId = row.data('row');

        row.find('.mass-article').val(article);
        row.find('.mass-description').val(description);

        row.find('.mass-article-result').empty().removeClass('show');

        const pekerjaanSelect = row.find('.mass-pekerjaan');

        // Reset No PO to SELECT first. Jika hasil AJAX kosong,
        // field akan otomatis berubah menjadi INPUT manual.
        resetMassNoPo(row);
        const poSelect = row.find('.mass-no-po');

        // Reset pekerjaan
        pekerjaanSelect.empty().append(
            '<option value="">Memuat pekerjaan...</option>'
        ).prop('disabled', true);

        // Reset No PO
        poSelect.empty().append(
            '<option value="">Memuat No PO...</option>'
        ).prop('disabled', true);

        row.find('.mass-harga').val(0);
        calculateMassRow(row);

        /*
        |--------------------------------------------------------------------------
        | LOAD PEKERJAAN
        |--------------------------------------------------------------------------
        */
        $.ajax({
            url: "{{ route('upah.transaksi.search.pekerjaan') }}",
            type: 'GET',
            data: {
                article: article,
                q: ''
            },
            success: function(response) {

                // Laravel endpoint normally returns an array.
                // Keep this tolerant in case the response is wrapped in data.
                if (!Array.isArray(response)) {
                    response = Array.isArray(response?.data) ? response.data : [];
                }

                // Jangan menimpa pilihan jika user sudah memilih article lain
                // pada row yang sama saat request sebelumnya masih berjalan.
                if (row.data('row') != rowId || row.find('.mass-article').val().trim() !== article) {
                    return;
                }

                pekerjaanSelect.empty().append(
                    '<option value="">Pilih pekerjaan...</option>'
                );

                response.forEach(function(item) {
                    const jenis = item.jenis || '';
                    const harga = parseFloat(item.harga) || 0;

                    if (!jenis) return;

                    pekerjaanSelect.append(
                        $('<option>', {
                            value: jenis,
                            text: jenis + ' — Rp ' + formatRupiah(harga)
                        }).attr('data-harga', harga)
                    );
                });

                pekerjaanSelect.prop('disabled', false);

                if (!response.length) {
                    pekerjaanSelect.append(
                        '<option value="">Tidak ada pekerjaan untuk article ini</option>'
                    );
                }
            },
            error: function(xhr) {
                console.error('Load mass pekerjaan error:', xhr);

                pekerjaanSelect.empty().append(
                    '<option value="">Gagal memuat pekerjaan</option>'
                ).prop('disabled', false);
            }
        });

        /*
        |--------------------------------------------------------------------------
        | LOAD NO PO
        |--------------------------------------------------------------------------
        */
        $.ajax({
            url: "{{ route('upah.transaksi.search.po') }}",
            type: 'GET',
            data: {
                article: article,
                description: description
            },
            success: function(response) {

                if (!Array.isArray(response)) {
                    response = Array.isArray(response?.data) ? response.data : [];
                }

                if (row.data('row') != rowId || row.find('.mass-article').val().trim() !== article) {
                    return;
                }

                const seenPo = {};
                const poList = [];

                response.forEach(function(item) {
                    const noPo = item.no_po || '';

                    if (!noPo || seenPo[noPo]) {
                        return;
                    }

                    seenPo[noPo] = true;
                    poList.push(noPo);
                });

                /*
                |--------------------------------------------------------------------------
                | PO DITEMUKAN
                |--------------------------------------------------------------------------
                */
                if (poList.length) {

                    resetMassNoPo(row);

                    const poSelect = row.find('.mass-no-po');

                    poList.forEach(function(noPo) {

                        poSelect.append(
                            $('<option>', {
                                value: noPo,
                                text: noPo
                            })
                        );

                    });

                    poSelect.prop('disabled', false);

                /*
                |--------------------------------------------------------------------------
                | PO TIDAK DITEMUKAN
                |--------------------------------------------------------------------------
                | SELECT diganti INPUT agar user bisa mengetik No PO manual.
                |--------------------------------------------------------------------------
                */
                } else {

                    makeMassNoPoManual(row);

                }
            },
            error: function(xhr) {
                console.error('Load mass No PO error:', xhr);

                makeMassNoPoManual(row);
            }
        });

        setTimeout(function() {
            pekerjaanSelect.trigger('focus');
        }, 50);
    });


    /*
    |--------------------------------------------------------------------------
    | MASS PEKERJAAN SELECT
    |--------------------------------------------------------------------------
    */

    $(document).on('change', '.mass-pekerjaan', function() {

        const select = $(this);
        const row = select.closest('.mass-upah-row');

        const option = select.find('option:selected');
        const harga = parseFloat(option.attr('data-harga')) || 0;

        row.find('.mass-harga').val(harga);

        calculateMassRow(row);

        setTimeout(function() {
            row.find('.mass-qty').trigger('focus').select();
        }, 20);
    });
/*
    |--------------------------------------------------------------------------
    | MASS CALCULATION
    |--------------------------------------------------------------------------
    */

    $(document).on('input', '.mass-qty, .mass-harga', function() {
        calculateMassRow($(this).closest('.mass-upah-row'));
    });


    /*
    |--------------------------------------------------------------------------
    | MASS ROW REMOVE
    |--------------------------------------------------------------------------
    */

    $(document).on('click', '.btn-remove-mass-row', function(e) {

        e.preventDefault();
        e.stopPropagation();

        const rows = $('#massUpahBodyRows .mass-upah-row');
        const row = $(this).closest('.mass-upah-row');
        const rowId = row.data('row');

        clearTimeout(massArticleTimers[rowId]);
        clearTimeout(massPekerjaanTimers[rowId]);

        if (rows.length <= 1) {

            row.find('input, textarea').val('');
            row.find('.mass-pekerjaan')
                .empty()
                .append('<option value="">Pilih pekerjaan...</option>');

            resetMassNoPo(row);

            row.find('.mass-tanggal')
                .val('{{ date('Y-m-d') }}');

            row.find('.mass-qty').val(1);
            row.find('.mass-harga').val(0);

            row.find('.mass-article-result, .mass-pekerjaan-result')
                .empty()
                .removeClass('show');

            row.removeClass('mass-row-invalid');

            calculateMassRow(row);

            row.find('.mass-article').trigger('focus');

            return;
        }

        row.remove();

        renumberMassRows();

        $('#massUpahBodyRows .mass-upah-row')
            .last()
            .find('.mass-article')
            .trigger('focus');
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
        let firstInvalidRow = null;

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

            const rowInvalid =
                !item.article ||
                !item.tanggal ||
                !item.pekerjaan ||
                qty <= 0 ||
                harga < 0;

            row.removeClass('mass-row-invalid');

            if (rowInvalid) {

                invalid = true;
                row.addClass('mass-row-invalid');

                if (!firstInvalidRow) {
                    firstInvalidRow = row;
                }
            }

            rows.push(item);
        });

        if (invalid) {

            $('#massUpahError')
                .removeClass('d-none')
                .text(
                    'Mohon lengkapi Article, Tanggal, Pekerjaan, Qty, dan Harga pada semua baris.'
                );

            if (firstInvalidRow) {

                const wrapper =
                    $('#modalInsertUpah .mass-table-wrapper');

                if (wrapper.length) {
                    wrapper.animate({
                        scrollTop:
                            firstInvalidRow.position().top +
                            wrapper.scrollTop() -
                            50
                    }, 200);
                }

                setTimeout(function() {
                    firstInvalidRow
                        .find('.mass-article')
                        .trigger('focus');
                }, 220);
            }

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

                let message =
                    'Gagal menyimpan data mass.';

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

        editingUpahId = null;
        window.editingUpahId = null;

        if (typeof window.resetUpahQtyCheckState === 'function') {
            window.resetUpahQtyCheckState();
        }

        $('#modalInsertUpah .modal-title').html(`
            <i class="fas fa-money-bill-wave mr-1"></i>
            Tambah Transaksi Upah
        `);

        $('#btnToggleMassUpah').removeClass('d-none');

        $('#btnSaveUpahTransaksi')
            .prop('disabled', false)
            .html('<i class="fas fa-save mr-1"></i> Simpan');
    });


    /*
    |--------------------------------------------------------------------------
    | CLOSE SEARCH
    |--------------------------------------------------------------------------
    */

    $(document).on('click', function(e) {

        if (
            !$(e.target).closest('.article-search-wrapper').length &&
            !$(e.target).closest('.mass-search-wrapper').length
        ) {

            $('#articleSearchResult')
                .empty()
                .removeClass('show');

            $('#pekerjaanSearchResult')
                .empty()
                .removeClass('show');

            $('.mass-article-result, .mass-pekerjaan-result')
                .empty()
                .removeClass('show');
        }
    });


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
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }


    $(document).on('click', '.btn-edit-upah', function () {

    const button = $(this);

    // Ambil ID langsung dari HTML attribute agar selalu mendapatkan
    // transaksi yang sedang diedit. ID ini dipakai sebagai exclude_id
    // pada realtime qty check.
    editingUpahId = parseInt(button.attr('data-id'), 10) || null;
    window.editingUpahId = editingUpahId;
    $('#insert_upah_id').val(editingUpahId || '');

    // Buang hasil validasi transaksi sebelumnya.
    if (typeof window.resetUpahQtyCheckState === 'function') {
        window.resetUpahQtyCheckState();
    } else {
        $('#insert_qty').removeClass('is-invalid');
        $('#insert_qty_limit_info').hide();
    }

    showNormalUpah();

    $('#modalInsertUpah .modal-title').html(`
        <i class="fas fa-edit mr-1"></i>
        Edit Transaksi Upah
    `);

    $('#btnSaveUpahTransaksi')
        .removeClass('d-none')
        .prop('disabled', false)
        .html('<i class="fas fa-save mr-1"></i> Update');

    $('#btnToggleMassUpah').addClass('d-none');

    $('#formUpahError')
        .addClass('d-none')
        .empty();

    $('#articleNotFound')
        .removeClass('show');

    $('#articleSearchResult')
        .empty()
        .removeClass('show');

    $('#pekerjaanSearchResult')
        .empty()
        .removeClass('show');

    $('#insert_article').val(button.data('article') || '');
    $('#insert_description').val(button.data('description') || '');
    $('#insert_tanggal').val(button.data('tanggal') || '');
    $('#insert_person').val(button.data('person') || '');
    $('#insert_qty').val(button.data('qty') ?? 0);
    $('#insert_harga').val(button.data('harga') ?? 0);
    $('#insert_no_spk').val(button.attr('data-no-spk') || '');

    const article = $('#insert_article').val().trim();
    const pekerjaan = button.data('pekerjaan') || '';
    const harga = parseFloat(button.data('harga')) || 0;
    const noPo = button.attr('data-no-po') || '';

    $('#insert_pekerjaan_new')
        .addClass('d-none')
        .val('');

    $('#insert_pekerjaan')
        .removeClass('d-none')
        .empty()
        .append('<option value="">Memuat pekerjaan...</option>');

    $('#insert_no_po')
        .empty()
        .append('<option value="">Memuat No PO...</option>');

    loadPekerjaanByArticle(article, pekerjaan, harga);
    loadPoByArticle(article, $('#insert_description').val().trim(), noPo);

    calculateTotal();

    $('#modalInsertUpah').modal('show');

});


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
function ensureSingleNoPoSelect(placeholder = 'Pilih No PO...') {

    let field = $('#insert_no_po');

    if (!field.length) return null;

    if (!field.is('select')) {
        field.replaceWith(`
            <select id="insert_no_po" name="no_po" class="form-control">
                <option value="">${placeholder}</option>
            </select>
        `);
        field = $('#insert_no_po');
    } else {
        field
            .empty()
            .append(`<option value="">${placeholder}</option>`)
            .prop('disabled', false);
    }

    return field;
}


function makeSingleNoPoManual(value = '') {

    let field = $('#insert_no_po');

    if (!field.length) return null;

    if (field.is('input')) {
        return field
            .val(value)
            .prop('disabled', false)
            .attr('placeholder', 'Ketik No PO');
    }

    field.replaceWith(`
        <input
            type="text"
            id="insert_no_po"
            name="no_po"
            class="form-control"
            value="${String(value).replace(/"/g, '&quot;')}"
            placeholder="Ketik No PO"
            autocomplete="off"
        >
    `);

    return $('#insert_no_po');
}


function loadPoByArticle(article, description = '', selectedNoPo = '') {

    ensureSingleNoPoSelect('Memuat No PO...');

    let select = $('#insert_no_po');

    if (!article) {
        ensureSingleNoPoSelect('Pilih No PO...');
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
                response = Array.isArray(response?.data) ? response.data : [];
            }

            const seenPo = {};
            const poList = [];

            response.forEach(function(item) {

                const noPo = item.no_po || '';

                if (!noPo || seenPo[noPo]) {
                    return;
                }

                seenPo[noPo] = true;
                poList.push(noPo);
            });

            /*
            |--------------------------------------------------------------
            | PO DITEMUKAN -> SELECT
            |--------------------------------------------------------------
            */
            if (poList.length) {

                ensureSingleNoPoSelect();
                select = $('#insert_no_po');

                poList.forEach(function(noPo) {

                    var poItem = response.find(function (x) {
                        return String(x.no_po || '') === String(noPo);
                    });

                    select.append(
                        $('<option>', {
                            value: noPo,
                            text: noPo
                        }).attr(
                            'data-po-qty',
                            poItem ? (poItem.po_qty || 0) : 0
                        )
                    );

                });

                if (selectedNoPo) {
                    select.val(selectedNoPo);
                }

                return;
            }

            /*
            |--------------------------------------------------------------
            | PO TIDAK DITEMUKAN -> INPUT MANUAL
            |--------------------------------------------------------------
            */
            makeSingleNoPoManual(selectedNoPo || '');

        },

        error: function(xhr) {

            console.error(
                'Load PO error:',
                xhr
            );

            /* Jika endpoint gagal, tetap izinkan user mengetik PO manual. */
            makeSingleNoPoManual(selectedNoPo || '');
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

<!-- =========================================================
     REALTIME REMOTE CURSOR - UPah / TRANSAKSI UPAH
     Mengikuti pola cursor pada halaman Detail Barang.
     ========================================================= -->
<style>
    #upahRemoteCursors {
        position: fixed;
        inset: 0;
        pointer-events: none;
        z-index: 9999999;
    }

    .upah-remote-cursor {
        position: fixed;
        pointer-events: none;
        transform: translate(-1px, -1px);
        transition:
            left 90ms linear,
            top 90ms linear;
        will-change: left, top;
    }

    .upah-remote-cursor-arrow {
        width: 0;
        height: 0;

        border-top: 0 solid transparent;
        border-bottom: 15px solid transparent;
        border-left: 11px solid #2563eb;

        transform: rotate(-42deg);

        filter: drop-shadow(0 1px 1px rgba(0, 0, 0, .25));
    }

    .upah-remote-cursor-name {
        position: absolute;
        left: 9px;
        top: 12px;

        padding: 3px 7px;

        border-radius: 4px;

        background: #2563eb;
        color: #fff;

        font-size: 10px;
        font-weight: 700;
        line-height: 1.2;

        white-space: nowrap;

        box-shadow: 0 2px 5px rgba(0, 0, 0, .18);
    }

    .upah-remote-cursor.is-idle {
        opacity: .45;
    }
</style>

<div id="upahRemoteCursors"></div>

<script>
(function () {
    'use strict';

    /* =========================================================
       USER
       ========================================================= */
    const currentUserId = @json(auth()->id());
    const currentUserName = @json(auth()->user()->name ?? 'User');

    if (!currentUserId) {
        console.warn('[UPAH Cursor] User ID tidak tersedia.');
        return;
    }

    /* =========================================================
       PUSHER CONFIG
       ========================================================= */
    const pusherKey =
        @json(config('broadcasting.connections.pusher.key'));

    const pusherCluster =
        @json(config('broadcasting.connections.pusher.options.cluster'));

    if (!pusherKey) {
        console.warn('[UPAH Cursor] Pusher key belum tersedia.');
        return;
    }

    /* =========================================================
       CONTAINER
       ========================================================= */
    const container = document.getElementById('upahRemoteCursors');

    if (!container) {
        console.warn('[UPAH Cursor] Container tidak ditemukan.');
        return;
    }

    /* =========================================================
       PUSHER
       ========================================================= */
    if (typeof Pusher === 'undefined') {
        console.warn('[UPAH Cursor] Pusher belum termuat.');
        return;
    }

    const pusher = new Pusher(pusherKey, {
        cluster: pusherCluster || 'ap1',
        forceTLS: true,
        authEndpoint: @json(route('pusher.auth')),
        auth: {
            headers: {
                'X-CSRF-TOKEN': document
                    .querySelector('meta[name="csrf-token"]')
                    ?.getAttribute('content') || ''
            }
        }
    });

    /* =========================================================
       CHANNEL
       Semua user pada halaman Transaksi Upah memakai channel
       yang sama, sehingga cursor bisa terlihat bersama.
       ========================================================= */
    const channelName = 'presence-upah-transaksi';
    const channel = pusher.subscribe(channelName);

    // Expose channel khusus agar fitur lain (Live Chat) dapat
    // menggunakan koneksi Pusher yang sama tanpa mengganggu
    // realtime cursor yang sudah berjalan.
    window.upahRealtimeChannel = channel;
    window.upahRealtimePusher = pusher;
    window.upahRealtimeChannelName = channelName;

    /* =========================================================
       CURSOR STORAGE
       ========================================================= */
    const remoteCursors = {};

    /* =========================================================
       CREATE CURSOR
       ========================================================= */
    function createCursor(userId, name) {
        const id = 'upah-remote-cursor-' + userId;

        let cursor = document.getElementById(id);

        if (cursor) {
            const label = cursor.querySelector('.upah-remote-cursor-name');
            if (label && name) {
                label.textContent = name;
            }
            return cursor;
        }

        cursor = document.createElement('div');
        cursor.id = id;
        cursor.className = 'upah-remote-cursor';

        const arrow = document.createElement('div');
        arrow.className = 'upah-remote-cursor-arrow';

        const label = document.createElement('div');
        label.className = 'upah-remote-cursor-name';
        label.textContent = name || 'User';

        cursor.appendChild(arrow);
        cursor.appendChild(label);

        container.appendChild(cursor);

        remoteCursors[String(userId)] = {
            element: cursor,
            lastMove: Date.now(),
            x: 0,
            y: 0
        };

        return cursor;
    }

    /* =========================================================
       REMOVE CURSOR
       ========================================================= */
    function removeCursor(userId) {
        const key = String(userId);
        const data = remoteCursors[key];

        if (!data) {
            return;
        }

        data.element.remove();
        delete remoteCursors[key];
    }

    /* =========================================================
       UPDATE CURSOR
       ========================================================= */
    function updateCursor(data) {
        if (!data) {
            return;
        }

        const userId = String(data.user_id);

        /* Jangan tampilkan cursor sendiri */
        if (userId === String(currentUserId)) {
            return;
        }

        const cursor = createCursor(userId, data.name);
        const state = remoteCursors[userId];

        if (!state) {
            return;
        }

        const x = Number(data.x);
        const y = Number(data.y);

        if (!Number.isFinite(x) || !Number.isFinite(y)) {
            return;
        }

        state.x = x;
        state.y = y;
        state.lastMove = Date.now();

        cursor.style.left = x + 'px';
        cursor.style.top = y + 'px';

        cursor.classList.remove('is-idle');
    }

    /* =========================================================
       CONNECTION LOG
       ========================================================= */
    pusher.connection.bind('connected', function () {
        console.log(
            '[PUSHER UPAH] Connected:',
            pusher.connection.socket_id
        );
    });

    pusher.connection.bind('error', function (err) {
        console.error('[PUSHER UPAH] Connection error:', err);
    });

    /* =========================================================
       SUBSCRIPTION
       ========================================================= */
    channel.bind('pusher:subscription_succeeded', function (members) {
        console.log('[PUSHER UPAH] Presence connected');
        console.log('[PUSHER UPAH] Channel:', channelName);
        console.log('[PUSHER UPAH] Members:', members.count);
    });

    /* =========================================================
       SEND CURSOR
       ========================================================= */
    let lastSend = 0;
    let lastX = null;
    let lastY = null;

    const SEND_INTERVAL = 150;
    const MIN_DISTANCE = 5;

    document.addEventListener('mousemove', function (event) {
        const now = Date.now();

        /* Throttle */
        if (now - lastSend < SEND_INTERVAL) {
            return;
        }

        const x = event.clientX;
        const y = event.clientY;

        /* Jangan kirim jika gerak terlalu sedikit */
        if (lastX !== null && lastY !== null) {
            const dx = x - lastX;
            const dy = y - lastY;
            const distance = Math.sqrt(dx * dx + dy * dy);

            if (distance < MIN_DISTANCE) {
                return;
            }
        }

        lastX = x;
        lastY = y;
        lastSend = now;

        try {
            channel.trigger('client-upah-cursor', {
                user_id: currentUserId,
                name: currentUserName,
                x: x,
                y: y
            });
        } catch (error) {
            console.warn('[UPAH Cursor]', error);
        }
    }, {
        passive: true
    });

    /* =========================================================
       RECEIVE CURSOR
       ========================================================= */
    channel.bind('client-upah-cursor', function (data) {
        updateCursor(data);
    });

    /* =========================================================
       MEMBER ADDED
       ========================================================= */
    channel.bind('pusher:member_added', function (member) {
        console.log(
            '[UPAH Cursor] User masuk:',
            member.info?.name || member.id
        );
    });

    /* =========================================================
       MEMBER REMOVED
       ========================================================= */
    channel.bind('pusher:member_removed', function (member) {
        removeCursor(String(member.id));
    });

    /* =========================================================
       IDLE
       ========================================================= */
    setInterval(function () {
        const now = Date.now();

        Object.keys(remoteCursors).forEach(function (userId) {
            const state = remoteCursors[userId];

            if (!state) {
                return;
            }

            if (now - state.lastMove > 5000) {
                state.element.classList.add('is-idle');
            }
        });
    }, 1000);

    /* =========================================================
       CLEANUP
       ========================================================= */
    window.addEventListener('beforeunload', function () {
        try {
            pusher.unsubscribe(channelName);
        } catch (e) {}
    });

})();
</script>




<script>
/* =========================================================
 * QTY LOCK REKAP UPAH
 * Limit = Detail PO Qty
 * Key = Article Code + No PO + Jenis Pekerjaan
 *
 * TIDAK menggunakan detail_po_id di tabel upah.
 * ========================================================= */
(function () {
    'use strict';

    var qtyCheckTimer = null;
    var qtyCheckRequest = null;
    var lastQtyLimit = null;
    var lastAlertKey = '';
    var lastAlertAt = 0;

    // Expose a safe reset function so the Edit/Add handler in the
    // main script can reset the REAL qty-lock state. The variables
    // above belong to this IIFE and are intentionally not global.
    window.resetUpahQtyCheckState = function () {
        clearTimeout(qtyCheckTimer);

        if (qtyCheckRequest && qtyCheckRequest.readyState !== 4) {
            qtyCheckRequest.abort();
        }

        qtyCheckTimer = null;
        qtyCheckRequest = null;
        lastQtyLimit = null;
        lastAlertKey = '';
        lastAlertAt = 0;

        $('#insert_qty').removeClass('is-invalid');
        $('#insert_qty_limit_info').hide();
    };

    function escHtml(value) {
        return $('<div>').text(value == null ? '' : String(value)).html();
    }

    function toNumber(value) {
        if (value == null || String(value).trim() === '') return 0;

        var s = String(value).trim().replace(/\s/g, '');

        if (s.indexOf(',') !== -1 && s.indexOf('.') !== -1) {
            s = s.replace(/\./g, '').replace(',', '.');
        } else if (s.indexOf(',') !== -1) {
            s = s.replace(',', '.');
        }

        var n = parseFloat(s);
        return isFinite(n) ? n : 0;
    }

    function formatQty(value) {
        return toNumber(value).toLocaleString('id-ID', {
            minimumFractionDigits: 0,
            maximumFractionDigits: 2
        });
    }

    function getPekerjaanValue() {
        var select = $('#insert_pekerjaan');

        if (select.length && !select.hasClass('d-none')) {
            var value = select.val();
            if (Array.isArray(value)) {
                return value.length ? String(value[0]).trim() : '';
            }
            return String(value || '').trim();
        }

        return String($('#insert_pekerjaan_new').val() || '').trim();
    }

    function getQtyValue() {
        return Math.max(0, toNumber($('#insert_qty').val()));
    }

    function showInlineInfo(data) {
        var box = $('#insert_qty_limit_info');
        if (!box.length) return;

        box.show();

        $('#insert_qty_po').text(formatQty(data.qty_po));

        // Tampilkan total yang benar-benar sudah diupahkan,
        // termasuk transaksi yang sedang diedit.
        // Untuk validasi kuota tetap gunakan data.used_qty
        // karena transaksi edit memang dikecualikan dari perhitungan.
        var displayedUsed = data.used_qty_total != null
            ? data.used_qty_total
            : data.used_qty;

        $('#insert_qty_used').text(formatQty(displayedUsed));
        $('#insert_qty_remaining').text(formatQty(data.remaining_qty));

        var message = $('#insert_qty_limit_message');
        var requested = toNumber(data.requested_qty);
        var remaining = toNumber(data.remaining_qty);

        if (!data.found) {
            message
                .removeClass('text-success text-muted')
                .addClass('text-danger fw-semibold')
                .html(escHtml(data.message || 'Detail PO tidak ditemukan.'));

            $('#insert_qty').addClass('is-invalid');
            return;
        }

      if (!data.valid || data.over || requested > remaining) {
    message
        .removeClass('text-success text-muted')
        .addClass('text-danger fw-semibold')
        .html(
            'Qty melebihi sisa. ' +
            '<strong>' +
            escHtml(formatQty(data.used_qty)) +
            '/' +
            escHtml(formatQty(data.qty_po)) +
            ' pcs' +
            '</strong> ' +
            '(sisa ' +
            '<strong>' +
            escHtml(formatQty(data.remaining_qty)) +
            ' pcs</strong>).'
        );

    $('#insert_qty').addClass('is-invalid');
    return;
}

        message
            .removeClass('text-danger text-muted')
            .addClass('text-success')
            .html(
                'Qty masih tersedia. Sisa setelah input: <strong>' +
                escHtml(formatQty(Math.max(0, remaining - requested))) +
                '</strong>.'
            );

        $('#insert_qty').removeClass('is-invalid');
    }

    function showQtyAlert(data) {
        var requested = toNumber(data.requested_qty);
        var remaining = toNumber(data.remaining_qty);

        var article = String($('#insert_article').val() || '').trim();
        var noPo = String($('#insert_no_po').val() || '').trim();
        var pekerjaan = getPekerjaanValue();

        var alertKey = [
            article,
            noPo,
            pekerjaan,
            formatQty(requested),
            formatQty(remaining)
        ].join('|');

        var now = Date.now();

        // Jangan popup berkali-kali untuk input yang sama.
        if (alertKey === lastAlertKey && (now - lastAlertAt) < 2000) {
            return;
        }

        lastAlertKey = alertKey;
        lastAlertAt = now;

        if (typeof Swal !== 'undefined') {
        Swal.fire({
    imageUrl: '{{ asset("storage/rouf.jpeg") }}',
    imageWidth: 75,
    imageHeight: 90,
    imageAlt: 'Foto item',

    title: 'Qty Melebihi Sisa',

    html:
        '<div style="text-align:left">' +
        '<div><b>Article:</b> ' + escHtml(article) + '</div>' +
        '<div><b>No PO:</b> ' + escHtml(noPo) + '</div>' +
        '<div><b>Pekerjaan:</b> ' + escHtml(pekerjaan) + '</div>' +
        '<hr style="margin:8px 0">' +
        '<div><b>Qty PO:</b> ' + escHtml(formatQty(data.qty_po)) + '</div>' +
        '<div><b>Sudah Upah:</b> ' +
        escHtml(formatQty(
            data.used_qty_total != null ? data.used_qty_total : data.used_qty
        )) +
        '</div>' +
        '<div><b>Sisa:</b> ' + escHtml(formatQty(remaining)) + '</div>' +
        '<div><b>Input:</b> ' + escHtml(formatQty(requested)) + '</div>' +
        '</div>',

    confirmButtonText: 'OK'
});   
        } else {
            alert(
                'Qty melebihi sisa.\n\n' +
                'Qty PO: ' + formatQty(data.qty_po) + '\n' +
                'Sudah Upah: ' +
                formatQty(
                    data.used_qty_total != null
                        ? data.used_qty_total
                        : data.used_qty
                ) + '\n' +
                'Sisa: ' + formatQty(remaining) + '\n' +
                'Input: ' + formatQty(requested)
            );
        }
    }

    function hideQtyInfo() {
        $('#insert_qty_limit_info').hide();
        $('#insert_qty').removeClass('is-invalid');
        lastQtyLimit = null;
    }

    window.checkInsertQtyLimit = function (immediate) {
        var article = String($('#insert_article').val() || '').trim();
        var noPo = String($('#insert_no_po').val() || '').trim();
        var pekerjaan = getPekerjaanValue();
        var qty = getQtyValue();

        if (!article || !noPo || !pekerjaan) {
            hideQtyInfo();
            return;
        }

        if (!immediate) {
            clearTimeout(qtyCheckTimer);
            qtyCheckTimer = setTimeout(function () {
                window.checkInsertQtyLimit(true);
            }, 80);
            return;
        }

        if (!window.checkQtyUpahUrl) {
            console.error('checkQtyUpahUrl belum tersedia.');
            return;
        }

        if (qtyCheckRequest && qtyCheckRequest.readyState !== 4) {
            qtyCheckRequest.abort();
        }

        var excludeId = '';

        if (window.editingUpahId) {
            excludeId = parseInt(window.editingUpahId, 10) || '';
        } else if (typeof editingUpahId !== 'undefined' && editingUpahId) {
            excludeId = parseInt(editingUpahId, 10) || '';
        } else if ($('#insert_upah_id').val()) {
            excludeId = parseInt($('#insert_upah_id').val(), 10) || '';
        }

        var requestEditId = excludeId;

        qtyCheckRequest = $.ajax({
            url: window.checkQtyUpahUrl,
            type: 'GET',
            dataType: 'json',
            data: {
                article: article,
                no_po: noPo,
                pekerjaan: pekerjaan,
                qty: qty,
                exclude_id: excludeId
            }
        }).done(function (res) {

            if (!res || !res.success) {
                return;
            }

            // Jangan terapkan response dari transaksi lama jika user
            // sudah berpindah ke transaksi lain.
            var currentEditId =
                window.editingUpahId
                    ? parseInt(window.editingUpahId, 10)
                    : '';

            if (String(currentEditId || '') !== String(requestEditId || '')) {
                return;
            }

            /*
             * Abaikan response lama jika user sudah mengetik angka baru
             * sebelum AJAX sebelumnya selesai.
             */
            var currentQty = getQtyValue();

            if (currentQty !== qty) {
                return;
            }

            res.requested_qty = qty;
            res.over =
                !res.valid ||
                (res.remaining_qty !== null &&
                 qty > toNumber(res.remaining_qty));

            lastQtyLimit = res;

            showInlineInfo(res);

            // INI YANG MEMUNCULKAN ALERT SAAT USER MENGETIK 21,
            // apabila sisa hanya 20 atau kurang.
            if (res.over) {
                showQtyAlert(res);
            }

        }).fail(function (xhr) {

            if (xhr.status === 0) return;

            var msg = 'Gagal mengecek kuota Qty.';

            if (xhr.responseJSON) {
                msg =
                    xhr.responseJSON.message ||
                    (xhr.responseJSON.errors
                        ? Object.values(xhr.responseJSON.errors).flat().join('\n')
                        : msg);
            }

            $('#insert_qty_limit_info').show();

            $('#insert_qty_limit_message')
                .removeClass('text-success text-muted')
                .addClass('text-danger fw-semibold')
                .text(msg);

            $('#insert_qty').addClass('is-invalid');
        });
    };

    /*
     * IMPORTANT:
     * Jangan bergantung hanya pada blur/change.
     * Qty harus dicek ketika user MASIH mengetik.
     *
     * Kita pakai native event + delegated jQuery:
     * - input  : ketika angka berubah
     * - keyup  : fallback untuk browser/input number tertentu
     *
     * Validasi dijalankan langsung, tanpa menunggu user klik field lain.
     */
    function triggerQtyRealtimeCheck() {
        try {
            if (typeof calculateTotal === 'function') {
                calculateTotal();
            }
        } catch (err) {
            console.warn('calculateTotal error:', err);
        }

        /*
         * FIRST: validasi LOCAL dari hasil check server terakhir.
         *
         * Jadi ketika server sebelumnya sudah memberi:
         * Qty PO = 20
         * Terpakai = 10
         * Sisa = 10
         *
         * user mengetik 11 -> alert TIDAK menunggu AJAX.
         */
        var currentQty = getQtyValue();

        if (
            lastQtyLimit &&
            lastQtyLimit.found &&
            lastQtyLimit.remaining_qty !== null
        ) {
            var remaining = toNumber(lastQtyLimit.remaining_qty);

            if (currentQty > remaining) {
                var localResult = $.extend({}, lastQtyLimit, {
                    requested_qty: currentQty,
                    over: true,
                    valid: false
                });

                showInlineInfo(localResult);
                showQtyAlert(localResult);
            } else {
                var localResultOk = $.extend({}, lastQtyLimit, {
                    requested_qty: currentQty,
                    over: false,
                    valid: true
                });

                showInlineInfo(localResultOk);
            }
        }

        /*
         * SECOND: refresh ke server secara asynchronous.
         * Ini tetap penting supaya nilai "Sudah Upah" terbaru tidak stale.
         * Tetapi UI tidak menunggu request ini untuk memberi warning.
         */
        clearTimeout(qtyCheckTimer);

        qtyCheckTimer = setTimeout(function () {
            window.checkInsertQtyLimit(true);
        }, 150);
    }

    $(document)
        .off(
            'input.upahQtyLockRealtime',
            '#insert_qty'
        )
        .on(
            'input.upahQtyLockRealtime',
            '#insert_qty',
            function () {
                triggerQtyRealtimeCheck();
            }
        );

    $(document)
        .off(
            'keyup.upahQtyLockRealtime',
            '#insert_qty'
        )
        .on(
            'keyup.upahQtyLockRealtime',
            '#insert_qty',
            function () {
                triggerQtyRealtimeCheck();
            }
        );

    /*
     * Native capture listener.
     * Ini menjadi fallback paling awal jika ada script lain
     * yang melakukan off()/replace terhadap event jQuery.
     */
    document.addEventListener(
        'input',
        function (event) {
            if (
                event.target &&
                event.target.id === 'insert_qty'
            ) {
                clearTimeout(qtyCheckTimer);

                qtyCheckTimer = setTimeout(function () {
                    window.checkInsertQtyLimit(true);
                }, 50);
            }
        },
        true
    );

    $(document)
        .off('change.upahQtyLock', '#insert_no_po, #insert_pekerjaan')
        .on('change.upahQtyLock', '#insert_no_po, #insert_pekerjaan', function () {
            window.checkInsertQtyLimit(true);
        });

    $(document)
        .off('input.upahQtyLockManual', '#insert_pekerjaan_new')
        .on('input.upahQtyLockManual', '#insert_pekerjaan_new', function () {
            window.checkInsertQtyLimit(false);
        });

    $(document)
        .off('blur.upahQtyLock', '#insert_qty')
        .on('blur.upahQtyLock', '#insert_qty', function () {
            window.checkInsertQtyLimit(true);
        });

    window.validateInsertQtyLimit = function () {
        var qty = getQtyValue();

        if (!lastQtyLimit) {
            return true;
        }

        var remaining = toNumber(lastQtyLimit.remaining_qty);

        if (!lastQtyLimit.valid || qty > remaining) {
            lastQtyLimit.requested_qty = qty;
            lastQtyLimit.over = true;
            showInlineInfo(lastQtyLimit);
            showQtyAlert(lastQtyLimit);
            $('#insert_qty').focus();
            return false;
        }

        return true;
    };

    $(document).on(
        'shown.bs.modal',
        '#modalInsertUpah, #modalInsert, #modalUpah',
        function () {
            setTimeout(function () {
                window.checkInsertQtyLimit(true);
            }, 100);
        }
    );
})();

/* Global submit guard for Rekap Upah.
 * Server-side controller validation remains the final authority.
 */
$(document).on('submit.upahQtyLock', 'form', function (e) {
    if ($(this).find('#insert_qty').length) {
        if (typeof window.validateInsertQtyLimit === 'function' &&
            !window.validateInsertQtyLimit()) {
            e.preventDefault();
            e.stopImmediatePropagation();
            return false;
        }
    }
});
</script>


<!-- =========================================================
     UPAH LIVE CHAT - WHATSAPP STYLE
     Realtime Pusher + typing indicator
     Menggunakan channel Pusher yang sama:
     presence-upah-transaksi
     Tidak mengubah logic transaksi/cursor yang sudah ada.
========================================================= -->
<script>
(function () {
    'use strict';

    let upahChatInitialized = false;
    let upahChatSubscribed = false;
    let upahTypingTimer = null;
    let upahTypingActive = false;
    let upahTypingUsers = {};

    const UPAH_CHAT_EVENT = 'client-upah-live-message';
    const UPAH_TYPING_EVENT = 'client-upah-typing';
    // Endpoint Laravel untuk menyimpan gambar hasil paste/upload.
    // Sesuaikan hanya jika route Anda menggunakan URL lain.
    const UPAH_CHAT_UPLOAD_URL = '/transaksi/upah/chat/upload-image';

    let upahPendingImage = null;
    let upahImageUploading = false;

    const currentUserId = String(@json(auth()->id()));
    const currentUserName = @json(auth()->user()->name ?? 'User');

    function getUpahChatChannel() {
        return window.upahRealtimeChannel || null;
    }

    function escapeUpahChatHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function scrollUpahChatToBottom() {
        const box = document.getElementById('upahChatMessages');

        if (box) {
            box.scrollTop = box.scrollHeight;
        }
    }

    function injectUpahChatStyle() {

        if (document.getElementById('upahChatRealtimeStyle')) {
            return;
        }

        const style = document.createElement('style');
        style.id = 'upahChatRealtimeStyle';

        style.innerHTML = `
            #upahChatModal {
                position: fixed !important;
                inset: 0 !important;
                z-index: 99999 !important;
                display: none;
                background: rgba(0,0,0,.38);
                backdrop-filter: blur(2px);
            }

            #upahChatModal.show {
                display: block !important;
            }

            #upahChatModal .upah-chat-window {
                position: absolute;
                right: 28px;
                bottom: 28px;
                width: min(390px, calc(100vw - 30px));
                height: min(610px, calc(100vh - 50px));
                display: flex;
                flex-direction: column;
                overflow: hidden;
                background: #efeae2;
                border-radius: 18px;
                box-shadow: 0 18px 55px rgba(0,0,0,.25);
                border: 1px solid rgba(0,0,0,.08);
            }

            #upahChatModal .upah-chat-header {
                min-height: 68px;
                padding: 11px 14px;
                display: flex;
                align-items: center;
                gap: 11px;
                background: #075e54;
                color: #fff;
                flex-shrink: 0;
            }

            #upahChatModal .upah-chat-avatar {
                width: 43px;
                height: 43px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                background: rgba(255,255,255,.18);
                font-size: 20px;
                flex-shrink: 0;
            }

            #upahChatModal .upah-chat-title {
                flex: 1;
                min-width: 0;
            }

            #upahChatModal .upah-chat-title strong {
                display: block;
                font-size: 14px;
                font-weight: 700;
                line-height: 1.2;
            }

            #upahChatModal .upah-chat-status {
                display: block;
                margin-top: 3px;
                font-size: 11px;
                opacity: .85;
                white-space: nowrap;
                overflow: hidden;
                text-overflow: ellipsis;
            }

            #upahChatModal .upah-chat-close {
                width: 36px;
                height: 36px;
                border: 0;
                border-radius: 50%;
                background: transparent;
                color: #fff;
                font-size: 25px;
                line-height: 1;
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            #upahChatModal .upah-chat-close:hover {
                background: rgba(255,255,255,.14);
            }

            #upahChatModal .upah-chat-messages {
                flex: 1;
                overflow-y: auto;
                overflow-x: hidden;
                padding: 16px 12px 10px;
                background-color: #efeae2;
                background-image:
                    radial-gradient(rgba(0,0,0,.035) 1px, transparent 1px);
                background-size: 18px 18px;
                scroll-behavior: smooth;
            }

            #upahChatModal .upah-chat-empty {
                height: 100%;
                display: flex;
                align-items: center;
                justify-content: center;
                text-align: center;
                color: #667781;
                font-size: 12px;
                padding: 30px;
            }

            #upahChatModal .upah-chat-message {
                max-width: 82%;
                width: fit-content;
                margin-bottom: 7px;
                padding: 7px 9px 5px;
                border-radius: 9px;
                position: relative;
                word-break: break-word;
                font-size: 13px;
                line-height: 1.45;
                box-shadow: 0 1px 1px rgba(0,0,0,.08);
                clear: both;
            }

            #upahChatModal .upah-chat-message.mine {
                float: right;
                margin-left: 18%;
                background: #d9fdd3;
                border-top-right-radius: 3px;
            }

            #upahChatModal .upah-chat-message.other {
                float: left;
                margin-right: 18%;
                background: #fff;
                border-top-left-radius: 3px;
            }

            #upahChatModal .upah-chat-message-name {
                color: #075e54;
                font-size: 11px;
                font-weight: 700;
                margin-bottom: 2px;
            }

            #upahChatModal .upah-chat-message-text {
                white-space: pre-wrap;
                color: #111b21;
                padding-right: 42px;
            }

            #upahChatModal .upah-chat-message-content {
                white-space: pre-wrap !important;
                overflow-wrap: anywhere !important;
                word-break: break-word !important;
                color: #111b21 !important;
                display: block !important;
                visibility: visible !important;
            }

            #upahChatModal .upah-chat-message-meta {
                display: block !important;
                text-align: right !important;
                color: #667781 !important;
                font-size: 9px !important;
                line-height: 12px !important;
                margin-top: 3px !important;
                min-height: 12px;
            }

            #upahChatModal .upah-chat-message {
                visibility: visible !important;
                opacity: 1 !important;
            }

            #upahChatModal .upah-chat-message-time {
                float: right;
                color: #667781;
                font-size: 9px;
                margin: 4px 0 0 8px;
                line-height: 12px;
            }

            #upahChatModal .upah-chat-typing {
                min-height: 25px;
                padding: 0 13px 5px;
                background: #efeae2;
                color: #667781;
                font-size: 11px;
                font-style: italic;
                display: none;
                flex-shrink: 0;
            }

            #upahChatModal .upah-chat-typing.show {
                display: block;
            }

            #upahChatModal .upah-typing-dots {
                display: inline-flex;
                gap: 2px;
                margin-left: 2px;
                vertical-align: middle;
            }

            #upahChatModal .upah-typing-dots span {
                width: 4px;
                height: 4px;
                border-radius: 50%;
                background: #667781;
                animation: upahTypingDot 1.2s infinite ease-in-out;
            }

            #upahChatModal .upah-typing-dots span:nth-child(2) {
                animation-delay: .15s;
            }

            #upahChatModal .upah-typing-dots span:nth-child(3) {
                animation-delay: .30s;
            }

            @keyframes upahTypingDot {
                0%, 60%, 100% { transform: translateY(0); opacity: .45; }
                30% { transform: translateY(-3px); opacity: 1; }
            }

            #upahChatModal .upah-chat-input-area {
                display: flex;
                align-items: flex-end;
                gap: 7px;
                padding: 9px;
                background: #f0f2f5;
                flex-shrink: 0;
            }

            #upahChatModal .upah-chat-input-wrap {
                flex: 1;
                min-width: 0;
                background: #fff;
                border-radius: 22px;
                display: flex;
                align-items: center;
                padding: 0 12px;
            }

            #upahChatModal #upahChatInput {
                width: 100%;
                min-height: 40px;
                max-height: 105px;
                resize: none;
                border: 0 !important;
                outline: 0 !important;
                box-shadow: none !important;
                background: transparent !important;
                padding: 10px 0 !important;
                font-size: 13px;
                color: #111b21;
            }

            #upahChatModal .upah-chat-send {
                width: 42px;
                height: 42px;
                border: 0;
                border-radius: 50%;
                background: #075e54;
                color: #fff;
                cursor: pointer;
                display: flex;
                align-items: center;
                justify-content: center;
                flex-shrink: 0;
                font-size: 17px;
                transition: transform .12s ease, opacity .12s ease;
            }

            #upahChatModal .upah-chat-send:hover {
                transform: scale(1.04);
            }

            #upahChatModal .upah-chat-send:active {
                transform: scale(.96);
            }


            #upahChatModal .upah-chat-image {
                display: block;
                max-width: 260px;
                max-height: 260px;
                width: auto;
                height: auto;
                border-radius: 8px;
                margin-top: 4px;
                cursor: pointer;
                object-fit: cover;
            }

            #upahChatModal .upah-chat-image-preview {
                display: none;
                padding: 6px 9px;
                background: #f0f2f5;
                border-top: 1px solid rgba(0,0,0,.06);
            }

            #upahChatModal .upah-chat-image-preview.show {
                display: flex;
                align-items: center;
                gap: 8px;
            }

            #upahChatModal .upah-chat-image-preview img {
                width: 54px;
                height: 54px;
                object-fit: cover;
                border-radius: 7px;
            }

            #upahChatModal .upah-chat-image-preview-info {
                flex: 1;
                min-width: 0;
                font-size: 11px;
                color: #667781;
            }

            #upahChatModal .upah-chat-image-remove {
                border: 0;
                background: transparent;
                color: #667781;
                font-size: 20px;
                cursor: pointer;
            }

            #upahChatModal .upah-chat-attach {
                width: 38px;
                height: 38px;
                border: 0;
                background: transparent;
                color: #54656f;
                cursor: pointer;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 18px;
                flex-shrink: 0;
            }

            #upahChatModal .upah-chat-attach:hover {
                background: rgba(0,0,0,.06);
            }

            @media (max-width: 600px) {
                #upahChatModal .upah-chat-window {
                    right: 8px;
                    bottom: 8px;
                    width: calc(100vw - 16px);
                    height: calc(100vh - 16px);
                    border-radius: 14px;
                }
            }
        `;

        document.head.appendChild(style);
    }

    function ensureUpahChatModal() {

        // Jangan hanya mengecek #upahChatModal. Pada halaman Upah bisa saja
        // sudah ada modal dengan ID tersebut tetapi isinya berasal dari Blade
        // lama dan belum mempunyai #upahChatMessages.
        let modal = document.getElementById('upahChatModal');

        if (!modal) {
            $('body').append(`
            <div id="upahChatModal" aria-hidden="true">

                <div class="upah-chat-window">

                    <div class="upah-chat-header">

                        <div class="upah-chat-avatar">
                            <i class="fas fa-comments"></i>
                        </div>

                        <div class="upah-chat-title">
                            <strong>Transaksi Upah</strong>
                            <span class="upah-chat-status">
                                Live Chat
                            </span>
                        </div>

                        <button
                            type="button"
                            id="upahChatClose"
                            class="upah-chat-close"
                            aria-label="Close"
                        >
                            &times;
                        </button>

                    </div>

                    <div
                        id="upahChatMessages"
                        class="upah-chat-messages"
                    >
                        <div class="upah-chat-empty">
                            Belum ada pesan.<br>
                            Mulai percakapan dengan user lain.
                        </div>
                    </div>

                    <div
                        id="upahChatTyping"
                        class="upah-chat-typing"
                    ></div>

                    <div id="upahChatImagePreview" class="upah-chat-image-preview">
                        <img id="upahChatImagePreviewImg" src="" alt="Preview">
                        <div id="upahChatImagePreviewInfo" class="upah-chat-image-preview-info">Gambar siap dikirim</div>
                        <button type="button" id="upahChatImageRemove" class="upah-chat-image-remove" title="Hapus gambar">&times;</button>
                    </div>

                    <div class="upah-chat-input-area">

                        <input type="file" id="upahChatImageInput" accept="image/*" style="display:none;">

                        <button type="button" id="upahChatAttach" class="upah-chat-attach" title="Kirim gambar">
                            <i class="fas fa-paperclip"></i>
                        </button>

                        <div class="upah-chat-input-wrap">

                            <textarea
                                id="upahChatInput"
                                rows="1"
                                maxlength="1000"
                                placeholder="Ketik pesan..."
                                autocomplete="off"
                            ></textarea>

                        </div>

                        <button
                            type="button"
                            id="upahChatSend"
                            class="upah-chat-send"
                            title="Kirim"
                        >
                            <i class="fas fa-paper-plane"></i>
                        </button>

                    </div>

                </div>

            </div>
        `);

            modal = document.getElementById('upahChatModal');
        }

        // Repair modal yang sudah ada tetapi elemen chat-nya tidak lengkap.
        if (!modal) {
            console.error('[UPAH CHAT] Gagal membuat #upahChatModal.');
            return;
        }

        if (!document.getElementById('upahChatMessages')) {
            const windowEl = modal.querySelector('.upah-chat-window') || modal;
            const typingEl = document.getElementById('upahChatTyping');
            const inputArea = modal.querySelector('.upah-chat-input-area');

            const messagesEl = document.createElement('div');
            messagesEl.id = 'upahChatMessages';
            messagesEl.className = 'upah-chat-messages';
            messagesEl.innerHTML = '<div class="upah-chat-empty">Belum ada pesan.<br>Mulai percakapan dengan user lain.</div>';

            if (typingEl && typingEl.parentNode === windowEl) {
                windowEl.insertBefore(messagesEl, typingEl);
            } else if (inputArea && inputArea.parentNode === windowEl) {
                windowEl.insertBefore(messagesEl, inputArea);
            } else {
                windowEl.appendChild(messagesEl);
            }

            console.log('[UPAH CHAT] #upahChatMessages diperbaiki/dibuat.');
        }

        // Pastikan typing area juga tersedia.
        if (!document.getElementById('upahChatTyping')) {
            const windowEl = modal.querySelector('.upah-chat-window') || modal;
            const typingEl = document.createElement('div');
            typingEl.id = 'upahChatTyping';
            typingEl.className = 'upah-chat-typing';
            windowEl.appendChild(typingEl);
        }
    }

    function clearEmptyState() {
        $('#upahChatMessages .upah-chat-empty').remove();
    }

    function scrollUpahChatToBottom() {
        const box = document.getElementById('upahChatMessages');

        if (box) {
            box.scrollTop = box.scrollHeight;
        }
    }


    function clearUpahPendingImage() {
        upahPendingImage = null;
        $('#upahChatImageInput').val('');
        $('#upahChatImagePreview')
            .removeClass('show');
        $('#upahChatImagePreviewImg').attr('src', '');
        $('#upahChatImagePreviewInfo').text('');
    }

    function setUpahPendingImage(file) {
        if (!file || !file.type || !file.type.startsWith('image/')) {
            alert('File yang dipilih harus berupa gambar.');
            return;
        }

        if (file.size > 8 * 1024 * 1024) {
            alert('Ukuran gambar maksimal 8 MB.');
            return;
        }

        upahPendingImage = file;

        const reader = new FileReader();
        reader.onload = function (e) {
            $('#upahChatImagePreviewImg').attr('src', e.target.result);
            $('#upahChatImagePreviewInfo').text(
                file.name + ' • ' + Math.round(file.size / 1024) + ' KB'
            );
            $('#upahChatImagePreview').addClass('show');
            $('#upahChatInput').trigger('focus');
        };
        reader.readAsDataURL(file);
    }

    async function uploadUpahChatImage(file) {
        const csrf = $('meta[name="csrf-token"]').attr('content') || '';
        const formData = new FormData();
        formData.append('image', file);
        formData.append('_token', csrf);

        const response = await fetch(UPAH_CHAT_UPLOAD_URL, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json'
            },
            body: formData,
            credentials: 'same-origin'
        });

        if (!response.ok) {
            const text = await response.text();
            throw new Error('Upload gambar gagal (' + response.status + '): ' + text);
        }

        const result = await response.json();

        if (!result.success || !result.url) {
            throw new Error(result.message || 'URL gambar tidak diterima dari server.');
        }

        return result.url;
    }

    function appendUpahChatMessage(data) {

        console.log('[UPAH CHAT] RENDER MESSAGE:', data);

        // Pastikan modal dan container selalu tersedia, termasuk ketika
        // pesan diterima sebelum user menekan Ctrl+Shift+Z.
        ensureUpahChatModal();

        let box = document.getElementById('upahChatMessages');

        if (!box && document.body) {
            console.warn('[UPAH CHAT] Container pesan belum ada, membuat ulang modal.');
            ensureUpahChatModal();
            box = document.getElementById('upahChatMessages');
        }

        if (!box) {
            console.error('[UPAH CHAT] #upahChatMessages tidak ditemukan saat render.');
            return;
        }

        if (!data || typeof data !== 'object') {
            console.warn('[UPAH CHAT] Payload message tidak valid:', data);
            return;
        }

        const message = String(data.message ?? '').trim();
        const imageUrl = String(data.image_url ?? '').trim();

        if (!message && !imageUrl) {
            console.warn('[UPAH CHAT] Message kosong:', data);
            return;
        }

        clearEmptyState();

        const senderId = String(data.user_id ?? '');
        const name = escapeUpahChatHtml(data.name || 'User');
        const safeMessage = escapeUpahChatHtml(message);
        const safeImageUrl = escapeUpahChatHtml(imageUrl);
        const isMine = senderId === currentUserId;

        const timestamp = Number(data.timestamp);
        const date = Number.isFinite(timestamp) && timestamp > 0
            ? new Date(timestamp)
            : new Date();

        const time = date.toLocaleTimeString('id-ID', {
            hour: '2-digit',
            minute: '2-digit'
        });

        const messageEl = document.createElement('div');
        messageEl.className = 'upah-chat-message ' + (isMine ? 'mine' : 'other');

        const nameEl = document.createElement('div');
        nameEl.className = 'upah-chat-message-name';
        nameEl.textContent = data.name || 'User';
        messageEl.appendChild(nameEl);

        if (message) {
            const textEl = document.createElement('div');
            textEl.className = 'upah-chat-message-content';
            textEl.textContent = message;
            messageEl.appendChild(textEl);
        }

        if (imageUrl) {
            const image = document.createElement('img');
            image.className = 'upah-chat-image';
            image.src = imageUrl;
            image.alt = 'Gambar';
            image.loading = 'lazy';
            image.addEventListener('click', function () {
                window.open(imageUrl, '_blank', 'noopener');
            });
            messageEl.appendChild(image);
        }

        const meta = document.createElement('div');
        meta.className = 'upah-chat-message-meta';
        meta.textContent = time + (isMine ? '  ✓✓' : '');
        messageEl.appendChild(meta);

        box.appendChild(messageEl);

        // Paksa browser melakukan layout sebelum scroll.
        requestAnimationFrame(function () {
            box.scrollTop = box.scrollHeight;
        });

        console.log(
            '[UPAH CHAT] MESSAGE RENDERED:',
            { senderId: senderId, mine: isMine, message: message }
        );
    }

    function renderUpahTyping() {

        const typingBox = $('#upahChatTyping');

        if (!typingBox.length) {
            return;
        }

        const now = Date.now();

        Object.keys(upahTypingUsers).forEach(function (userId) {

            if (
                now - upahTypingUsers[userId].lastSeen >
                3500
            ) {
                delete upahTypingUsers[userId];
            }
        });

        const names = Object.keys(upahTypingUsers)
            .map(function (id) {
                return upahTypingUsers[id].name;
            })
            .filter(Boolean);

        if (!names.length) {

            typingBox
                .removeClass('show')
                .html('');

            return;
        }

        let text;

        if (names.length === 1) {

            text = escapeUpahChatHtml(names[0]) +
                ' sedang mengetik';

        } else if (names.length === 2) {

            text =
                escapeUpahChatHtml(names[0]) +
                ' dan ' +
                escapeUpahChatHtml(names[1]) +
                ' sedang mengetik';

        } else {

            text =
                names.length +
                ' orang sedang mengetik';
        }

        typingBox
            .addClass('show')
            .html(`
                ${text}
                <span class="upah-typing-dots">
                    <span></span>
                    <span></span>
                    <span></span>
                </span>
            `);
    }

    function receiveTyping(data) {

        if (!data) {
            return;
        }

        const userId = String(data.user_id ?? '');

        if (!userId || userId === currentUserId) {
            return;
        }

        if (data.typing === false) {

            delete upahTypingUsers[userId];

        } else {

            upahTypingUsers[userId] = {
                name: data.name || 'User',
                lastSeen: Date.now()
            };
        }

        renderUpahTyping();
    }

    function sendTypingState(isTyping) {

        const channel = getUpahChatChannel();

        if (!channel || !upahChatSubscribed) {
            return;
        }

        try {

            channel.trigger(UPAH_TYPING_EVENT, {
                user_id: currentUserId,
                name: currentUserName,
                typing: !!isTyping,
                timestamp: Date.now()
            });

        } catch (error) {

            console.warn(
                '[UPAH CHAT] typing trigger gagal:',
                error
            );
        }
    }

    function stopTyping() {

        if (!upahTypingActive) {
            return;
        }

        upahTypingActive = false;

        if (upahTypingTimer) {
            clearTimeout(upahTypingTimer);
            upahTypingTimer = null;
        }

        sendTypingState(false);
    }

    function handleTypingInput() {

        if (!upahChatSubscribed) {
            return;
        }

        if (!upahTypingActive) {

            upahTypingActive = true;

            sendTypingState(true);
        }

        if (upahTypingTimer) {
            clearTimeout(upahTypingTimer);
        }

        upahTypingTimer = setTimeout(function () {

            stopTyping();

        }, 1800);
    }

    function bindUpahChat() {

        const channel = getUpahChatChannel();

        if (!channel) {
            return false;
        }

        if (upahChatInitialized) {
            return true;
        }

        console.log(
            '[UPAH CHAT] Binding channel:',
            window.upahRealtimeChannelName || channel.name
        );

        /*
         * PENTING:
         * subscription_succeeded menentukan bahwa client benar-benar
         * sudah masuk ke presence-upah-transaksi.
         */
        channel.bind(
            'pusher:subscription_succeeded',
            function (members) {

                upahChatSubscribed = true;

                console.log(
                    '[UPAH CHAT] Pusher subscription READY:',
                    window.upahRealtimeChannelName || channel.name,
                    'members:',
                    members ? members.count : '?'
                );

                const status = $('#upahChatModal .upah-chat-status');

                if (status.length) {
                    status.text('online • Live Chat');
                }
            }
        );

        /*
         * MESSAGE
         */
        channel.bind(
            UPAH_CHAT_EVENT,
            function (data) {

                console.log(
                    '[UPAH CHAT] MESSAGE RECEIVED:',
                    data
                );

                appendUpahChatMessage(data);

                // AUTO OPEN: pesan dari user lain langsung membuka chat.
                // Gunakan currentUserId (variabel yang memang tersedia di chat).
                if (String(data.user_id) !== String(currentUserId)) {
                    setTimeout(function () {
                        if (typeof window.openUpahChat === 'function') {
                            window.openUpahChat();
                        } else {
                            // Fallback jika fungsi global belum siap.
                            ensureUpahChatModal();
                            const modal = $('#upahChatModal');
                            if (modal.length) {
                                modal.addClass('show')
                                    .css('display', 'block')
                                    .attr('aria-hidden', 'false');
                                setTimeout(function () {
                                    $('#upahChatInput').trigger('focus');
                                    scrollUpahChatToBottom();
                                }, 50);
                            }
                        }
                    }, 50);
                }
            }
        );

        /*
         * TYPING
         */
        channel.bind(
            UPAH_TYPING_EVENT,
            function (data) {

                console.log(
                    '[UPAH CHAT] TYPING RECEIVED:',
                    data
                );

                receiveTyping(data);
            }
        );

        /*
         * DEBUG ERROR
         */
        channel.bind(
            'pusher:subscription_error',
            function (status) {

                upahChatSubscribed = false;

                console.error(
                    '[UPAH CHAT] Subscription error:',
                    status
                );
            }
        );

        upahChatInitialized = true;

        console.log(
            '[UPAH CHAT] Event listener berhasil dipasang.'
        );

        return true;
    }

    window.sendUpahChat = async function () {

        const input = $('#upahChatInput');

        if (!input.length) {
            return;
        }

        const message = String(
            input.val() || ''
        ).trim();

        if (!message && !upahPendingImage) {
            return;
        }

        const channel = getUpahChatChannel();

        if (!channel) {
            console.error(
                '[UPAH CHAT] Channel Pusher tidak ditemukan.'
            );
            return;
        }

        if (!upahChatSubscribed) {
            console.warn(
                '[UPAH CHAT] Channel belum subscribed. Pesan tidak dikirim.'
            );
            return;
        }

        if (upahImageUploading) {
            return;
        }

        stopTyping();

        let imageUrl = '';

        try {

            if (upahPendingImage) {

                upahImageUploading = true;

                $('#upahChatSend')
                    .prop('disabled', true)
                    .css('opacity', '.6');

                $('#upahChatImagePreviewInfo')
                    .text('Mengunggah gambar...');

                imageUrl = await uploadUpahChatImage(
                    upahPendingImage
                );
            }

            const data = {
                user_id: currentUserId,
                name: currentUserName,
                message: message,
                image_url: imageUrl,
                timestamp: Date.now()
            };

            console.log(
                '[UPAH CHAT] SEND:',
                data
            );

            channel.trigger(
                UPAH_CHAT_EVENT,
                data
            );

            appendUpahChatMessage(data);

            input
                .val('')
                .css('height', 'auto')
                .trigger('focus');

            clearUpahPendingImage();

        } catch (error) {

            console.error(
                '[UPAH CHAT] Kirim/upload gagal:',
                error
            );

            alert(
                'Gagal mengirim pesan/gambar. Periksa route upload gambar.'
            );

        } finally {

            upahImageUploading = false;

            $('#upahChatSend')
                .prop('disabled', false)
                .css('opacity', '');
        }
    };

    window.openUpahChat = function () {

        ensureUpahChatModal();

        const modal = $('#upahChatModal');

        if (!modal.length) {
            return;
        }

        modal
            .addClass('show')
            .css('display', 'block')
            .attr('aria-hidden', 'false');

        setTimeout(function () {

            $('#upahChatInput')
                .trigger('focus');

            scrollUpahChatToBottom();

        }, 100);
    };

    window.closeUpahChat = function () {

        const modal = $('#upahChatModal');

        if (!modal.length) {
            return;
        }

        stopTyping();

        modal
            .removeClass('show')
            .css('display', '')
            .attr('aria-hidden', 'true');

        $('#upahChatInput').val('');
    };


    /*
     * Attachment button.
     */
    $(document).on(
        'click',
        '#upahChatAttach',
        function (e) {
            e.preventDefault();
            $('#upahChatImageInput').trigger('click');
        }
    );

    $(document).on(
        'change',
        '#upahChatImageInput',
        function () {
            if (this.files && this.files[0]) {
                setUpahPendingImage(this.files[0]);
            }
        }
    );

    $(document).on(
        'click',
        '#upahChatImageRemove',
        function (e) {
            e.preventDefault();
            clearUpahPendingImage();
            $('#upahChatInput').trigger('focus');
        }
    );

    /*
     * WhatsApp-style paste image from clipboard.
     * Ctrl+V screenshot / copied image => preview => Send.
     */
    $(document).on(
        'paste',
        '#upahChatInput',
        function (e) {

            const clipboard = e.originalEvent && e.originalEvent.clipboardData;

            if (!clipboard || !clipboard.items) {
                return;
            }

            for (let i = 0; i < clipboard.items.length; i++) {

                const item = clipboard.items[i];

                if (item.type && item.type.indexOf('image/') === 0) {

                    const file = item.getAsFile();

                    if (file) {
                        e.preventDefault();
                        setUpahPendingImage(file);
                    }

                    return;
                }
            }
        }
    );

    function initUpahChat() {

        injectUpahChatStyle();
        ensureUpahChatModal();

        if (bindUpahChat()) {
            return;
        }

        let attempts = 0;

        const timer = setInterval(function () {

            attempts++;

            if (bindUpahChat()) {

                clearInterval(timer);
                return;
            }

            if (attempts >= 80) {

                clearInterval(timer);

                console.error(
                    '[UPAH CHAT] Channel Pusher tidak ditemukan setelah 20 detik.'
                );
            }

        }, 250);
    }

    /*
     * Ctrl + Shift + Z
     */
    window.addEventListener(
        'keydown',
        function (e) {

            if (
                e.ctrlKey &&
                e.shiftKey &&
                e.code === 'KeyZ'
            ) {

                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();

                window.openUpahChat();

                return false;
            }

            if (
                e.key === 'Escape' &&
                $('#upahChatModal').hasClass('show')
            ) {

                e.preventDefault();

                window.closeUpahChat();
            }

        },
        true
    );

    /*
     * Close
     */
    $(document).on(
        'click',
        '#upahChatClose',
        function (e) {

            e.preventDefault();
            e.stopPropagation();

            window.closeUpahChat();
        }
    );

    /*
     * Send
     */
    $(document).on(
        'click',
        '#upahChatSend',
        function (e) {

            e.preventDefault();
            e.stopPropagation();

            window.sendUpahChat();
        }
    );

    /*
     * Input:
     * - Enter = send
     * - Shift + Enter = new line
     * - Ctrl + Enter = send
     */
    $(document).on(
        'keydown',
        '#upahChatInput',
        function (e) {

            if (
                e.key === 'Enter' &&
                !e.shiftKey
            ) {

                e.preventDefault();

                window.sendUpahChat();

                return;
            }

            if (
                e.key === 'Enter' &&
                e.ctrlKey
            ) {

                e.preventDefault();

                window.sendUpahChat();
            }
        }
    );

    /*
     * Detect typing realtime.
     */
    $(document).on(
        'input',
        '#upahChatInput',
        function () {

            const value = String(
                $(this).val() || ''
            ).trim();

            if (value.length > 0) {

                handleTypingInput();

            } else {

                stopTyping();
            }

            /*
             * Auto resize textarea seperti WhatsApp.
             */
            this.style.height = 'auto';
            this.style.height =
                Math.min(this.scrollHeight, 105) + 'px';
        }
    );

    /*
     * Jika pindah tab / window, kirim typing=false.
     */
    $(window).on(
        'blur',
        function () {
            stopTyping();
        }
    );

    $(document).ready(function () {

        initUpahChat();

        /*
         * Refresh typing indicator setiap 1 detik
         * supaya user otomatis hilang setelah timeout.
         */
        setInterval(function () {
            renderUpahTyping();
        }, 1000);
    });

})();
</script>