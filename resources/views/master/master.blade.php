        <!DOCTYPE html>
        <html lang="en">

        <head>
            <meta charset="utf-8" />
            <title>@yield('title')</title>
            <meta name="description" content="Admin, Dashboard, Bootstrap, Bootstrap 4, Angular, AngularJS" />
            <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, minimal-ui" />
            <meta http-equiv="X-UA-Compatible" content="IE=edge">

            <!-- for ios 7 style, multi-resolution icon of 152x152 -->
            <meta name="apple-mobile-web-app-capable" content="yes">
            <meta name="apple-mobile-web-app-status-barstyle" content="black-translucent">
            <link rel="apple-touch-icon" href="{{asset('assets/images/NEWWICKER WHITE.png')}}">
            <meta name="apple-mobile-web-app-title" content="Flatkit">
            <!-- for Chrome on Android, multi-resolution icon of 196x196 -->
            <meta name="mobile-web-app-capable" content="yes">
            <link rel="shortcut icon" href="{{asset('assets/images/newwicker.jpg')}}">
            <meta name="csrf-token" content="{{ csrf_token() }}">
            <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">


            <!-- style -->
            <link rel="stylesheet" href="{{asset('assets/animate.css/animate.min.css')}}" type="text/css" />
            <link rel="stylesheet" href="{{asset('assets/glyphicons/glyphicons.css')}}" type="text/css" />
            <link rel="stylesheet" href="{{asset('assets/font-awesome/css/font-awesome.min.css')}}" type="text/css" />
            <link rel="stylesheet" href="{{asset('assets/material-design-icons/material-design-icons.css')}}" type="text/css" />

            <link rel="stylesheet" href="{{asset('assets/bootstrap/dist/css/bootstrap.min.css')}}" type="text/css" />
            <!-- build:css ../assets/styles/app.min.css -->
            <link rel="stylesheet" href="{{asset('assets/styles/app.css')}}" type="text/css" />
            <!-- endbuild -->
            <link rel="stylesheet" href="{{asset('assets/styles/font.css')}}" type="text/css" />
            <link rel="stylesheet" href="{{asset('assets/styles/custome.css')}}" type="text/css" />
            <link rel="stylesheet" href="{{asset('assets/styles/style_pengajuan.css')}}" type="text/css" />
            <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/css/bootstrap.min.css" /> -->
            <!-- <link href="{{asset('assets/editable/css/bootstrap.min.css')}}}}" rel="stylesheet"> -->
            <script src="{{asset('assets/editable/js/bootstrap.min.js')}}"></script>
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

            <!-- <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet"> -->
            <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
            <meta http-equiv="Pragma" content="no-cache">
            <meta http-equiv="Expires" content="0">
            <style>

    #drawerOverlay{
        position:fixed;
        inset:0;
        background:rgba(0,0,0,.5);
        display:none;
        z-index:9998;
    }

    #profileDrawer{
        position:fixed;
        top:0;
        right:-420px;
        width:420px;
        height:100vh;
        background:#fff;
        z-index:9999;
        transition:.3s;
        overflow-y:auto;
        box-shadow:-5px 0 20px rgba(0,0,0,.15);
    }

    #profileDrawer.show{
        right:0;
    }

    .drawer-header{
        display:flex;
        justify-content:space-between;
        align-items:center;
        padding:15px;
        border-bottom:1px solid #eee;
    }

    .profile-top{
        text-align:center;
        padding:20px;
    }

    .drawer-avatar{
        width:80px;
        height:80px;
        border-radius:50%;
        margin-bottom:10px;
    }

    .password-rule{
        color:red;
        margin-bottom:5px;
        font-size:13px;
    }

    .password-rule.valid{
        color:green;
    }

    </style>
        </head>

        <body>
            <div class="app" id="app">

                <!-- ############ LAYOUT START-->

                @include('master.sidebar')

                <!-- content -->
                <div id="content" class="app-content box-shadow-z0" role="main">
                    <div class="app-header white box-shadow">
                        <div class="navbar navbar-toggleable-sm flex-row align-items-center">
                            <!-- Open side - Naviation on mobile -->
                            <a data-toggle="modal" data-target="#aside" class="hidden-lg-up mr-3">
                                <i class="material-icons">&#xe5d2;</i>
                            </a>
                            <!-- / -->

                            <!-- Page title - Bind to $state's title -->
                            <div class="mb-0 h5 no-wrap" ng-bind="$state.current.data.title" id="pageTitle"></div>

                            <!-- navbar collapse -->
                            <div class="collapse navbar-collapse" id="collapse">
                                <!-- link and dropdown -->
                                <ul class="nav navbar-nav mr-auto">
                                    <li class="nav-item dropdown">
                                        <a class="nav-link" href data-toggle="dropdown">
                                            <!-- <i class="fa fa-fw fa-info text-muted"></i> -->
                                            <span>System Informasi PT Newwicker Indonesia</span>
                                        </a>
                                    </li>
                                </ul>

                                <!-- <div ui-include="'../views/blocks/navbar.form.html'"></div> -->
                                <!-- / -->
                            </div>
                            <!-- / navbar collapse -->

                            <!-- navbar right -->
                            <ul class="nav navbar-nav ml-auto flex-row">
                                {{-- <li class="nav-item dropdown pos-stc-xs">
                                    <a class="nav-link mr-2" href data-toggle="dropdown">
                                        <i class="material-icons">&#xe7f5;</i>
                                        <span class="label label-sm up warn">3</span>
                                    </a>
                                    <!-- <div ui-include="'../views/blocks/dropdown.notification.html'"></div> -->
                                </li> --}}
    <li class="nav-item">

        <a href="javascript:void(0)"
        id="openProfileDrawer"
        class="nav-link p-0">

            <span class="avatar w-32">

                <img
                    src="{{ asset('assets/images/a0.jpg') }}"
                    alt="Profile"
                    style="width:32px;height:32px;border-radius:50%;object-fit:cover;">

                <small
                    style="
                        display:block;
                        font-size:10px;
                        text-align:center;
                        color:#333;
                    ">
                    {{ auth()->user()->name }}
                </small>

            </span>

        </a>

    </li>
                            </ul>

                            <!-- / navbar right -->
                        </div>
                    </div>
                    {{-- PROFILE DRAWER --}}
    <div id="drawerOverlay"></div>

    <div id="profileDrawer">

        <div class="drawer-header">

            <h5 class="mb-0">
                Account
            </h5>

            <button
                type="button"
                id="closeDrawer"
                class="btn btn-sm btn-danger">
                ✕
            </button>

        </div>

        <div id="drawerMenu">

            <div class="profile-top">

                <img
                    src="../assets/images/a0.jpg"
                    class="drawer-avatar">

                <h6 class="mb-1">
                    {{ auth()->user()->name }}
                </h6>

                <small class="text-muted">
                    {{ auth()->user()->email }}
                </small>

            </div>

            <div class="list-group list-group-flush">

                <a href="#"
                    class="list-group-item"
                    id="btnProfile">

                    👤 Profile

                </a>

                <a href="#"
                    class="list-group-item"
                    id="btnPassword">

                    🔒 Change Password

                </a>

                <a href="#"
                    class="list-group-item text-danger"
                    id="btnLogout">

                    🚪 Logout

                </a>

            </div>

        </div>

        <div id="profileContent"
            style="display:none">
        </div>

    </div>

    <form
        id="logout-form"
        action="{{ route('logout') }}"
        method="POST"
        style="display:none">

        @csrf

    </form>
                    <div class="app-footer">
                        <div class="p-2 text-xs">
                            <div class="pull-right text-muted py-1">
                                &copy; Copyright <strong>Newwicker</strong> <span class="hidden-xs-down">-Develop from Rouf M</span>
                                <a ui-scroll-to="content"><i class="fa fa-long-arrow-up p-x-sm"></i></a>
                            </div>

                        </div>
                    </div>

                    <div ui-view class="app-body" id="view">

                        <!-- ############ PAGE START-->
                        @yield('content')


                        <!-- ############ LAYOUT END-->

                    </div>



                    <!-- jQuery (WAJIB PALING ATAS) -->

                    <!-- Bootstrap 4 -->
        <script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>

                    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

                    <script>
                        var $ = jQuery.noConflict();
                    </script>
                    <!-- <script src="{{asset('assets/libs/jquery/jquery/dist/jquery.js')}}"></script> -->
                    <!-- Bootstrap -->
                    <script src="{{asset('assets/libs/jquery/tether/dist/js/tether.min.js')}}"></script>
                    <script src="{{asset('assets/libs/jquery/bootstrap/dist/js/bootstrap.js')}}"></script>
                    <!-- core -->
                    <script src="{{asset('assets/libs/jquery/underscore/underscore-min.js')}}"></script>
                    <script src="{{asset('assets/libs/jquery/jQuery-Storage-API/jquery.storageapi.min.js')}}"></script>
                    <script src="{{asset('assets/libs/jquery/PACE/pace.min.js')}}"></script>

                    <script src="{{asset('assets/scripts/config.lazyload.js')}}"></script>

                    <script src="{{asset('assets/scripts/palette.js')}}"></script>
                    <script src="{{asset('assets/scripts/ui-load.js')}}"></script>
                    <script src="{{asset('assets/scripts/ui-jp.js')}}"></script>
                    <script src="{{asset('assets/scripts/ui-include.js')}}"></script>
                    <script src="{{asset('assets/scripts/ui-device.js')}}"></script>
                    <script src="{{asset('assets/scripts/ui-form.js')}}"></script>
                    <script src="{{asset('assets/scripts/ui-nav.js')}}"></script>
                    <!-- <script src="{{asset('assets/scripts/ui-screenfull.js')}}"></script> -->
                    <script src="{{asset('assets/scripts/ui-scroll-to.js')}}"></script>
                    <script src="{{asset('assets/scripts/ui-toggle-class.js')}}"></script>

                    <script src="{{asset('assets/scripts/app.js')}}"></script>
                    <!-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap4-multiselect/css/bootstrap-multiselect.css"> -->
                    <!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap4-multiselect/js/bootstrap-multiselect.min.js"></script> -->

                    <!-- Bootstrap JS (include Popper) -->
                    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

                    <!-- ajax -->
                    <!-- <script src="{{asset('assets/libs/jquery/jquery-pjax/jquery.pjax.js')}}"></script> -->
                    <script src="{{asset('assets/scripts/ajax.js')}}"></script>
                    <!-- endbuild -->
                    <!-- <script src=" https://code.jquery.com/jquery-3.6.0.min.js"></script> -->

                    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>

                    <link href="//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/jquery-editable/css/jquery-editable.css" rel="stylesheet" />

                    <script>
                        $.fn.poshytip = {
                            defaults: null
                        }
                    </script>

                    <script src="//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/jquery-editable/js/jquery-editable-poshytip.min.js"></script>
                    <script src="{{asset('assets/main.js')}}"></script>
                    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
                    <script src="{{asset('assets/cam/cam.js')}}" type="text/javascript"></script>
                    <script src="{{asset('assets/chatr.js')}}" type="text/javascript"></script>
                    <!-- <script src="{{asset('assets/pengajuan.js')}}" type="text/javascript"></script> -->

                    <script src="https://cdn.sheetjs.com/xlsx-latest/package/dist/xlsx.full.min.js"></script>

                    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
                    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>

                    <!-- JS -->
                    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
                    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
                    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

                    <!-- DataTables Buttons extension -->
                    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
                    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
                    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>

                    <!-- JSZip (dibutuhkan untuk export Excel) -->
                    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
                    <script>
                        // if (window.history && window.history.pushState) {
                        //     window.history.pushState(null, null, window.location.href);
                        //     window.onpopstate = function() {
                        //         window.location.replace("/");
                        //     };
                        // }
                    </script>
                    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    {{-- new cript --}}
    <script>

    $('#openProfileDrawer').click(function(){

        $('#profileDrawer').addClass('show');
        $('#drawerOverlay').show();

    });

    $('#closeDrawer,#drawerOverlay').click(function(){

        $('#profileDrawer').removeClass('show');
        $('#drawerOverlay').hide();

    });

    $('#btnLogout').click(function(){

        if(confirm('Logout sekarang?')){

            $('#logout-form').submit();

        }

    });

    $('#btnProfile').click(function(e){

        e.preventDefault();

        $('#drawerMenu').hide();

        $('#profileContent').html(`
            <div class="p-3">

                <button
                    class="btn btn-light btn-sm mb-3"
                    onclick="backMenu()">

                    ← Back

                </button>

                <h5>Profile</h5>

                <table class="table table-bordered">

                    <tr>
                        <th>Nama</th>
                        <td>{{ auth()->user()->karyawan->nama_lengkap ?? auth()->user()->name }}</td>
                    </tr>

                    <tr>
                        <th>NIK</th>
                        <td>{{ auth()->user()->karyawan->nik ?? '-' }}</td>
                    </tr>

                    <tr>
                        <th>Divisi</th>
                        <td>{{ auth()->user()->karyawan->divisi->nama ?? '-' }}</td>
                    </tr>

                    <tr>
                        <th>Status</th>
                        <td>{{ auth()->user()->karyawan->status ?? '-' }}</td>
                    </tr>

                    <tr>
                        <th>Lokasi</th>
                        <td>{{ auth()->user()->karyawan->lokasi ?? '-' }}</td>
                    </tr>

                    <tr>
                        <th>Tanggal Join</th>
                        <td>{{ auth()->user()->karyawan->tanggal_join ?? '-' }}</td>
                    </tr>

                </table>

            </div>
        `).show();

    });

    $('#btnPassword').click(function(e){

        e.preventDefault();

        $('#drawerMenu').hide();

        $('#profileContent').html(`

            <div class="p-3">

                <button
                    class="btn btn-light btn-sm mb-3"
                    onclick="backMenu()">

                    ← Back

                </button>

                <h5>Change Password</h5>

            <div class="form-group">

        <label>Password Baru</label>

        <div class="input-group">
    <input
        type="hidden"
        id="_token"
        value="{{ csrf_token() }}">
            <input
                type="password"
                class="form-control"
                id="new_password"
                placeholder="Masukkan password baru">

            <div class="input-group-append">

            <span id="togglePassword" style="cursor:pointer">
        <i class="material-icons">visibility</i>
    </span>

            </div>

        </div>

    </div>

    <button
        type="button"
        id="btnSavePassword"
        class="btn btn-success mt-3">

        Update Password

    </button>
            </div>

        `).show();

    });
    $(document).on('click', '#togglePassword', function () {

        let input = $('#new_password');

        let icon = $(this).find('i');

        if (input.attr('type') === 'password') {

            input.attr('type', 'text');

            icon.removeClass('fa-eye')
                .addClass('fa-eye-slash');

        } else {

            input.attr('type', 'password');

            icon.removeClass('fa-eye-slash')
                .addClass('fa-eye');

        }

    });
    function setRule(selector, valid){

        $(selector).toggleClass(
            'valid',
            valid
        );

    }
    $(document).on(
        'click',
        '#btnSavePassword',
        function () {

            let password =
                $('#new_password').val();

            if (!password) {

                Swal.fire({
                    icon: 'warning',
                    title: 'Password Kosong',
                    text: 'Silakan isi password baru'
                });

                return;
            }

            $.ajax({

                url:
                    "{{ route('profile.change-password') }}",

                type: 'POST',

                data: {

                    _token:
                        "{{ csrf_token() }}",

                    password:
                        password

                },

                success: function (res) {

                    Swal.fire({
                        icon: 'success',
                        title: 'jangan sampe lupa password ya..',
                        text: res.message,
                        timer: 2000,
                        showConfirmButton: false
                    });

                    $('#new_password').val('');

                },

                error: function (xhr) {

                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal',
                        text:
                            xhr.responseJSON?.message ??
                            'Terjadi kesalahan'
                    });

                }

            });

        }
    );
    function backMenu(){

        $('#profileContent')
            .hide()
            .html('');

        $('#drawerMenu')
            .show();

    }

    </script>
    {{-- notifi --}}
    <script>

$(function () {
    checkPfiNotification();
});

function checkPfiNotification() {

    let today =
        new Date()
        .toISOString()
        .slice(0, 10);

    // sudah dibisukan hari ini
    if (
        localStorage.getItem(
            'mute_pfi_notification'
        ) === today
    ) {
        return;
    }

    $.get(
        '/pfi/notifications',
        function (res) {

            if (!res || !res.length) {
                return;
            }

            playNotification();

            showPfiToast(res);

        }
    );
}

function showPfiToast(items) {

    let html = '';

    items.forEach(item => {

        html += `
            <div style="
                margin-bottom:10px;
                text-align:left;
            ">
                🔔 <b>${item.order_no}</b>
                <br>
                Shipment :
                ${item.shipment_date ?? '-'}
                <br>
                Release :
                ${item.created_at}
            </div>
        `;

    });

    html += `
        <hr style="margin:8px 0">

        <div
            id="mutePfiBtn"
            style="
                color:#dc3545;
                cursor:pointer;
                font-weight:600;
                text-align:center;
            "
        >
            🔕 Bisukan Hari Ini
        </div>
    `;

    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'info',
        title: 'PFI Baru Release',
        html: html,
        showConfirmButton: false,
        timer: 15000,
        timerProgressBar: true,

        didOpen: () => {

            $('#mutePfiBtn').on(
                'click',
                function () {

                    let today =
                        new Date()
                        .toISOString()
                        .slice(0, 10);

                    localStorage.setItem(
                        'mute_pfi_notification',
                        today
                    );

                    Swal.close();

                }
            );

        }
    });

}

function playNotification() {

    const audio =
        new Audio(
            '/assets/sounds/notify.wav'
        );

    audio.volume = 1;

    audio.play()
        .catch(() => {});

}

</script>
                    @stack('scripts')

        </body>

        </html>
