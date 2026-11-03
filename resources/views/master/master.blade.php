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
            <link rel="apple-touch-icon" href="{{ asset('assets/images/NEWWICKER WHITE.png') }}">
            <meta name="apple-mobile-web-app-title" content="Flatkit">
            <!-- for Chrome on Android, multi-resolution icon of 196x196 -->
            <meta name="mobile-web-app-capable" content="yes">
            <link rel="shortcut icon" href="{{ asset('assets/images/newwicker.jpg') }}">
            <meta name="csrf-token" content="{{ csrf_token() }}">
            {{-- Turbo hanya aktif pada link yang diberi data-turbo="true" --}}
            <meta name="turbo-cache-control" content="no-cache">
            <script src="https://cdn.jsdelivr.net/npm/@hotwired/turbo@8.0.20/dist/turbo.es2017-umd.js" defer></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/icon?family=Material+Icons"
      rel="stylesheet">
            {{-- NewWicker Turbo Boost: opt-in only, aman untuk halaman legacy --}}
<style>
    /* Material Icons Sidebar */
.nav-icon .material-icons {
    font-family: 'Material Icons' !important;
    font-weight: normal;
    font-style: normal;
    font-size: 20px;
    line-height: 1;
    letter-spacing: normal;
    text-transform: none;
    white-space: nowrap;
    word-wrap: normal;
    direction: ltr;
    display: inline-block;
    font-feature-settings: 'liga';
    *-webkit-font-feature-settings*: 'liga';
    *-webkit-font-smoothing*: antialiased;
}
/* Ukuran icon sidebar */
.nav-icon {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 24px;
    min-width: 24px;
    vertical-align: middle;
}
</style>
            <script defer>
                (function() {
                    function setupTurbo() {
                        if (!window.Turbo || window.__newwickerTurboConfigured) return;
                        window.__newwickerTurboConfigured = true;
                        // Default: semua link tetap navigasi biasa.
                        // Hanya <a data-turbo="true"> yang memakai Turbo.
                        Turbo.session.drive = false;
                        Turbo.setProgressBarDelay(150);
                        // Prefetch hanya pada link yang diberi kedua atribut:
                        // data-turbo="true" data-turbo-prefetch="true"
                        document.addEventListener('turbo:before-prefetch', function(event) {
                            var link = event.target && event.target.closest ?
                                event.target.closest('a[href]') : null;
                            if (!link || link.getAttribute('data-turbo') !== 'true' ||
                                link.getAttribute('data-turbo-prefetch') !== 'true') {
                                event.preventDefault();
                                return;
                            }
                            // Jangan prefetch halaman berat / sensitif.
                            try {
                                var url = new URL(link.href, location.href);
                                if (url.origin !== location.origin ||
                                    /^(\/it-dashboard|\/produksi\/inventor|\/spk|\/inventory|\/qc)(\/|$)/i.test(url
                                        .pathname)) {
                                    event.preventDefault();
                                }
                            } catch (_) {
                                event.preventDefault();
                            }
                        });
                    }
                    if (document.readyState === 'loading') {
                        document.addEventListener('DOMContentLoaded', setupTurbo, {
                            once: true
                        });
                    } else {
                        setupTurbo();
                    }
                })();
            </script>
            <style>
                .turbo-progress-bar {
                    height: 3px;
                    background: #2563eb;
                    z-index: 99999;
                }
            </style>
            <link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
            <!-- style -->
            <link rel="stylesheet" href="{{ asset('assets/animate.css/animate.min.css') }}" type="text/css" />
            <link rel="stylesheet" href="{{ asset('assets/glyphicons/glyphicons.css') }}" type="text/css" />
            <link rel="stylesheet" href="{{ asset('assets/font-awesome/css/font-awesome.min.css') }}" type="text/css" />
            <link rel="stylesheet" href="{{ asset('assets/material-design-icons/material-design-icons.css') }}"
                type="text/css" />
            <link rel="stylesheet" href="{{ asset('assets/bootstrap/dist/css/bootstrap.min.css') }}" type="text/css" />
            <!-- build:css ../assets/styles/app.min.css -->
            <link rel="stylesheet" href="{{ asset('assets/styles/app.css') }}" type="text/css" />
            <!-- endbuild -->
            <link rel="stylesheet" href="{{ asset('assets/styles/font.css') }}" type="text/css" />
            <link rel="stylesheet" href="{{ asset('assets/styles/custome.css') }}" type="text/css" />
            <link rel="stylesheet" href="{{ asset('assets/styles/style_pengajuan.css') }}" type="text/css" />
            <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/css/bootstrap.min.css" /> -->
            <!-- <link href="{{ asset('assets/editable/css/bootstrap.min.css') }}}}" rel="stylesheet"> -->
            <script src="{{ asset('assets/editable/js/bootstrap.min.js') }}"></script>
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
            <!-- <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet"> -->
            <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
            <meta http-equiv="Pragma" content="no-cache">
            <meta http-equiv="Expires" content="0">
            <style>
                #drawerOverlay {
                    position: fixed;
                    inset: 0;
                    background: rgba(0, 0, 0, .5);
                    display: none;
                    z-index: 9998;
                }
                #profileDrawer {
                    position: fixed;
                    top: 0;
                    right: -420px;
                    width: 420px;
                    height: 100vh;
                    background: #fff;
                    z-index: 9999;
                    transition: .3s;
                    overflow-y: auto;
                    box-shadow: -5px 0 20px rgba(0, 0, 0, .15);
                }
                #profileDrawer.show {
                    right: 0;
                }
                .drawer-header {
                    display: flex;
                    justify-content: space-between;
                    align-items: center;
                    padding: 15px;
                    border-bottom: 1px solid #eee;
                }
                .profile-top {
                    text-align: center;
                    padding: 20px;
                }
                .drawer-avatar {
                    width: 80px;
                    height: 80px;
                    border-radius: 50%;
                    margin-bottom: 10px;
                }
                .password-rule {
                    color: red;
                    margin-bottom: 5px;
                    font-size: 13px;
                }
                .password-rule.valid {
                    color: green;
                }
            </style>
        </head>
        <body data-turbo="false">
            @php
                $loginUser = auth()->user();
                // QC permission: only users assigned access_module.name = notify_qc
                $canNotifyQc = $loginUser
                    ? \App\Models\UserAccess::query()
                        ->where('user_id', $loginUser->id)
                        ->whereHas('module', function ($query) {
                            $query->where('name', 'notify_qc');
                        })
                        ->exists()
                    : false;
                $profileKaryawan = $loginUser?->karyawan_id
                    ? \App\Models\Karyawan::find($loginUser->karyawan_id)
                    : null;
                $profileInitial = mb_strtoupper(mb_substr(trim($loginUser?->name ?: '?'), 0, 1));
                $profilePhoto = null;
                if ($profileKaryawan && !empty($profileKaryawan->photo) && $profileKaryawan->photo !== 'default.png') {
                    $photoPath = 'upload/user/' . $profileKaryawan->photo;
                    if (\Illuminate\Support\Facades\Storage::disk('public')->exists($photoPath)) {
                        $profilePhoto = asset('storage/' . $photoPath);
                    }
                }
            @endphp
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
                            {{-- master blade --}}
                            <!-- navbar collapse -->
                            <div class="collapse navbar-collapse" id="collapse">
                                <!-- link and dropdown -->
                                <ul class="nav navbar-nav mr-auto">
                                    <li class="nav-item dropdown">
                                        <a class="nav-link" href data-toggle="dropdown">
                                            <!-- <i class="fa fa-fw fa-info text-muted"></i> -->
                                            {{-- <span>System Informasi PT Newwicker Indonesia</span> --}}
                                            @yield('btn')
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
                                                {{-- QC Activity Notification: sebelum User Profile --}}
                @if ($canNotifyQc)
                <li class="nav-item" id="nw-qc-nav" style="display:flex;align-items:center;margin-right:12px">
                    <button type="button" id="nw-qc-toggle" class="nav-link" aria-label="Buka notifikasi QC" title="QC Activity" style="position:relative;background:transparent;border:0;cursor:pointer;color:#344054;padding:8px">
                        <i class="material-icons" style="font-size:25px;vertical-align:middle">notifications_none</i>
                        <span id="nw-qc-badge" style="display:none;position:absolute;top:0;right:0;background:#dc3545;color:#fff;border-radius:15px;font-size:10px;min-width:17px;padding:1px 4px">0</span>
                    </button>
                </li>
                @endif
<li class="nav-item">
                                    <a href="javascript:void(0)" id="openProfileDrawer" class="nav-link p-0">
                                        <span class="avatar w-32">
                                            @if ($profilePhoto)
                                                <img src="{{ $profilePhoto }}" alt="Profile"
                                                    style="width:32px;height:32px;border-radius:50%;object-fit:cover;">
                                            @else
                                                <span aria-label="Inisial profil {{ $profileInitial }}"
                                                    style="display:flex;width:32px;height:32px;border-radius:50%;background:#2563eb;color:#fff;align-items:center;justify-content:center;font-size:15px;font-weight:700;">{{ $profileInitial }}</span>
                                            @endif
                                            <small
                                                style="
                        display:block;
                        font-size:10px;
                        text-align:center;
                        color:#333;
                    ">
                                                {{ auth()->user()?->name ?? 'Approval Guest' }}
                                            </small>
                                        </span>
                                    </a>
                                </li>
                            </ul>
                            <!-- / navbar right -->
                        </div>
                    </div>
                @if ($canNotifyQc)
                {{-- QC NOTIFICATION CENTER - independen dari Profile Drawer --}}
                <div id="nw-qc-overlay" style="display:none;position:fixed;inset:0;background:rgba(15,23,42,.32);z-index:10040"></div>
                <aside id="nw-qc-panel" role="dialog" aria-modal="true" aria-label="QC Activity Notifications" aria-hidden="true" style="position:fixed;right:0;top:0;bottom:0;width:min(430px,100vw);background:#fff;z-index:10041;box-shadow:-5px 0 30px #0002;transform:translateX(105%);transition:transform .25s;display:flex;flex-direction:column">
                    <header style="padding:17px;border-bottom:1px solid #e5e7eb;display:flex;justify-content:space-between;align-items:center;gap:10px">
                        <div><strong style="font-size:18px">QC Activity Notifications</strong><div style="font-size:12px;color:#667085">NewWicker · Inspection & Incoming</div></div>
                        <button type="button" id="nw-qc-close" aria-label="Tutup" style="border:0;background:#f2f4f7;border-radius:8px;padding:7px 11px;cursor:pointer">✕</button>
                    </header>
                    <div style="padding:10px 15px;border-bottom:1px solid #eee;display:flex;align-items:center;justify-content:space-between">
                        <span style="font-size:12px;color:#667085" id="nw-qc-status">Live via Pusher</span>
                        <button type="button" id="nw-qc-read-all" style="border:0;background:transparent;color:#2563eb;cursor:pointer;font-size:12px">Mark all as read</button>
                    </div>
                    <div id="nw-qc-list" aria-live="polite" style="overflow:auto;flex:1;padding:10px"><div style="padding:20px;color:#667085;text-align:center">Klik untuk memuat aktivitas QC terbaru.</div></div>
                </aside>
                <style>
                    .nw-qc-item{padding:13px;margin:5px 0 10px;border:1px solid #e5e7eb;border-radius:12px;background:#fff}
                    .nw-qc-item.nw-qc-unread{border-color:#bfdbfe;background:#eff6ff}
                    .nw-qc-tag{display:inline-block;font-size:11px;border-radius:6px;padding:3px 7px;margin-right:5px;margin-top:8px}
                </style>
                @endif
                {{-- PROFILE DRAWER --}}
                    <div id="drawerOverlay"></div>
                    <div id="profileDrawer">
                        <div class="drawer-header">
                            <h5 class="mb-0">
                                Account
                            </h5>
                            <button type="button" id="closeDrawer" class="btn btn-sm btn-danger">
                                ✕
                            </button>
                        </div>
                        <div id="drawerMenu">
                            <div class="profile-top">
                                @if ($profilePhoto)
                                    <img src="{{ $profilePhoto }}" class="drawer-avatar"
                                        alt="Foto profil" style="object-fit:cover;">
                                @else
                                    <div class="drawer-avatar" aria-label="Inisial profil {{ $profileInitial }}"
                                        style="background:#2563eb;color:#fff;margin-left:auto;margin-right:auto;display:flex;align-items:center;justify-content:center;font-size:34px;font-weight:700;">{{ $profileInitial }}</div>
                                @endif
                                <h6 class="mb-1">
                                    {{ auth()->user()?->name ?? 'Approval Guest' }}
                                </h6>
                                <small class="text-muted">
                                    {{ auth()->user()?->email ?? '-' }}
                                </small>
                            </div>
                            <div class="list-group list-group-flush">
                                <a href="#" class="list-group-item" id="btnProfile">
                                    👤 Profile
                                </a>
                                <a href="#" class="list-group-item" id="btnPassword">
                                    🔒 Change Password
                                </a>
                                <a href="#" class="list-group-item text-danger" id="btnLogout">
                                    🚪 Logout
                                </a>
                            </div>
                        </div>
                        <div id="profileContent" style="display:none">
                        </div>
                    </div>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display:none">
                        @csrf
                    </form>
                    <div class="app-footer">
                        <div class="p-2 text-xs">
                            <div class="pull-right text-muted py-1">
                                &copy; Copyright <strong>Newwicker</strong> <span class="hidden-xs-down">-Develop from
                                    Rouf M</span>
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
                    <script src="{{ asset('assets/libs/jquery/jquery/dist/jquery.js') }}"></script>
                    <!-- Bootstrap -->
                    <script src="{{ asset('assets/libs/jquery/tether/dist/js/tether.min.js') }}"></script>
                    <script src="{{ asset('assets/libs/jquery/bootstrap/dist/js/bootstrap.js') }}"></script>
                    <!-- core -->
                    <script src="{{ asset('assets/libs/jquery/underscore/underscore-min.js') }}"></script>
                    {{-- <script src="{{asset('assets/libs/jquery/jQuery-Storage-API/jquery.storageapi.min.js')}}"></script> --}}
                    <script src="{{ asset('assets/libs/jquery/PACE/pace.min.js') }}"></script>
                    <script src="{{ asset('assets/scripts/config.lazyload.js') }}"></script>
                    <script src="{{ asset('assets/scripts/palette.js') }}"></script>
                    <script src="{{ asset('assets/scripts/ui-load.js') }}"></script>
                    <script src="{{ asset('assets/scripts/ui-jp.js') }}"></script>
                    <script src="{{ asset('assets/scripts/ui-include.js') }}"></script>
                    <script src="{{ asset('assets/scripts/ui-device.js') }}"></script>
                    <script src="{{ asset('assets/scripts/ui-form.js') }}"></script>
                    <script src="{{ asset('assets/scripts/ui-nav.js') }}"></script>
                    <!-- <script src="{{ asset('assets/scripts/ui-screenfull.js') }}"></script> -->
                    <script src="{{ asset('assets/scripts/ui-scroll-to.js') }}"></script>
                    <script src="{{ asset('assets/scripts/ui-toggle-class.js') }}"></script>
                    <script src="{{ asset('assets/scripts/app.js') }}"></script>
                    <!-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap4-multiselect/css/bootstrap-multiselect.css"> -->
                    <!-- <script src="https://cdn.jsdelivr.net/npm/bootstrap4-multiselect/js/bootstrap-multiselect.min.js"></script> -->
                    <!-- Bootstrap JS (include Popper) -->
                    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
                    <!-- ajax -->
                    <!-- <script src="{{ asset('assets/libs/jquery/jquery-pjax/jquery.pjax.js') }}"></script> -->
                    <script src="{{ asset('assets/scripts/ajax.js') }}"></script>
                    <!-- endbuild -->
                    <!-- <script src=" https://code.jquery.com/jquery-3.6.0.min.js"></script> -->
                    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
                    <link
                        href="//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/jquery-editable/css/jquery-editable.css"
                        rel="stylesheet" />
                    <script>
                        $.fn.poshytip = {
                            defaults: null
                        }
                    </script>
                    <script src="//cdnjs.cloudflare.com/ajax/libs/x-editable/1.5.0/jquery-editable/js/jquery-editable-poshytip.min.js">
                    </script>
                    <script src="{{ asset('assets/main.js') }}"></script>
                    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
                    <script src="{{ asset('assets/cam/cam.js') }}" type="text/javascript"></script>
                    {{-- <script src="{{asset('assets/chatr.js')}}" type="text/javascript"></script> --}}
                    <!-- <script src="{{ asset('assets/pengajuan.js') }}" type="text/javascript"></script> -->
                    <script src="https://cdn.sheetjs.com/xlsx-latest/package/dist/xlsx.full.min.js"></script>
                    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
                    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
                    <!-- JS -->
                    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
                    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
                    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
                    <!-- DataTables Buttons extension -->
                    <link rel="stylesheet"
                        href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
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
                        // Drawer & logout ditangani dengan native JS di bawah,
                        // agar tetap berfungsi walaupun plugin jQuery lain bermasalah.
                        $('#btnProfile').click(function(e) {
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
                        <td>{{ auth()->user()?->karyawan?->nama_lengkap ?? (auth()->user()?->name ?? 'Guest') }}</td>
                    </tr>
                    <tr>
                        <th>NIK</th>
                        <td>{{ auth()->user()?->karyawan?->nik ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Divisi</th>
                        <td>{{ auth()->user()?->karyawan?->divisi?->nama ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>{{ auth()->user()?->karyawan?->status ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Lokasi</th>
                        <td>{{ auth()->user()?->karyawan?->lokasi ?? '-' }}</td>
                    </tr>
                    <tr>
                        <th>Tanggal Join</th>
                        <td>{{ auth()->user()?->karyawan?->tanggal_join ?? '-' }}</td>
                    </tr>
                </table>
            </div>
        `).show();
                        });
                        $('#btnPassword').click(function(e) {
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
                        $(document).on('click', '#togglePassword', function() {
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
                        function setRule(selector, valid) {
                            $(selector).toggleClass(
                                'valid',
                                valid
                            );
                        }
                        $(document).on(
                            'click',
                            '#btnSavePassword',
                            function() {
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
                                    url: "{{ route('profile.change-password') }}",
                                    type: 'POST',
                                    data: {
                                        _token: "{{ csrf_token() }}",
                                        password: password
                                    },
                                    success: function(res) {
                                        Swal.fire({
                                            icon: 'success',
                                            title: 'Berhasil reset password.',
                                            text: res.message,
                                            timer: 2000,
                                            showConfirmButton: false
                                        });
                                        $('#new_password').val('');
                                    },
                                    error: function(xhr) {
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Gagal',
                                            text: xhr.responseJSON?.message ??
                                                'Terjadi kesalahan'
                                        });
                                    }
                                });
                            }
                        );
                        function backMenu() {
                            $('#profileContent')
                                .hide()
                                .html('');
                            $('#drawerMenu')
                                .show();
                        }
                    </script>
                    {{-- notifi --}}
                    <script>
                        $(function() {
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
                                function(res) {
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
`;
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'info',
                                title: `PFI Baru Release <span style="
                display:inline-flex;
                align-items:center;
                justify-content:center;
                min-width:18px;
                height:18px;
                padding:0 5px;
                margin-left:6px;
                border-radius:999px;
                background:#dc3545;
                color:#fff;
                font-size:10px;
                font-weight:700;
                line-height:1;
            ">${items.length}</span>
            <button type="button" id="mutePfiBtn" style="
                border:0;
                background:transparent;
                color:#dc3545;
                cursor:pointer;
                font-size:11px;
                font-weight:600;
                padding:0;
                margin-left:5px;
            ">🔕 Bisukan</button>`,
                                html: html,
                                showConfirmButton: false,
                                timer: 15000,
                                timerProgressBar: true,
                                didOpen: () => {
                                    $('#mutePfiBtn').on(
                                        'click',
                                        function() {
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
                    {{-- Profile drawer: tidak bergantung pada jQuery --}}
                    <script>
                    (function () {
                        if (window.__newwickerProfileDrawerReady) return;
                        window.__newwickerProfileDrawerReady = true;
                        document.addEventListener('click', function (event) {
                            const open = event.target.closest('#openProfileDrawer');
                            const close = event.target.closest('#closeDrawer, #drawerOverlay');
                            const logout = event.target.closest('#btnLogout');
                            if (!open && !close && !logout) return;
                            event.preventDefault();
                            const drawer = document.getElementById('profileDrawer');
                            const overlay = document.getElementById('drawerOverlay');
                            if (open) {
                                if (drawer) drawer.classList.add('show');
                                if (overlay) overlay.style.display = 'block';
                            } else if (close) {
                                if (drawer) drawer.classList.remove('show');
                                if (overlay) overlay.style.display = 'none';
                            } else if (logout && confirm('Logout sekarang?')) {
                                const form = document.getElementById('logout-form');
                                if (form) form.submit();
                            }
                        });
                    })();
                    </script>
                    @stack('scripts')
                @if ($canNotifyQc)
                {{-- QC Notification realtime: tanpa interval/polling --}}
                <script>
                (function () {
                    'use strict';
                    if (window.__nwQcWidgetReady) return;
                    window.__nwQcWidgetReady = true;
                    const $ = id => document.getElementById(id);
                    const endpoint = @json(url('/qc/notifications'));
                    const storageKey = 'nw_qc_seen_{{ auth()->id() ?? "guest" }}';
                    let notifications = [];
                    let loaded = false;
                    let seen = new Set();
                    try { seen = new Set(JSON.parse(localStorage.getItem(storageKey) || '[]').map(String)); } catch (_) {}
                    const esc = value => String(value ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
                    function persistSeen() {
                        try { localStorage.setItem(storageKey, JSON.stringify([...seen].slice(-500))); } catch (_) {}
                    }
                    function isSupported(item) { return ['post','incoming'].includes(String(item?.type ?? '').trim().toLowerCase()); }
                    function notificationKey(item) { return String(item.type || 'post') + ':' + String(item.id || ''); }
                    function normalize(item) {
                        const passed = Number(item.passed) || 0;
                        const rejected = Number(item.rejected) || 0;
                        return {
                            id: String(item.id ?? ''), type: String(item.type ?? 'post').toLowerCase(),
                            user: item.user || 'QC', spk_no: item.spk_no || item.spk_id || '-',
                            item: item.item || '-', kategori: item.kategori || 'QC',
                            passed, rejected, qty: Number(item.qty ?? (passed + rejected)),
                            created_at: item.created_at || null
                        };
                    }
                    function updateBadge() {
                        const count = notifications.filter(n => n.id && !seen.has(notificationKey(n))).length;
                        const badge = $('nw-qc-badge');
                        badge.textContent = count > 99 ? '99+' : String(count);
                        badge.style.display = count ? 'block' : 'none';
                    }
                    function render() {
                        const root = $('nw-qc-list');
                        if (!notifications.length) {
                            root.innerHTML = '<div style="text-align:center;padding:25px;color:#667085">Belum ada aktivitas QC.</div>';
                            updateBadge(); return;
                        }
                        root.innerHTML = notifications.map(n => {
                            const date = n.created_at ? new Date(n.created_at) : null;
                            const time = date && !isNaN(date.getTime()) ? date.toLocaleString('id-ID') : '-';
                            return `<article class="nw-qc-item ${seen.has(notificationKey(n)) ? '' : 'nw-qc-unread'}">
                                <div style="display:flex;justify-content:space-between;gap:8px"><strong style="color:#155e75">${n.type === 'incoming' ? 'QC Incoming Submitted' : 'QC Inspection Submitted'}</strong><small style="color:#667085">${esc(time)}</small></div>
                                <div style="margin-top:7px;font-size:13px;line-height:1.65"><b>${esc(n.user)}</b> ${n.type === 'incoming' ? 'submitted incoming for' : 'has inspected SPK <b>' + esc(n.spk_no) + '</b>,'} item <b>${esc(n.item)}</b>, <b>${esc(n.qty)} pcs</b>.</div>
                                <div><span class="nw-qc-tag" style="background:#dcfce7;color:#166534">Passed ${esc(n.passed)}</span><span class="nw-qc-tag" style="background:#fee2e2;color:#991b1b">Rejected ${esc(n.rejected)}</span></div>
                                <div style="font-size:11px;color:#667085;margin-top:8px">${esc(n.kategori)}</div>
                            </article>`;
                        }).join('');
                        updateBadge();
                    }
                    function addNotification(raw) {
                        if (!isSupported(raw)) return false;
                        const n = normalize(raw);
                        if (!n.id || notifications.some(x => notificationKey(x) === notificationKey(n))) return false;
                        notifications.unshift(n);
                        notifications = notifications.slice(0, 50);
                        render(); return true;
                    }
                    function openPanel() {
                        $('nw-qc-overlay').style.display = 'block';
                        $('nw-qc-panel').style.transform = 'translateX(0)';
                        $('nw-qc-panel').setAttribute('aria-hidden', 'false');
                    }
                    function closePanel() {
                        $('nw-qc-overlay').style.display = 'none';
                        $('nw-qc-panel').style.transform = 'translateX(105%)';
                        $('nw-qc-panel').setAttribute('aria-hidden', 'true');
                    }
                    async function loadHistory() {
                        if (loaded) return;
                        try {
                            const res = await fetch(endpoint, {credentials:'same-origin', headers:{Accept:'application/json'}});
                            if (!res.ok) throw new Error('HTTP ' + res.status);
                            const payload = await res.json();
                            const rows = Array.isArray(payload.data) ? payload.data : [];
                            // Merge agar event realtime yang datang saat request tidak hilang.
                            rows.filter(isSupported).forEach(row => {
                                const n = normalize(row);
                                if (n.id && !notifications.some(x => notificationKey(x) === notificationKey(n))) notifications.push(n);
                            });
                            notifications.sort((a,b) => (Date.parse(b.created_at)||0)-(Date.parse(a.created_at)||0) || Number(b.id)-Number(a.id));
                            notifications = notifications.slice(0,50);
                            loaded = true;
                            render();
                        } catch (e) {
                            $('nw-qc-status').textContent = 'Riwayat gagal dimuat: ' + e.message;
                        }
                    }
                    $('nw-qc-toggle').addEventListener('click', () => { openPanel(); loadHistory(); });
                    $('nw-qc-close').addEventListener('click', closePanel);
                    $('nw-qc-overlay').addEventListener('click', closePanel);
                    $('nw-qc-read-all').addEventListener('click', () => {
                        notifications.forEach(n => { if(n.id) seen.add(notificationKey(n)); });
                        persistSeen(); render();
                    });
                    document.addEventListener('keydown', e => { if (e.key === 'Escape') closePanel(); });
function subscribe() {
    if (typeof window.Pusher === 'undefined') {
        const message = 'Pusher JS belum tersedia.';
        console.error('QC Notification:', message);
        $('nw-qc-status').textContent = message;
        return;
    }
    const key = @json(config('broadcasting.connections.pusher.key'));
    const cluster = @json(config('broadcasting.connections.pusher.options.cluster'));
    if (!key) {
        console.error('QC Notification: PUSHER_APP_KEY belum dikonfigurasi.');
        $('nw-qc-status').textContent = 'Pusher key belum dikonfigurasi';
        return;
    }
    const pusher = new window.Pusher(key, {
        cluster: cluster || 'ap1',
        forceTLS: true,
        channelAuthorization: {
            endpoint: @json(url('/broadcasting/auth')),
            transport: 'ajax',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                'Accept': 'application/json'
            }
        }
    });
    window.__nwQcPusher = pusher;
    pusher.connection.bind('connected', function () {
        console.log('QC Pusher connected');
        $('nw-qc-status').textContent = 'Live via Pusher · Connected';
    });
    pusher.connection.bind('disconnected', function () {
        $('nw-qc-status').textContent = 'Pusher disconnected';
    });
    pusher.connection.bind('error', function (error) {
        console.error('QC Pusher connection error:', error);
        $('nw-qc-status').textContent = 'Pusher connection error';
    });
    const channel = pusher.subscribe('private-qc-activity');
    channel.bind('pusher:subscription_succeeded', function () {
        console.log('QC Notification channel subscribed');
        $('nw-qc-status').textContent = 'Live via Pusher · online';
    });
    channel.bind('pusher:subscription_error', function (error) {
        console.error('QC Notification subscription error:', error);
        $('nw-qc-status').textContent = 'Subscription error (cek /broadcasting/auth)';
    });
    channel.bind('qc.inspection.submitted', function (data) {
        console.log('QC Notification received:', data);
        if (!isSupported(data)) return;
        // addNotification() menjaga duplikat berdasarkan type + id,
        // memasukkan event terbaru di posisi pertama, dan memperbarui badge.
        if (addNotification(data)) {
            openPanel();
            loadHistory();
        }
    });
}
// Muat Pusher sebelum subscribe, tanpa polling atau cronjob.
function initQcPusher() {
    if (window.Pusher) {
        subscribe();
        return;
    }
    const script = document.createElement('script');
    script.src = 'https://js.pusher.com/8.4.0/pusher.min.js';
    script.async = false;
    script.onload = function () {
        console.log('QC Notification: Pusher JS berhasil dimuat');
        subscribe();
    };
    script.onerror = function () {
        console.error('QC Notification: gagal memuat Pusher JS (cek Network/CSP).');
        $('nw-qc-status').textContent = 'Pusher JS gagal dimuat';
    };
    document.head.appendChild(script);
}
// Subscribe sekali setelah DOM siap.
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initQcPusher, { once: true });
} else {
    initQcPusher();
}
                })();
                </script>
                @endif
        </body>
        </html>
