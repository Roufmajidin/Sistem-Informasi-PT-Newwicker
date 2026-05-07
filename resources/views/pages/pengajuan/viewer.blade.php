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
            overflow: visible;
            transition:0.3s;
        }

        .page-header {
            padding:8px 12px;
            font-size:12px;
            color:#999;
            background:#f5f5f5;
        }

        .image-wrapper {
            position: relative;
            display:flex;
            justify-content:center;
            align-items:center;
            background:#000;
            overflow: visible;
            transition:0.3s;
        }

        .zoomable {
            max-width:100%;
            height:auto;
            cursor: grab;
            user-select:none;
            transition: transform 0.15s ease;
            transform-origin:center center;
        }

        .btn-rotate {
            position:absolute;
            top:50%;
            left:50%;
            transform:translate(-50%, -50%);
            background:rgba(0,0,0,0.6);
            color:#fff;
            border:none;
            border-radius:50%;
            padding:12px;
            cursor:pointer;
            opacity:0;
            transition:0.3s;
            z-index:10;
        }

        .image-wrapper:hover .btn-rotate {
            opacity:1;
        }

        @media (max-width:768px){
            .pdf-container {
                padding:10px;
            }
        }
    </style>
</head>

<body>

<div class="topbar">📄 Preview Dokumen</div>

<div class="pdf-container">


@foreach($files as $i => $f)
    <div class="page">
        <div class="page-header">
            Halaman {{ $i+1 }}
        </div>

        <div class="image-wrapper">
            <img class="zoomable" src="/storage/{{ $f->file_path }}">
            <button style="width: 100px;height:100px" class="btn-rotate">⟳</button>
        </div>
    </div>
@endforeach

</div>

<script>
document.querySelectorAll('.image-wrapper').forEach(wrapper => {

    const img = wrapper.querySelector('.zoomable');
    const rotateBtn = wrapper.querySelector('.btn-rotate');

    if (!img) return;

    let scale = 1;
    let rotation = 0;
    let posX = 0;
    let posY = 0;

    let isDragging = false;
    let startX, startY;

    function update(){

        img.style.transform =
            `translate(${posX}px, ${posY}px) scale(${scale}) rotate(${rotation}deg)`;

        // 🔥 bikin page ikut menyesuaikan
        setTimeout(() => {
            if (rotation % 180 !== 0) {
                wrapper.style.height = img.offsetWidth + 'px';
            } else {
                wrapper.style.height = img.offsetHeight + 'px';
            }
        }, 30);
    }

    // 🔄 ROTATE
    if (rotateBtn) {
        rotateBtn.addEventListener('click', (e) => {
            e.stopPropagation();

            rotation += 90;

            scale = 1;
            posX = 0;
            posY = 0;

            update();
        });
    }

    // 🔍 ZOOM
    wrapper.addEventListener('wheel', (e) => {
        e.preventDefault();

        let delta = e.deltaY > 0 ? -0.1 : 0.1;
        scale += delta;

        if (scale < 1) scale = 1;
        if (scale > 4) scale = 4;

        update();
    });

    // 🔥 DOUBLE CLICK RESET
    img.addEventListener('dblclick', () => {
        scale = 1;
        rotation = 0;
        posX = 0;
        posY = 0;
        update();
    });

    // ✋ DRAG
    img.addEventListener('mousedown', (e) => {
        if (scale <= 1) return;

        isDragging = true;
        startX = e.clientX - posX;
        startY = e.clientY - posY;

        img.style.cursor = 'grabbing';
    });

    window.addEventListener('mousemove', (e) => {
        if (!isDragging) return;

        posX = e.clientX - startX;
        posY = e.clientY - startY;

        update();
    });

    window.addEventListener('mouseup', () => {
        isDragging = false;
        img.style.cursor = 'grab';
    });

});
</script>

</body>
</html>
