<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title></title>
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
    body {
        background: #020617;
        overflow-x: hidden;
    }

    /* WRAPPER */
    .detail-wrapper {
        padding: 15px;
        min-height: 100vh;
    }

    /* MAIN CARD */
    .main-card {
        border-radius: 30px;
        overflow: hidden;
        background: rgba(255, 255, 255, .04);
        border: 1px solid rgba(255, 255, 255, .05);
        backdrop-filter: blur(20px);
    }

    /* LEFT */
    .left-side {
        position: relative;
        height: 88vh;
        background: black;
        overflow: hidden;
    }

    /* IMAGE */
    #imagePanel {
        position: absolute;
        inset: 0;
    }

    #imagePanel img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .image-overlay {
        position: absolute;
        inset: 0;
        background:
            linear-gradient(to top,
                rgba(2, 6, 23, .95),
                rgba(2, 6, 23, .1));
    }

    .floating-badge {
        position: absolute;
        top: 20px;
        left: 20px;
        background: rgba(255, 255, 255, .1);
        border: 1px solid rgba(255, 255, 255, .1);
        backdrop-filter: blur(20px);
        color: white;
        padding: 10px 18px;
        border-radius: 999px;
        font-size: 11px;
        letter-spacing: 1px;
        z-index: 2;
    }

    .image-title {
        position: absolute;
        left: 30px;
        bottom: 30px;
        color: white;
        z-index: 2;
    }

    .image-title h1 {
        font-size: 48px;
        font-weight: 700;
        margin-bottom: 5px;
        word-break: break-word;
    }

    .image-title p {
        opacity: .7;
        font-size: 18px;
    }

    /* RIGHT */
    .right-side {
        padding: 25px;
        color: white;
        height: 88vh;
        overflow-y: auto;
    }

    .section-title {
        color: #64748b;
        font-size: 11px;
        letter-spacing: 2px;
        margin-bottom: 14px;
        text-transform: uppercase;
    }

    .spec-card {
        padding: 18px;
        border-radius: 22px;
        margin-bottom: 15px;
        background: rgba(255, 255, 255, .04);
        border: 1px solid rgba(255, 255, 255, .05);
    }

    .spec-label {
        font-size: 11px;
        color: #94a3b8;
        margin-bottom: 5px;
    }

    .spec-value {
        font-size: 18px;
        font-weight: 600;
        word-break: break-word;
    }

    .notes-box {
        padding: 20px;
        border-radius: 22px;
        background: rgba(255, 255, 255, .04);
        color: #cbd5e1;
        line-height: 1.8;
        word-break: break-word;
    }

    /* TIMELINE */
    .timeline-item {
        border-left: 2px solid rgba(255, 255, 255, .08);
        padding-left: 15px;
        margin-bottom: 20px;
    }

    .timeline-date {
        font-size: 11px;
        color: #64748b;
    }

    .timeline-text {
        color: white;
        margin-top: 5px;
    }

    /* CHAT */
    #chatPanel {
        position: absolute;
        inset: 0;
        background: #020617;
        display: none;
        flex-direction: column;
        z-index: 10;
    }

    .chat-header {
        padding: 20px;
        color: white;
        font-size: 20px;
        font-weight: 700;
        border-bottom: 1px solid rgba(255, 255, 255, .05);
    }

    .chat-body {
        flex: 1;
        overflow-y: auto;
        padding: 20px;
    }

    .chat-item {
        margin-bottom: 18px;
    }

    .chat-user {
        color: #38bdf8;
        font-size: 13px;
        font-weight: 600;
        margin-bottom: 5px;
    }

    .chat-message {
        display: inline-block;
        background: rgba(255, 255, 255, .06);
        padding: 12px 16px;
        border-radius: 18px;
        color: white;
        max-width: 85%;
        word-break: break-word;
    }

    .chat-time {
        margin-top: 5px;
        color: #64748b;
        font-size: 11px;
    }

    .chat-footer {
        padding: 15px;
        border-top: 1px solid rgba(255, 255, 255, .05);
    }

    #commentForm {
        display: flex;
        gap: 10px;
    }

    .chat-input {
        flex: 1;
        border: none;
        height: 50px;
        border-radius: 999px;
        background: rgba(255, 255, 255, .06);
        color: white;
        padding: 0 18px;
        min-width: 0;
    }

    .chat-input:focus {
        outline: none;
    }

    .send-btn {
        border: none;
        width: 90px;
        border-radius: 999px;
        background: #38bdf8;
        color: white;
        font-weight: 600;
    }

    /* BUTTON */
    .open-chat-btn {
        width: 100%;
        height: 52px;
        border: none;
        border-radius: 18px;
        background: #38bdf8;
        color: white;
        font-weight: 600;
    }

    .close-chat-btn {
        position: absolute;
        top: 15px;
        right: 15px;
        width: 42px;
        height: 42px;
        border: none;
        border-radius: 50%;
        background: red;
        color: white;
        z-index: 20;
    }

    .back-btn {
        position: fixed;
        right: 20px;
        bottom: 20px;
        width: 58px;
        height: 58px;
        border: none;
        border-radius: 50%;
        background: white;
        color: #020617;
        font-size: 22px;
        font-weight: bold;
        z-index: 999;
    }

    /* MOBILE */
    @media(max-width:991px) {

        .left-side {
            height: 50vh;
        }

        .right-side {
            height: auto;
            overflow: visible;
        }

        .image-title h1 {
            font-size: 30px;
        }

        .image-title p {
            font-size: 14px;
        }

        .chat-message {
            max-width: 100%;
        }

        .main-card {
            border-radius: 20px;
        }

    }

    @media(max-width:575px) {

        .detail-wrapper {
            padding: 10px;
        }

        .right-side {
            padding: 18px;
        }

        .image-title {
            left: 20px;
            bottom: 20px;
        }

        .image-title h1 {
            font-size: 24px;
        }

        .spec-value {
            font-size: 16px;
        }

        .chat-header {
            font-size: 18px;
        }

        .send-btn {
            width: 75px;
            font-size: 13px;
        }

    }
</style>
</head>

<body>
    <div class="app" id="app">



        <!-- content -->

<div class="detail-wrapper">

    <div class="main-card">

        <div class="row no-gutters">

            <!-- LEFT -->
            <div class="col-lg-7">

                <div class="left-side">

                    <!-- IMAGE -->
                    <div id="imagePanel">

                        <img src="{{ asset('foto_inventory/' . $inventory->foto) }}">

                        <div class="image-overlay"></div>

                        <div class="floating-badge">
                            INVENTORY ASSET
                        </div>

                        <div class="image-title">
                            <h1>{{ $inventory->merk }}</h1>
                            <p>{{ $inventory->jenis }}</p>
                        </div>

                    </div>

                    <!-- CHAT -->
                    <div id="chatPanel">

                        <button class="close-chat-btn"
                            id="closeChat">
                            ×
                        </button>

                        <div class="chat-header">
                            Asset Discussion
                        </div>

                        <div class="chat-body">

                            @foreach($inventory->comments as $comment)

                            <div class="chat-item">

                                <div class="chat-user">
                                    {{ $comment->user->name }}
                                </div>

                                <div class="chat-message">
                                    {{ $comment->message }}
                                </div>

                                <div class="chat-time">
                                    {{ $comment->created_at->diffForHumans() }}
                                </div>

                            </div>

                            @endforeach

                        </div>

                        <div class="chat-footer">

                            <form id="commentForm">

                                @csrf

                                <input type="hidden"
                                    name="inventory_id"
                                    value="{{ $inventory->id }}">

                                <input type="text"
                                    name="message"
                                    class="chat-input"
                                    placeholder="Write message...">

                                <button class="send-btn">
                                    Send
                                </button>

                            </form>

                        </div>

                    </div>

                </div>

            </div>

            <!-- RIGHT -->
            <div class="col-lg-5">

                <div class="right-side">

                    <div class="section-title">
                        Information
                    </div>

                    <div class="spec-card">
                        <div class="spec-label">
                            Pemegang
                        </div>

                        <div class="spec-value">
                            {{ $inventory->karyawan->nama_lengkap ?? '-' }}
                        </div>
                    </div>

                    <div class="spec-card">
                        <div class="spec-label">
                            Keterangan
                        </div>

                        <div class="spec-value">
                            {{ $inventory->keterangan ?? '-' }}
                        </div>
                    </div>

                    <div class="spec-card">
                        <div class="spec-label">
                            Updated
                        </div>

                        <div class="spec-value">
                            {{ $inventory->updated_at->format('d M Y H:i') }}
                        </div>
                    </div>

                    <div class="mt-4 section-title">
                        Description
                    </div>

                    <div class="notes-box">
                        {{ $inventory->deskripsi ?? 'Tidak ada deskripsi.' }}
                    </div>

                    <div class="mt-4">
                        <button class="open-chat-btn"
                            id="openChat">
                            Add Comment
                        </button>
                    </div>

                    <div class="mt-5 section-title">
                        Activity
                    </div>

                    <div class="timeline">

                        <div class="timeline-item">

                            <div class="timeline-date">
                                {{ $inventory->created_at->format('d M Y') }}
                            </div>

                            <div class="timeline-text">
                                Asset berhasil dibuat
                            </div>

                        </div>

                        <div class="timeline-item">

                            <div class="timeline-date">
                                {{ $inventory->updated_at->format('d M Y') }}
                            </div>

                            <div class="timeline-text">
                                Data terakhir diperbarui
                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>

<button class="back-btn"
 onclick="goBackPage()">
    ←
</button>



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

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>

function goBackPage(){

    // kalau sebelumnya login
    if(document.referrer.includes('/login')){

        window.location.href = "/";

        return;

    }

    // normal back
    window.history.back();

}

</script>
<script>

    // hapus login redirect dari history
    if(document.referrer.includes('/login')){

        history.replaceState(
            null,
            null,
            window.location.href
        );

    }

</script>
<script>
    // =========================
    // AUTO SCROLL CHAT BOTTOM
    // =========================
    function scrollChatToBottom() {

        let chatBody = document.querySelector(".chat-body");

        if (chatBody) {

            chatBody.scrollTop = chatBody.scrollHeight;

        }

    }

    // =========================
    // OPEN CHAT PANEL
    // =========================
    $("#openChat").click(function() {

        $("#imagePanel").fadeOut(200, function() {

            $("#chatPanel")
                .css("display", "flex")
                .hide()
                .fadeIn(250, function() {

                    scrollChatToBottom();

                    $(".chat-input").focus();

                });

        });

    });

    // =========================
    // CLOSE CHAT PANEL
    // =========================
    $("#closeChat").click(function() {

        $("#chatPanel").fadeOut(200, function() {

            $("#imagePanel").fadeIn(250);

        });

    });

    // =========================
    // SUBMIT COMMENT
    // =========================
  $("#commentForm").submit(function(e){

    e.preventDefault();

    // cek login
    let isLogin = {{ auth()->check() ? 'true' : 'false' }};

    // kalau belum login
 if(!isLogin){

    Swal.fire({

        icon: 'warning',

        title: 'Login Required',

        text: 'Silakan login terlebih dahulu untuk mengirim pesan.',

        confirmButtonText: 'Login Sekarang',

        background: '#0f172a',

        color: '#fff',

        confirmButtonColor: '#38bdf8'

    }).then((result) => {

        if(result.isConfirmed){

            window.location.href =
                "{{ route('login') }}" +
                "?redirect=" +
                encodeURIComponent(window.location.href);

        }

    });

    return;
}

    let form = $(this);

    let input = $(".chat-input");

    let message = input.val().trim();

    if(message == ""){

        input.focus();

        return;

    }

    $.ajax({

        url:"{{ route('inventory.comment.store') }}",

        type:"POST",

        data:form.serialize(),

        beforeSend:function(){

            $(".send-btn")
                .html("Sending...")
                .prop("disabled", true);

        },

        success:function(){

            $(".chat-body").append(`

                <div class="chat-item">

                    <div class="chat-user">
                        {{ auth()->check() ? auth()->user()->name : '' }}
                    </div>

                    <div class="chat-message">
                        ${message}
                    </div>

                    <div class="chat-time">
                        baru saja
                    </div>

                </div>

            `);

            input.val("");

            scrollChatToBottom();

        },

        complete:function(){

            $(".send-btn")
                .html("Send")
                .prop("disabled", false);

        }

    });

});


    // =========================
    // ENTER TO SEND
    // =========================
    $(".chat-input").keypress(function(e) {

        if (e.which == 13) {

            e.preventDefault();

            $("#commentForm").submit();

        }

    });

    // =========================
    // AUTO SCROLL ON LOAD
    // =========================
    $(document).ready(function() {

        scrollChatToBottom();

    });
</script>
        @stack('scripts')

</body>

</html>

