@extends('master.master')

@section('title', 'Laporan Stok')

@section('content')





    @if (session('success'))
        <script>
            toastr.success("{{ session('success') }}");
        </script>
    @endif



    @if (session('error'))
        <script>
            toastr.error("{{ session('error') }}");
        </script>
    @endif



    <div class="padding">

        <div class="box">



            <div class="box-header d-flex justify-content-between">

                <h2>Laporan Stok</h2>

            </div>



            <div id="spkInfo"></div>



            <div class="box-body">



                {{-- BARIS FILTER DAN TOMBOL --}}

                <div class="row mb-3 align-items-end">



                    {{-- FILTER JENIS --}}

                    <div class="col-md-3">

                        <label>Jenis Barang</label>

                        <select id="filterJenis" class="form-control">

                            <option value="">Semua Jenis</option>

                            <option value="bahan baku" {{ request('jenis') == 'bahan baku' ? 'selected' : '' }}>

                                Bahan Baku

                            </option>

                            <option value="bahan penolong" {{ request('jenis') == 'bahan penolong' ? 'selected' : '' }}>

                                Bahan Penolong

                            </option>

                            <option value="bahan penolong alat"
                                {{ request('jenis') == 'bahan penolong alat' ? 'selected' : '' }}>

                                Bahan Penolong Alat

                            </option>

                            <option value="bahan finishing" {{ request('jenis') == 'bahan finishing' ? 'selected' : '' }}>

                                Bahan Finishing

                            </option>

                        </select>

                    </div>



                    {{-- SEARCH --}}

                    <div class="col-md-2">

                        <label>Cari Barang</label>

                        <input type="text" id="searchBarang" class="form-control" placeholder="Cari nama barang..."
                            value="{{ request('search', '') }}" autocomplete="off">

                    </div>



                    {{-- HISTORY DAN EXPORT --}}

                    <div class="col-md-4">

                        <div class="d-flex align-items-center flex-wrap" style="gap: 8px;">



                            <a href="/laporan/warehouse-history" class="btn btn-primary" style="white-space: nowrap;">

                                <i class="fa fa-history"></i>

                                History

                            </a>



                            <a href="{{ route('laporan.export', request()->only(['jenis', 'search'])) }}"
                                class="btn btn-success" id="btnExportExcel" style="white-space: nowrap;">

                                <i class="fa fa-file-excel-o"></i>

                                Export Excel

                            </a>

                            {{-- FILTER STATUS STOCK OPNAME --}}
                            <select id="filterOpname" class="form-control" aria-label="Filter status stock opname"
                                style="width:auto;min-width:155px;max-width:190px;">
                                <option value=""
                                    {{ request('opname') === null || request('opname') === '' ? 'selected' : '' }}>Filter</option>
                                <option value="has_opname" {{ request('opname') === 'has_opname' ? 'selected' : '' }}>Has
                                    Opname</option>
                            </select>



                        </div>

                    </div>



                    {{-- TAMBAH BARIS --}}

                    <div class="col-md-3 text-md-right mt-2 mt-md-0">

                        <button type="button" class="btn btn-primary" id="addRowss">

                            <i class="fa fa-plus"></i>

                           add bahan baku

                        </button>

                    </div>



                </div>






                {{-- MODAL STOCK OPNAME: backend update harus menerima is_opname/opname_at --}}
                <div class="modal fade" id="modalOpnameStok" tabindex="-1" role="dialog"
                    aria-labelledby="modalOpnameStokLabel" aria-hidden="true">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title" id="modalOpnameStokLabel">Stock Opname</h4>
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                        aria-hidden="true">&times;</span></button>
                            </div>
                            <div class="modal-body">
                                <p id="opnameNamaBarang" class="font-weight-bold"></p>
                                <div class="form-group">
                                    <label>Hasil Opname Fisik (Stok Awal Baru)</label>
                                    <input type="number" min="0" step="0.001" id="opnameQty" class="form-control"
                                        required>
                                </div>
                                <div class="form-group">
                                    <label>Tanggal Cut-off Opname</label>
                                    <input type="datetime-local" id="opnameAt" class="form-control" required>
                                </div>
                                <small class="text-muted">Transaksi sebelum cut-off tetap tersimpan, tetapi tidak dihitung
                                    pada saldo periode baru setelah backend opname diterapkan.</small>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Batal</button>
                                <button type="button" class="btn btn-primary" id="btnSubmitOpname">Simpan Opname</button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ================================================================

                 TABLE WRAPPER

            ================================================================= --}}

                <div id="wrapperStok"
                    style="

                    overflow-x:auto;

                    overflow-y:hidden;

                    width:100%;

                ">



                    <div id="canvasStok"
                        style="

                        width:2200px;

                        display:flex;

                        align-items:flex-start;

                    ">



                        <div id="masterPanel"
                            style="

                            min-width:1300px;

                            padding-right:20px;

                        ">



                            <div class="table-stok-wrapper">



                                <table class="table table-bordered table-striped">



                                    <thead>



                                        <tr>



                                            <th width="50">

                                                No

                                            </th>



                                            <th width="110">

                                                Kode Barang

                                            </th>



                                            <th>

                                                Nama Barang

                                            </th>



                                            <th width="170">

                                                Jenis

                                            </th>



                                            <th width="60">

                                                Satuan

                                            </th>






                                            <th width="90">

                                                Stok

                                            </th>


                                            <th width="100">

                                                Harga

                                            </th>

                                            {{-- <th width="80">

                                                Stok In

                                            </th> --}}



                                            {{-- <th width="80">

                                                Stok Out

                                            </th> --}}






                                            <th width="80">

                                                Aksi

                                            </th>



                                            <th width="100">

                                                Detail

                                            </th>



                                        </tr>



                                    </thead>







                                    <tbody id="tableBody">



                                        {{-- Saldo tampilan menggunakan format Indonesia dari partial rows; nilai hitung tetap di controller. --}}
                                        @include('pages.laporan.partials.rows')



                                    </tbody>





                                </table>



                            </div>



                        </div>



                    </div>



                </div>



            </div>



        </div>

    </div>





    {{-- ================================================================

     TOASTR

\================================================================= --}}

    <div class="stok-pagination d-flex justify-content-between align-items-center flex-wrap"
        style="gap:12px;margin:14px 0">
        <small id="stokTotalInfo" class="text-muted">Total {{ $stoks->total() }} barang</small>
        <div id="stokPagination">{{ $stoks->links() }}</div>
    </div>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">



    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>





    <script>
        $(document).ready(function() {



            // ============================================================

            // TAMBAH BARIS

            // ============================================================



            // Delegated handler supaya tombol Tambah Baris tetap bekerja

            // walaupun table/DOM berubah atau script dijalankan ulang.

            $(document)

                .off('click.addRowss', '#addRowss')

                .on('click.addRowss', '#addRowss', function(e) {



                    e.preventDefault();

                    e.stopPropagation();



                    const $tbody = $('#tableBody');



                    if (!$tbody.length) {

                        console.error('[TAMBAH BARIS] #tableBody tidak ditemukan.');

                        return;

                    }



                    const rowCount = 1;



                    const today = new Date();

                    const yyyy = today.getFullYear();

                    const mm = String(today.getMonth() + 1).padStart(2, '0');

                    const dd = String(today.getDate()).padStart(2, '0');

                    const todayValue = `${yyyy}-${mm}-${dd}`;



                    const row = `

                    <tr data-id="" class="row-new">



                        <td class="row-number">

                            ${rowCount}

                        </td>



                        <td>

                            <input type="hidden" class="id" value="">



                            <input

                                type="text"

                                class="form-control kode_barang"

                                autocomplete="off"

                            >

                        </td>



                        <td>

                            <input

                                type="text"

                                class="form-control nama_barang"

                                autocomplete="off"

                            >

                        </td>



                        <td>

                            <select class="form-control jenis">

                                <option value="bahan baku">Bahan Baku</option>

                                <option value="bahan penolong">Bahan Penolong</option>

                                <option value="bahan penolong alat">Bahan Penolong Alat</option>

                                <option value="bahan finishing">Bahan Finishing</option>

                            </select>

                        </td>



                        <td>

                            <input

                                type="text"

                                class="form-control satuan"

                                autocomplete="off"

                            >

                        </td>



                        <td>

                            <input

                                type="text"

                                class="form-control harga"

                                autocomplete="off"

                            >

                        </td>



                        <td>

                            <input

                                type="number"

                                step="0.001"

                                class="form-control stok_awal"

                                value="0"

                            >

                        </td>



                        <!-- <td>0</td> <td>0</td> -->



        



                        <td>

                            <button

                                type="button"

                                class="btn btn-success btn-sm btn-save"

                                title="Simpan"

                            >

                                <i class="fa fa-save"></i>

                            </button>



                            <button

                                type="button"

                                class="btn btn-danger btn-sm remove-row"

                                title="Hapus"

                            >

                                <i class="fa fa-trash"></i>

                            </button>

                        </td>



                        <td></td>



                    </tr>

                `;



                    const $newRow = $(row);



                    // BARIS BARU SELALU MASUK PALING ATAS

                    $tbody.prepend($newRow);



                    // Jangan panggil filterData() di sini.

                    // Kalau user sedang melakukan search, baris baru bisa langsung

                    // tersembunyi sehingga terlihat seperti tombol tidak bekerja.

                    $newRow.show();



                    // Update nomor semua baris yang ada.

                    $tbody.find('tr').each(function(index) {

                        $(this).find('.row-number, td:first').first().text(index + 1);

                    });



                    // Langsung fokus ke Kode Barang.

                    $newRow.find('.kode_barang').trigger('focus');



                    // Scroll ke baris baru agar terlihat.

                    const wrapper = document.getElementById('wrapperStok');



                    if (wrapper) {

                        const rowRect = $newRow[0].getBoundingClientRect();

                        const wrapperRect = wrapper.getBoundingClientRect();



                        if (

                            rowRect.bottom > wrapperRect.bottom ||

                            rowRect.top < wrapperRect.top

                        ) {

                            $newRow[0].scrollIntoView({

                                behavior: 'smooth',

                                block: 'nearest',

                                inline: 'nearest'

                            });

                        }

                    }



                    console.log(

                        '[TAMBAH BARIS] Berhasil menambahkan baris baru:',

                        rowCount

                    );

                });





            // ============================================================

            // FORMAT HARGA

            // ============================================================



            $(document).on(

                'keyup',

                '.harga',

                function() {



                    let value =

                        $(this)

                        .val()

                        .replace(/\D/g, '');



                    if (value === '') {



                        $(this).val('');



                        return;



                    }



                    $(this).val(

                        new Intl.NumberFormat('id-ID')

                        .format(value)

                    );



                }

            );





            // ============================================================

            // SIMPAN

            // ============================================================



            $(document).on(

                'click',

                '.btn-save',

                function(e) {



                    e.preventDefault();



                    let btn =

                        $(this);



                    let row =

                        btn.closest('tr');





                    let id =

                        row.find('.id').val();



                    let kode_barang =

                        row.find('.kode_barang').val();



                    let nama_barang =

                        row.find('.nama_barang').val();



                    let jenis =

                        row.find('.jenis').val();



                    let satuan =

                        row.find('.satuan').val();



                    let harga =

                        row.find('.harga').val();



                    let stok_awal =

                        row.find('.stok_awal').val();





                    // ----------------------------------------------------

                    // VALIDASI

                    // ----------------------------------------------------



                    if (!kode_barang) {



                        toastr.warning(

                            'Kode barang wajib diisi.'

                        );



                        row.find('.kode_barang').focus();



                        return;



                    }





                    if (!nama_barang) {



                        toastr.warning(

                            'Nama barang wajib diisi.'

                        );



                        row.find('.nama_barang').focus();



                        return;



                    }





                    // ----------------------------------------------------

                    // AJAX

                    // ----------------------------------------------------



                    $.ajax({



                        url: "{{ route('laporan.update') }}",



                        type: "POST",



                        data: {



                            _token: "{{ csrf_token() }}",



                            id: id,



                            kode_barang: kode_barang,



                            nama_barang: nama_barang,



                            jenis: jenis,



                            satuan: satuan,



                            harga: harga,



                            stok_awal: id ? undefined : stok_awal



                        },





                        beforeSend: function() {



                            btn

                                .prop(

                                    'disabled',

                                    true

                                )

                                .html(

                                    '<i class="fa fa-spinner fa-spin"></i>'

                                );



                        },





                        success: function(res) {



                            toastr.success(

                                res.message ||

                                'Data berhasil disimpan'

                            );





                            /*

                            |--------------------------------------------------------------------------

                            | BARIS BARU

                            |--------------------------------------------------------------------------

                            */



                            if (

                                row.find('.id').val() === '' &&

                                res.id

                            ) {



                                row

                                    .find('.id')

                                    .val(res.id);



                                row.attr(

                                    'data-id',

                                    res.id

                                );



                            }



                        },





                        error: function(xhr) {



                            console.error(

                                'LAPORAN UPDATE ERROR:',

                                xhr.status,

                                xhr.responseText

                            );





                            let message =

                                'Gagal menyimpan';





                            if (

                                xhr.responseJSON &&

                                xhr.responseJSON.message

                            ) {



                                message =

                                    xhr.responseJSON.message;



                            }





                            toastr.error(

                                message

                            );



                        },





                        complete: function() {



                            btn

                                .prop(

                                    'disabled',

                                    false

                                )

                                .html(

                                    '<i class="fa fa-save"></i>'

                                );



                        }



                    });



                }

            );





            // ============================================================

            // HAPUS BARIS BARU

            // ============================================================



            $(document).on(

                'click',

                '.remove-row',

                function(e) {



                    e.preventDefault();



                    let row =

                        $(this).closest('tr');



                    let id =

                        row.find('.id').val();





                    if (!id) {



                        row.remove();



                        return;



                    }





                    toastr.warning(

                        'Data sudah tersimpan. Delete database akan kita buat berikutnya.'

                    );



                }

            );






            // ============================================================
            // FILTER AJAX + PAGINATION SERVER-SIDE (TANPA FULL RELOAD)
            // ============================================================
            let filterTimer = null;
            let filterXHR = null;
            let requestNumber = 0;

            function currentFilterUrl(page = 1) {
                const url = new URL(window.location.pathname, window.location.origin);
                const search = ($('#searchBarang').val() || '').trim();
                const jenis = $('#filterJenis').val() || '';
                const opname = $('#filterOpname').val() || '';
                if (search) url.searchParams.set('search', search);
                if (jenis) url.searchParams.set('jenis', jenis);
                if (opname) url.searchParams.set('opname', opname);
                if (page > 1) url.searchParams.set('page', page);
                return url;
            }

            function syncExportUrl() {
                const $export = $('#btnExportExcel');
                if (!$export.length) return;
                const url = new URL($export.attr('href'), window.location.origin);
                const search = ($('#searchBarang').val() || '').trim();
                const jenis = $('#filterJenis').val() || '';
                url.searchParams.delete('search');
                url.searchParams.delete('jenis');
                if (search) url.searchParams.set('search', search);
                if (jenis) url.searchParams.set('jenis', jenis);
                $export.attr('href', url.toString());
            }

            function loadStok(page = 1) {
                const url = currentFilterUrl(page);
                const thisRequest = ++requestNumber;
                window.history.replaceState({}, '', url.pathname + url.search);
                syncExportUrl();
                if (filterXHR && filterXHR.readyState !== 4) filterXHR.abort();
                $('#tableBody').addClass('stok-loading');
                filterXHR = $.ajax({
                    url: url.toString(),
                    method: 'GET',
                    dataType: 'json',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    success: function(res) {
                        if (thisRequest !== requestNumber) return;
                        if (!res || typeof res.html !== 'string') {
                            toastr.error('Response tabel tidak valid.');
                            return;
                        }
                        $('#tableBody').html(res.html);
                        $('#stokPagination').html(res.pagination || '');
                        $('#stokTotalInfo').text('Total ' + (res.total ?? 0) + ' barang');
                    },
                    error: function(xhr, status) {
                        if (status === 'abort' || thisRequest !== requestNumber) return;
                        toastr.error(xhr.responseJSON?.message || 'Gagal mengambil data stok.');
                    },
                    complete: function() {
                        if (thisRequest === requestNumber) $('#tableBody').removeClass('stok-loading');
                    }
                });
            }

            function filterData() {
                // Kompatibilitas dengan handler hapus baris baru.
                // Tidak reload halaman, hanya query ulang data jika dibutuhkan.
                loadStok(1);
            }

            $('#searchBarang').off('input.stok').on('input.stok', function() {
                clearTimeout(filterTimer);
                filterTimer = setTimeout(() => loadStok(1), 350);
            });
            $('#searchBarang').off('keydown.stok').on('keydown.stok', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    clearTimeout(filterTimer);
                    loadStok(1);
                }
            });
            $('#filterJenis').off('change.stok').on('change.stok', function() {
                clearTimeout(filterTimer);
                loadStok(1);
            });
            $('#filterOpname').off('change.stok').on('change.stok', function() {
                clearTimeout(filterTimer);
                loadStok(1);
            });

            $(document).on('click.stokPagination', '#stokPagination a', function(e) {
                e.preventDefault();
                const url = new URL($(this).attr('href'), window.location.origin);
                loadStok(Number(url.searchParams.get('page') || 1));
            });
            syncExportUrl();

            // ============================================================

            // AUTO SEARCH BARANG

            // ============================================================



            $(document).on(

                'keyup',

                '.nama_barang',

                function() {



                    let input =

                        $(this);



                    let q =

                        input.val();





                    if (

                        !q ||

                        q.length < 2

                    ) {



                        return;



                    }





                    $.get(



                        '/stok/search',



                        {

                            q: q

                        },



                        function(res) {



                            if (!res) {



                                return;



                            }





                            let row =

                                input.closest('tr');





                            /*

                            |--------------------------------------------------------------------------

                            | KODE BARANG

                            |--------------------------------------------------------------------------

                            */



                            if (

                                row

                                .find('.kode_barang')

                                .val() === ''

                            ) {



                                row

                                    .find('.kode_barang')

                                    .val(

                                        res.kode_barang

                                    );



                            }





                            /*

                            |--------------------------------------------------------------------------

                            | SATUAN

                            |--------------------------------------------------------------------------

                            */



                            if (

                                row

                                .find('.satuan')

                                .val() === ''

                            ) {



                                row

                                    .find('.satuan')

                                    .val(

                                        res.satuan

                                    );



                            }





                            /*

                            |--------------------------------------------------------------------------

                            | HARGA

                            |--------------------------------------------------------------------------

                            */



                            if (

                                row

                                .find('.harga')

                                .val() === ''

                            ) {



                                row

                                    .find('.harga')

                                    .val(



                                        new Intl.NumberFormat(

                                            'id-ID'

                                        ).format(

                                            res.harga

                                        )



                                    );



                            }



                        }



                    );



                }

            );





            // ============================================================

            // ============================================================
            // EDIT BARIS + OPSI STOCK OPNAME
            // ============================================================
            function setEditMode($row, enabled) {
                if ($row.hasClass('row-new')) return;
                $row.find('.kode_barang, .nama_barang, .satuan, .harga, .tanggal')
                    .prop('readonly', !enabled);
                $row.find('select.jenis').prop('disabled', !enabled);
                $row.toggleClass('stok-edit-active', enabled);
                $row.find('.btn-edit-row').toggle(enabled);
                $row.find('.btn-opname').toggle(enabled);
            }

            $(document).on('click', '#tableBody tr[data-id]', function(e) {
                const $row = $(this);
                if (!$row.find('.id').val() || $row.hasClass('row-new')) return;
                if ($(e.target).closest('button, a').length) return;
                if (!$row.hasClass('stok-edit-active')) {
                    $('#tableBody tr.stok-edit-active').each(function() {
                        setEditMode($(this), false);
                    });
                    setEditMode($row, true);
                }
            });

            $(document).on('click', '.btn-edit-row', function(e) {
                e.preventDefault();
                e.stopPropagation();
                setEditMode($(this).closest('tr'), false);
            });

            let $selectedOpnameRow = null;
            $(document).on('click', '.btn-opname', function(e) {
                e.preventDefault();
                e.stopPropagation();
                $selectedOpnameRow = $(this).closest('tr');
                $('#opnameNamaBarang').text(
                    $selectedOpnameRow.find('.kode_barang').val() + ' — ' +
                    $selectedOpnameRow.find('.nama_barang').val()
                );
                $('#opnameQty').val('');
                const d = new Date();
                const local = new Date(d.getTime() - d.getTimezoneOffset() * 60000);
                $('#opnameAt').val(local.toISOString().slice(0, 16));
                if ($.fn.modal) {
                    $('#modalOpnameStok').modal('show');
                } else {
                    toastr.error('Bootstrap modal belum tersedia di layout master.');
                }
            });

            $('#btnSubmitOpname').on('click', function() {
                if (!$selectedOpnameRow) return;
                const qty = $('#opnameQty').val();
                const opnameAt = $('#opnameAt').val();
                if (qty === '' || Number(qty) < 0 || !opnameAt) {
                    toastr.warning('Isi jumlah opname dan tanggal cut-off yang valid.');
                    return;
                }
                // Endpoint khusus Stock Opname; jangan gunakan laporan.update.
                const $btn = $(this).prop('disabled', true);
                $.ajax({
                    url: "{{ route('laporan.opname') }}",
                    method: 'POST',
                    dataType: 'json',
                    data: {
                        _token: "{{ csrf_token() }}",
                        id: $selectedOpnameRow.find('.id').val(),
                        stok_awal: qty,
                        opname_at: opnameAt.replace('T', ' ') + ':00'
                    },
                    success: function(res) {
                        // Jangan tampilkan sukses jika backend belum mengonfirmasi opname.
                        if (!res || res.is_opname !== true) {
                            toastr.warning(
                                'Endpoint update belum mengonfirmasi penyimpanan cut-off opname. Perlu update controller terlebih dahulu.'
                                );
                            return;
                        }
                        $('#modalOpnameStok').modal('hide');
                        toastr.success(res.message || 'Opname berhasil disimpan.');
                        loadStok(Number(new URLSearchParams(window.location.search).get(
                            "page") || 1));
                    },
                    error: function(xhr) {
                        toastr.error(xhr.responseJSON?.message || 'Gagal menyimpan opname.');
                    },
                    complete: function() {
                        $btn.prop('disabled', false);
                    }
                });
            });

            // INITIAL FILTER: data halaman pertama sudah dirender server.
            // Tidak perlu request AJAX tambahan saat pertama dibuka.





        });
    </script>





    {{-- Styles now self-contained; no external laporan.style include --}}



    <style>
        /* Standalone visual styling for the stock report */
        :root {
            --stok-navy: #162c4d;
            --stok-blue: #2563eb;
            --stok-border: #e4eaf2;
            --stok-muted: #64748b;
        }

        .padding {
            padding: 22px !important;
            background: #f5f7fb;
            min-height: calc(100vh - 90px);
        }

        .padding>.box {
            border: 1px solid var(--stok-border);
            background: #fff;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 8px 30px rgba(22, 44, 77, .055);
        }

        .box-header {
            padding: 22px 24px 16px !important;
            border-bottom: 1px solid #edf1f7;
            background: linear-gradient(110deg, #fff, #f8fbff);
        }

        .box-header h2 {
            color: var(--stok-navy);
            font-size: 22px;
            font-weight: 750;
            margin: 0;
            letter-spacing: -.5px;
        }

        .box-body {
            padding: 22px 24px 26px !important;
        }

        .box-body label {
            color: #41526b;
            font-weight: 650;
            font-size: 12px;
            margin-bottom: 7px;
        }

        .box-body .row.mb-3 {
            background: #f8fafc;
            border: 1px solid var(--stok-border);
            border-radius: 12px;
            padding: 16px 8px 10px;
            margin-bottom: 20px !important;
        }

        .box-body .form-control {
            border: 1px solid #dbe3ed;
            border-radius: 8px;
            min-height: 36px;
            box-shadow: none;
            font-size: 13px;
            color: #24364c;
            background: #fff;
        }

        .box-body .form-control:focus {
            border-color: #76a4f9;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, .10);
        }

        .box-body .btn,
        .modal .btn {
            border-radius: 8px;
            font-size: 12px;
            font-weight: 650;
            padding: 8px 12px;
            box-shadow: none;
        }

        .box-body .btn-primary,
        .modal .btn-primary {
            background: #2563eb;
            border-color: #2563eb;
        }

        .box-body .btn-success {
            background: #079669;
            border-color: #079669;
        }

        #wrapperStok {
            border: 1px solid var(--stok-border);
            border-radius: 12px;
            background: #fff;
        }

        #masterPanel {
            padding-right: 0 !important;
        }

        .table-stok-wrapper .table {
            margin-bottom: 0;
            border-collapse: separate;
            border-spacing: 0;
        }

        .table-stok-wrapper .table thead th {
            background: #edf3fb;
            color: #344a65;
            border-color: #e0e8f2;
            font-size: 11px;
            letter-spacing: .3px;
            text-transform: uppercase;
            font-weight: 750;
            vertical-align: middle;
            padding: 13px 10px;
            white-space: nowrap;
        }

        .table-stok-wrapper .table tbody td {
            border-color: #edf1f6;
            padding: 8px 9px;
            vertical-align: middle;
            font-size: 12px;
            color: #334155;
            background: #fff;
        }

        .table-stok-wrapper .table tbody tr:nth-child(even) td {
            background: #fbfcff;
        }

        #tableBody tr[data-id]:not(.row-new) {
            cursor: pointer;
            transition: background .15s;
        }

        #tableBody tr[data-id]:not(.row-new):hover td {
            background: #eef6ff !important;
        }

        #tableBody tr.stok-edit-active td {
            background: #eaf2ff !important;
            border-top: 1px solid #93b4f5;
            border-bottom: 1px solid #93b4f5;
        }

        #tableBody tr.stok-edit-active td:first-child {
            box-shadow: inset 4px 0 #2563eb;
        }

        #tableBody .form-control {
            font-size: 12px;
            min-height: 32px;
            padding: 5px 8px;
        }

        #tableBody input[readonly],
        #tableBody select:disabled {
            border-color: transparent;
            background: transparent;
            box-shadow: none;
            color: #334155;
            opacity: 1;
            -webkit-text-fill-color: #334155;
        }

        #tableBody input[readonly]:focus {
            outline: none;
        }

        #tableBody .btn-sm {
            padding: 6px 9px;
            margin: 2px;
        }

        #tableBody .btn-opname {
            background: #7c3aed;
            border-color: #7c3aed;
            color: #fff;
        }

        #tableBody .btn-edit-row {
            background: #475569;
            border-color: #475569;
            color: #fff;
        }

        #tableBody .row-new td {
            background: #ecfdf5 !important;
        }

        #modalOpnameStok .modal-content {
            border: 0;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 24px 70px rgba(15, 23, 42, .2);
        }

        #modalOpnameStok .modal-header {
            background: linear-gradient(120deg, #173c75, #2563eb);
            color: white;
            padding: 20px 24px;
            border: 0;
        }

        #modalOpnameStok .modal-title {
            font-weight: 750;
            font-size: 18px;
        }

        #modalOpnameStok .modal-header .close {
            color: white;
            opacity: .9;
            text-shadow: none;
        }

        #modalOpnameStok .modal-body {
            padding: 24px;
        }

        #modalOpnameStok .modal-body label {
            display: block;
            color: #344a65;
            font-size: 12px;
            font-weight: 700;
            margin-bottom: 7px;
        }

        #modalOpnameStok .modal-body .form-control {
            border-radius: 9px;
            min-height: 42px;
            border: 1px solid #d9e3f0;
        }

        #modalOpnameStok .modal-footer {
            padding: 14px 24px;
            background: #f8fafc;
            border-top: 1px solid #edf1f6;
        }

        #opnameNamaBarang {
            background: #eff6ff;
            border-radius: 9px;
            padding: 12px 14px;
            color: #173c75;
            font-weight: 700;
        }

        @media(max-width:768px) {
            .padding {
                padding: 10px !important;
            }

            .box-header,
            .box-body {
                padding: 15px !important;
            }

            .box-header h2 {
                font-size: 19px;
            }
        }

        #tableBody.stok-loading {
            opacity: .55;
            pointer-events: none;
            transition: opacity .12s;
        }

        #stokPagination .pagination {
            margin: 0;
        }
    </style>
@endsection
