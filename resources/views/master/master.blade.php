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
            {{-- NewWicker Turbo Boost: opt-in only, aman untuk halaman legacy --}}
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

        {{-- Terapkan mode SEBELUM body dirender agar tidak terjadi sidebar flash --}}
        <script id="nw-navigation-early-mode">
            (function () {
                var mode = 'sidebar';
                try { if (localStorage.getItem('nw_navigation_mode') === 'navbar') mode = 'navbar'; } catch (e) {}
                document.documentElement.classList.toggle('nw-navbar-mode', mode === 'navbar');
            })();
        </script>
        <style id="nw-navigation-no-flash">
            @media (min-width: 992px) {
                html.nw-navbar-mode #aside { display: none !important; }
                html.nw-navbar-mode #content.app-content { margin-left: 0 !important; padding-left: 0 !important; width: 100% !important; }
                html.nw-navbar-mode #nwHorizontalNav:not([data-nw-ready="1"]) { visibility: hidden !important; }
            }
        </style>
        {{-- Navbar alternatif dibangun dari menu sidebar yang sama; sidebar asli tidak diubah --}}
        <style id="nw-navigation-mode-style">
            .nw-mode-header-item { display:flex; align-items:center; margin-right:12px; }
            .nw-mode-select { border:1px solid #d9e1ec; border-radius:7px; background:#fff; color:#334155; font-size:12px; padding:6px 8px; max-width:126px; cursor:pointer; }
            .nw-mode-drawer-item { background:#f8fafc; }
            #nwHorizontalNav { display:none; }
            @media(min-width:992px) {
                html.nw-navbar-mode #aside { display:none !important; }
                html.nw-navbar-mode #nwHorizontalNav {
                    display:flex !important; align-items:flex-start; gap:15px; width:100%;
                    position:sticky; top:0; z-index:3000; padding:10px 18px; background:#193a58;
                    box-shadow:0 3px 12px rgba(15,35,55,.15); min-height:66px;
                }
                #nwHorizontalNav .nw-brand { flex:0 0 125px; display:flex; align-items:center; min-height:45px; }
                #nwHorizontalNav .nw-brand img { width:120px; max-height:52px; object-fit:contain; }
                #nwHorizontalNav .nw-nav-main { flex:1; min-width:0; }
                #nwHorizontalNav ul { list-style:none; padding:0; margin:0; }
                #nwHorizontalNav .nw-nav-root { display:flex; flex-wrap:wrap; align-items:center; gap:3px 5px; }
                #nwHorizontalNav .nw-nav-root > li { position:relative; flex:0 0 auto; }
                #nwHorizontalNav .nw-nav-root > li.nav-header { display:none; }
                #nwHorizontalNav a { text-decoration:none; cursor:pointer; }
                #nwHorizontalNav .nw-nav-root > li > a {
                    display:flex; align-items:center; gap:6px; min-height:38px; padding:7px 10px;
                    color:#f4f8fd; font-size:12px; font-weight:500; border-radius:7px;
                }
                #nwHorizontalNav a.nw-current-link { background:#dbeafe !important; color:#164c91 !important; font-weight:700 !important; box-shadow:inset 3px 0 #2563eb; }
                #nwHorizontalNav .nw-nav-root > li > a.nw-current-parent { background:#2864a5 !important; color:#fff !important; font-weight:700 !important; }
                #aside a.nw-current-link { background:rgba(88,163,238,.22) !important; color:#fff !important; font-weight:700 !important; }
                #aside li.nw-current-parent > a { color:#fff !important; font-weight:700 !important; }
                #nwHorizontalNav .nw-nav-root > li > a:hover,
                #nwHorizontalNav li.nw-sub-open > a { background:#2864a5; color:#fff; }
                #nwHorizontalNav .nav-icon { display:inline-flex; width:auto; margin:0; }
                #nwHorizontalNav .nav-icon i { font-size:17px; }
                #nwHorizontalNav .nav-text { display:inline; white-space:nowrap; }
                #nwHorizontalNav .nav-caret { display:inline-flex; margin-left:2px; }
                #nwHorizontalNav ul.nav-sub {
                    display:none !important; position:absolute !important; top:100% !important; left:0 !important;
                    width:240px !important; min-width:240px; max-height:70vh; overflow:visible;
                    padding:7px !important; background:#fff !important; border:1px solid #dce5ef;
                    border-radius:9px; box-shadow:0 12px 30px rgba(12,35,62,.2); z-index:1150;
                }
                #nwHorizontalNav li.nw-sub-open > ul.nav-sub { display:block !important; }
                #nwHorizontalNav ul.nav-sub li { position:relative; }
                #nwHorizontalNav ul.nav-sub a {
                    display:flex; align-items:center; justify-content:space-between; gap:8px;
                    color:#26394d !important; padding:9px 11px !important; border-radius:6px; font-size:12px;
                }
                #nwHorizontalNav ul.nav-sub a:hover { background:#eaf3ff !important; color:#1558a6 !important; }
                #nwHorizontalNav ul.nav-sub ul.nav-sub { top:0 !important; left:100% !important; }
                #nwHorizontalNav li.nw-edge > ul.nav-sub { left:auto !important; right:0 !important; }
                #nwHorizontalNav ul.nav-sub li.nw-edge > ul.nav-sub { left:auto !important; right:100% !important; }
                html.nw-navbar-mode #app { padding-left:0 !important; overflow:visible !important; }
                html.nw-navbar-mode body { overflow-x:clip; }
                html.nw-navbar-mode #nwHorizontalNav .nw-nav-main { overflow:visible !important; }

                html.nw-navbar-mode #content.app-content {
                    margin-left:0 !important; padding-left:0 !important; padding-top:0 !important;
                    width:100% !important; min-height:0;
                }
                html.nw-navbar-mode #content .app-header { position:relative !important; top:auto !important; left:auto !important; width:100% !important; }
                html.nw-navbar-mode .app-body { width:100% !important; }
            }
            @media(max-width:991px) { .nw-mode-header-item { display:none !important; } }
        </style>
        <script id="nw-navigation-mode-script">
        (function(){
            var KEY='nw_navigation_mode';
            function getMode(){try{return localStorage.getItem(KEY)==='navbar'?'navbar':'sidebar';}catch(e){return 'sidebar';}}
            function closeMenus(){document.querySelectorAll('#nwHorizontalNav .nw-sub-open').forEach(function(li){li.classList.remove('nw-sub-open');});}
            function apply(mode,save){
                mode=mode==='navbar'?'navbar':'sidebar';
                if(save){try{localStorage.setItem(KEY,mode);}catch(e){}}
                document.documentElement.classList.toggle('nw-navbar-mode',mode==='navbar');
                document.querySelectorAll('#nwModeHeader,#nwModeDrawer').forEach(function(el){el.value=mode;});
                closeMenus();
            }
            function buildNavbar(){
                var sidebar=document.querySelector('#aside');
                if(!sidebar || document.getElementById('nwHorizontalNav'))return;
                var root=sidebar.querySelector('ul.nav[ui-nav]');
                if(!root)return;
                var bar=document.createElement('nav');bar.id='nwHorizontalNav';bar.setAttribute('aria-label','Navigasi utama');
                var brand=document.createElement('div');brand.className='nw-brand';
                var img=sidebar.querySelector('.navbar-brand img');
                if(img){var logo=img.cloneNode(true);brand.appendChild(logo);}
                var main=document.createElement('div');main.className='nw-nav-main';
                var tree=root.cloneNode(true);tree.classList.add('nw-nav-root');
                tree.removeAttribute('ui-nav');
                tree.querySelectorAll('[ui-include]').forEach(function(node){node.removeAttribute('ui-include');});
                main.appendChild(tree);bar.appendChild(brand);bar.appendChild(main);
                sidebar.insertAdjacentElement('afterend',bar);
                bar.setAttribute('data-nw-ready','1');
            }
            function markActiveLinks(){
                var current=location.pathname.replace(/\/+$/,'') || '/';
                ['#aside','#nwHorizontalNav'].forEach(function(selector){
                    var area=document.querySelector(selector);
                    if(!area)return;
                    area.querySelectorAll('a.nw-current-link').forEach(function(a){a.classList.remove('nw-current-link');a.removeAttribute('aria-current');});
                    area.querySelectorAll('li.nw-current-parent').forEach(function(li){li.classList.remove('nw-current-parent');});
                    area.querySelectorAll('a[href]').forEach(function(a){
                        var url;try{url=new URL(a.getAttribute('href'),location.href);}catch(e){return;}
                        if(url.origin!==location.origin || (url.pathname.replace(/\/+$/,'')||'/')!==current)return;
                        a.classList.add('nw-current-link');a.setAttribute('aria-current','page');
                        var li=a.closest('li');
                        while(li){
                            li.classList.add('nw-current-parent');
                            li=li.parentElement && li.parentElement.closest('li');
                        }
                    });
                });
            }
            function init(){buildNavbar();apply(getMode(),false);markActiveLinks();}
            document.addEventListener('change',function(e){if(e.target.matches('#nwModeHeader,#nwModeDrawer'))apply(e.target.value,true);});
            document.addEventListener('click',function(e){
                if(!document.documentElement.classList.contains('nw-navbar-mode'))return;
                var a=e.target.closest('#nwHorizontalNav li > a');
                if(a){
                    var li=a.parentElement, sub=li.querySelector(':scope > ul.nav-sub');
                    if(sub && (!a.hasAttribute('href') || !a.getAttribute('href') || a.getAttribute('href')==='#')){
                        e.preventDefault();e.stopPropagation();
                        var opening=!li.classList.contains('nw-sub-open');
                        var parent=li.parentElement.closest('li');
                        Array.from(li.parentElement.children).forEach(function(sibling){if(sibling!==li)sibling.classList.remove('nw-sub-open');});
                        if(!parent)closeMenus();
                        li.classList.toggle('nw-sub-open',opening);
                        if(opening){var r=li.getBoundingClientRect();li.classList.toggle('nw-edge',r.left+255>innerWidth);}
                        return;
                    }
                }
                if(!e.target.closest('#nwHorizontalNav'))closeMenus();
            },true);
            document.addEventListener('keydown',function(e){if(e.key==='Escape')closeMenus();});
            if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',init);else init();
            document.addEventListener('turbo:load',init);
            document.addEventListener('turbo:before-render',function(){document.documentElement.classList.toggle('nw-navbar-mode',getMode()==='navbar');});
        })();
        </script>

        </head>

        <body data-turbo="false">
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

                            {{-- Mode navigasi: default Sidebar --}}
                            <li class="nav-item nw-mode-header-item">
                                <label for="nwModeHeader" class="sr-only">Mode navigasi</label>
                                <select id="nwModeHeader" class="nw-mode-select" aria-label="Mode navigasi">
                                    <option value="sidebar">☰ Sidebar</option>
                                    <option value="navbar">▤ Navbar</option>
                                </select>
                            </li>
                                <li class="nav-item">

                                    <a href="javascript:void(0)" id="openProfileDrawer" class="nav-link p-0">

                                        <span class="avatar w-32">

                                            <img src="{{ asset('assets/images/a0.jpg') }}" alt="Profile"
                                                style="width:32px;height:32px;border-radius:50%;object-fit:cover;">

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

                                <img src="../assets/images/a0.jpg" class="drawer-avatar">

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

                                {{-- Pilihan mode tetap tersedia di menu Account, setelah Logout --}}
                                <div class="list-group-item nw-mode-drawer-item">
                                    <label for="nwModeDrawer" class="mb-2 d-block" style="font-weight:600">Mode Navigasi</label>
                                    <select id="nwModeDrawer" class="form-control" aria-label="Mode navigasi">
                                        <option value="sidebar">☰ Sidebar</option>
                                        <option value="navbar">▤ Navbar</option>
                                    </select>
                                </div>

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
                    <script src="{{ asset('assets/libs/jquery/jquery/dist/jquery.js') }}"></script> -->
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
                        $('#openProfileDrawer').click(function() {

                            $('#profileDrawer').addClass('show');
                            $('#drawerOverlay').show();

                        });

                        $('#closeDrawer,#drawerOverlay').click(function() {

                            $('#profileDrawer').removeClass('show');
                            $('#drawerOverlay').hide();

                        });

                        $('#btnLogout').click(function() {

                            if (confirm('Logout sekarang?')) {

                                $('#logout-form').submit();

                            }

                        });

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
                    @stack('scripts')

        </body>

        </html>
