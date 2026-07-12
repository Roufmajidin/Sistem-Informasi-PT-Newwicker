
/*
|--------------------------------------------------------------------------
| Container Loading - Tetris Drag & Drop
|--------------------------------------------------------------------------
| Features:
| - Drag carton dengan mouse
| - Snap ke grid (seperti Tetris)
| - Tidak bisa keluar container
| - Tidak bisa menembus carton lain
| - Nama carton di atas box
|--------------------------------------------------------------------------
*/

let scene, camera, renderer, controls;

let cartons = [];
let selectable = [];
let selected = null;
let isDragging = false;

const mouse = new THREE.Vector2();
const raycaster = new THREE.Raycaster();

const dragPlane = new THREE.Plane(
    new THREE.Vector3(0, 1, 0),
    0
);

const dragOffset = new THREE.Vector3();

const containers = {
    '20':   { nama: '20ft',    p: 589,  l: 235, t: 239 },
    '40':   { nama: '40ft',    p: 1203, l: 235, t: 239 },
    '40hc': { nama: '40ft HC', p: 1203, l: 235, t: 269 }
};

document.addEventListener('DOMContentLoaded', function () {
    initViewer();
});

/*
|--------------------------------------------------------------------------
| Viewer
|--------------------------------------------------------------------------
*/
function initViewer() {

    const viewer = document.getElementById('viewer');

    scene = new THREE.Scene();
    scene.background = new THREE.Color(0xf3f3f3);

    camera = new THREE.PerspectiveCamera(
        45,
        viewer.clientWidth / viewer.clientHeight,
        1,
        10000
    );

    camera.position.set(-800, 400, 800);

    renderer = new THREE.WebGLRenderer({
        antialias: true
    });

    renderer.setSize(
        viewer.clientWidth,
        viewer.clientHeight
    );

    viewer.innerHTML = '';
    viewer.appendChild(renderer.domElement);

    controls = new THREE.OrbitControls(
        camera,
        renderer.domElement
    );

    controls.enableDamping = true;
    controls.target.set(0, 100, 0);

    scene.add(
        new THREE.AmbientLight(
            0xffffff,
            1
        )
    );

    const light =
        new THREE.DirectionalLight(
            0xffffff,
            1
        );

    light.position.set(
        500,
        1000,
        500
    );

    scene.add(light);

    const grid =
        new THREE.GridHelper(
            3000,
            60
        );

    scene.add(grid);

    drawContainer();

    renderer.domElement.addEventListener(
        'pointerdown',
        onPointerDown
    );

    renderer.domElement.addEventListener(
        'pointermove',
        onPointerMove
    );

    renderer.domElement.addEventListener(
        'pointerup',
        onPointerUp
    );

    animate();
}

function drawContainer() {

    const c =
        containers['40hc'];

    const floor =
        new THREE.Mesh(

            new THREE.BoxGeometry(
                c.p,
                4,
                c.l
            ),

            new THREE.MeshStandardMaterial({
                color: 0x444444
            })
        );

    floor.position.y = 2;

    scene.add(floor);

    const body =
        new THREE.Mesh(

            new THREE.BoxGeometry(
                c.p,
                c.t,
                c.l
            ),

            new THREE.MeshStandardMaterial({
                color: 0x777777,
                transparent: true,
                opacity: 0.15,
                side: THREE.DoubleSide
            })
        );

    body.position.y = c.t / 2;

    scene.add(body);

    const frame =
        new THREE.LineSegments(

            new THREE.EdgesGeometry(
                new THREE.BoxGeometry(
                    c.p,
                    c.t,
                    c.l
                )
            ),

            new THREE.LineBasicMaterial({
                color: 0x666666
            })
        );

    frame.position.y = c.t / 2;

    scene.add(frame);
}

/*
|--------------------------------------------------------------------------
| Carton
|--------------------------------------------------------------------------
*/
function createCarton(item) {

    const group =
        new THREE.Group();

    group.userData = {
        nama: item.nama,
        p: item.p,
        l: item.l,
        t: item.t,
        movable: true
    };

    const geometry =
        new THREE.BoxGeometry(
            item.p,
            item.t,
            item.l
        );

    const mesh =
        new THREE.Mesh(
            geometry,
            new THREE.MeshStandardMaterial({
                color: item.warna
            })
        );

    group.add(mesh);

    const outline =
        new THREE.LineSegments(

            new THREE.EdgesGeometry(
                geometry
            ),

            new THREE.LineBasicMaterial({
                color: 0x222222
            })
        );

    group.add(outline);

    const label =
        makeTextSprite(
            item.nama
        );

    label.position.set(
        0,
        item.t / 2 + 10,
        0
    );

    group.add(label);

    return group;
}

function makeTextSprite(text) {

    const canvas =
        document.createElement('canvas');

    const ctx =
        canvas.getContext('2d');

    canvas.width = 512;
    canvas.height = 128;

    ctx.fillStyle =
        'rgba(255,255,255,.9)';

    ctx.fillRect(
        0,
        0,
        canvas.width,
        canvas.height
    );

    ctx.fillStyle = '#000';
    ctx.font = 'Bold 40px Arial';
    ctx.textAlign = 'center';

    ctx.fillText(
        text,
        canvas.width / 2,
        75
    );

    const texture =
        new THREE.CanvasTexture(
            canvas
        );

    const sprite =
        new THREE.Sprite(

            new THREE.SpriteMaterial({
                map: texture
            })
        );

    sprite.scale.set(
        90,
        22,
        1
    );

    return sprite;
}

/*
|--------------------------------------------------------------------------
| Tetris Snap
|--------------------------------------------------------------------------
*/
function snap(v, size = 5) {
    return Math.round(v / size) * size;
}

function isColliding(target) {

    const box1 =
        new THREE.Box3()
            .setFromObject(target);

    for (const item of selectable) {

        if (
            item.uuid === target.uuid
        ) {
            continue;
        }

        const box2 =
            new THREE.Box3()
                .setFromObject(item);

        if (
            box1.intersectsBox(box2)
        ) {
            return true;
        }
    }

    return false;
}

/*
|--------------------------------------------------------------------------
| Drag
|--------------------------------------------------------------------------
*/
function onPointerDown(e) {

    const rect =
        renderer.domElement
            .getBoundingClientRect();

    mouse.x =
        ((e.clientX - rect.left)
        / rect.width) * 2 - 1;

    mouse.y =
        -((e.clientY - rect.top)
        / rect.height) * 2 + 1;

    raycaster.setFromCamera(
        mouse,
        camera
    );

    const hit =
        raycaster.intersectObjects(
            selectable,
            true
        );

    if (!hit.length) {
        selected = null;
        return;
    }

    selected =
        hit[0].object;

    while (
        selected.parent &&
        selected.parent.type === 'Group'
    ) {
        selected =
            selected.parent;
    }

    isDragging = true;
    controls.enabled = false;

    dragOffset.copy(
        selected.position
    ).sub(hit[0].point);
}

function onPointerMove(e) {

    if (
        !selected ||
        !isDragging
    ) {
        return;
    }

    const rect =
        renderer.domElement
            .getBoundingClientRect();

    mouse.x =
        ((e.clientX - rect.left)
        / rect.width) * 2 - 1;

    mouse.y =
        -((e.clientY - rect.top)
        / rect.height) * 2 + 1;

    raycaster.setFromCamera(
        mouse,
        camera
    );

    const point =
        new THREE.Vector3();

    raycaster.ray.intersectPlane(
        dragPlane,
        point
    );

    const oldPos =
        selected.position.clone();

    selected.position.x =
        snap(
            point.x +
            dragOffset.x
        );

    selected.position.z =
        snap(
            point.z +
            dragOffset.z
        );

    if (
        isColliding(selected)
    ) {
        selected.position.copy(
            oldPos
        );
    }
}

function onPointerUp() {

    isDragging = false;
    controls.enabled = true;

    if (!selected) {
        return;
    }

    const c =
        containers['40hc'];

    const box =
        new THREE.Box3()
            .setFromObject(selected);

    const size =
        new THREE.Vector3();

    box.getSize(size);

    const maxX =
        c.p / 2 -
        size.x / 2;

    const minX =
        -c.p / 2 +
        size.x / 2;

    const maxZ =
        c.l / 2 -
        size.z / 2;

    const minZ =
        -c.l / 2 +
        size.z / 2;

    selected.position.x =
        Math.max(
            minX,
            Math.min(
                maxX,
                selected.position.x
            )
        );

    selected.position.z =
        Math.max(
            minZ,
            Math.min(
                maxZ,
                selected.position.z
            )
        );
}

/*
|--------------------------------------------------------------------------
| Demo
|--------------------------------------------------------------------------
*/
const demo =
    createCarton({
        nama: 'CHAIR-A',
        p: 120,
        l: 80,
        t: 70,
        warna: '#f4dd3a'
    });

demo?.position?.set?.(0, 35, 0);

window.addEventListener('load', () => {
    if (!scene) return;

    scene.add(demo);
    cartons.push(demo);
    selectable.push(demo);
});

/*
|--------------------------------------------------------------------------
| Render
|--------------------------------------------------------------------------
*/
function animate() {

    requestAnimationFrame(
        animate
    );

    controls?.update();

    renderer?.render(
        scene,
        camera
    );
}
