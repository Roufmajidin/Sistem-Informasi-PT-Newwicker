<!DOCTYPE html>
<html>
<head>
    <title>Preview Dokumen</title>

    <style>
        body {
            margin:0;
            background:#202124;
            font-family:sans-serif;
        }

        .topbar {
            position:sticky;
            top:0;
            background:#111;
            color:#fff;
            padding:12px 20px;
            z-index:10;
            font-size:16px;
        }

        .pdf-container {
            max-width:900px;
            margin:auto;
            padding:20px;
        }

        .page {
            background:#fff;
            margin-bottom:25px;
            border-radius:10px;
            box-shadow:0 5px 15px rgba(0,0,0,0.4);
            overflow:hidden;
        }

        .page-header {
            padding:8px 12px;
            font-size:12px;
            color:#999;
            background:#f5f5f5;
        }

        /* 🔥 WRAPPER */
        .image-wrapper {
            width:100%;
            background:#000;
            display:flex;
            justify-content:center;
            align-items:center;
        }

        /* 🔥 IMAGE AUTO RATIO */
        .image-wrapper img {
            max-width:100%;
            height:auto;
            display:block;
        }

        /* 🔥 RESPONSIVE HP */
        @media (max-width:768px){
            .pdf-container {
                padding:10px;
            }
        }
        .zoomable {
    max-width:100%;
    height:auto;
    cursor: grab;
    transition: transform 0.2s ease;
}
    </style>
</head>

<body>

<div class="topbar">
    📄 Preview Dokumen
</div>

<div class="pdf-container">

@foreach($files as $i => $f)
    <div class="page">

        <div class="page-header">
            Halaman {{ $i+1 }}
        </div>

       <div class="image-wrapper">
    <img class="zoomable" src="/storage/{{ $f->file_path }}">
</div>

    </div>
@endforeach

</div>

</body>
</html>
<script>
document.querySelectorAll('.zoomable').forEach(img => {

    let scale = 1;
    let posX = 0;
    let posY = 0;
    let isDragging = false;
    let startX, startY;

    // 🔥 SCROLL ZOOM
    img.addEventListener('wheel', function(e){
        e.preventDefault();

        let delta = e.deltaY > 0 ? -0.1 : 0.1;
        scale += delta;

        if(scale < 1) scale = 1;
        if(scale > 5) scale = 5;

        update();
    });

    // 🔥 DOUBLE CLICK
    img.addEventListener('dblclick', function(){
        scale = scale === 1 ? 2 : 1;
        posX = 0;
        posY = 0;
        update();
    });

    // 🔥 DRAG
    img.addEventListener('mousedown', function(e){
        isDragging = true;
        startX = e.clientX - posX;
        startY = e.clientY - posY;
        img.style.cursor = 'grabbing';
    });

    window.addEventListener('mousemove', function(e){
        if(!isDragging) return;

        posX = e.clientX - startX;
        posY = e.clientY - startY;

        update();
    });

    window.addEventListener('mouseup', function(){
        isDragging = false;
        img.style.cursor = 'grab';
    });

    function update(){
        img.style.transform = `translate(${posX}px, ${posY}px) scale(${scale})`;
    }

});
</script>
