import React, { useEffect, useRef, useState } from "react";
import * as THREE from "three";
import { OrbitControls } from "three/examples/jsm/controls/OrbitControls.js";
import { PlacedBox, CandidateCorner } from "../types";
import { 
  findBestDragPosition,
  getValidCandidateCorners 
} from "../packingSolver";
import { Rotate3d, Maximize2, Trash2, HelpCircle, Eye, Move } from "lucide-react";

interface ContainerViewerProps {
  placedBoxes: PlacedBox[];
  onMoveBox: (boxId: string, x: number, y: number, z: number, w: number, h: number, d: number) => void;
  onSelectBox: (boxId: string | null) => void;
  onDeleteBox?: (boxId: string) => void;
  onRotateBox?: () => void;
  selectedBoxId: string | null;
  requireSupport: boolean;
  showLabels: boolean;
  highlightedItemId: string | null;
  containerW: number;
  containerH: number;
  containerD: number;
}

export const ContainerViewer: React.FC<ContainerViewerProps> = ({
  placedBoxes,
  onMoveBox,
  onSelectBox,
  onDeleteBox,
  onRotateBox,
  selectedBoxId,
  requireSupport,
  showLabels,
  highlightedItemId,
  containerW,
  containerH,
  containerD,
}) => {
  const mountRef = useRef<HTMLDivElement>(null);
  const [hoveredBox, setHoveredBox] = useState<PlacedBox | null>(null);
  const [hoveredPos, setHoveredPos] = useState<{ x: number; y: number } | null>(null);
  const [dragActive, setDragActive] = useState<boolean>(false);
  const [activeDeletePopup, setActiveDeletePopup] = useState<{ boxId: string; x: number; y: number } | null>(null);
  const [interactionMode, setInteractionMode] = useState<"orbit" | "drag">("orbit");

  // Keep references to access inside event listeners without rebuilding
  const stateRef = useRef({
    placedBoxes,
    selectedBoxId,
    requireSupport,
    highlightedItemId,
    showLabels,
    interactionMode,
  });

  // Update immediately in render to ensure it is always fresh and eliminates any effect ordering race conditions
  stateRef.current = {
    placedBoxes,
    selectedBoxId,
    requireSupport,
    highlightedItemId,
    showLabels,
    interactionMode,
  };

  // View control functions exposed via refs or local handlers
  const cameraTargetRef = useRef<THREE.Vector3>(new THREE.Vector3(0, 1.345, 0));
  const cameraTargetPosRef = useRef<THREE.Vector3>(new THREE.Vector3(5, 5, 8));
  const isTransitioningCamera = useRef<boolean>(false);

  const setCameraPreset = (preset: "front" | "left" | "top" | "isometric") => {
    isTransitioningCamera.current = true;
    const target = cameraTargetRef.current;
    
    switch (preset) {
      case "front":
        cameraTargetPosRef.current.set(0, 1.5, 9);
        break;
      case "left":
        cameraTargetPosRef.current.set(-8, 1.5, 0);
        break;
      case "top":
        cameraTargetPosRef.current.set(0, 10, 0.01);
        break;
      case "isometric":
      default:
        cameraTargetPosRef.current.set(6, 6, 8);
        break;
    }
  };

  useEffect(() => {
    if (!mountRef.current) return;

    const containerDiv = mountRef.current;
    const width = containerDiv.clientWidth;
    const height = containerDiv.clientHeight || 500;

    // --- SCENE & CAMERA ---
    const scene = new THREE.Scene();
    scene.background = new THREE.Color("#f8fafc"); // Light elegant background slate-50

    const camera = new THREE.PerspectiveCamera(45, width / height, 0.1, 100);
    camera.position.copy(cameraTargetPosRef.current);

    // --- RENDERER ---
    const renderer = new THREE.WebGLRenderer({ antialias: true });
    renderer.setSize(width, height);
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2));
    renderer.shadowMap.enabled = true;
    renderer.shadowMap.type = THREE.PCFSoftShadowMap;
    containerDiv.appendChild(renderer.domElement);

    // --- CONTROLS ---
    const controls = new OrbitControls(camera, renderer.domElement);
    controls.enableDamping = true;
    controls.dampingFactor = 0.05;
    controls.rotateSpeed = 1.3;
    controls.enablePan = true;
    controls.panSpeed = 1.0;
    controls.maxPolarAngle = Math.PI / 2 - 0.02; // Don't go below floor
    controls.minDistance = 2;
    controls.maxDistance = 30;
    controls.target.copy(cameraTargetRef.current);

    // --- LIGHTS ---
    const ambientLight = new THREE.AmbientLight("#ffffff", 0.65);
    scene.add(ambientLight);

    const dirLight = new THREE.DirectionalLight("#ffffff", 0.85);
    dirLight.position.set(10, 15, 8);
    dirLight.castShadow = true;
    dirLight.shadow.mapSize.width = 2048;
    dirLight.shadow.mapSize.height = 2048;
    dirLight.shadow.camera.near = 0.5;
    dirLight.shadow.camera.far = 40;
    const d = 10;
    dirLight.shadow.camera.left = -d;
    dirLight.shadow.camera.right = d;
    dirLight.shadow.camera.top = d;
    dirLight.shadow.camera.bottom = -d;
    dirLight.shadow.bias = -0.0005;
    scene.add(dirLight);

    const secondaryLight = new THREE.DirectionalLight("#93c5fd", 0.25); // Subtle bluish fill
    secondaryLight.position.set(-10, 5, -8);
    scene.add(secondaryLight);

    // --- CONTAINER VISUAL ---
    // Scaled down by 100:
    const cW = containerW / 100;
    const cH = containerH / 100;
    const cD = containerD / 100;

    // Outer Container wireframe
    const containerGeo = new THREE.BoxGeometry(cW, cH, cD);
    const edgesGeo = new THREE.EdgesGeometry(containerGeo);
    const containerEdges = new THREE.LineSegments(
      edgesGeo,
      new THREE.LineBasicMaterial({ color: "#64748b", linewidth: 2 }) // slate-500
    );
    containerEdges.position.set(0, cH / 2, 0);
    scene.add(containerEdges);

    // Semi-transparent wall meshes (except front door side so user can look inside)
    const wallMat = new THREE.MeshPhysicalMaterial({
      color: "#cbd5e1", // slate-300
      transparent: true,
      opacity: 0.12,
      roughness: 0.4,
      metalness: 0.1,
      clearcoat: 0.3,
      side: THREE.DoubleSide,
      depthWrite: false,
    });

    const wallsMesh = new THREE.Mesh(containerGeo, wallMat);
    wallsMesh.position.set(0, cH / 2, 0);
    scene.add(wallsMesh);

    // Grid Floor container
    // We create grid helper customly to represent 100cm (1m) lines
    const gridColor = "#94a3b8"; // slate-400
    const gridLines = new THREE.Group();
    
    // Draw grid along length (Z)
    const segmentsZ = Math.ceil(containerD / 50); // every 50cm
    for (let i = 0; i <= segmentsZ; i++) {
      const zVal = (i * 50) / 100 - cD / 2;
      const points = [
        new THREE.Vector3(-cW / 2, 0.001, zVal),
        new THREE.Vector3(cW / 2, 0.001, zVal)
      ];
      const lineGeo = new THREE.BufferGeometry().setFromPoints(points);
      const isMajor = (i * 50) % 100 === 0;
      const lineMat = new THREE.LineBasicMaterial({
        color: gridColor,
        transparent: true,
        opacity: isMajor ? 0.35 : 0.15,
        linewidth: isMajor ? 1.5 : 1
      });
      gridLines.add(new THREE.Line(lineGeo, lineMat));
    }

    // Draw grid along width (X)
    const segmentsX = Math.ceil(containerW / 50);
    for (let i = 0; i <= segmentsX; i++) {
      const xVal = (i * 50) / 100 - cW / 2;
      const points = [
        new THREE.Vector3(xVal, 0.001, -cD / 2),
        new THREE.Vector3(xVal, 0.001, cD / 2)
      ];
      const lineGeo = new THREE.BufferGeometry().setFromPoints(points);
      const isMajor = (i * 50) % 100 === 0;
      const lineMat = new THREE.LineBasicMaterial({
        color: gridColor,
        transparent: true,
        opacity: isMajor ? 0.35 : 0.15,
        linewidth: isMajor ? 1.5 : 1
      });
      gridLines.add(new THREE.Line(lineGeo, lineMat));
    }
    scene.add(gridLines);

    // --- CARGO BOXES ---
    const boxGroup = new THREE.Group();
    scene.add(boxGroup);

    // Keep map of visual meshes and labels
    const meshMap = new Map<string, {
      mesh: THREE.Mesh,
      outline: THREE.LineSegments,
      sprite?: THREE.Sprite,
      targetPos: THREE.Vector3,
      targetScale: THREE.Vector3,
    }>();

    // --- GHOST BOX FOR DRAG FEEDBACK ---
    const ghostGeo = new THREE.BoxGeometry(1, 1, 1);
    const ghostMat = new THREE.MeshBasicMaterial({
      color: "#22c55e", // green-500
      transparent: true,
      opacity: 0.45,
      wireframe: false,
    });
    const ghostMesh = new THREE.Mesh(ghostGeo, ghostMat);
    ghostMesh.visible = false;
    scene.add(ghostMesh);

    // Ghost outline
    const ghostOutline = new THREE.LineSegments(
      new THREE.EdgesGeometry(ghostGeo),
      new THREE.LineBasicMaterial({ color: "#15803d", linewidth: 2 })
    );
    ghostMesh.add(ghostOutline);

    // Helper: Canvas-based high-fidelity text sprite
    const createTextSprite = (text: string, color: string = "#ffffff", bgColor: string = "rgba(15, 23, 42, 0.85)") => {
      const canvas = document.createElement("canvas");
      const ctx = canvas.getContext("2d");
      if (!ctx) return new THREE.Sprite();

      canvas.width = 384;
      canvas.height = 96;

      // Draw shadow
      ctx.shadowColor = "rgba(0, 0, 0, 0.3)";
      ctx.shadowBlur = 6;
      ctx.shadowOffsetX = 2;
      ctx.shadowOffsetY = 4;

      // Draw background capsule
      ctx.fillStyle = bgColor;
      ctx.beginPath();
      ctx.roundRect(8, 8, canvas.width - 16, canvas.height - 16, 16);
      ctx.fill();

      // Draw text without shadow
      ctx.shadowBlur = 0;
      ctx.shadowOffsetX = 0;
      ctx.shadowOffsetY = 0;
      ctx.fillStyle = color;
      ctx.font = "bold 26px 'Inter', system-ui, sans-serif";
      ctx.textAlign = "center";
      ctx.textBaseline = "middle";
      ctx.fillText(text, canvas.width / 2, canvas.height / 2);

      const texture = new THREE.CanvasTexture(canvas);
      const spriteMat = new THREE.SpriteMaterial({ 
        map: texture, 
        transparent: true,
        depthTest: true,
        sizeAttenuation: true
      });
      const sprite = new THREE.Sprite(spriteMat);
      sprite.scale.set(0.6, 0.15, 1.0);
      return sprite;
    };

    // Update meshes representation based on placedBoxes state
    const syncPlacedBoxes = () => {
      const currentBoxes = stateRef.current.placedBoxes;
      const currentSelId = stateRef.current.selectedBoxId;
      const currentHighlightId = stateRef.current.highlightedItemId;
      const currentShowLabels = stateRef.current.showLabels;

      // Track active keys to delete stale ones
      const activeIds = new Set<string>();

      currentBoxes.forEach((box) => {
        activeIds.add(box.id);

        const w3d = box.w / 100;
        const h3d = box.h / 100;
        const d3d = box.d / 100;
        const x3d = (box.x + box.w / 2 - containerW / 2) / 100;
        const y3d = (box.y + box.h / 2) / 100;
        const z3d = (box.z + box.d / 2 - containerD / 2) / 100;

        const targetPos = new THREE.Vector3(x3d, y3d, z3d);
        const targetScale = new THREE.Vector3(w3d, h3d, d3d);

        let entry = meshMap.get(box.id);

        if (!entry) {
          // Create new Mesh
          const geo = new THREE.BoxGeometry(1, 1, 1); // unit box, scale dynamically
          
          // Nice textured/shaded physical material
          const mat = new THREE.MeshStandardMaterial({
            color: new THREE.Color(box.color),
            roughness: 0.25,
            metalness: 0.15,
          });

          const mesh = new THREE.Mesh(geo, mat);
          mesh.castShadow = true;
          mesh.receiveShadow = true;
          mesh.userData = { boxId: box.id };

          // Outline
          const outlineGeo = new THREE.EdgesGeometry(geo);
          const outline = new THREE.LineSegments(
            outlineGeo,
            new THREE.LineBasicMaterial({ color: "#000000", linewidth: 1.5 })
          );
          mesh.add(outline);

          // Name Sprite Label
          const sprite = createTextSprite(box.name);
          sprite.visible = currentShowLabels;
          scene.add(sprite);

          boxGroup.add(mesh);

          // Animate scale/position from origin or custom spawn
          mesh.position.set(x3d, y3d + 2, z3d); // spawn slightly higher for drop animation
          mesh.scale.set(0.01, 0.01, 0.01);

          entry = {
            mesh,
            outline,
            sprite,
            targetPos,
            targetScale,
          };
          meshMap.set(box.id, entry);
        } else {
          // Update targets
          entry.targetPos.copy(targetPos);
          entry.targetScale.copy(targetScale);
        }

        // Apply visual highlights based on states
        const isSelected = box.id === currentSelId;
        const isHighlighted = box.itemId === currentHighlightId;

        // Custom emission or coloring for selected
        const baseMat = entry.mesh.material as THREE.MeshStandardMaterial;
        const outlineMat = entry.outline.material as THREE.LineBasicMaterial;
        if (isSelected) {
          baseMat.emissive.setHex(0x1e3a8a); // Glowing dark blue accent
          baseMat.emissiveIntensity = 0.35;
          outlineMat.color.setHex(0x2563eb); // Bright blue outline
          outlineMat.linewidth = 3;
        } else if (isHighlighted) {
          baseMat.emissive.setHex(0xffffff); // Glow white
          baseMat.emissiveIntensity = 0.2;
          outlineMat.color.setHex(0x000000);
          outlineMat.linewidth = 2;
        } else {
          baseMat.emissive.setHex(0x000000);
          baseMat.emissiveIntensity = 0;
          outlineMat.color.setHex(0x000000);
          outlineMat.linewidth = 1;
        }

        // Toggle label visibility
        if (entry.sprite) {
          entry.sprite.visible = currentShowLabels;
        }
      });

      // Cleanup removed boxes
      meshMap.forEach((entry, id) => {
        if (!activeIds.has(id)) {
          // Remove mesh from parents
          if (entry.mesh.parent) {
            entry.mesh.parent.remove(entry.mesh);
          }
          boxGroup.remove(entry.mesh);
          scene.remove(entry.mesh);

          entry.mesh.geometry.dispose();
          if (Array.isArray(entry.mesh.material)) {
            entry.mesh.material.forEach((m) => m.dispose());
          } else {
            entry.mesh.material.dispose();
          }

          // Remove outline from parents
          if (entry.outline.parent) {
            entry.outline.parent.remove(entry.outline);
          }
          entry.outline.geometry.dispose();
          if (Array.isArray(entry.outline.material)) {
            entry.outline.material.forEach((m) => m.dispose());
          } else {
            entry.outline.material.dispose();
          }

          // Remove sprite from scene
          if (entry.sprite) {
            if (entry.sprite.parent) {
              entry.sprite.parent.remove(entry.sprite);
            }
            scene.remove(entry.sprite);
            entry.sprite.material.map?.dispose();
            entry.sprite.material.dispose();
          }

          meshMap.delete(id);
        }
      });
    };

    // Run initial sync
    syncPlacedBoxes();

    // --- RAYCASTING AND MOUSE DRAGGING SYSTEM ---
    const raycaster = new THREE.Raycaster();
    const mouse = new THREE.Vector2();
    let draggedBoxId: string | null = null;
    const dragPlane = new THREE.Plane();
    const planeNormal = new THREE.Vector3(0, 1, 0); // drag along horizontal floor level
    const dragOffset = new THREE.Vector3();
    let currentGhostPlacement: { x: number; y: number; z: number; w: number; h: number; d: number } | null = null;

    let mouseDownPos = { x: 0, y: 0 };
    let mouseDownTime = 0;

    const handleMouseDown = (e: MouseEvent) => {
      // Clear active delete popup on click
      setActiveDeletePopup(null);

      mouseDownPos = { x: e.clientX, y: e.clientY };
      mouseDownTime = Date.now();

      const rect = renderer.domElement.getBoundingClientRect();
      mouse.x = ((e.clientX - rect.left) / rect.width) * 2 - 1;
      mouse.y = -((e.clientY - rect.top) / rect.height) * 2 + 1;

      raycaster.setFromCamera(mouse, camera);
      const childrenMeshes = Array.from(meshMap.values()).map(entry => entry.mesh);
      const intersects = raycaster.intersectObjects(childrenMeshes);

      if (intersects.length > 0) {
        const hitMesh = intersects[0].object as THREE.Mesh;
        const boxId = hitMesh.userData.boxId as string;
        
        // Start dragging only if we are in "drag" mode!
        if (stateRef.current.interactionMode === "drag") {
          draggedBoxId = boxId;
          onSelectBox(boxId);
          setDragActive(true);
          controls.enabled = false; // Freeze camera rotation

          // Show direct dialog for rotate/delete on click at mouse coordinates
          setActiveDeletePopup({ boxId, x: e.clientX, y: e.clientY });

          // Find associated placed box data
          const boxData = stateRef.current.placedBoxes.find(b => b.id === boxId);
          if (boxData) {
            // Initialize drag plane at the height of the clicked box center
            const boxCenterY = (boxData.y + boxData.h / 2) / 100;
            dragPlane.setFromNormalAndCoplanarPoint(planeNormal, new THREE.Vector3(0, boxCenterY, 0));
            
            // Intersect ray with drag plane to find the click offset
            const intersectPoint = new THREE.Vector3();
            raycaster.ray.intersectPlane(dragPlane, intersectPoint);
            dragOffset.copy(hitMesh.position).sub(intersectPoint);
          }
        }
      } else {
        // Clicked empty space: clear selection if in drag mode
        if (stateRef.current.interactionMode === "drag") {
          onSelectBox(null);
        }
      }
    };

    const handleMouseMove = (e: MouseEvent) => {
      const rect = renderer.domElement.getBoundingClientRect();
      mouse.x = ((e.clientX - rect.left) / rect.width) * 2 - 1;
      mouse.y = -((e.clientY - rect.top) / rect.height) * 2 + 1;

      // Update hover tooltip when not dragging
      if (!draggedBoxId) {
        raycaster.setFromCamera(mouse, camera);
        const childrenMeshes = Array.from(meshMap.values()).map(entry => entry.mesh);
        const intersects = raycaster.intersectObjects(childrenMeshes);

        if (intersects.length > 0) {
          const hitMesh = intersects[0].object as THREE.Mesh;
          const boxId = hitMesh.userData.boxId as string;
          const boxData = stateRef.current.placedBoxes.find(b => b.id === boxId);
          if (boxData) {
            setHoveredBox(boxData);
            setHoveredPos({ x: e.clientX, y: e.clientY });
          }
        } else {
          setHoveredBox(null);
        }
        return;
      }

      // Dragging is active (only in "drag" mode)!
      raycaster.setFromCamera(mouse, camera);
      const intersectPoint = new THREE.Vector3();
      
      if (raycaster.ray.intersectPlane(dragPlane, intersectPoint)) {
        // Calculate the target box position in 3D (including offset)
        const targetPos3d = intersectPoint.clone().add(dragOffset);
        
        // Convert to centimeters inside the container coordinate frame
        const cx = targetPos3d.x * 100 + containerW / 2;
        const cy = targetPos3d.y * 100;
        const cz = targetPos3d.z * 100 + containerD / 2;

        const boxData = stateRef.current.placedBoxes.find(b => b.id === draggedBoxId);
        if (boxData) {
          // Find the best valid snapped candidate position close to the cursor
          const bestSnap = findBestDragPosition(
            boxData,
            cx,
            cy,
            cz,
            stateRef.current.placedBoxes,
            stateRef.current.requireSupport,
            containerW,
            containerH,
            containerD
          );

          if (bestSnap) {
            currentGhostPlacement = bestSnap;
            ghostMesh.visible = true;

            // Scale and reposition ghost mesh
            const gw = bestSnap.w / 100;
            const gh = bestSnap.h / 100;
            const gd = bestSnap.d / 100;
            const gx = (bestSnap.x + bestSnap.w / 2 - containerW / 2) / 100;
            const gy = (bestSnap.y + bestSnap.h / 2) / 100;
            const gz = (bestSnap.z + bestSnap.d / 2 - containerD / 2) / 100;

            ghostMesh.scale.set(gw, gh, gd);
            ghostMesh.position.set(gx, gy, gz);
          } else {
            // No valid position: hide ghost
            currentGhostPlacement = null;
            ghostMesh.visible = false;
          }

          // Move the dragged mesh visually immediately to cursor (for high interactive feedback)
          const entry = meshMap.get(draggedBoxId);
          if (entry) {
            entry.mesh.position.copy(targetPos3d);
            if (entry.sprite) {
              entry.sprite.position.copy(targetPos3d).y += (boxData.h / 200) + 0.35;
            }
          }
        }
      }
    };

    const handleMouseUp = (e: MouseEvent) => {
      // Check if it's a quick click (no major movement and low duration)
      const dx = e.clientX - mouseDownPos.x;
      const dy = e.clientY - mouseDownPos.y;
      const dist = Math.sqrt(dx * dx + dy * dy);
      const duration = Date.now() - mouseDownTime;

      // Click threshold: moved less than 5px and clicked for less than 300ms
      if (dist < 6 && duration < 300) {
        const rect = renderer.domElement.getBoundingClientRect();
        mouse.x = ((e.clientX - rect.left) / rect.width) * 2 - 1;
        mouse.y = -((e.clientY - rect.top) / rect.height) * 2 + 1;

        raycaster.setFromCamera(mouse, camera);
        const childrenMeshes = Array.from(meshMap.values()).map(entry => entry.mesh);
        const intersects = raycaster.intersectObjects(childrenMeshes);

        if (intersects.length > 0) {
          const hitMesh = intersects[0].object as THREE.Mesh;
          const boxId = hitMesh.userData.boxId as string;
          onSelectBox(boxId);
          // Show popup at mouse position
          setActiveDeletePopup({ boxId, x: e.clientX, y: e.clientY });
        } else {
          onSelectBox(null);
          setActiveDeletePopup(null);
        }
      }

      if (draggedBoxId) {
        if (currentGhostPlacement) {
          // Relocate box to the ghost snapped location
          onMoveBox(
            draggedBoxId,
            currentGhostPlacement.x,
            currentGhostPlacement.y,
            currentGhostPlacement.z,
            currentGhostPlacement.w,
            currentGhostPlacement.h,
            currentGhostPlacement.d
          );
        }
        
        draggedBoxId = null;
        currentGhostPlacement = null;
        ghostMesh.visible = false;
        setDragActive(false);
        controls.enabled = true; // Release camera controls
      }
    };

    const handleDblClick = (e: MouseEvent) => {
      const rect = renderer.domElement.getBoundingClientRect();
      mouse.x = ((e.clientX - rect.left) / rect.width) * 2 - 1;
      mouse.y = -((e.clientY - rect.top) / rect.height) * 2 + 1;

      raycaster.setFromCamera(mouse, camera);
      const childrenMeshes = Array.from(meshMap.values()).map(entry => entry.mesh);
      const intersects = raycaster.intersectObjects(childrenMeshes);

      if (intersects.length > 0) {
        const hitMesh = intersects[0].object as THREE.Mesh;
        const boxId = hitMesh.userData.boxId as string;
        // Open floating delete popup at mouse coordinates
        setActiveDeletePopup({ boxId, x: e.clientX, y: e.clientY });
      }
    };

    // Attach listeners to renderer container to restrict scope
    renderer.domElement.addEventListener("mousedown", handleMouseDown);
    renderer.domElement.addEventListener("mousemove", handleMouseMove);
    renderer.domElement.addEventListener("dblclick", handleDblClick);
    window.addEventListener("mouseup", handleMouseUp);

    // --- ANIMATION TICK LOOP ---
    const clock = new THREE.Clock();
    let animFrameId: number;

    const animate = () => {
      animFrameId = requestAnimationFrame(animate);

      // Smoothly move camera if transitioning
      if (isTransitioningCamera.current) {
        camera.position.lerp(cameraTargetPosRef.current, 0.08);
        controls.target.lerp(cameraTargetRef.current, 0.08);
        
        if (camera.position.distanceTo(cameraTargetPosRef.current) < 0.02) {
          isTransitioningCamera.current = false;
        }
      }

      controls.update();

      // Smooth lerp animations for meshes (pop in, slide)
      meshMap.forEach((entry, boxId) => {
        // Only slide when not being manually dragged
        if (draggedBoxId !== boxId) {
          entry.mesh.position.lerp(entry.targetPos, 0.16);
          entry.mesh.scale.lerp(entry.targetScale, 0.16);
        }

        // Align text sprite directly on the front face of the mesh (stuck on the box)
        if (entry.sprite && entry.sprite.visible) {
          entry.sprite.position.copy(entry.mesh.position);
          // offset by half depth of the box + tiny gap to look like a sticker/label on front face
          const depthOffset = entry.mesh.scale.z / 2 + 0.008;
          entry.sprite.position.z += depthOffset;
        }
      });

      renderer.render(scene, camera);
    };

    animate();

    // --- HANDLE RESIZING ---
    const handleResize = () => {
      if (!mountRef.current) return;
      const w = mountRef.current.clientWidth;
      const h = mountRef.current.clientHeight || 500;
      
      camera.aspect = w / h;
      camera.updateProjectionMatrix();
      renderer.setSize(w, h);
    };

    const resizeObserver = new ResizeObserver(() => {
      handleResize();
    });
    resizeObserver.observe(containerDiv);

    // --- EXPOSE REFS ---
    // Save function on mount element to allow state-updates from parent
    (containerDiv as any).syncPlacedBoxes = syncPlacedBoxes;
    (containerDiv as any).setCameraPreset = setCameraPreset;

    // --- CLEANUP ---
    return () => {
      cancelAnimationFrame(animFrameId);
      resizeObserver.disconnect();
      
      renderer.domElement.removeEventListener("mousedown", handleMouseDown);
      renderer.domElement.removeEventListener("mousemove", handleMouseMove);
      renderer.domElement.removeEventListener("dblclick", handleDblClick);
      window.removeEventListener("mouseup", handleMouseUp);
      
      if (containerDiv.contains(renderer.domElement)) {
        containerDiv.removeChild(renderer.domElement);
      }
      
      // Dispose materials/geometries to avoid memory leaks
      scene.clear();
      containerGeo.dispose();
      wallMat.dispose();
      edgesGeo.dispose();
      containerEdges.geometry.dispose();
      (containerEdges.material as THREE.Material).dispose();
      ghostGeo.dispose();
      ghostMat.dispose();
      ghostOutline.geometry.dispose();
      (ghostOutline.material as THREE.Material).dispose();
      gridLines.traverse((node) => {
        if (node instanceof THREE.Line) {
          node.geometry.dispose();
          (node.material as THREE.Material).dispose();
        }
      });

      meshMap.forEach((entry) => {
        entry.mesh.geometry.dispose();
        if (Array.isArray(entry.mesh.material)) {
          entry.mesh.material.forEach((m) => m.dispose());
        } else {
          entry.mesh.material.dispose();
        }
        entry.outline.geometry.dispose();
        if (Array.isArray(entry.outline.material)) {
          entry.outline.material.forEach((m) => m.dispose());
        } else {
          entry.outline.material.dispose();
        }
        if (entry.sprite) {
          entry.sprite.material.map?.dispose();
          entry.sprite.material.dispose();
        }
      });
      
      renderer.dispose();
    };
  }, [containerW, containerH, containerD]);

  // Update visual meshes state whenever relevant props change
  useEffect(() => {
    if (mountRef.current && (mountRef.current as any).syncPlacedBoxes) {
      (mountRef.current as any).syncPlacedBoxes();
    }
  }, [placedBoxes, selectedBoxId, requireSupport, showLabels, highlightedItemId]);

  // Handle camera presets triggering
  const triggerCameraPreset = (preset: "front" | "left" | "top" | "isometric") => {
    if (mountRef.current && (mountRef.current as any).setCameraPreset) {
      (mountRef.current as any).setCameraPreset(preset);
    }
  };

  return (
    <div className="relative w-full h-full flex flex-col bg-white rounded-xl shadow-md border border-slate-200 overflow-hidden" id="container_3d_wrapper">
      {/* 3D Viewport Controls / Camera Presets and Interaction Mode */}
      <div className="absolute top-4 left-4 z-10 flex flex-col gap-2 pointer-events-auto">
        {/* Interaction Mode Switch */}
        <div className="flex bg-slate-900/90 backdrop-blur-md p-1 rounded-xl shadow-lg border border-slate-700/50">
          <button
            onClick={() => setInteractionMode("orbit")}
            className={`flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold transition-all duration-200 ${
              interactionMode === "orbit"
                ? "bg-blue-600 text-white shadow-md shadow-blue-500/20"
                : "text-slate-300 hover:text-white hover:bg-slate-800/50"
            }`}
            id="btn_mode_orbit"
          >
            <Eye className="w-3.5 h-3.5" />
            <span>Mode Kamera (Putar)</span>
          </button>
          <button
            onClick={() => setInteractionMode("drag")}
            className={`flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold transition-all duration-200 ${
              interactionMode === "drag"
                ? "bg-emerald-600 text-white shadow-md shadow-emerald-500/20"
                : "text-slate-300 hover:text-white hover:bg-slate-800/50"
            }`}
            id="btn_mode_drag"
          >
            <Move className="w-3.5 h-3.5" />
            <span>Mode Pindah Box</span>
          </button>
        </div>

        {/* Camera Presets */}
        <div className="flex flex-wrap gap-1 bg-white/95 backdrop-blur-md p-1.5 rounded-xl shadow-lg border border-slate-200">
          <button
            onClick={() => triggerCameraPreset("isometric")}
            className="px-2.5 py-1 text-xs font-bold text-slate-700 hover:text-blue-600 hover:bg-blue-50/50 rounded-lg transition-all duration-150"
            id="btn_camera_iso"
          >
            Isometric
          </button>
          <button
            onClick={() => triggerCameraPreset("front")}
            className="px-2.5 py-1 text-xs font-bold text-slate-700 hover:text-blue-600 hover:bg-blue-50/50 rounded-lg transition-all duration-150"
            id="btn_camera_front"
          >
            Depan (Front)
          </button>
          <button
            onClick={() => triggerCameraPreset("left")}
            className="px-2.5 py-1 text-xs font-bold text-slate-700 hover:text-blue-600 hover:bg-blue-50/50 rounded-lg transition-all duration-150"
            id="btn_camera_left"
          >
            Kiri (Left)
          </button>
          <button
            onClick={() => triggerCameraPreset("top")}
            className="px-2.5 py-1 text-xs font-bold text-slate-700 hover:text-blue-600 hover:bg-blue-50/50 rounded-lg transition-all duration-150"
            id="btn_camera_top"
          >
            Atas (Top)
          </button>
        </div>
      </div>

      {/* Hover Instruction Overlay */}
      <div className="absolute top-4 right-4 z-10 flex flex-col items-end gap-1 pointer-events-none">
        <div className="flex items-center gap-1.5 bg-slate-900/90 backdrop-blur-md text-white px-3 py-1.5 rounded-xl text-xs font-medium shadow-lg border border-slate-800">
          <div className={`w-1.5 h-1.5 rounded-full ${interactionMode === 'orbit' ? 'bg-blue-400' : 'bg-emerald-400'} animate-pulse`} />
          <span>
            {interactionMode === "orbit" 
              ? "Kamera Bebas: Klik box untuk opsi | Drag di mana saja untuk putar" 
              : "Mode Pindah: Drag box untuk reposisi | Klik box untuk opsi"}
          </span>
        </div>
        <div className="text-[10px] text-slate-500 bg-white/80 backdrop-blur-sm px-2 py-0.5 rounded border border-slate-200">
          Klik kanan + geser / 2 jari = Geser Kamera (Pan) | Scroll = Zoom
        </div>
      </div>

      {/* Rendering target mount point */}
      <div ref={mountRef} className="w-full h-[460px] md:h-[500px] flex-grow relative" />

      {/* Dragging Overlay Indicator */}
      {dragActive && (
        <div className="absolute inset-x-0 bottom-4 mx-auto w-fit bg-emerald-500/90 backdrop-blur-md text-white px-4 py-1.5 rounded-full text-xs font-medium shadow-md border border-emerald-400/30 flex items-center gap-1.5 animate-pulse">
          <div className="w-2 h-2 rounded-full bg-white" />
          <span>Snapping ke corner terdekat yang valid...</span>
        </div>
      )}

      {/* Floating 2D tooltip details on hover */}
      {hoveredBox && hoveredPos && !dragActive && (
        <div
          className="fixed z-40 bg-slate-900/95 backdrop-blur text-white p-3 rounded-lg shadow-xl text-xs flex flex-col gap-1 border border-slate-700 pointer-events-none max-w-xs transition-opacity duration-150"
          style={{
            left: hoveredPos.x + 15,
            top: hoveredPos.y + 15,
          }}
        >
          <div className="font-bold text-sm border-b border-slate-700 pb-1 mb-1 text-blue-400 flex items-center gap-1.5">
            <span className="w-2 h-2 rounded-full" style={{ backgroundColor: hoveredBox.color }} />
            {hoveredBox.name}
          </div>
          <div><span className="text-slate-400 font-medium">Original Dim:</span> {hoveredBox.p} × {hoveredBox.l} × {hoveredBox.t} cm</div>
          <div><span className="text-slate-400 font-medium">Placed Dim:</span> {hoveredBox.d} × {hoveredBox.w} × {hoveredBox.h} cm</div>
          <div><span className="text-slate-400 font-medium">Posisi:</span> X: {hoveredBox.x}, Y: {hoveredBox.y}, Z: {hoveredBox.z} cm</div>
          <div className="mt-1 pt-1 border-t border-slate-800 text-[10px] text-emerald-400">
            Diletakkan di Candidate Corner
          </div>
        </div>
      )}

      {/* Floating 2D Popover for Clicked/Double-Clicked Box Action */}
      {activeDeletePopup && (
        <div
          className="fixed z-50 bg-slate-900 border border-slate-800 text-white rounded-2xl shadow-2xl p-3 flex flex-col gap-2 min-w-[200px] animate-fade-in"
          style={{
            left: Math.max(10, activeDeletePopup.x - 100),
            top: Math.max(10, activeDeletePopup.y - 120),
          }}
          onClick={(e) => e.stopPropagation()}
        >
          <div className="flex items-center justify-between border-b border-slate-850 pb-1.5">
            <span className="text-[10px] uppercase font-extrabold tracking-wider text-slate-400">Aksi Box Terpilih</span>
            <button
              type="button"
              onClick={() => setActiveDeletePopup(null)}
              className="text-[9px] font-bold text-slate-400 hover:text-white px-1.5 py-0.5 bg-slate-800 hover:bg-slate-700 rounded transition-colors cursor-pointer"
            >
              Tutup
            </button>
          </div>

          <div className="flex flex-col gap-1.5">
            {/* ROTATE BUTTON */}
            <button
              type="button"
              onClick={(e) => {
                e.stopPropagation();
                if (onRotateBox) {
                  onRotateBox();
                }
              }}
              className="flex items-center justify-center gap-1.5 bg-indigo-600 hover:bg-indigo-550 text-white text-[11px] font-black py-2 px-3 rounded-lg shadow-md transition-all active:scale-95 cursor-pointer"
              title="Putar Orientasi Box"
            >
              <Rotate3d className="w-3.5 h-3.5" />
              <span>PUTAR (ROTATE)</span>
            </button>

            {/* DELETE BUTTON */}
            <button
              type="button"
              onClick={(e) => {
                e.stopPropagation();
                if (onDeleteBox) {
                  onDeleteBox(activeDeletePopup.boxId);
                }
                setActiveDeletePopup(null);
              }}
              className="flex items-center justify-center gap-1.5 bg-red-600 hover:bg-red-700 text-white text-[11px] font-black py-2 px-3 rounded-lg shadow-md transition-all active:scale-95 cursor-pointer"
              title="Klik untuk menghapus karton"
              id="btn_delete_floating_box"
            >
              <Trash2 className="w-3.5 h-3.5" />
              <span>HAPUS (DELETE)</span>
            </button>
          </div>
        </div>
      )}
    </div>
  );
};
