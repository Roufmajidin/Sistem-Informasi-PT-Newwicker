    const containers = {
        '20': { nama: '20ft', p: 589, l: 235, t: 239 },
        '40': { nama: '40ft', p: 1203, l: 235, t: 239 },
        '40hc': { nama: '40ft HC', p: 1203, l: 235, t: 269 }
    };

    const ORIENTATIONS = ['plt', 'ptl', 'lpt', 'ltp', 'tpl', 'tlp'];
    const ORIENTATION_LABEL = {
        plt: 'P × L × T',
        ptl: 'P × T × L',
        lpt: 'L × P × T',
        ltp: 'L × T × P',
        tpl: 'T × P × L',
        tlp: 'T × L × P'
    };

    let scene, camera, renderer, controls;
    let containerGroup = null;
    let raycaster, mouse;
    let cartons = [];
    let occupied = [];
    let selectable = [];
    let selected = null;
    let dragging = false;
    let dragData = null;
    let ghostBox = null;
    let activeContainer = containers['40hc'];
    let defaultItems = [
        { nama: 'Mirror Bilbao', p: 129, l: 101, t: 16, qty: 30, warna: '#f4dd3a', orientation: 'auto' },
        { nama: 'Headboard Boudoir', p: 252, l: 12, t: 152, qty: 10, warna: '#ff4f93', orientation: 'auto' },
        { nama: 'Dining Chair Ewan', p: 68, l: 63, t: 83, qty: 72, warna: '#ff8c1a', orientation: 'auto' },
        { nama: 'Dining Table Tiago', p: 83, l: 83, t: 82, qty: 32, warna: '#3fd7be', orientation: 'auto' }
    ];

    function getContainer() {
        return containers[$('#containerType').val()] || containers['40hc'];
    }

    function initViewer() {
        const viewer = document.getElementById('viewer');
        scene = new THREE.Scene();
        scene.background = new THREE.Color(0xf3f3f3);

        camera = new THREE.PerspectiveCamera(45, viewer.clientWidth / viewer.clientHeight, 1, 10000);
        camera.position.set(-800, 400, 800);
        camera.lookAt(0, 100, 0);

        renderer = new THREE.WebGLRenderer({ antialias: true });
        renderer.setSize(viewer.clientWidth, viewer.clientHeight);
        renderer.setPixelRatio(window.devicePixelRatio);
        viewer.innerHTML = '';
        viewer.appendChild(renderer.domElement);

        controls = new THREE.OrbitControls(camera, renderer.domElement);
        controls.enableDamping = true;
        controls.dampingFactor = 0.05;
        controls.enableZoom = true;
        controls.zoomSpeed = 1.2;
        controls.enablePan = true;
        controls.panSpeed = 1;
        controls.target.set(0, 100, 0);
        controls.update();

        scene.add(new THREE.AmbientLight(0xffffff, 1));
        const light = new THREE.DirectionalLight(0xffffff, 1);
        light.position.set(500, 1000, 500);
        scene.add(light);

        const grid = new THREE.GridHelper(3000, 60, 0x999999, 0xcccccc);
        scene.add(grid);

        raycaster = new THREE.Raycaster();
        mouse = new THREE.Vector2();

        buildContainer();
        addDefaultItems();
        initEvents();
        animate();
    }

    function buildContainer() {
        if (containerGroup) {
            scene.remove(containerGroup);
        }

        const container = getContainer();
        activeContainer = container;
        containerGroup = new THREE.Group();

        const floor = new THREE.Mesh(
            new THREE.BoxGeometry(container.p, 4, container.l),
            new THREE.MeshStandardMaterial({ color: 0x444444 })
        );
        floor.position.set(0, 2, 0);
        containerGroup.add(floor);

        const body = new THREE.Mesh(
            new THREE.BoxGeometry(container.p, container.t, container.l),
            new THREE.MeshStandardMaterial({ color: 0x777777, transparent: true, opacity: 0.2, side: THREE.DoubleSide })
        );
        body.position.y = container.t / 2;
        containerGroup.add(body);

        const frame = new THREE.LineSegments(
            new THREE.EdgesGeometry(new THREE.BoxGeometry(container.p, container.t, container.l)),
            new THREE.LineBasicMaterial({ color: 0x666666 })
        );
        frame.position.y = container.t / 2;
        containerGroup.add(frame);

        scene.add(containerGroup);
        updateContainerStats();
    }

    function initEvents() {
        $('#btnGenerate').click(() => generateLoadingPlan(getItems()));
        $('#btnRotateSelected').click(() => rotateSelectedBox());
        $('#btnAddItem').click(() => addItemRow());
        $('#containerType').change(() => {
            buildContainer();
            generateLoadingPlan(getItems());
        });
        $('#btnTop').click(() => setCameraPreset('top'));
        $('#btnFront').click(() => setCameraPreset('front'));
        $('#btnLeft').click(() => setCameraPreset('left'));
        $('#btnReset').click(() => resetCamera());

        $('#itemBody').on('change', 'input, select', debounce(() => generateLoadingPlan(getItems()), 300));
        $(document).on('click', '.remove', function () { $(this).closest('tr').remove(); generateLoadingPlan(getItems()); });

        renderer.domElement.addEventListener('pointerdown', onPointerDown);
        renderer.domElement.addEventListener('pointermove', onPointerMove);
        renderer.domElement.addEventListener('pointerup', onPointerUp);

        window.addEventListener('resize', () => {
            const viewer = document.getElementById('viewer');
            if (!viewer) return;
            camera.aspect = viewer.clientWidth / viewer.clientHeight;
            camera.updateProjectionMatrix();
            renderer.setSize(viewer.clientWidth, viewer.clientHeight);
        });
    }

    function addDefaultItems() {
        $('#itemBody').html('');
        defaultItems.forEach(item => addItemRow(item));
    }

    function addItemRow(item = {}) {
        const color = item.warna || colors[colorIndex % colors.length];
        if (!item.warna) colorIndex++;
        const orientation = item.orientation || 'auto';
        $('#itemBody').append(`
            <tr>
                <td><input class="form-control nama" value="${item.nama || ''}" placeholder="Item Name"></td>
                <td>
                    <div class="d-flex gap-2">
                        <input class="form-control p" value="${item.p || ''}" placeholder="P">
                        <input class="form-control l" value="${item.l || ''}" placeholder="L">
                        <input class="form-control t" value="${item.t || ''}" placeholder="T">
                    </div>
                </td>
                <td><input class="form-control qty" type="number" min="1" value="${item.qty || 1}"></td>
                <td>
                    <select class="form-control orientation">
                        ${ORIENTATIONS.map(code => `<option value="${code}" ${orientation === code ? 'selected' : ''}>${ORIENTATION_LABEL[code]}</option>`).join('')}
                        <option value="auto" ${orientation === 'auto' ? 'selected' : ''}>Auto Rotate</option>
                    </select>
                </td>
                <td>
                    <div style="width:30px;height:30px;background:${color};border-radius:6px;margin-bottom:4px"></div>
                    <input type="hidden" class="warna" value="${color}">
                </td>
                <td><button class="btn btn-danger btn-sm remove">×</button></td>
            </tr>
        `);
    }

    function getItems() {
        const items = [];
        $('#itemBody tr').each(function () {
            const row = $(this);
            const item = {
                nama: row.find('.nama').val().trim(),
                p: parseFloat(row.find('.p').val()) || 0,
                l: parseFloat(row.find('.l').val()) || 0,
                t: parseFloat(row.find('.t').val()) || 0,
                qty: parseInt(row.find('.qty').val()) || 0,
                warna: row.find('.warna').val() || '#ffffff',
                orientation: row.find('.orientation').val() || 'auto'
            };
            if (item.nama && item.p > 0 && item.l > 0 && item.t > 0 && item.qty > 0) {
                items.push(item);
            }
        });
        return items;
    }

    function getDimensions(item, orientation) {
        const p = item.p;
        const l = item.l;
        const t = item.t;
        switch (orientation) {
            case 'plt': return { p, l, t };
            case 'ptl': return { p, l: t, t: l };
            case 'lpt': return { p: l, l: p, t };
            case 'ltp': return { p: l, l: t, t: p };
            case 'tpl': return { p: t, l: p, t: l };
            case 'tlp': return { p: t, l, t: p };
            default: return { p, l, t };
        }
    }

    function computeCandidateCorners() {
        const corners = new Map();
        function addCorner(x, y, z) {
            const key = `${Math.round(x)}_${Math.round(y)}_${Math.round(z)}`;
            if (!corners.has(key)) corners.set(key, { x, y, z });
        }
        addCorner(0, 0, 0);
        occupied.forEach(o => {
            addCorner(o.x + o.p, o.y, o.z);
            addCorner(o.x, o.y, o.z + o.l);
            addCorner(o.x, o.y + o.t, o.z);
        });
        return Array.from(corners.values());
    }

    function clamp(v, min, max) {
        return Math.max(min, Math.min(max, v));
    }

    function isWithinContainer(x, y, z, p, l, t) {
        return x >= 0 && y >= 0 && z >= 0 && x + p <= activeContainer.p && y + t <= activeContainer.t && z + l <= activeContainer.l;
    }

    function isOccupied(x, y, z, p, l, t, ignoreId = null) {
        return occupied.some(o => {
            if (ignoreId && o.id === ignoreId) return false;
            return x < o.x + o.p && x + p > o.x && y < o.y + o.t && y + t > o.y && z < o.z + o.l && z + l > o.z;
        });
    }

    function computeScore(x, y, z) {
        return y * 1000000 + z * 1000 + x;
    }

    function findBestPlacement(dims, ignoreId = null) {
        let best = null;
        let bestScore = Infinity;
        computeCandidateCorners().forEach(corner => {
            const x = Math.round(corner.x);
            const y = Math.round(corner.y);
            const z = Math.round(corner.z);
            if (!isWithinContainer(x, y, z, dims.p, dims.l, dims.t)) return;
            if (isOccupied(x, y, z, dims.p, dims.l, dims.t, ignoreId)) return;
            const score = computeScore(x, y, z);
            if (score < bestScore) {
                bestScore = score;
                best = { x, y, z, score };
            }
        });
        return best;
    }

    function getWorldPosition(x, y, z, dims) {
        return new THREE.Vector3(
            activeContainer.p / 2 - x - dims.p / 2,
            y + dims.t / 2,
            -activeContainer.l / 2 + z + dims.l / 2
        );
    }

    function createCarton(item, dims, position, id) {
        const group = new THREE.Group();
        group.userData = {
            movable: true,
            nama: item.nama,
            item: item,
            orientation: item.orientation,
            id,
            dims: { ...dims }
        };

        const geometry = new THREE.BoxGeometry(dims.p, dims.t, dims.l);
        const material = new THREE.MeshStandardMaterial({ color: item.warna, roughness: 0.6, metalness: 0 });
        const mesh = new THREE.Mesh(geometry, material);
        group.add(mesh);

        const edges = new THREE.EdgesGeometry(geometry);
        const lines = new THREE.LineSegments(edges, new THREE.LineBasicMaterial({ color: 0x222222 }));
        group.add(lines);

        const sprite = makeTextSprite(item.nama);
        sprite.position.set(0, dims.t / 2 + 5, 0);
        group.add(sprite);

        group.position.copy(position);
        scene.add(group);
        cartons.push(group);
        selectable.push(group);
        return group;
    }

    function makeTextSprite(text) {
        const canvas = document.createElement('canvas');
        const ctx = canvas.getContext('2d');
        canvas.width = 512;
        canvas.height = 128;
        ctx.fillStyle = 'rgba(255,255,255,0.85)';
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        ctx.fillStyle = '#000';
        ctx.font = 'bold 38px Arial';
        ctx.textAlign = 'center';
        ctx.textBaseline = 'middle';
        ctx.fillText(text, canvas.width / 2, canvas.height / 2);
        const texture = new THREE.CanvasTexture(canvas);
        const material = new THREE.SpriteMaterial({ map: texture, transparent: true });
        const sprite = new THREE.Sprite(material);
        sprite.scale.set(100, 25, 1);
        return sprite;
    }

    function clearLoading() {
        cartons.forEach(group => scene.remove(group));
        cartons = [];
        selectable = [];
        occupied = [];
        selected = null;
        if (ghostBox) {
            scene.remove(ghostBox);
            ghostBox = null;
        }
    }

    function generateLoadingPlan(items) {
        clearLoading();
        activeContainer = getContainer();
        let totalVolume = 0;
        let totalCarton = 0;
        let unplaced = [];
        const expanded = [];
        items.forEach((item, index) => {
            for (let i = 0; i < item.qty; i++) {
                expanded.push({ ...item, originalIndex: index, uid: `${index}-${i}` });
            }
        });
        expanded.sort((a, b) => b.p * b.l * b.t - a.p * a.l * a.t);
        expanded.forEach(item => {
            const orientations = item.orientation === 'auto' ? ORIENTATIONS : [item.orientation];
            let bestPlacement = null;
            let bestDims = null;
            orientations.forEach(orientation => {
                const dims = getDimensions(item, orientation);
                const candidate = findBestPlacement(dims);
                if (!candidate) return;
                const score = computeScore(candidate.x, candidate.y, candidate.z);
                if (!bestPlacement || score < bestPlacement.score) {
                    bestPlacement = { ...candidate, orientation };
                    bestDims = dims;
                }
            });
            if (bestPlacement) {
                const worldPos = getWorldPosition(bestPlacement.x, bestPlacement.y, bestPlacement.z, bestDims);
                createCarton(item, bestDims, worldPos, item.uid);
                occupied.push({ id: item.uid, x: bestPlacement.x, y: bestPlacement.y, z: bestPlacement.z, ...bestDims });
                totalVolume += (bestDims.p * bestDims.l * bestDims.t) / 1000000;
                totalCarton += 1;
            } else {
                unplaced.push(item);
            }
        });
        updateSummary(totalVolume, totalCarton, unplaced);
    }

    function updateSummary(totalVolume, totalCarton, unplaced = []) {
        $('#totalVolume').text(`${totalVolume.toFixed(2)} m³`);
        $('#totalCarton').text(`${totalCarton} pcs`);
        const container = getContainer();
        const containerVolume = (container.p * container.l * container.t) / 1000000;
        const util = containerVolume === 0 ? 0 : (totalVolume / containerVolume) * 100;
        $('#utilization').text(`${util.toFixed(2)}%`);
        $('#utilBar').css('width', `${util}%`);
        const remaining = Math.max(0, containerVolume - totalVolume);
        $('#remainingVolume').text(`${remaining.toFixed(2)} m³`);
        if (unplaced.length === 0) {
            $('#unplacedInfo').text('All cartons are packed.');
        } else {
            $('#unplacedInfo').html(`Unable to place <strong>${unplaced.length}</strong> cartons:<br>${unplaced.slice(0, 5).map(item => `${item.nama} (${item.p}×${item.l}×${item.t})`).join('<br>')}`);
        }
    }

    function setCameraPreset(direction) {
        switch (direction) {
            case 'top':
                camera.position.set(0, 1200, 0);
                controls.target.set(0, 0, 0);
                break;
            case 'front':
                camera.position.set(-900, 150, 0);
                controls.target.set(0, 100, 0);
                break;
            case 'left':
                camera.position.set(0, 150, 900);
                controls.target.set(0, 100, 0);
                break;
        }
        controls.update();
    }

    function resetCamera() {
        camera.position.set(-800, 400, 800);
        controls.target.set(0, 100, 0);
        controls.update();
    }

    function onPointerDown(event) {
        event.preventDefault();
        const rect = renderer.domElement.getBoundingClientRect();
        mouse.x = ((event.clientX - rect.left) / rect.width) * 2 - 1;
        mouse.y = -((event.clientY - rect.top) / rect.height) * 2 + 1;
        raycaster.setFromCamera(mouse, camera);
        const hits = raycaster.intersectObjects(selectable, true);
        if (!hits.length) {
            selected = null;
            return;
        }
        const group = getParentGroup(hits[0].object);
        if (!group) return;
        selectGroup(group);
        startDrag(group, event);
    }

    function startDrag(group, event) {
        dragging = true;
        const itemData = group.userData;
        dragData = {
            id: itemData.id,
            dims: { ...itemData.dims },
            original: { x: group.position.x, y: group.position.y, z: group.position.z },
            orientation: itemData.orientation,
            item: itemData.item
        };
        occupied = occupied.filter(o => o.id !== dragData.id);
        createGhost(dragData.dims);
    }

    function onPointerMove(event) {
        if (!dragging) return;
        const rect = renderer.domElement.getBoundingClientRect();
        mouse.x = ((event.clientX - rect.left) / rect.width) * 2 - 1;
        mouse.y = -((event.clientY - rect.top) / rect.height) * 2 + 1;
        raycaster.setFromCamera(mouse, camera);
        const plane = new THREE.Plane(new THREE.Vector3(0, 1, 0), 0);
        const intersection = new THREE.Vector3();
        if (raycaster.ray.intersectPlane(plane, intersection)) {
            const targetX = intersection.x + activeContainer.p / 2;
            const targetZ = intersection.z + activeContainer.l / 2;
            let bestCandidate = null;
            let bestDistance = Infinity;
            computeCandidateCorners().forEach(corner => {
                const x = Math.round(corner.x);
                const y = Math.round(corner.y);
                const z = Math.round(corner.z);
                if (!isWithinContainer(x, y, z, dragData.dims.p, dragData.dims.l, dragData.dims.t)) return;
                if (isOccupied(x, y, z, dragData.dims.p, dragData.dims.l, dragData.dims.t, dragData.id)) return;
                const world = getWorldPosition(x, y, z, dragData.dims);
                const dx = world.x - intersection.x;
                const dz = world.z - intersection.z;
                const distance = Math.sqrt(dx * dx + dz * dz);
                if (distance < bestDistance) {
                    bestDistance = distance;
                    bestCandidate = { x, y, z, world };
                }
            });
            if (bestCandidate) {
                setGhostPosition(bestCandidate.world, dragData.dims);
                dragData.candidate = bestCandidate;
            }
        }
    }

    function onPointerUp() {
        if (!dragging) return;
        dragging = false;
        if (dragData && dragData.candidate && selected) {
            selected.position.copy(dragData.candidate.world);
            occupied.push({ id: dragData.id, x: dragData.candidate.x, y: dragData.candidate.y, z: dragData.candidate.z, ...dragData.dims });
        } else if (selected && dragData) {
            selected.position.set(dragData.original.x, dragData.original.y, dragData.original.z);
            occupied.push({ id: dragData.id, x: getObjectX(selected), y: getObjectY(selected), z: getObjectZ(selected), ...dragData.dims });
        }
        if (ghostBox) {
            scene.remove(ghostBox);
            ghostBox = null;
        }
        dragData = null;
    }

    function getObjectX(group) {
        return Math.round(activeContainer.p / 2 - group.position.x - group.userData.dims.p / 2);
    }

    function getObjectY(group) {
        return Math.round(group.position.y - group.userData.dims.t / 2);
    }

    function getObjectZ(group) {
        return Math.round(group.position.z + activeContainer.l / 2 - group.userData.dims.l / 2);
    }

    function getParentGroup(object) {
        while (object && object.parent) {
            if (object.type === 'Group') return object;
            object = object.parent;
        }
        return null;
    }

    function setGhostPosition(worldPos, dims) {
        if (!ghostBox) {
            const geometry = new THREE.BoxGeometry(dims.p, dims.t, dims.l);
            const material = new THREE.MeshStandardMaterial({ color: 0xffffff, opacity: 0.35, transparent: true });
            ghostBox = new THREE.Mesh(geometry, material);
            scene.add(ghostBox);
        }
        ghostBox.position.copy(worldPos);
    }

    function selectGroup(group) {
        if (selected === group) return;
        if (selected) {
            selected.traverse(child => {
                if (child.isMesh) child.material.emissive && child.material.emissive.set(0x000000);
            });
        }
        selected = group;
        selected.traverse(child => {
            if (child.isMesh) child.material.emissive && child.material.emissive.set(0x444444);
        });
    }

    function rotateSelectedBox() {
        if (!selected) return;
        const data = selected.userData;
        const currentIndex = ORIENTATIONS.indexOf(data.orientation);
        const nextIndex = currentIndex === -1 ? 0 : (currentIndex + 1) % ORIENTATIONS.length;
        data.orientation = ORIENTATIONS[nextIndex];
        data.dims = getDimensions(data.item, data.orientation);
        const item = data.item;
        const existingIndex = occupied.findIndex(o => o.id === data.id);
        if (existingIndex !== -1) occupied.splice(existingIndex, 1);
        const best = findBestPlacement(data.dims);
        if (best) {
            selected.position.copy(getWorldPosition(best.x, best.y, best.z, data.dims));
            occupied.push({ id: data.id, x: best.x, y: best.y, z: best.z, ...data.dims });
        } else {
            occupied.push({ id: data.id, x: getObjectX(selected), y: getObjectY(selected), z: getObjectZ(selected), ...data.dims });
        }
        scene.remove(selected);
        const newGroup = createCarton(item, data.dims, selected.position, data.id);
        selectGroup(newGroup);
        if (dragging) dragging = false;
        if (ghostBox) {
            scene.remove(ghostBox);
            ghostBox = null;
        }
    }

    function updateContainerStats() {
        const c = getContainer();
        $('#containerName').text(c.nama);
        $('#containerSize').text(`${c.p} x ${c.l} x ${c.t} cm`);
        $('#cP').text(c.p);
        $('#cL').text(c.l);
        $('#cT').text(c.t);
        $('#cVolume').text(`${((c.p * c.l * c.t) / 1000000).toFixed(2)} m³`);
    }

    function debounce(fn, wait) {
        let timeout;
        return function () {
            clearTimeout(timeout);
            timeout = setTimeout(() => fn.apply(this, arguments), wait);
        };
    }

    function animate() {
        requestAnimationFrame(animate);
        controls.update();
        renderer.render(scene, camera);
    }

    const colors = ['#f4dd3a', '#ff4f93', '#ff8c1a', '#3fd7be', '#6f63d9'];
    let colorIndex = 0;

    document.addEventListener('DOMContentLoaded', () => initViewer());
</script>

@endsection

    //---------------------------------
    // SCENE
    //---------------------------------

    scene =
        new THREE.Scene();

    scene.background =
        new THREE.Color(
            0xf3f3f3
        );

    //---------------------------------
    // CAMERA
    //---------------------------------

    camera =
        new THREE.PerspectiveCamera(
            45,
            viewer.clientWidth /
            viewer.clientHeight,
            1,
            10000
        );

    camera.position.set(
        -800,
        400,
        800
    );

    camera.lookAt(
        0,
        100,
        0
    );

    //---------------------------------
    // RENDERER
    //---------------------------------

    renderer =
        new THREE.WebGLRenderer({
            antialias: true
        });

    renderer.setSize(
        viewer.clientWidth,
        viewer.clientHeight
    );

    renderer.setPixelRatio(
        window.devicePixelRatio
    );

    viewer.innerHTML = '';

    viewer.appendChild(
        renderer.domElement
    );
    renderer.domElement.addEventListener(
    'pointerdown',
    onSelect
);
controls =
    new THREE.OrbitControls(
        camera,
        renderer.domElement
    );

controls.enableDamping = true;
controls.dampingFactor = 0.05;

// rotate
controls.enableRotate = true;

// zoom
controls.enableZoom = true;
controls.zoomSpeed = 1.2;

// geser (pan)
controls.enablePan = true;
controls.panSpeed = 1;

// target tengah container
controls.target.set(
    0,
    100,
    0
);

controls.update();
    //---------------------------------
    // LIGHT
    //---------------------------------

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

    //---------------------------------
    // GRID
    //---------------------------------

    const grid =
        new THREE.GridHelper(
            3000,
            60,
            0x999999,
            0xcccccc
        );

    scene.add(grid);

    //---------------------------------
    // CONTAINER FLOOR
    //---------------------------------

    const floor =
        new THREE.Mesh(

            new THREE.BoxGeometry(
                1203,
                4,
                235
            ),

            new THREE.MeshStandardMaterial({
                color: 0x444444
            })

        );

    floor.position.set(
        0,
        2,
        0
    );

    scene.add(floor);

    //---------------------------------
    // CONTAINER BODY
    //---------------------------------

    const body =
        new THREE.Mesh(

            new THREE.BoxGeometry(
                1203,
                269,
                235
            ),

            new THREE.MeshStandardMaterial({

                color: 0x777777,

                transparent: true,

                opacity: 0.20,

                side:
                    THREE.DoubleSide

            })

        );

    body.position.y =
        269 / 2;

    scene.add(body);

    //---------------------------------
    // FRAME
    //---------------------------------

    const frame =
        new THREE.LineSegments(

            new THREE.EdgesGeometry(

                new THREE.BoxGeometry(
                    1203,
                    269,
                    235
                )

            ),

            new THREE.LineBasicMaterial({
                color: 0x666666
            })

        );

    frame.position.y =
        269 / 2;

    scene.add(frame);

    //---------------------------------
    // DEMO BOX
    //---------------------------------



    //---------------------------------
    // START
    //---------------------------------

    animate();
}

function animate()
{
    requestAnimationFrame(
        animate
    );

    controls.update();

    renderer.render(
        scene,
        camera
    );
}

window.addEventListener(
    'resize',
    function ()
    {
        const viewer =
            document.getElementById(
                'viewer'
            );

        if (!viewer) return;

        camera.aspect =
            viewer.clientWidth /
            viewer.clientHeight;

        camera.updateProjectionMatrix();

        renderer.setSize(
            viewer.clientWidth,
            viewer.clientHeight
        );
    }
);
// tbl
$('#btnTop').click(function () {

    camera.position.set(
        0,
        1200,
        0
    );

    controls.target.set(
        0,
        0,
        0
    );

    controls.update();
});
$('#btnFront').click(function () {

    camera.position.set(
        -900,
        150,
        0
    );

    controls.target.set(
        0,
        100,
        0
    );
});
$('#btnLeft').click(function () {

    camera.position.set(
        0,
        150,
        900
    );

    controls.target.set(
        0,
        100,
        0
    );
});

// add

$('#btnAddItem').click(function () {

    const color =
        colors[
            colorIndex %
            colors.length
        ];

    colorIndex++;

    $('#itemBody').append(`

        <tr>

            <td>
                <input
                    class="
                        form-control
                        nama
                    "
                    placeholder="CHAIR-A">
            </td>

            <td>

                <div
                    class="
                        d-flex
                        gap-2
                    "
                >

                    <input
                        class="
                            form-control
                            p
                        "
                        placeholder="P">

                    <input
                        class="
                            form-control
                            l
                        "
                        placeholder="L">

                    <input
                        class="
                            form-control
                            t
                        "
                        placeholder="T">

                </div>

            </td>

            <td>
                <input
                    class="
                        form-control
                        qty
                    "
                    value="1">
            </td>
            <td>

           <select class="form-control orientation">

    <option value="auto">
        Auto Rotate
    </option>

    <option value="plt">
        P × L × T
    </option>

    <option value="ptl">
        P × T × L
    </option>

    <option value="lpt">
        L × P × T
    </option>

    <option value="ltp">
        L × T × P
    </option>

    <option value="tpl">
        T × P × L
    </option>

    <option value="tlp">
        T × L × P
    </option>

</select>

            </td>
            <td>

                <div
                    style="
                        width:30px;
                        height:30px;
                        background:${color};
                        border-radius:6px;
                    ">
                </div>

                <input
                    type="hidden"
                    class="warna"
                    value="${color}">

            </td>

            <td>

                <button
                    class="
                        btn
                        btn-danger
                        btn-sm
                        remove
                    ">
                    ×
                </button>

            </td>

        </tr>

    `);

});
$(document).on(
    'click',
    '.remove',
    function () {

        $(this)
            .closest('tr')
            .remove();

    }
);
