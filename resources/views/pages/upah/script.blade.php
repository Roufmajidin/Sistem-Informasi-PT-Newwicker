<script>

/*
|--------------------------------------------------------------------------
| GLOBAL URL
|--------------------------------------------------------------------------
| Didefinisikan di luar document.ready agar dapat digunakan
| oleh form normal DAN Mass Input yang dibuat secara dinamis.
|--------------------------------------------------------------------------
*/

const storeUrl =
    "{{ route('upah.store') }}";

const articleUrl =
    "{{ route('upah.ajax.articles') }}";


/*
|--------------------------------------------------------------------------
| GLOBAL HELPER
|--------------------------------------------------------------------------
*/
let pekerjaanArticleCache = {};
let pekerjaanRequest = null;
let pekerjaanKeywordTimer = null;

function loadPekerjaanSuggestions(article, keyword = '') {

    article = String(article || '').trim();
    keyword = String(keyword || '').trim().toLowerCase();

    const resultBox = $('#pekerjaanSearchResult');

    if (!resultBox.length) {
        return;
    }

    if (!article) {
        resultBox.empty().removeClass('show');
        return;
    }

    /*
    |--------------------------------------------------------------------------
    | FILTER DARI CACHE JIKA SUDAH PERNAH DIAMBIL
    |--------------------------------------------------------------------------
    */

    if (pekerjaanArticleCache[article]) {
        renderPekerjaanSuggestions(
            pekerjaanArticleCache[article],
            keyword
        );
        return;
    }

    resultBox
        .html(`
            <div class="article-search-loading">
                Memuat jenis pekerjaan...
            </div>
        `)
        .addClass('show');

    if (pekerjaanRequest) {
        pekerjaanRequest.abort();
    }

    pekerjaanRequest = $.ajax({

        url: articleUrl,

        type: 'GET',

        data: {
            q: article
        },

        success: function (response) {

            if (!Array.isArray(response)) {
                response = [];
            }

            /*
            |--------------------------------------------------------------------------
            | SIMPAN RESPONSE ARTICLE
            |--------------------------------------------------------------------------
            */

            pekerjaanArticleCache[article] = response;

            renderPekerjaanSuggestions(
                response,
                keyword
            );
        },

        error: function (xhr, status) {

            if (status === 'abort') {
                return;
            }

            console.error(
                'Pekerjaan search error:',
                xhr
            );

            resultBox
                .html(`
                    <div class="article-search-loading">
                        Gagal memuat jenis pekerjaan.
                    </div>
                `)
                .addClass('show');
        },

        complete: function () {
            pekerjaanRequest = null;
        }

    });
}


function renderPekerjaanSuggestions(response, keyword = '') {

    const resultBox =
        $('#pekerjaanSearchResult');

    if (!resultBox.length) {
        return;
    }

    keyword =
        String(keyword || '')
            .trim()
            .toLowerCase();

    const pekerjaanMap = new Map();

    /*
    |--------------------------------------------------------------------------
    | AMBIL PEKERJAAN + HARGA
    |--------------------------------------------------------------------------
    */

    response.forEach(function (item) {

        let pekerjaanValues = [];

        /*
        | Bentuk:
        | pekerjaan: ["Packing Foam", "Packing Box"]
        */

        if (Array.isArray(item.pekerjaan)) {

            pekerjaanValues =
                item.pekerjaan.map(function (value) {
                    return {
                        pekerjaan: value,
                        harga: item.harga ?? item.price ?? 0
                    };
                });

        }

        /*
        | Bentuk:
        | pekerjaan: "Packing Foam"
        */

        else if (item.pekerjaan) {

            pekerjaanValues.push({
                pekerjaan: item.pekerjaan,
                harga: item.harga ?? item.price ?? 0
            });

        }

        /*
        | Backend lama mungkin menggunakan "jenis"
        */

        else if (item.jenis) {

            pekerjaanValues.push({
                pekerjaan: item.jenis,
                harga: item.harga ?? item.price ?? 0
            });

        }

        pekerjaanValues.forEach(function (data) {

            const nama =
                String(data.pekerjaan || '')
                    .trim();

            if (!nama) {
                return;
            }

            const key =
                nama.toLowerCase();

            /*
            | Simpan pertama kali saja agar tidak duplicate.
            */

            if (!pekerjaanMap.has(key)) {

                pekerjaanMap.set(key, {
                    pekerjaan: nama,
                    harga: data.harga
                });

            }

        });

    });


    let list =
        Array.from(
            pekerjaanMap.values()
        );


    /*
    |--------------------------------------------------------------------------
    | FILTER SAAT USER MENGETIK
    |--------------------------------------------------------------------------
    */

    if (keyword !== '') {

        list = list.filter(function (item) {

            return item.pekerjaan
                .toLowerCase()
                .includes(keyword);

        });

    }


    /*
    |--------------------------------------------------------------------------
    | URUTKAN
    |--------------------------------------------------------------------------
    */

    list.sort(function (a, b) {

        return a.pekerjaan.localeCompare(
            b.pekerjaan,
            'id'
        );

    });


    /*
    |--------------------------------------------------------------------------
    | TIDAK ADA HASIL
    |--------------------------------------------------------------------------
    */

    if (list.length === 0) {

        resultBox
            .html(`
                <div class="article-search-loading">
                    Tidak ada jenis pekerjaan
                    untuk article ini.
                </div>
            `)
            .addClass('show');

        return;
    }


    /*
    |--------------------------------------------------------------------------
    | RENDER
    |--------------------------------------------------------------------------
    */

    let html = '';

    list.forEach(function (item) {

        const hargaNumber =
            Number(
                String(item.harga ?? 0)
                    .replace(/[^0-9.-]/g, '')
            ) || 0;

        const hargaFormatted =
            new Intl.NumberFormat(
                'id-ID'
            ).format(hargaNumber);

        html += `
            <div
                class="pekerjaan-item"
                data-pekerjaan="${escapeHtml(item.pekerjaan)}"
                data-harga="${hargaNumber}"
            >

                <div class="article-item-title">
                    ${escapeHtml(item.pekerjaan)}
                </div>

                <div class="article-item-description">
                    Rp ${hargaFormatted}
                </div>

            </div>
        `;

    });


    resultBox
        .html(html)
        .addClass('show');
}


/*
|--------------------------------------------------------------------------
| FOCUS / CLICK PEKERJAAN
|--------------------------------------------------------------------------
|
| Saat field pekerjaan diklik, langsung tampilkan SEMUA pekerjaan
| berdasarkan article yang sedang dipilih.
|
*/

$(document).on(
    'focus click',
    '#insert_pekerjaan',
    function () {

        const article =
            $('#insert_article')
                .val()
                .trim();

        if (!article) {
            return;
        }

        loadPekerjaanSuggestions(
            article,
            ''
        );
    }
);


/*
|--------------------------------------------------------------------------
| KETIK PEKERJAAN
|--------------------------------------------------------------------------
|
| User tetap bisa mengetik untuk memfilter suggestion.
|
*/

$(document).on(
    'input',
    '#insert_pekerjaan',
    function () {

        const input =
            $(this);

        const article =
            $('#insert_article')
                .val()
                .trim();

        const keyword =
            input.val()
                .trim();

        if (!article) {
            $('#pekerjaanSearchResult')
                .empty()
                .removeClass('show');

            return;
        }

        clearTimeout(
            pekerjaanKeywordTimer
        );

        pekerjaanKeywordTimer =
            setTimeout(function () {

                loadPekerjaanSuggestions(
                    article,
                    keyword
                );

            }, 150);

    }
);


/*
|--------------------------------------------------------------------------
| KLIK HASIL PEKERJAAN
|--------------------------------------------------------------------------
*/

$(document).on(
    'mousedown',
    '.pekerjaan-item',
    function (e) {

        e.preventDefault();

        const item =
            $(this);

        const pekerjaan =
            item.attr(
                'data-pekerjaan'
            ) || '';

        const harga =
            item.attr(
                'data-harga'
            ) || 0;


        /*
        | Isi pekerjaan
        */

        $('#insert_pekerjaan')
            .val(pekerjaan);


        /*
        | Isi harga otomatis
        */

        $('#insert_harga')
            .val(harga);


        /*
        | Trigger perhitungan total
        */

        $('#insert_harga')
            .trigger('input')
            .trigger('change');


        /*
        | Tutup suggestion
        */

        $('#pekerjaanSearchResult')
            .empty()
            .removeClass('show');

    }
);


/*
|--------------------------------------------------------------------------
| CLOSE PEKERJAAN SEARCH KETIKA KLIK DI LUAR
|--------------------------------------------------------------------------
*/

$(document).on(
    'mousedown',
    function (e) {

        if (
            !$(e.target)
                .closest(
                    '#insert_pekerjaan, #pekerjaanSearchResult'
                )
                .length
        ) {

            $('#pekerjaanSearchResult')
                .empty()
                .removeClass('show');

        }

    }
);


function escapeHtml(value) {

    if (value === null || value === undefined) {
        return '';
    }

    return String(value)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}


let upahFormMode = 'create';

$(document).ready(function () {

    let articleTimer = null;


    /*
    |--------------------------------------------------------------------------
    | ESCAPE HTML
    |--------------------------------------------------------------------------
    */



    /*
    |--------------------------------------------------------------------------
    | RESET FORM
    |--------------------------------------------------------------------------
    */

    function resetFormUpah() {

        $('#formUpah')[0].reset();

        $('#upah_id').val('');

        // Kembalikan field ke mode Tambah.
        // Mode Copy/Edit akan mengatur ulang sesuai kebutuhannya.
        upahFormMode = 'create';

        $('#article').prop('readonly', false);
        $('#description').prop('readonly', false);
        $('#jenis').prop('readonly', false);
        $('#harga').prop('readonly', false);

        $('#modalUpahTitle')
            .text('Tambah Upah Borongan');

        $('#btnSimpanUpah')
            .prop('disabled', false)
            .html(
                '<i class="fas fa-save"></i> Simpan'
            );

        $('#formUpahError')
            .addClass('d-none')
            .empty();


        /*
        |--------------------------------------------------------------------------
        | RESET ARTICLE SEARCH
        |--------------------------------------------------------------------------
        */

        $('#articleSearchResult')
            .empty()
            .removeClass('show');

        $('#articleNotFound')
            .addClass('d-none');

        $('#clearArticle')
            .removeClass('show');

        clearTimeout(articleTimer);
    }


    /*
    |--------------------------------------------------------------------------
    | ARTICLE SEARCH
    |--------------------------------------------------------------------------
    */

    $('#article').on('input', function () {

        const input =
            $(this);

        const keyword =
            input.val().trim();


        /*
        |--------------------------------------------------------------------------
        | CLEAR HASIL SEBELUMNYA
        |--------------------------------------------------------------------------
        */

        $('#articleSearchResult')
            .empty()
            .removeClass('show');

        $('#articleNotFound')
            .addClass('d-none');


        /*
        |--------------------------------------------------------------------------
        | CLEAR BUTTON
        |--------------------------------------------------------------------------
        */

        if (keyword !== '') {

            $('#clearArticle')
                .addClass('show');

        } else {

            $('#clearArticle')
                .removeClass('show');
        }


        /*
        |--------------------------------------------------------------------------
        | DESCRIPTION
        |--------------------------------------------------------------------------
        |
        | Ketika user mengetik article baru,
        | description hasil pencarian sebelumnya
        | dikosongkan.
        |
        */

        $('#description').val('');


        clearTimeout(articleTimer);


        /*
        |--------------------------------------------------------------------------
        | MINIMUM SEARCH
        |--------------------------------------------------------------------------
        */

        if (keyword.length < 2) {
            return;
        }


        /*
        |--------------------------------------------------------------------------
        | AJAX DELAY 300ms
        |--------------------------------------------------------------------------
        */

        articleTimer = setTimeout(function () {

            $.ajax({

                url: articleUrl,

                type: 'GET',

                data: {
                    q: keyword
                },


                /*
                |--------------------------------------------------------------------------
                | SUCCESS
                |--------------------------------------------------------------------------
                */

                success: function (response) {

                    /*
                    |--------------------------------------------------------------------------
                    | Pastikan response array
                    |--------------------------------------------------------------------------
                    */

                    if (!Array.isArray(response)) {
                        response = [];
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | TIDAK DITEMUKAN
                    |--------------------------------------------------------------------------
                    */

                    if (response.length === 0) {

                        $('#articleSearchResult')
                            .empty()
                            .removeClass('show');


                        $('#articleNotFound')
                            .removeClass('d-none');


                        return;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | DITEMUKAN
                    |--------------------------------------------------------------------------
                    */

                    $('#articleNotFound')
                        .addClass('d-none');


                    let html = '';


                    response.forEach(function (item) {

                        const article =
                            item.article || '';

                        const description =
                            item.description || '';


                        html += `
                            <div
                                class="article-item"
                                data-article="${escapeHtml(article)}"
                                data-description="${escapeHtml(description)}"
                            >

                                <div class="article-item-title">
                                    ${escapeHtml(article)}
                                </div>

                                <div class="article-item-description">
                                    ${escapeHtml(description || '-')}
                                </div>

                            </div>
                        `;

                    });


                    $('#articleSearchResult')
                        .html(html)
                        .addClass('show');

                },


                /*
                |--------------------------------------------------------------------------
                | ERROR
                |--------------------------------------------------------------------------
                */

                error: function (xhr) {

                    console.error(
                        'Article search error:',
                        xhr
                    );


                    $('#articleSearchResult')
                        .empty()
                        .removeClass('show');


                    $('#articleNotFound')
                        .removeClass('d-none');

                }

            });

        }, 300);

    });


    /*
    |--------------------------------------------------------------------------
    | CLICK ARTICLE RESULT
    |--------------------------------------------------------------------------
    |
    | PENTING:
    | Event ini di luar AJAX success.
    |
    */

    $(document).on(
        'click',
        '.article-item',
        function () {

            const item =
                $(this);


            const article =
                item.attr('data-article') || '';


            const description =
                item.attr('data-description') || '';


            /*
            |--------------------------------------------------------------------------
            | ISI ARTICLE
            |--------------------------------------------------------------------------
            */

            $('#article')
                .val(article);


            /*
            |--------------------------------------------------------------------------
            | ISI DESCRIPTION
            |--------------------------------------------------------------------------
            */

            $('#description')
                .val(description);


            /*
            |--------------------------------------------------------------------------
            | HILANGKAN NOT FOUND
            |--------------------------------------------------------------------------
            */

            $('#articleNotFound')
                .addClass('d-none');


            /*
            |--------------------------------------------------------------------------
            | HILANGKAN DROPDOWN
            |--------------------------------------------------------------------------
            */

            $('#articleSearchResult')
                .empty()
                .removeClass('show');


            /*
            |--------------------------------------------------------------------------
            | CLEAR BUTTON
            |--------------------------------------------------------------------------
            */

            if (article !== '') {

                $('#clearArticle')
                    .addClass('show');

            }

        }
    );


    /*
    |--------------------------------------------------------------------------
    | CLEAR ARTICLE
    |--------------------------------------------------------------------------
    */

    $('#clearArticle').on(
        'click',
        function () {

            $('#article')
                .val('')
                .focus();


            $('#description')
                .val('');


            $('#articleSearchResult')
                .empty()
                .removeClass('show');


            $('#articleNotFound')
                .addClass('d-none');


            $(this)
                .removeClass('show');

        }
    );


    /*
    |--------------------------------------------------------------------------
    | TAMBAH
    |--------------------------------------------------------------------------
    */

    $('#btnTambahUpah').on(
        'click',
        function () {

            resetFormUpah();


            $('#modalUpah')
                .modal('show');


            setTimeout(function () {

                $('#article')
                    .focus();

            }, 300);

        }
    );


    /*
    |--------------------------------------------------------------------------
    | EDIT
    |--------------------------------------------------------------------------
    */

    $(document).on(
        'click',
        '.btn-edit-upah',
        function () {

            const id =
                $(this).data('id');


            resetFormUpah();

            upahFormMode = 'edit';

            $('#modalUpahTitle')
                .text('Edit Upah Borongan');


            $('#btnSimpanUpah')
                .prop('disabled', true)
                .html(
                    '<i class="fas fa-spinner fa-spin"></i> Memuat...'
                );


            /*
            |--------------------------------------------------------------------------
            | MODAL LANGSUNG DIBUKA
            |--------------------------------------------------------------------------
            */

            $('#modalUpah')
                .modal('show');


            /*
            |--------------------------------------------------------------------------
            | AJAX
            |--------------------------------------------------------------------------
            */

            $.ajax({

                url:
                    "{{ url('/upah') }}/" +
                    id +
                    "/data",

                type: 'GET',


                success: function (response) {

                    const data =
                        response.data;


                    $('#upah_id')
                        .val(data.id);


                    $('#article')
                        .val(data.article);


                    $('#description')
                        .val(
                            data.description || ''
                        );


                    $('#jenis')
                        .val(data.jenis);


                    $('#harga')
                        .val(data.harga);


                    /*
                    |--------------------------------------------------------------------------
                    | CLEAR ARTICLE SEARCH
                    |--------------------------------------------------------------------------
                    */

                    $('#articleSearchResult')
                        .empty()
                        .removeClass('show');


                    $('#articleNotFound')
                        .addClass('d-none');


                    /*
                    |--------------------------------------------------------------------------
                    | ENABLE SAVE
                    |--------------------------------------------------------------------------
                    */

                    $('#btnSimpanUpah')
                        .prop('disabled', false)
                        .html(
                            '<i class="fas fa-save"></i> Update'
                        );

                },


                error: function (xhr) {

                    console.error(xhr);


                    $('#btnSimpanUpah')
                        .prop('disabled', false)
                        .html(
                            '<i class="fas fa-save"></i> Update'
                        );


                    $('#formUpahError')
                        .removeClass('d-none')
                        .text(
                            'Gagal mengambil data upah.'
                        );

                }

            });

        }
    );


    /*
    |--------------------------------------------------------------------------
    | STORE / UPDATE
    |--------------------------------------------------------------------------
    */

    $('#formUpah').on(
        'submit',
        function (e) {

            e.preventDefault();


            const id =
                $('#upah_id').val();

            let url =
                storeUrl;


            /*
            |--------------------------------------------------------------------------
            | UPDATE
            |--------------------------------------------------------------------------
            */

            if (id) {

                url =
                    "{{ url('/upah') }}/" +
                    id;

            }


            const formData =
                new FormData(this);


            /*
            |--------------------------------------------------------------------------
            | METHOD UPDATE
            |--------------------------------------------------------------------------
            */

            if (id) {

                formData.append(
                    '_method',
                    'PUT'
                );

            }


            const button =
                $('#btnSimpanUpah');


            button
                .prop('disabled', true)
                .html(
                    '<i class="fas fa-spinner fa-spin"></i> Menyimpan...'
                );


            $.ajax({

                url: url,

                type: 'POST',

                data: formData,

                processData: false,

                contentType: false,


                /*
                |--------------------------------------------------------------------------
                | SUCCESS
                |--------------------------------------------------------------------------
                */

                success: function (response) {

                    if (response.success) {

                        $('#modalUpah')
                            .modal('hide');


                        location.reload();

                    }

                },


                /*
                |--------------------------------------------------------------------------
                | ERROR
                |--------------------------------------------------------------------------
                */

                error: function (xhr) {

                    let message =
                        'Terjadi kesalahan.';


                    if (
                        xhr.responseJSON &&
                        xhr.responseJSON.errors
                    ) {

                        message =
                            Object.values(
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


                    $('#formUpahError')
                        .removeClass('d-none')
                        .html(message);


                    button
                        .prop('disabled', false)
                        .html(
                            '<i class="fas fa-save"></i> ' +
                            (
                                id
                                    ? 'Update'
                                    : 'Simpan'
                            )
                        );

                }

            });

        }
    );


    /*
    |--------------------------------------------------------------------------
    | DELETE
    |--------------------------------------------------------------------------
    */

    $(document).on(
        'click',
        '.btn-delete-upah',
        function () {

            const id =
                $(this).data('id');


            if (
                !confirm(
                    'Apakah Anda yakin ingin menghapus data ini?'
                )
            ) {
                return;
            }


            $.ajax({

                url:
                    "{{ url('/upah') }}/" +
                    id,

                type: 'POST',

                data: {

                    _token:
                        "{{ csrf_token() }}",

                    _method:
                        'DELETE'

                },


                success: function (response) {

                    if (response.success) {

                        location.reload();

                    }

                },


                error: function (xhr) {

                    console.error(xhr);


                    alert(
                        'Gagal menghapus data.'
                    );

                }

            });

        }
    );


    /*
    |--------------------------------------------------------------------------
    | SEARCH TABLE BY TR
    |--------------------------------------------------------------------------
    */

    const searchInput =
        document.getElementById('searchUpah');

    const clearButton =
        document.getElementById('clearSearchUpah');

    const tbody =
        document.getElementById('upahTableBody');

    const info =
        document.getElementById('searchUpahInfo');


    function filterUpah() {

        const keyword =
            searchInput.value
                .toLowerCase()
                .trim();


        const rows =
            tbody.querySelectorAll('tr');


        let total = 0;

        let found = 0;


        rows.forEach(function (row) {

            const cells =
                row.querySelectorAll('td');


            /*
            |--------------------------------------------------------------------------
            | EMPTY ROW
            |--------------------------------------------------------------------------
            */

            if (
                cells.length === 1 &&
                cells[0].hasAttribute('colspan')
            ) {

                return;
            }


            total++;


            /*
            |--------------------------------------------------------------------------
            | SEARCH SELURUH ROW
            |--------------------------------------------------------------------------
            */

            const text =
                row.textContent
                    .toLowerCase()
                    .replace(/\s+/g, ' ')
                    .trim();


            const match =
                keyword === '' ||
                text.includes(keyword);


            row.style.display =
                match
                    ? ''
                    : 'none';


            if (match) {

                found++;

            }

        });


        /*
        |--------------------------------------------------------------------------
        | INFO
        |--------------------------------------------------------------------------
        */

        info.textContent =
            keyword
                ? `${found} dari ${total} data`
                : `${total} data`;


        /*
        |--------------------------------------------------------------------------
        | CLEAR SEARCH
        |--------------------------------------------------------------------------
        */

        clearButton.classList.toggle(
            'show',
            keyword !== ''
        );

    }


    searchInput.addEventListener(
        'input',
        filterUpah
    );


    clearButton.addEventListener(
        'click',
        function () {

            searchInput.value = '';

            filterUpah();

            searchInput.focus();

        }
    );


    /*
    |--------------------------------------------------------------------------
    | INITIAL
    |--------------------------------------------------------------------------
    */

    filterUpah();

});
/*
|--------------------------------------------------------------------------
| ADD MASS
|--------------------------------------------------------------------------
*/

$('#btnAddMass').on('click', function () {

    /*
    |--------------------------------------------------------------------------
    | Hide normal form
    |--------------------------------------------------------------------------
    */

    $('#formUpah').addClass('d-none');


    /*
    |--------------------------------------------------------------------------
    | Show mass form
    |--------------------------------------------------------------------------
    */

    $('#massUpahSection')
        .removeClass('d-none');


    /*
    |--------------------------------------------------------------------------
    | Expand modal
    |--------------------------------------------------------------------------
    */

    $('#modalUpahDialog')
        .addClass('mass-mode');


    /*
    |--------------------------------------------------------------------------
    | Title
    |--------------------------------------------------------------------------
    */

    $('#modalUpahTitle')
        .text('Input Mass Upah Borongan');


    /*
    |--------------------------------------------------------------------------
    | Kalau belum ada row
    |--------------------------------------------------------------------------
    */

    if (
        $('#massUpahBody tr').length === 0
    ) {

        addMassRow();

    }

});
/*
|--------------------------------------------------------------------------
| CLOSE MASS
|--------------------------------------------------------------------------
*/

$('#btnCloseMass, #btnCancelMass').on(
    'click',
    function () {

        $('#massUpahSection')
            .addClass('d-none');


        $('#formUpah')
            .removeClass('d-none');


        $('#modalUpahDialog')
            .removeClass('mass-mode');


        $('#modalUpahTitle')
            .text('Tambah Upah Borongan');

    }
);
/*
|--------------------------------------------------------------------------
| ADD MASS ROW
|--------------------------------------------------------------------------
*/

let massRowNumber = 0;


$('#btnAddMassRow').on(
    'click',
    function () {

        addMassRow();

    }
);


function addMassRow() {

    massRowNumber++;


    const row = `
        <tr class="mass-row"
            data-row="${massRowNumber}">

            <td class="text-center mass-number">
                ${massRowNumber}
            </td>


            <td style="min-width: 210px;">

                <div class="mass-article-wrapper">

                    <input type="text"
                           class="form-control mass-article"
                           placeholder="Cari article..."
                           autocomplete="off">

                    <div class="mass-article-result"></div>

                </div>

            </td>


            <td style="min-width: 260px;">

                <input type="text"
                       class="form-control mass-description"
                       placeholder="Description">

            </td>


            <td style="min-width: 150px;">

                <input type="text"
                       class="form-control mass-jenis"
                       placeholder="Jenis">

            </td>


            <td>

                <input type="number"
                       class="form-control mass-harga"
                       min="0"
                       step="0.01"
                       placeholder="0">

            </td>


            <td class="text-center">

                <button type="button"
                        class="btn btn-sm btn-outline-danger btn-remove-mass-row"
                        title="Hapus baris">

                    <i class="fas fa-trash"></i>

                </button>

            </td>

        </tr>
    `;


    $('#massUpahBody')
        .append(row);


    renumberMassRows();


    /*
    |--------------------------------------------------------------------------
    | FOCUS ARTICLE
    |--------------------------------------------------------------------------
    */

    $('#massUpahBody tr:last .mass-article')
        .focus();

}
$(document).on(
    'click',
    '.btn-remove-mass-row',
    function () {

        $(this)
            .closest('tr')
            .remove();


        renumberMassRows();

    }
);


function renumberMassRows() {

    $('#massUpahBody tr').each(
        function (index) {

            $(this)
                .find('.mass-number')
                .text(index + 1);

        }
    );

}
let massArticleTimer = {};


$(document).on(
    'input',
    '.mass-article',
    function () {

        const input = $(this);

        const row = input.closest('.mass-row');

        const keyword = input.val().trim();

        const resultBox =
            row.find('.mass-article-result');

        const description =
            row.find('.mass-description');


        /*
        |--------------------------------------------------------------------------
        | RESET DESCRIPTION
        |--------------------------------------------------------------------------
        */

        description.val('');


        /*
        |--------------------------------------------------------------------------
        | HAPUS HASIL LAMA
        |--------------------------------------------------------------------------
        */

        resultBox
            .empty()
            .removeClass('show');


        /*
        |--------------------------------------------------------------------------
        | MINIMUM 2 CHARACTER
        |--------------------------------------------------------------------------
        */

        if (keyword.length < 2) {

            return;

        }


        /*
        |--------------------------------------------------------------------------
        | TIMER PER ROW
        |--------------------------------------------------------------------------
        */

        const rowId =
            row.attr('data-row');


        clearTimeout(
            massArticleTimer[rowId]
        );


        massArticleTimer[rowId] =
            setTimeout(function () {

                $.ajax({

                    url: articleUrl,

                    type: 'GET',

                    data: {
                        q: keyword
                    },


                    success: function (response) {

                        // Jika row sudah dihapus sebelum AJAX selesai,
                        // jangan mencoba menampilkan hasil ke row tersebut.
                        if (!$.contains(document, row[0])) {
                            return;
                        }

                        if (!Array.isArray(response)) {

                            response = [];

                        }


                        /*
                        |--------------------------------------------------------------------------
                        | NOT FOUND
                        |--------------------------------------------------------------------------
                        */

                        if (response.length === 0) {

                            resultBox
                                .html(`
                                    <div class="mass-article-not-found">

                                        <i class="fas fa-exclamation-circle"></i>

                                        <span>
                                            Article tidak ditemukan.
                                            Silahkan lanjut mengisi
                                            article code dengan seksama!
                                        </span>

                                    </div>
                                `)
                                .addClass('show');

                            return;
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | FOUND
                        |--------------------------------------------------------------------------
                        */

                        let html = '';


                        response.forEach(function (item) {

                            const article =
                                item.article || '';

                            const desc =
                                item.description || '';


                            html += `

                                <div class="mass-article-item"
                                     data-article="${escapeHtml(article)}"
                                     data-description="${escapeHtml(desc)}">

                                    <div class="mass-article-title">

                                        ${escapeHtml(article)}

                                    </div>

                                    <div class="mass-article-description">

                                        ${escapeHtml(desc || '-')}

                                    </div>

                                </div>

                            `;

                        });


                        resultBox
                            .html(html)
                            .addClass('show');

                    },


                    error: function (xhr) {

                        console.error(
                            'Mass article search error:',
                            xhr
                        );

                        if (!$.contains(document, row[0])) {
                            return;
                        }

                        resultBox
                            .html(`
                                <div class="mass-article-not-found">

                                    <i class="fas fa-exclamation-circle"></i>

                                    <span>
                                        Article tidak ditemukan.
                                        Silahkan lanjut mengisi
                                        article code dengan seksama!
                                    </span>

                                </div>
                            `)
                            .addClass('show');

                    }

                });

            }, 300);

    }
);
/*
|--------------------------------------------------------------------------
| CLICK MASS ARTICLE RESULT
|--------------------------------------------------------------------------
*/

$(document).on(
    'click',
    '.mass-article-item',
    function () {

        const item =
            $(this);

        const row =
            item.closest('.mass-row');


        const article =
            item.attr('data-article') || '';


        const description =
            item.attr('data-description') || '';


        /*
        |--------------------------------------------------------------------------
        | SET ARTICLE
        |--------------------------------------------------------------------------
        */

        row.find('.mass-article')
            .val(article);


        /*
        |--------------------------------------------------------------------------
        | SET DESCRIPTION
        |--------------------------------------------------------------------------
        */

        row.find('.mass-description')
            .val(description);


        /*
        |--------------------------------------------------------------------------
        | CLOSE RESULT
        |--------------------------------------------------------------------------
        */

        row.find('.mass-article-result')
            .empty()
            .removeClass('show');

    }
);

/*
|--------------------------------------------------------------------------
| CLOSE MASS SEARCH WHEN CLICKING OUTSIDE
|--------------------------------------------------------------------------
*/

$(document).on('click', function (e) {

    if (
        !$(e.target).closest('.mass-article-wrapper').length
    ) {
        $('.mass-article-result')
            .empty()
            .removeClass('show');
    }

});


/*
|--------------------------------------------------------------------------
| RESET MASS WHEN MODAL IS CLOSED
|--------------------------------------------------------------------------
*/

$('#modalUpah').on('hidden.bs.modal', function () {

    $('#massUpahBody').empty();

    $('#massUpahSection')
        .addClass('d-none');

    $('#formUpah')
        .removeClass('d-none');

    $('#modalUpahDialog')
        .removeClass('mass-mode');

    $('#modalUpahTitle')
        .text('Tambah Upah Borongan');

    upahFormMode = 'create';

    massRowNumber = 0;

    massArticleTimer = {};

});
$('#btnSaveMass').on('click', function () {

    const button = $(this);

    const rows = $('#massUpahBody .mass-row');

    if (rows.length === 0) {

        alert('Belum ada data yang akan disimpan.');

        return;
    }


    const items = [];

    let errorMessage = null;


    rows.each(function (index) {

        const row = $(this);

        const article =
            row.find('.mass-article')
                .val()
                .trim();

        const description =
            row.find('.mass-description')
                .val()
                .trim();

        const jenis =
            row.find('.mass-jenis')
                .val()
                .trim();

        const harga =
            row.find('.mass-harga')
                .val()
                .trim();


        if (!article) {

            errorMessage =
                `Article pada baris ${index + 1} belum diisi.`;

            return false;
        }


        if (!jenis) {

            errorMessage =
                `Jenis pada baris ${index + 1} belum diisi.`;

            return false;
        }


        if (
            harga === '' ||
            isNaN(harga) ||
            Number(harga) < 0
        ) {

            errorMessage =
                `Harga pada baris ${index + 1} belum valid.`;

            return false;
        }


        items.push({

            article: article,

            description:
                description || null,

            jenis: jenis,

            harga: harga

        });

    });


    if (errorMessage) {

        alert(errorMessage);

        return;
    }


    if (
        !confirm(
            `Simpan ${items.length} data upah borongan?`
        )
    ) {

        return;
    }


    button
        .prop('disabled', true)
        .html(
            '<i class="fas fa-spinner fa-spin mr-1"></i>' +
            ' Menyimpan...'
        );


    $.ajax({

        url: "/upah/store/upah/mass",

        type: "POST",

        data: {

            _token:
                "{{ csrf_token() }}",

            items: items

        },


        success: function (response) {

            if (response.success) {

                $('#modalUpah')
                    .modal('hide');

                location.reload();

                return;
            }


            alert(
                response.message ||
                'Gagal menyimpan data.'
            );

        },


        error: function (xhr) {

            console.error(xhr);

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


            alert(message);

        },


        complete: function () {

            button
                .prop('disabled', false)
                .html(
                    '<i class="fas fa-save mr-1"></i>' +
                    ' Simpan Semua'
                );

        }

    });

});
$(document).on('click', '.btn-copy-upah', function () {

    const id = $(this).data('id');

    /*
    |--------------------------------------------------------------------------
    | COPY -> CREATE
    |--------------------------------------------------------------------------
    | Copy hanya mengisi form Tambah dengan data item lama.
    | Saat Simpan, form tetap masuk ke storeUrl sehingga membuat
    | record BARU, bukan update record lama.
    |--------------------------------------------------------------------------
    */

    // Reset form secara langsung di handler global ini.
    // resetFormUpah() berada di dalam document.ready, jadi tidak bisa
    // dipanggil dari handler yang berada di luar scope tersebut.
    $('#formUpah')[0].reset();
    $('#upah_id').val('');
    $('#article').prop('readonly', false);
    $('#description').prop('readonly', false);
    $('#jenis').prop('readonly', false);
    $('#harga').prop('readonly', false);
    $('#formUpahError').addClass('d-none').empty();
    $('#articleSearchResult').empty().removeClass('show');
    $('#articleNotFound').addClass('d-none');
    $('#clearArticle').removeClass('show');

    upahFormMode = 'create';

    $('#upah_id').val('');

    $('#modalUpahTitle')
        .text('Tambah Upah Borongan');


    $('#btnSimpanUpah')
        .prop('disabled', true)
        .html(
            '<i class="fas fa-spinner fa-spin"></i> Memuat...'
        );


    $('#modalUpah').modal('show');


    $.ajax({

        url:
            "{{ url('/upah') }}/" +
            id +
            "/data",

        type: 'GET',

        success: function (response) {

            const data = response.data;


            $('#article')
                .val(data.article);

            $('#description')
                .val(data.description || '');

            $('#jenis')
                .val(data.jenis || '');

            $('#harga')
                .val(data.harga);

            $('#btnSimpanUpah')
                .prop('disabled', false)
                .html(
                    '<i class="fas fa-save"></i> Simpan'
                );

        },

        error: function () {

            $('#formUpahError')
                .removeClass('d-none')
                .text(
                    'Gagal mengambil data item.'
                );

            $('#btnSimpanUpah')
                .prop('disabled', true);

        }

    });

});
</script>
