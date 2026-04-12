
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">

    <!-- Mobile Web-app fullscreen -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="mobile-web-app-capable" content="yes">

    <!-- Meta tags -->
    <meta name="description" content="">
    <meta name="author" content="">
        <link rel="shortcut icon" href="{{asset('assets/images/newwicker.jpg')}}">

    <!--Title-->
    <title>NewWicker Catalogue</title>

    <!--CSS bundle -->
    <link rel="stylesheet" media="all" href="{{asset('assets/style2/css/bootstrap.css')}}" />
    <link rel="stylesheet" media="all" href="{{asset('assets/style2/css/animate.css')}}" />
    <link rel="stylesheet" media="all" href="{{asset('assets/style2/css/font-awesome.css')}}" />
    <link rel="stylesheet" media="all" href="{{asset('assets/style2/css/ion-range-slider.css')}}" />
    <link rel="stylesheet" media="all" href="{{asset('assets/style2/css/linear-icons.css')}}" />
    <link rel="stylesheet" media="all" href="{{asset('assets/style2/css/magnific-popup.css')}}" />
    <link rel="stylesheet" media="all" href="{{asset('assets/style2/css/owl.carousel.css')}}" />
    <link rel="stylesheet" media="all" href="{{asset('assets/style2/css/theme.css')}}" />

    <!--Google fonts-->
    <link href="https://fonts.googleapis.com/css?family=Poppins:400,500,600" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,700&amp;subset=latin-ext" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Rajdhani:400,600,700&amp;subset=latin-ext" rel="stylesheet">


    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->

</head>

<body>
    <!-- token -->
<div class="modal fade" id="tokenModal" data-backdrop="static">

<div class="modal-dialog modal-dialog-centered">
<div class="modal-content p-4">

<h5 class="text-center mb-4">Enter Access Token</h5>

<div class="otp-inputs text-center mb-3">

<input class="otp" maxlength="1">
<input class="otp" maxlength="1">
<input class="otp" maxlength="1">
<input class="otp" maxlength="1">
<input class="otp" maxlength="1">
<input class="otp" maxlength="1">

</div>

<button class="btn btn-dark w-100" onclick="checkToken()">Verify Token</button>

<div id="visitorForm" style="display:none;margin-top:20px">

<input type="text" id="name" class="form-control mb-2" placeholder="Your Name">

<input type="text" id="company" class="form-control mb-2" placeholder="Company Name">

<input type="email" id="email" class="form-control mb-2" placeholder="Email">

<button class="btn btn-primary w-100" onclick="saveVisitor()">Enter Catalogue</button>

</div>

</div>
</div>

</div>
    <!--  -->
    <!-- <div class="page-loader">
        <div class="spinner-border" role="status">
            <span class="sr-only">Loading...</span>
        </div>
    </div> -->
 <section class="blog blog-block">

    <!--Header-->
    <header>
        <div class="container">
            <h2 class="title">Featured categories</h2>
            <div class="text">
                <p>We just keep things minimal.</p>
            </div>
        </div>
    </header>

    <!--Content-->
    <div class="container">

        <div class="scroll-wrapper">

            <div class="row scroll text-center" id="catalogueGrid">

                <!-- Produk dari AJAX akan masuk ke sini -->

            </div>

        </div>

    </div>

</section>

    <!--Scripts -->
    <script src="{{asset('assets/style2/js/jquery.min.js')}}"></script>
    <script src="{{asset('assets/style2/js/bootstrap.js')}}"></script>
    <script src="{{asset('assets/style2/js/ion.rangeSlider.js')}}"></script>
    <script src="{{asset('assets/style2/js/magnific-popup.js')}}"></script>
    <script src="{{asset('assets/style2/js/owl.carousel.js')}}"></script>
    <script src="{{asset('assets/style2/js/tilt.jquery.js')}}"></script>
    <script src="{{asset('assets/style2/js/jquery.easypiechart.js')}}"></script>
    <script src="{{asset('assets/style2/js/bigtext.js')}}"></script>
    <script src="{{asset('assets/style2/js/main.js')}}"></script>
<script src="https://cdn.jsdelivr.net/npm/@panzoom/panzoom/dist/panzoom.min.js"></script>

    <script>
        $(window).on('load', function () {
            setTimeout(function () {
                $('.filters-fixed').addClass('active');
            }, 2000)
            setTimeout(function () {
                $('.filters-fixed').removeClass('active');
            }, 5500)
        });

    </script>
   </script>
<script src="https://cdn.jsdelivr.net/npm/@panzoom/panzoom/dist/panzoom.min.js"></script>
<!-- otp -->
<script>
$(document).ready(function(){

@if(!session('catalogue_access'))

$('#tokenModal').modal({
    backdrop:'static',
    keyboard:false
});

@else

loadCatalogue();

@endif
});
/* =========================
   OTP INPUT
========================= */

$('.otp').on('keyup', function(e){

let key = e.keyCode || e.which;

/* pindah ke input berikut */
if(this.value.length === 1){
$(this).next('.otp').focus();
}

/* backspace kembali */
if(key === 8){
$(this).prev('.otp').focus();
}

/* auto verify jika sudah 6 */
let token='';
$('.otp').each(function(){
token += $(this).val();
});

if(token.length === 6){
checkToken();
}

});

/* =========================
   PASTE TOKEN
========================= */
/* =========================
   OTP INPUT
========================= */

$('.otp').on('keyup', function(e){

let key = e.keyCode || e.which;

/* pindah ke kotak berikut */
if(this.value.length === 1){
$(this).next('.otp').focus();
}

/* backspace kembali */
if(key === 8){
$(this).prev('.otp').focus();
}

/* cek jika sudah 6 */
let token='';
$('.otp').each(function(){
token += $(this).val();
});

if(token.length === 6){
checkToken();
}

});


/* =========================
   SUPPORT CTRL+V PASTE
========================= */

$('.otp').first().on('paste', function(e){

e.preventDefault();

let paste = (e.originalEvent || e).clipboardData.getData('text');

paste = paste.replace(/[^A-Za-z0-9]/g,'').toUpperCase();

/* hanya ambil 6 karakter */
paste = paste.substring(0,6);

$('.otp').each(function(i){
$(this).val(paste[i] || '');
});

/* fokus ke terakhir */
$('.otp').last().focus();

/* langsung cek token */
if(paste.length === 6){
checkToken();
}

});
/* =========================
   ENTER KEY SUBMIT
========================= */

$(document).on('keypress',function(e){
if(e.which == 13){
checkToken();
}
});

/* =========================
   CHECK TOKEN
========================= */

function checkToken(){

let token='';

$('.otp').each(function(){
token += $(this).val();
});

if(token.length !== 6){
return;
}

$.post('/token-check',{
token:token,
_token:'{{ csrf_token() }}'
},function(res){

if(res.status){

/* token sudah pernah isi visitor */
if(res.direct){

$('#tokenModal').modal('hide');
loadCatalogue();

}else{

$('#visitorForm').fadeIn();
window.token_id = res.token_id;

}

}else{

alert('Token tidak valid');

$('.otp').val('');
$('.otp').first().focus();

}

});

}

/* =========================
   SAVE VISITOR
========================= */

function saveVisitor(){

let name = $('#name').val();
let company = $('#company').val();
let email = $('#email').val();

if(name == '' || company == '' || email == ''){
alert('Please complete your information');
return;
}

$.post('/save-visitor',{

token_id:window.token_id,
name:name,
company:company,
email:email,
_token:'{{ csrf_token() }}'

},function(res){

if(res.status){

$('#tokenModal').modal('hide');
loadCatalogue();

}

});

}


/* =========================
   CHECK TOKEN
========================= */

function checkToken(){

let token='';

$('.otp').each(function(){
token += $(this).val();
});

if(token.length !== 6){
return;
}

$.post('/token-check',{
token:token,
_token:'{{ csrf_token() }}'
},function(res){

if(res.status){

/* jika visitor sudah pernah isi */
if(res.direct){

$('#tokenModal').modal('hide');
loadCatalogue();

}else{

/* tampilkan form visitor */
$('#visitorForm').fadeIn();
window.token_id = res.token_id;

}

}else{

alert('Token tidak valid');

$('.otp').val('');
$('.otp').first().focus();

}

});

}

/* =========================
   SAVE VISITOR
========================= */

function saveVisitor(){

$.post('/save-visitor',{

token_id:window.token_id,
name:$('#name').val(),
company:$('#company').val(),
email:$('#email').val(),
_token:'{{ csrf_token() }}'

},function(res){

if(res.status){

$('#tokenModal').modal('hide');
loadCatalogue();

}

});

}

</script>
<script>

$(document).ready(function(){
    // loadCatalogue();
});

function loadCatalogue(){

    const basePath = "{{ asset('storage/pameran/'.$nm) }}/";

    $.ajax({
        url: "/get-catalogue",
        type: "GET",
        success: function(res){

            if(!res.status) return;

            let html = '';

            res.products.forEach(function(p){

                let img = basePath + p.article_code + ".webp";
                img = img.replace(/ /g,"%20");

                html += `
                <div class="col-md-4">

                    <article data-3d>

                        <a href="${img}" class="popup-image">

                            <div class="image">

                                <img src="${img}"
                                     loading="lazy"
                                     decoding="async"
                                     onerror="this.onerror=null;this.src='{{ asset('assets/style2/no-image.png') }}';">

                                <img src="{{ asset('assets/style2/watermark.png') }}" class="item-watermark">

                            </div>

                            <div class="entry entry-block">

                                <div class="label">${p.categories ?? 'Collection'}</div>

                                <div class="title">
                                    <h2 class="h4">${p.name}</h2>
                                </div>

                                <div class="description d-none d-sm-block">
                                    <p>Article Code : ${p.article_code}</p>
                                </div>

                            </div>

                            <div class="show-more">
                                <span class="btn btn-clean">View product</span>
                            </div>

                        </a>

                    </article>

                </div>
                `;
            });

            $('#catalogueGrid').html(html);

            initPopup(); // panggil popup setelah html selesai dibuat
        }
    });
}

function initPopup(){

    $('.popup-image').magnificPopup({
        type: 'image',
        gallery:{
            enabled:true
        },
        callbacks:{

            open: function(){

                // hint zoom
                if(!$('.zoom-hint').length){
                    $('.mfp-wrap').append(`
                        <div class="zoom-hint">
                            Scroll to zoom • Drag to pan
                        </div>
                    `);

                    setTimeout(function(){
                        $('.zoom-hint').fadeOut(800);
                    },3000);
                }

                // watermark popup
                if(!$('.popup-watermark').length){
                    $('.mfp-wrap').append(`
                        <img src="{{ asset('assets/style2/no-image.png') }}" class="popup-watermark">
                    `);
                }

            },

            close:function(){
                $('.popup-watermark').remove();
            },

            imageLoadComplete:function(){

                const img = document.querySelector('.mfp-img');

                if(!img) return;

                const panzoom = Panzoom(img,{
                    maxScale:6,
                    minScale:1,
                    contain:'outside'
                });

                img.parentElement.addEventListener('wheel', panzoom.zoomWithWheel);

            }

        }
    });

}

</script>


<style>

/* thumbnail image */
.otp-inputs{
display:flex;
justify-content:center;
gap:10px;
}

.otp{
width:45px;
height:55px;
font-size:26px;
text-align:center;
border:1px solid #ddd;
border-radius:6px;
}
.image{
    position:relative;
}

.item-watermark{
    position:absolute;
    top:50%;
    left:50%;
    transform:translate(-50%,-50%);
    width:65%;
    opacity:0.08;
    pointer-events:none;
}


/* popup cursor */

.mfp-img{
    cursor:grab;
}

.mfp-img:active{
    cursor:grabbing;
}


/* popup watermark */

.popup-watermark{
    position:fixed;
    top:50%;
    left:50%;
    transform:translate(-50%,-50%);
    width:40%;
    opacity:0.20;
    pointer-events:none;
    z-index:9999;
}


/* zoom hint */

.zoom-hint{
    position:fixed;
    bottom:30px;
    left:50%;
    transform:translateX(-50%);
    background:rgba(0,0,0,0.75);
    color:#fff;
    padding:8px 16px;
    border-radius:20px;
    font-size:13px;
    z-index:10000;
}

</style>

</body>

</html>
