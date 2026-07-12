import { ItemType, PlacedBox, CandidateCorner } from "./types";

export const DEFAULT_CONTAINER_W = 235;  // Lebar (X) in cm
export const DEFAULT_CONTAINER_H = 269;  // Tinggi (Y) in cm
export const DEFAULT_CONTAINER_D = 1203; // Panjang (Z) in cm

// Container Spec Types
export interface ContainerSpec {
  id: string;
  name: string;
  w: number; // Lebar (X)
  h: number; // Tinggi (Y)
  d: number; // Panjang (Z)
}

export const CONTAINER_PRESETS: ContainerSpec[] = [
  { id: "20ft_std", name: "20ft GP (Standar) - 590 × 235 × 239 cm", w: 235, h: 239, d: 590 },
  { id: "40ft_std", name: "40ft GP (Standar) - 1203 × 235 × 239 cm", w: 235, h: 239, d: 1203 },
  { id: "40ft_hc", name: "40ft HC (High Cube) - 1203 × 235 × 269 cm", w: 235, h: 269, d: 1203 },
  { id: "45ft_hc", name: "45ft HC (High Cube) - 1356 × 235 × 269 cm", w: 235, h: 269, d: 1356 },
];

// Check if two boxes overlap in 3D
export function checkOverlap(
  x1: number, y1: number, z1: number, w1: number, h1: number, d1: number,
  x2: number, y2: number, z2: number, w2: number, h2: number, d2: number
): boolean {
  return (
    x1 < x2 + w2 && x1 + w1 > x2 &&
    y1 < y2 + h2 && y1 + h1 > y2 &&
    z1 < z2 + d2 && z1 + d1 > z2
  );
}

// Check if a box overlaps with any already placed box
export function checkCollision(
  x: number, y: number, z: number, w: number, h: number, d: number,
  placedBoxes: PlacedBox[],
  excludeId?: string
): boolean {
  for (const box of placedBoxes) {
    if (excludeId && box.id === excludeId) continue;
    if (checkOverlap(x, y, z, w, h, d, box.x, box.y, box.z, box.w, box.h, box.d)) {
      return true;
    }
  }
  return false;
}

// Check if box is inside container bounds
export function isInsideContainer(
  x: number, y: number, z: number, w: number, h: number, d: number,
  containerW: number = DEFAULT_CONTAINER_W,
  containerH: number = DEFAULT_CONTAINER_H,
  containerD: number = DEFAULT_CONTAINER_D
): boolean {
  return (
    x >= 0 && x + w <= containerW &&
    y >= 0 && y + h <= containerH &&
    z >= 0 && z + d <= containerD
  );
}

// Check if box is supported from below (gravity rule)
// Box is supported if y is 0 (on the floor) OR if there's a box directly underneath
export function isSupported(
  x: number, y: number, z: number, w: number, h: number, d: number,
  placedBoxes: PlacedBox[],
  excludeId?: string
): boolean {
  if (y === 0) return true;
  
  // We check if at least 10% of the box bottom surface is resting on other boxes
  let supportedArea = 0;
  const totalBottomArea = w * d;
  
  // For a simpler and highly robust check, let's see if there is any box that touches the bottom of this box
  // at B.y + B.h == y, and overlaps in the X-Z projection
  for (const box of placedBoxes) {
    if (excludeId && box.id === excludeId) continue;
    
    // Check if the top of 'box' matches the bottom of our box
    if (Math.abs((box.y + box.h) - y) < 1) { // 1cm tolerance
      // Check X-Z overlap area
      const overlapX = Math.max(0, Math.min(x + w, box.x + box.w) - Math.max(x, box.x));
      const overlapZ = Math.max(0, Math.min(z + d, box.z + box.d) - Math.max(z, box.z));
      
      supportedArea += overlapX * overlapZ;
    }
  }
  
  // Require at least some support (e.g. 10% of bottom surface area or simply at least one support point)
  // Let's use 10% surface area support to allow corner overlaps but prevent floating in free air
  return (supportedArea / totalBottomArea) >= 0.1;
}

// Get unique and valid candidate corners
export function getValidCandidateCorners(
  placedBoxes: PlacedBox[],
  containerW: number = DEFAULT_CONTAINER_W,
  containerH: number = DEFAULT_CONTAINER_H,
  containerD: number = DEFAULT_CONTAINER_D
): CandidateCorner[] {
  const corners: CandidateCorner[] = [{ x: 0, y: 0, z: 0 }];
  
  for (const box of placedBoxes) {
    // 1. Right side of box
    corners.push({ x: box.x + box.w, y: box.y, z: box.z });
    // 2. Top side of box
    corners.push({ x: box.x, y: box.y + box.h, z: box.z });
    // 3. Front side of box (along depth Z)
    corners.push({ x: box.x, y: box.y, z: box.z + box.d });
    
    // Extra corners at outer boundaries to fill potential gaps
    corners.push({ x: box.x + box.w, y: box.y + box.h, z: box.z });
    corners.push({ x: box.x + box.w, y: box.y, z: box.z + box.d });
    corners.push({ x: box.x, y: box.y + box.h, z: box.z + box.d });
  }
  
  // Filter and deduplicate
  const seenKeys = new Set<string>();
  const validCorners: CandidateCorner[] = [];
  
  for (const corner of corners) {
    // Ensure inside bounds
    if (corner.x < 0 || corner.x >= containerW ||
        corner.y < 0 || corner.y >= containerH ||
        corner.z < 0 || corner.z >= containerD) {
      continue;
    }
    
    // Snap to nearest integer
    const cx = Math.round(corner.x);
    const cy = Math.round(corner.y);
    const cz = Math.round(corner.z);
    
    const key = `${cx},${cy},${cz}`;
    if (seenKeys.has(key)) continue;
    seenKeys.add(key);
    
    // Check if the corner is strictly inside any placed box
    let inside = false;
    for (const box of placedBoxes) {
      if (cx >= box.x && cx < box.x + box.w &&
          cy >= box.y && cy < box.y + box.h &&
          cz >= box.z && cz < box.z + box.d) {
        inside = true;
        break;
      }
    }
    
    if (!inside) {
      validCorners.push({ x: cx, y: cy, z: cz });
    }
  }
  
  return validCorners;
}

// Get all 6 orientations for a box of dimensions (p, l, t)
export function getOrientations(p: number, l: number, t: number) {
  // Original: Length (Panjang - p), Width (Lebar - l), Height (Tinggi - t)
  // Let's permute them to fit X (width), Y (height), Z (depth/length)
  const configs = [
    { w: l, h: t, d: p }, // orientation 1
    { w: l, h: p, d: t }, // orientation 2
    { w: p, h: l, d: t }, // orientation 3
    { w: p, h: t, d: l }, // orientation 4
    { w: t, h: p, d: l }, // orientation 5
    { w: t, h: l, d: p }, // orientation 6
  ];
  
  // Deduplicate orientation dimensions (e.g. if carton is square or cube)
  const seen = new Set<string>();
  const uniqueConfigs: { w: number; h: number; d: number }[] = [];
  
  for (const c of configs) {
    const key = `${c.w},${c.h},${c.d}`;
    if (!seen.has(key)) {
      seen.add(key);
      uniqueConfigs.push(c);
    }
  }
  
  return uniqueConfigs;
}

// Generate Loading Plan automatically
export interface PackResult {
  packedBoxes: PlacedBox[];
  unpackedItems: { item: ItemType; count: number }[];
  utilizationPercent: number;
  totalVolumePacked: number; // in m^3
  totalVolumeContainer: number; // in m^3
}

export function generateLoadingPlan(
  items: ItemType[],
  sortingStrategy: "volume" | "qty" | "length" | "none" = "volume",
  requireSupport: boolean = true,
  containerW: number = DEFAULT_CONTAINER_W,
  containerH: number = DEFAULT_CONTAINER_H,
  containerD: number = DEFAULT_CONTAINER_D
): PackResult {
  // Clone and flatten all individual cartons
  interface CartonToPack {
    id: string;
    item: ItemType;
  }
  
  let cartons: CartonToPack[] = [];
  items.forEach((item) => {
    for (let i = 0; i < item.qty; i++) {
      cartons.push({
        id: `${item.id}_${i}`,
        item: item,
      });
    }
  });
  
  // Sort cartons based on strategy
  if (sortingStrategy === "volume") {
    cartons.sort((a, b) => {
      const volA = a.item.length * a.item.width * a.item.height;
      const volB = b.item.length * b.item.width * b.item.height;
      return volB - volA; // largest volume first
    });
  } else if (sortingStrategy === "length") {
    cartons.sort((a, b) => {
      const maxDimA = Math.max(a.item.length, a.item.width, a.item.height);
      const maxDimB = Math.max(b.item.length, b.item.width, b.item.height);
      return maxDimB - maxDimA; // longest carton first
    });
  } else if (sortingStrategy === "qty") {
    cartons.sort((a, b) => b.item.qty - a.item.qty); // item with highest total qty first
  }
  
  const placedBoxes: PlacedBox[] = [];
  const unpackedCounts: Record<string, number> = {};
  items.forEach((it) => {
    unpackedCounts[it.id] = 0;
  });
  
  for (const carton of cartons) {
    const p = carton.item.length;
    const l = carton.item.width;
    const t = carton.item.height;
    
    const corners = getValidCandidateCorners(placedBoxes, containerW, containerH, containerD);
    const orientations = getOrientations(p, l, t);
    
    let bestCorner: CandidateCorner | null = null;
    let bestOrientation: { w: number; h: number; d: number } | null = null;
    let bestScore = Infinity;
    
    for (const corner of corners) {
      for (const orient of orientations) {
        const { w, h, d } = orient;
        
        // 1. Fits in container bounds?
        if (!isInsideContainer(corner.x, corner.y, corner.z, w, h, d, containerW, containerH, containerD)) {
          continue;
        }
        
        // 2. Collision with other boxes?
        if (checkCollision(corner.x, corner.y, corner.z, w, h, d, placedBoxes)) {
          continue;
        }
        
        // 3. Support from below?
        if (requireSupport && !isSupported(corner.x, corner.y, corner.z, w, h, d, placedBoxes)) {
          continue;
        }
        
        // 4. Calculate score
        // Score: prioritizes filling container from back-to-front (Z first), then bottom-to-top (Y second), then left-to-right (X third)
        const score = (corner.z * 1000000) + (corner.y * 1000) + corner.x;
        
        if (score < bestScore) {
          bestScore = score;
          bestCorner = corner;
          bestOrientation = orient;
        }
      }
    }
    
    if (bestCorner && bestOrientation) {
      placedBoxes.push({
        id: carton.id,
        itemId: carton.item.id,
        name: carton.item.name,
        color: carton.item.color,
        w: bestOrientation.w,
        h: bestOrientation.h,
        d: bestOrientation.d,
        p: p,
        l: l,
        t: t,
        x: bestCorner.x,
        y: bestCorner.y,
        z: bestCorner.z,
      });
    } else {
      unpackedCounts[carton.item.id]++;
    }
  }
  
  // Format unpacked items list
  const unpackedItems = items
    .filter((it) => unpackedCounts[it.id] > 0)
    .map((it) => ({
      item: it,
      count: unpackedCounts[it.id],
    }));
  
  // Volume calculations in cubic meters (m^3)
  const containerVolM3 = (containerW * containerH * containerD) / 1000000;
  
  let packedVolCm3 = 0;
  placedBoxes.forEach((box) => {
    packedVolCm3 += box.w * box.h * box.d;
  });
  const totalVolumePacked = packedVolCm3 / 1000000;
  const utilizationPercent = Math.round((totalVolumePacked / containerVolM3) * 1000) / 10;
  
  return {
    packedBoxes: placedBoxes,
    unpackedItems,
    utilizationPercent,
    totalVolumePacked,
    totalVolumeContainer: containerVolM3,
  };
}

// Find closest valid placement for manual drag & drop
export function findBestDragPosition(
  draggedBox: PlacedBox,
  cursorX: number, // Target center/corner coordinates in container
  cursorY: number,
  cursorZ: number,
  placedBoxes: PlacedBox[],
  requireSupport: boolean = true,
  containerW: number = DEFAULT_CONTAINER_W,
  containerH: number = DEFAULT_CONTAINER_H,
  containerD: number = DEFAULT_CONTAINER_D
): { x: number; y: number; z: number; w: number; h: number; d: number; score: number } | null {
  const corners = getValidCandidateCorners(placedBoxes.filter(b => b.id !== draggedBox.id), containerW, containerH, containerD);
  const orientations = getOrientations(draggedBox.p, draggedBox.l, draggedBox.t);
  
  let bestPos: { x: number; y: number; z: number; w: number; h: number; d: number } | null = null;
  let minDistance = Infinity;
  
  for (const corner of corners) {
    for (const orient of orientations) {
      const { w, h, d } = orient;
      
      // 1. Check container bounds
      if (!isInsideContainer(corner.x, corner.y, corner.z, w, h, d, containerW, containerH, containerD)) {
        continue;
      }
      
      // 2. Check collision with other boxes (excluding self)
      if (checkCollision(corner.x, corner.y, corner.z, w, h, d, placedBoxes, draggedBox.id)) {
        continue;
      }
      
      // 3. Check stability if requireSupport is enabled
      if (requireSupport && !isSupported(corner.x, corner.y, corner.z, w, h, d, placedBoxes, draggedBox.id)) {
        continue;
      }
      
      // We want to calculate the distance from the dragged cursor position to this candidate box position
      // Let's use the center of the box for distance comparison
      const candidateCenterX = corner.x + w / 2;
      const candidateCenterY = corner.y + h / 2;
      const candidateCenterZ = corner.z + d / 2;
      
      const dx = candidateCenterX - cursorX;
      const dy = candidateCenterY - cursorY;
      const dz = candidateCenterZ - cursorZ;
      const distance = Math.sqrt(dx*dx + dy*dy + dz*dz);
      
      if (distance < minDistance) {
        minDistance = distance;
        bestPos = {
          x: corner.x,
          y: corner.y,
          z: corner.z,
          w,
          h,
          d,
        };
      }
    }
  }
  
  if (bestPos) {
    // Return with a scoring indicator
    const score = (bestPos.y * 1000000) + (bestPos.z * 1000) + bestPos.x;
    return { ...bestPos, score };
  }
  
  return null;
}
