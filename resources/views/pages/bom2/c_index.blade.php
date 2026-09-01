@extends('master.master')

@section('title','BOM Produksi')

@section('content')

<div class="padding">
    <div class="box">

        <div class="box-header">
            <h2>BOM Production</h2>
        </div>

        <div class="box-body">

            {{-- =====================================================
                GLOBAL LOADER
            ====================================================== --}}
            <div id="global-loader" style="display:none">
                <div class="loader-content">

                    <div class="spinner-border text-success"
                         style="width:70px;height:70px">
                    </div>

                    <h4 class="mt-4">
                        Sedang mencocokkan data...
                    </h4>

                    <small class="text-muted">
                        Mohon tunggu sebentar
                    </small>

                </div>
            </div>


            {{-- =====================================================
                TAB NAVIGATION
            ====================================================== --}}
            <ul class="nav nav-tabs" role="tablist">

                {{-- BOM DRAFT --}}
                <li class="nav-item">
                    <a
                        class="nav-link bom-tab"
                        href="{{ url('/bom-produksi?uri=draft') }}"
                        data-uri="draft"
                    >
                        BOM Draft
                    </a>
                </li>


                {{-- LIST HARGA --}}
                <li class="nav-item">
                    <a
                        class="nav-link bom-tab"
                        href="{{ url('/bom-produksi?uri=price_list') }}"
                        data-uri="price_list"
                    >
                        List Harga
                    </a>
                </li>


                {{-- MATERIAL FINISHING --}}
                <li class="nav-item">
                    <a
                        class="nav-link bom-tab"
                        href="{{ url('/bom-produksi?uri=finishing') }}"
                        data-uri="finishing"
                    >
                        Material Finishing
                    </a>
                </li>


                {{-- CREATE BOM --}}
                <li class="nav-item">
                    <a
                        class="nav-link bom-tab"
                        href="{{ url('/bom-produksi?uri=create_bom') }}"
                        data-uri="create_bom"
                    >
                        Create BOM
                    </a>
                </li>


                {{-- RELEASED BOM --}}
                <li class="nav-item">
                    <a
                        class="nav-link bom-tab"
                        href="{{ url('/bom-produksi?uri=released_bom') }}"
                        data-uri="released_bom"
                    >
                        Released BOM
                    </a>
                </li>


                {{-- CAD --}}
                <li class="nav-item">
                    <a
                        class="nav-link bom-tab"
                        href="{{ url('/bom-produksi?uri=cad') }}"
                        data-uri="cad"
                    >
                        C A D
                    </a>
                </li>

            </ul>


            {{-- =====================================================
                TAB CONTENT
            ====================================================== --}}
            <div class="tab-content p-a-3">

                {{-- =================================================
                    BOM DRAFT
                ================================================== --}}
                <div
                    class="tab-pane"
                    id="bom"
                    style="display:none;"
                >
                    @include('pages.bom2.partials.c_bill_of_material')
                </div>


                {{-- =================================================
                    LIST HARGA
                ================================================== --}}
                <div
                    class="tab-pane"
                    id="harga"
                    style="display:none;"
                >
                </div>


                {{-- =================================================
                    MATERIAL FINISHING
                ================================================== --}}
                <div
                    class="tab-pane"
                    id="finishing"
                    style="display:none;"
                >
                </div>


                {{-- =================================================
                    CREATE BOM
                ================================================== --}}
                <div
                    class="tab-pane"
                    id="create-bom"
                    style="display:none;"
                >
                </div>


                {{-- =================================================
                    RELEASED BOM
                ================================================== --}}
                <div
                    class="tab-pane"
                    id="released-bom"
                    style="display:none;"
                >
                </div>


                {{-- =================================================
                    CAD
                ================================================== --}}
                <div
                    class="tab-pane"
                    id="cad"
                    style="display:none;"
                >
                </div>

            </div>

        </div>

    </div>
</div>


{{-- =============================================================
    MODAL ADD HARGA
============================================================== --}}
@include('pages.bom2.partials.c_modal_add_harga')


<script>
$(document).ready(function () {

    console.log('====================================');
    console.log('BOM TAB SYSTEM START');
    console.log('====================================');


    /* ============================================================
       TAB MAP
    ============================================================ */

    const tabMap = {

        draft: '#bom',

        price_list: '#harga',

        finishing: '#finishing',

        create_bom: '#create-bom',

        released_bom: '#released-bom',

        cad: '#cad'

    };


    /* ============================================================
       LOAD STATUS
    ============================================================ */

    let loaded = {

        draft: true,

        price_list: false,

        finishing: false,

        create_bom: false,

        released_bom: false,

        cad: false

    };


    /* ============================================================
       GET URI FROM URL
    ============================================================ */

    function getCurrentUri() {

        const params =
            new URLSearchParams(
                window.location.search
            );

        let uri =
            params.get('uri');


        /*
         * Kalau tidak ada ?uri=
         * otomatis Draft
         */
        if (!uri) {

            uri = 'draft';

        }


        /*
         * Kalau URI tidak dikenal
         * kembali ke Draft
         */
        if (!tabMap[uri]) {

            uri = 'draft';

        }


        return uri;

    }


    /* ============================================================
       UPDATE URL
    ============================================================ */

    function updateUrl(uri) {

        const newUrl =
            window.location.pathname +
            '?uri=' +
            encodeURIComponent(uri);


        window.history.pushState(
            {
                uri: uri
            },
            '',
            newUrl
        );


        console.log(
            'URL changed:',
            newUrl
        );

    }


    /* ============================================================
       SET ACTIVE NAV
    ============================================================ */

    function setActiveNav(uri) {

        $('.bom-tab').removeClass('active');

        $('.nav-item').removeClass('active');


        const tab =
            $('.bom-tab[data-uri="' + uri + '"]');


        tab.addClass('active');

        tab.closest('.nav-item')
            .addClass('active');

    }


    /* ============================================================
       HIDE ALL TAB
    ============================================================ */

    function hideAllTabs() {

        $('.tab-pane')
            .removeClass('active show')
            .hide();

    }


    /* ============================================================
       SHOW TAB
    ============================================================ */

    function showTab(uri) {

        if (!tabMap[uri]) {

            uri = 'draft';

        }


        console.log(
            'SHOW TAB:',
            uri
        );


        const target =
            tabMap[uri];


        /*
         * Active navigation
         */
        setActiveNav(uri);


        /*
         * Hide semua
         */
        hideAllTabs();


        /*
         * Show target
         */
        $(target)
            .addClass('active show')
            .show();


        /*
         * Load content
         */
        loadTab(uri);

    }


    /* ============================================================
       LOAD TAB
    ============================================================ */

    function loadTab(uri) {

        console.log(
            'LOAD TAB:',
            uri
        );


        /* ========================================================
           DRAFT
        ======================================================== */

        if (uri === 'draft') {

            return;

        }


        /* ========================================================
           PRICE LIST
        ======================================================== */

        if (uri === 'price_list') {

            if (loaded.price_list) {

                return;

            }


            loaded.price_list = true;


            $('#harga').html(`
                <div class="text-center p-4">
                    <div
                        class="spinner-border text-success"
                        role="status">
                    </div>

                    <div class="mt-2 text-muted">
                        Memuat List Harga...
                    </div>
                </div>
            `);


            $.ajax({

                url:
                    '/bom-produksi/bom/harga-partial',

                type: 'GET',

                cache: false,

                success: function (html) {

                    console.log(
                        'PRICE LIST LOADED'
                    );


                    $('#harga')
                        .html(html);

                },

                error: function (
                    xhr,
                    status,
                    error
                ) {

                    console.error(
                        'PRICE LIST ERROR:',
                        xhr.status,
                        error,
                        xhr.responseText
                    );


                    loaded.price_list = false;


                    $('#harga').html(`
                        <div class="alert alert-danger">
                            Gagal memuat List Harga.
                            <br>
                            <small>
                                HTTP ${xhr.status}
                            </small>
                        </div>
                    `);

                }

            });


            return;

        }


        /* ========================================================
           FINISHING
        ======================================================== */

        if (uri === 'finishing') {

            if (loaded.finishing) {

                return;

            }


            loaded.finishing = true;


            $('#finishing').html(`
                <div class="text-center p-4">
                    <div
                        class="spinner-border text-success"
                        role="status">
                    </div>

                    <div class="mt-2 text-muted">
                        Memuat Material Finishing...
                    </div>
                </div>
            `);


            $.ajax({

                url:
                    '/bom-produksi/bom/finishing-partial',

                type: 'GET',

                cache: false,

                success: function (html) {

                    console.log(
                        'FINISHING LOADED'
                    );


                    $('#finishing')
                        .html(html);

                },

                error: function (
                    xhr,
                    status,
                    error
                ) {

                    console.error(
                        'FINISHING ERROR:',
                        xhr.status,
                        error,
                        xhr.responseText
                    );


                    loaded.finishing = false;


                    $('#finishing').html(`
                        <div class="alert alert-danger">
                            Gagal memuat Material Finishing.
                            <br>
                            <small>
                                HTTP ${xhr.status}
                            </small>
                        </div>
                    `);

                }

            });


            return;

        }


        /* ========================================================
           CREATE BOM
        ======================================================== */

        if (uri === 'create_bom') {

            if (loaded.create_bom) {

                return;

            }


            loaded.create_bom = true;


            $('#create-bom').html(`
                <div class="text-center p-4">
                    <div
                        class="spinner-border text-success"
                        role="status">
                    </div>

                    <div class="mt-2 text-muted">
                        Memuat Create BOM...
                    </div>
                </div>
            `);


            $.ajax({

                url:
                    '/bom-produksi/bom/create-partial',

                type: 'GET',

                cache: false,

                success: function (html) {

                    console.log(
                        'CREATE BOM LOADED'
                    );


                    $('#create-bom')
                        .html(html);


                    /*
                     * Debug
                     */
                    console.log(
                        'updateDimensionCalculation:',
                        typeof updateDimensionCalculation
                    );

                },

                error: function (
                    xhr,
                    status,
                    error
                ) {

                    console.error(
                        'CREATE BOM ERROR:',
                        xhr.status,
                        error,
                        xhr.responseText
                    );


                    loaded.create_bom = false;


                    $('#create-bom').html(`
                        <div class="alert alert-danger">
                            Gagal memuat Create BOM.
                            <br>
                            <small>
                                HTTP ${xhr.status}
                            </small>
                        </div>
                    `);

                }

            });


            return;

        }


        /* ========================================================
           RELEASED BOM
        ======================================================== */

        if (uri === 'released_bom') {

            if (loaded.released_bom) {

                return;

            }


            loaded.released_bom = true;


            $('#released-bom').html(`
                <div class="text-center p-4">
                    <div
                        class="spinner-border text-success"
                        role="status">
                    </div>

                    <div class="mt-2 text-muted">
                        Memuat Released BOM...
                    </div>
                </div>
            `);


            $.ajax({

                url:
                    '/bom-produksi/bom/released-partial',

                type: 'GET',

                cache: false,

                success: function (html) {

                    console.log(
                        'RELEASED BOM LOADED'
                    );


                    $('#released-bom')
                        .html(html);

                },

                error: function (
                    xhr,
                    status,
                    error
                ) {

                    console.error(
                        'RELEASED BOM ERROR:',
                        xhr.status,
                        error,
                        xhr.responseText
                    );


                    loaded.released_bom = false;


                    $('#released-bom').html(`
                        <div class="alert alert-danger">
                            Gagal memuat Released BOM.
                            <br>
                            <small>
                                HTTP ${xhr.status}
                            </small>
                        </div>
                    `);

                }

            });


            return;

        }


        /* ========================================================
           CAD
        ======================================================== */

        if (uri === 'cad') {

            console.log(
                'CAD TAB'
            );

            return;

        }

    }


    /* ============================================================
       CLICK TAB
    ============================================================ */

    $(document).on(
        'click',
        '.bom-tab',
        function (e) {

            e.preventDefault();


            const uri =
                $(this).attr('data-uri');


            console.log(
                '===================================='
            );

            console.log(
                'CLICK TAB:',
                uri
            );


            /*
             * Update URL
             */
            updateUrl(uri);


            /*
             * Tampilkan tab
             */
            showTab(uri);

        }
    );


    /* ============================================================
       BACK / FORWARD BROWSER
    ============================================================ */

    window.addEventListener(
        'popstate',
        function () {

            console.log(
                'BROWSER NAVIGATION'
            );


            const uri =
                getCurrentUri();


            console.log(
                'POPSTATE URI:',
                uri
            );


            showTab(uri);

        }
    );


    /* ============================================================
       INITIAL LOAD
    ============================================================ */

    const initialUri =
        getCurrentUri();


    console.log(
        'INITIAL URI:',
        initialUri
    );


    /*
     * Kalau URL belum mempunyai ?uri=
     * buat menjadi ?uri=draft
     */
    if (
        !new URLSearchParams(
            window.location.search
        ).has('uri')
    ) {

        updateUrl('draft');

    }


    /*
     * Tampilkan tab pertama
     */
    showTab(initialUri);


    console.log(
        '===================================='
    );

    console.log(
        'BOM TAB SYSTEM READY'
    );

    console.log(
        '===================================='

    );

});
</script>

@endsection