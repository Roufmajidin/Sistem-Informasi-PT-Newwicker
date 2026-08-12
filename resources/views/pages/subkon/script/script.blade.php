<script>
    $(document).ready(function() {

        /*
        |--------------------------------------------------------------------------
        | URL
        |--------------------------------------------------------------------------
        */

        const articleUrl =
            "{{ route('subkon.ajax.articles') }}";

        const supplierUrl =
            "{{ route('subkon.ajax.suppliers') }}";

        const kategoriUrl =
            "{{ route('subkon.ajax.kategori') }}";

        const storeUrl =
            "{{ route('subkon.store') }}";


        /*
        |--------------------------------------------------------------------------
        | OPEN MODAL
        |--------------------------------------------------------------------------
        */

        $('#btnTambahKontrak').on('click', function() {

            resetFormKontrak();

            $('#modalTambahKontrak').modal('show');

            setTimeout(function() {
                $('#article_code').focus();
            }, 400);

        });


        /*
        |--------------------------------------------------------------------------
        | RESET FORM
        |--------------------------------------------------------------------------
        */

       
        /*
        |--------------------------------------------------------------------------
        | ARTICLE SEARCH
        |--------------------------------------------------------------------------
        */

        let articleTimer = null;

        $('#article_code').on('input', function() {

            const keyword = $(this).val().trim();

            $('#detail_po_id').val('');

            $('#articleInfo').addClass('d-none');

            clearTimeout(articleTimer);

            if (keyword.length < 2) {

                $('#articleSearchResult')
                    .empty()
                    .removeClass('show');

                return;
            }

            articleTimer = setTimeout(function() {

                $.ajax({

                    url: articleUrl,

                    type: 'GET',

                    data: {
                        q: keyword
                    },

                    beforeSend: function() {

                        $('#articleSearchResult')
                            .html(
                                '<div class="search-empty">' +
                                'Mencari article...' +
                                '</div>'
                            )
                            .addClass('show');

                    },

                    success: function(response) {

                        let html = '';

                        if (!response.length) {

                            html =
                                '<div class="search-empty">' +
                                'Article tidak ditemukan.<br>' +
                                '<small>Article code dapat diketik manual.</small>' +
                                '</div>';

                        } else {

                            response.forEach(function(item) {

                                html += `
                                <div class="search-item article-item"
                                     data-id="${item.id}"
                                     data-article="${escapeHtml(item.article_code)}"
                                     data-description="${escapeHtml(item.description || '')}"
                                     data-qty="${item.qty || 0}"
                                     data-finishing="${escapeHtml(item.finishing || '')}">

                                    <div class="search-item-title">
                                        ${escapeHtml(item.article_code)}
                                    </div>

                                    <div class="search-item-meta">
                                        ${escapeHtml(item.description || '-')}
                                    </div>


                                </div>
                            `;

                            });

                        }

                        $('#articleSearchResult')
                            .html(html)
                            .addClass('show');

                    },

                    error: function() {

                        $('#articleSearchResult')
                            .html(
                                '<div class="search-empty text-danger">' +
                                'Gagal mencari article.' +
                                '</div>'
                            )
                            .addClass('show');

                    }

                });

            }, 300);

        });


        /*
        |--------------------------------------------------------------------------
        | SELECT ARTICLE
        |--------------------------------------------------------------------------
        */

        $(document).on(
            'click',
            '.article-item',
            function() {

                const item = $(this);

                $('#detail_po_id').val(
                    item.data('id')
                );

                $('#article_code').val(
                    item.data('article')
                );
                $('#description').val(
                    item.data('description') || ''
                );
                $('#articleDescription').text(
                    item.data('description') || '-'
                );

                // $('#articleQty').text(
                //     item.data('qty') || '-'
                // );

                $('#articleFinishing').text(
                    item.data('finishing') || '-'
                );

                $('#articleInfo').removeClass('d-none');

                $('#articleSearchResult')
                    .empty()
                    .removeClass('show');

            }
        );


        /*
        |--------------------------------------------------------------------------
        | SUPPLIER SEARCH
        |--------------------------------------------------------------------------
        */

        let supplierTimer = null;

        $('#supplier_search').on('input', function() {

            const keyword = $(this).val().trim();

            $('#supplier_id').val('');

            clearTimeout(supplierTimer);

            if (keyword.length < 1) {

                $('#supplierSearchResult')
                    .empty()
                    .removeClass('show');

                return;
            }

            supplierTimer = setTimeout(function() {

                $.ajax({

                    url: supplierUrl,

                    type: 'GET',

                    data: {
                        q: keyword
                    },

                    success: function(response) {

                        let html = '';

                        if (!response.length) {

                            html =
                                '<div class="search-empty">' +
                                'Supplier tidak ditemukan.' +
                                '</div>';

                        } else {

                            response.forEach(function(item) {

                                html += `
                                <div class="search-item supplier-item"
                                     data-id="${item.id}"
                                     data-name="${escapeHtml(item.text)}">

                                    <div class="search-item-title">
                                        ${escapeHtml(item.text)}
                                    </div>

                                    <div class="search-item-meta">
                                        Jenis:
                                        ${escapeHtml(item.jenis || '-')}
                                    </div>

                                    <div class="search-item-meta">
                                        Kategori:
                                        ${escapeHtml(item.kategori || '-')}
                                    </div>

                                </div>
                            `;

                            });

                        }

                        $('#supplierSearchResult')
                            .html(html)
                            .addClass('show');

                    }

                });

            }, 300);

        });


        /*
        |--------------------------------------------------------------------------
        | SELECT SUPPLIER
        |--------------------------------------------------------------------------
        */

        $(document).on(
            'click',
            '.supplier-item',
            function() {

                const item = $(this);

                $('#supplier_id').val(
                    item.data('id')
                );

                $('#supplier_search').val(
                    item.data('name')
                );

                $('#supplierSearchResult')
                    .empty()
                    .removeClass('show');

            }
        );


        /*
        |--------------------------------------------------------------------------
        | KATEGORI SEARCH
        |--------------------------------------------------------------------------
        */

        let kategoriTimer = null;

        $('#kategori_search').on('input', function() {

            const keyword = $(this).val().trim();

            $('#kategori').val('');

            clearTimeout(kategoriTimer);

            if (keyword.length < 1) {

                $('#kategoriSearchResult')
                    .empty()
                    .removeClass('show');

                return;
            }

            kategoriTimer = setTimeout(function() {

                $.ajax({

                    url: kategoriUrl,

                    type: 'GET',

                    data: {
                        q: keyword
                    },

                    success: function(response) {

                        let html = '';

                        if (!response.length) {

                            html =
                                '<div class="search-empty">' +
                                'Kategori tidak ditemukan.' +
                                '</div>';

                        } else {

                            response.forEach(function(item) {

                                html += `
                                <div class="search-item kategori-item"
                                     data-id="${escapeHtml(item.id)}"
                                     data-name="${escapeHtml(item.text)}">

                                    <div class="search-item-title">
                                        ${escapeHtml(item.text)}
                                    </div>

                                </div>
                            `;

                            });

                        }

                        $('#kategoriSearchResult')
                            .html(html)
                            .addClass('show');

                    }

                });

            }, 300);

        });


        /*
        |--------------------------------------------------------------------------
        | SELECT KATEGORI
        |--------------------------------------------------------------------------
        */

     $(document).on(
    'click',
    '.kategori-item',
    function() {

        const item = $(this);

        const kategori = item.data('id');

        console.log('Kategori dipilih:', kategori);

        // nilai yang dikirim ke Laravel
        $('#kategori').val(kategori);

        // nilai yang ditampilkan di input
        $('#kategori_search').val(kategori);

        // tutup hasil pencarian
        $('#kategoriSearchResult')
            .empty()
            .removeClass('show');

    }
);
        /*
        |--------------------------------------------------------------------------
        | SAVE
        |--------------------------------------------------------------------------
        */

     $('#formTambahKontrak').on('submit', function (e) {

    e.preventDefault();

    const form = $(this);

    const id = $('#subkon_id').val();

    const article = $('#article_code').val().trim();
    const supplier = $('#supplier_id').val();
    const harga = $('#harga_kontrak').val();

    if (!article) {
        showError('Article Code wajib diisi.');
        return;
    }

    if (!supplier) {
        showError('Supplier wajib dipilih.');
        return;
    }

    if (!harga || parseFloat(harga) < 0) {
        showError('Harga kontrak wajib diisi.');
        return;
    }


    /*
    |--------------------------------------------------------------------------
    | TENTUKAN CREATE / UPDATE
    |--------------------------------------------------------------------------
    */

    let url;
    let method;

    if (id) {

        // EDIT
        url = "{{ url('/subkon') }}/" + id;

        method = 'PUT';

    } else {

        // CREATE
        url = "{{ route('subkon.store') }}";

        method = 'POST';
    }


    /*
    |--------------------------------------------------------------------------
    | BUTTON
    |--------------------------------------------------------------------------
    */

    const button = $('#btnSimpanKontrak');

    button
        .prop('disabled', true)
        .html(
            '<i class="fas fa-spinner fa-spin"></i> ' +
            (id ? 'Mengupdate...' : 'Menyimpan...')
        );


    /*
    |--------------------------------------------------------------------------
    | DATA
    |--------------------------------------------------------------------------
    */

    const formData = new FormData(
        document.getElementById('formTambahKontrak')
    );

    /*
     * Kalau update, Laravel perlu _method PUT
     */
    if (id) {
        formData.append('_method', 'PUT');
    }


    /*
    |--------------------------------------------------------------------------
    | AJAX
    |--------------------------------------------------------------------------
    */

    $.ajax({

        url: url,

        type: 'POST',

        data: formData,

        processData: false,

        contentType: false,

        success: function (response) {

            if (response.success) {

                $('#modalTambahKontrak')
                    .modal('hide');

                location.reload();

            }

        },

        error: function (xhr) {

            console.error(xhr);

            let message =
                'Terjadi kesalahan saat menyimpan.';

            if (
                xhr.responseJSON &&
                xhr.responseJSON.errors
            ) {

                message = Object.values(
                    xhr.responseJSON.errors
                )
                .flat()
                .join('<br>');

            } else if (
                xhr.responseJSON &&
                xhr.responseJSON.message
            ) {

                message =
                    xhr.responseJSON.message;
            }

            showError(message);

            button
                .prop('disabled', false)
                .html(
                    '<i class="fas fa-save"></i> ' +
                    (id
                        ? 'Update Kontrak'
                        : 'Simpan Kontrak')
                );

        }

    });

});

        /*
        |--------------------------------------------------------------------------
        | CLOSE DROPDOWN
        |--------------------------------------------------------------------------
        */

        $(document).on('click', function(e) {

            if (!$(e.target).closest(
                    '#article_code, #articleSearchResult'
                ).length) {

                $('#articleSearchResult')
                    .removeClass('show');

            }

            if (!$(e.target).closest(
                    '#supplier_search, #supplierSearchResult'
                ).length) {

                $('#supplierSearchResult')
                    .removeClass('show');

            }

            if (!$(e.target).closest(
                    '#kategori_search, #kategoriSearchResult'
                ).length) {

                $('#kategoriSearchResult')
                    .removeClass('show');

            }

        });


        /*
        |--------------------------------------------------------------------------
        | ERROR
        |--------------------------------------------------------------------------
        */

        function showError(message) {
            $('#formTambahKontrakError')
                .html(message)
                .removeClass('d-none');
        }


        /*
        |--------------------------------------------------------------------------
        | ESCAPE HTML
        |--------------------------------------------------------------------------
        */

        function escapeHtml(value) {
            return $('<div>')
                .text(value ?? '')
                .html();
        }

    });
    // edit
    /*
    |--------------------------------------------------------------------------
    | EDIT KONTRAK
    |--------------------------------------------------------------------------
    */

    $(document).on(
        'click',
        '.btn-edit-kontrak',
        function() {

            const id = $(this).data('id');

            resetFormKontrak();

            $('#modalKontrakTitle').text(
                'Edit Kontrak Supplier'
            );

            $('#btnSimpanKontrak').html(
                '<i class="fas fa-save"></i> Update Kontrak'
            );

            $('#btnSimpanKontrak')
                .prop('disabled', true);


            $.ajax({

                url: "{{ url('/subkon') }}/" +
                    id +
                    "/data",

                type: 'GET',

                beforeSend: function() {

                    $('#article_code').prop(
                        'disabled',
                        true
                    );

                    $('#supplier_search').prop(
                        'disabled',
                        true
                    );

                    $('#kategori_search').prop(
                        'disabled',
                        true
                    );

                    $('#harga_kontrak').prop(
                        'disabled',
                        true
                    );

                    $('#remark').prop(
                        'disabled',
                        true
                    );

                },

                success: function(response) {

                    if (!response.success) {
                        return;
                    }

                    const data =
                        response.data;


                    /*
                    |--------------------------------------------------------------------------
                    | ID
                    |--------------------------------------------------------------------------
                    */

                    $('#subkon_id').val(
                        data.id
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | ARTICLE
                    |--------------------------------------------------------------------------
                    */

                    $('#article_code').val(
                        data.article_code || ''
                    );

                    $('#detail_po_id').val(
                        data.detail_po_id || ''
                    );

                    $('#description').val(
                        data.description || ''
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | TAMPILKAN INFO ARTICLE
                    |--------------------------------------------------------------------------
                    */

                    $('#articleDescription').text(
                        data.description || '-'
                    );

                    $('#articleInfo')
                        .removeClass('d-none');


                    /*
                    |--------------------------------------------------------------------------
                    | SUPPLIER
                    |--------------------------------------------------------------------------
                    */

                    $('#supplier_id').val(
                        data.supplier_id || ''
                    );

                    $('#supplier_search').val(
                        data.supplier_name || ''
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | KATEGORI
                    |--------------------------------------------------------------------------
                    */

                    $('#kategori').val(
                        data.kategori || ''
                    );

                    $('#kategori_search').val(
                        data.kategori || ''
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | HARGA
                    |--------------------------------------------------------------------------
                    */

                    $('#harga_kontrak').val(
                        data.harga_kontrak || 0
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | REMARK
                    |--------------------------------------------------------------------------
                    */

                    $('#remark').val(
                        data.remark || ''
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | ENABLE
                    |--------------------------------------------------------------------------
                    */

                    $('#article_code').prop(
                        'disabled',
                        false
                    );

                    $('#supplier_search').prop(
                        'disabled',
                        false
                    );

                    $('#kategori_search').prop(
                        'disabled',
                        false
                    );

                    $('#harga_kontrak').prop(
                        'disabled',
                        false
                    );

                    $('#remark').prop(
                        'disabled',
                        false
                    );


                    $('#btnSimpanKontrak')
                        .prop('disabled', false);


                    $('#modalTambahKontrak')
                        .modal('show');

                },

                error: function(xhr) {

                    alert(
                        'Gagal mengambil data kontrak.'
                    );

                    console.error(xhr);

                }

            });

        }
    );
  function resetFormKontrak() {

    $('#formTambahKontrak')[0].reset();

    // WAJIB: kembali ke mode CREATE
    $('#subkon_id').val('');

    $('#detail_po_id').val('');

    $('#supplier_id').val('');

    $('#kategori').val('');

    $('#description').val('');

    $('#articleSearchResult')
        .empty()
        .removeClass('show');

    $('#supplierSearchResult')
        .empty()
        .removeClass('show');

    $('#kategoriSearchResult')
        .empty()
        .removeClass('show');

    $('#articleInfo')
        .addClass('d-none');

    $('#formTambahKontrakError')
        .addClass('d-none')
        .empty();

    $('#btnSimpanKontrak')
        .prop('disabled', false)
        .html(
            '<i class="fas fa-save"></i> Simpan Kontrak'
        );
}


</script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    const searchInput = document.getElementById('searchSubkon');
    const clearButton = document.getElementById('clearSearchSubkon');
    const tbody = document.getElementById('subkonTableBody');
    const info = document.getElementById('searchSubkonInfo');

    if (!searchInput || !tbody) {
        console.log('Element search/table tidak ditemukan');
        return;
    }

    function filterTable() {

        const keyword = searchInput.value
            .toLowerCase()
            .trim();

        const rows = tbody.querySelectorAll('tr');

        let total = 0;
        let found = 0;

        rows.forEach(function (row) {

            const cells = row.querySelectorAll('td');

            // Row "Belum ada data"
            if (cells.length === 1 && cells[0].hasAttribute('colspan')) {
                return;
            }

            total++;

            /*
             * Ambil SEMUA isi td di dalam tr
             */
            const text = Array.from(cells)
                .map(function (td) {
                    return td.textContent || '';
                })
                .join(' ')
                .toLowerCase()
                .replace(/\s+/g, ' ')
                .trim();

            /*
             * Cari keyword
             */
            const match =
                keyword === '' ||
                text.includes(keyword);

            row.style.display = match
                ? ''
                : 'none';

            if (match) {
                found++;
            }

        });

        /*
         * Info hasil
         */
        if (keyword === '') {

            info.textContent =
                total + ' data';

        } else {

            info.textContent =
                found + ' dari ' + total + ' data ditemukan';

        }
    }


    /*
     |--------------------------------------------------------------------------
     | SEARCH
     |--------------------------------------------------------------------------
     */

    searchInput.addEventListener(
        'input',
        filterTable
    );


    /*
     |--------------------------------------------------------------------------
     | CLEAR
     |--------------------------------------------------------------------------
     */

    clearButton.addEventListener(
        'click',
        function () {

            searchInput.value = '';

            filterTable();

            searchInput.focus();

        }
    );


    /*
     |--------------------------------------------------------------------------
     | INITIAL
     |--------------------------------------------------------------------------
     */

    filterTable();

});
</script>
