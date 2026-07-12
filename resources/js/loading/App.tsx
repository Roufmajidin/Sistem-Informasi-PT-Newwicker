import React, { useState, useEffect, useRef, useCallback } from "react";
import { ItemType, PlacedBox } from "./types";
import { ContainerViewer } from "./components/ContainerViewer";
import { 
  generateLoadingPlan, 
  CONTAINER_PRESETS,
  getValidCandidateCorners,
  getOrientations,
  isInsideContainer,
  checkCollision,
  isSupported,
  ContainerSpec
} from "./packingSolver";
import { 
  Box, 
  Plus, 
  Trash2, 
  Play, 
  RotateCcw, 
  Sparkles, 
  Settings, 
  AlertTriangle, 
  CheckCircle2, 
  Info, 
  Layers, 
  Sliders, 
  Eye, 
  EyeOff, 
  Package, 
  ChevronRight,
  TrendingUp,
  Download,
  FileSpreadsheet,
  Rotate3d,
  Check,
  Code,
  ArrowRight,
  HelpCircle,
  Truck,
  Undo,
  Redo
} from "lucide-react";

// Default preset items (Mirrors, Headboards, Chairs, Tables)
const DEFAULT_ITEMS: ItemType[] = [
  {
    id: "item_bilbao",
    name: "Mirror Bilbao",
    length: 129,
    width: 101,
    height: 16,
    qty: 30,
    color: "#ff2d20", // Laravel Red
  },
  {
    id: "item_boudoir",
    name: "Headboard Boudoir",
    length: 252,
    width: 12,
    height: 152,
    qty: 10,
    color: "#eab308", // Yellow
  },
  {
    id: "item_ewan",
    name: "Dining Chair Ewan",
    length: 68,
    width: 63,
    height: 83,
    qty: 72,
    color: "#10b981", // Emerald
  },
  {
    id: "item_tiago",
    name: "Dining Table Tiago",
    length: 83,
    width: 83,
    height: 82,
    qty: 32,
    color: "#3b82f6", // Blue
  },
];

export default function App() {
  const [items, setItems] = useState<ItemType[]>(() => {
    const saved = localStorage.getItem("container_items");
    return saved ? JSON.parse(saved) : DEFAULT_ITEMS;
  });

  const [placedBoxes, setPlacedBoxes] = useState<PlacedBox[]>(() => {
    const saved = localStorage.getItem("container_placed_boxes");
    return saved ? JSON.parse(saved) : [];
  });

  const [selectedPresetId, setSelectedPresetId] = useState<string>(() => {
    return localStorage.getItem("container_preset_id") || "40ft_hc";
  });

  const [unpackedItems, setUnpackedItems] = useState<{ item: ItemType; count: number }[]>([]);
  const [requireSupport, setRequireSupport] = useState<boolean>(true);
  const [showLabels, setShowLabels] = useState<boolean>(true);
  const [sortingStrategy, setSortingStrategy] = useState<"volume" | "qty" | "length" | "none">("volume");
  const [packingSpeed, setPackingSpeed] = useState<"instant" | "animated">("animated");
  
  const [isPacking, setIsPacking] = useState<boolean>(false);
  const [packingStepText, setPackingStepText] = useState<string>("");
  const [selectedBoxId, setSelectedBoxId] = useState<string | null>(null);
  const [highlightedItemId, setHighlightedItemId] = useState<string | null>(null);
  const [fallingAlert, setFallingAlert] = useState<string | null>(null);
  
  // Custom Tab for Laravel Integration Guides
  const [activeTab, setActiveTab] = useState<"simulator" | "laravel">("simulator");

  // Form states for adding/editing items
  const [editingItem, setEditingItem] = useState<ItemType | null>(null);
  const [newItemName, setNewItemName] = useState("");
  const [newItemLength, setNewItemLength] = useState(100);
  const [newItemWidth, setNewItemWidth] = useState(100);
  const [newItemHeight, setNewItemHeight] = useState(100);
  const [newItemQty, setNewItemQty] = useState(5);
  const [newItemColor, setNewItemColor] = useState("#ff2d20");

  const currentPreset = CONTAINER_PRESETS.find(p => p.id === selectedPresetId) || CONTAINER_PRESETS[2];

  // Undo/Redo history states
  const [undoStack, setUndoStack] = useState<{ placedBoxes: PlacedBox[]; unpackedItems: any[] }[]>([]);
  const [redoStack, setRedoStack] = useState<{ placedBoxes: PlacedBox[]; unpackedItems: any[] }[]>([]);

  // Refs to access fresh state from event handlers and callbacks
  const placedBoxesRef = useRef<PlacedBox[]>([]);
  const unpackedItemsRef = useRef<any[]>([]);
  const packingIntervalRef = useRef<any>(null);
  const packingStepIntervalRef = useRef<any>(null);

  useEffect(() => {
    placedBoxesRef.current = placedBoxes;
  }, [placedBoxes]);

  useEffect(() => {
    unpackedItemsRef.current = unpackedItems;
  }, [unpackedItems]);

  const cancelPacking = useCallback(() => {
    if (packingIntervalRef.current) {
      clearInterval(packingIntervalRef.current);
      packingIntervalRef.current = null;
    }
    if (packingStepIntervalRef.current) {
      clearInterval(packingStepIntervalRef.current);
      packingStepIntervalRef.current = null;
    }
    setIsPacking(false);
  }, []);

  const saveToHistory = useCallback(() => {
    setUndoStack((prev) => [
      ...prev,
      {
        placedBoxes: JSON.parse(JSON.stringify(placedBoxesRef.current)),
        unpackedItems: JSON.parse(JSON.stringify(unpackedItemsRef.current))
      }
    ]);
    setRedoStack([]); // Clear redo stack on new action
  }, []);

  const handleUndo = useCallback(() => {
    if (undoStack.length === 0) return;
    
    cancelPacking();
    
    const nextUndo = [...undoStack];
    const prevState = nextUndo.pop();
    
    if (prevState) {
      // Push current to redoStack
      setRedoStack((prev) => [
        ...prev,
        {
          placedBoxes: JSON.parse(JSON.stringify(placedBoxesRef.current)),
          unpackedItems: JSON.parse(JSON.stringify(unpackedItemsRef.current))
        }
      ]);
      
      setPlacedBoxes(prevState.placedBoxes);
      setUnpackedItems(prevState.unpackedItems);
      setUndoStack(nextUndo);
      setSelectedBoxId(null);
    }
  }, [undoStack, cancelPacking]);

  const handleRedo = useCallback(() => {
    if (redoStack.length === 0) return;
    
    cancelPacking();
    
    const nextRedo = [...redoStack];
    const nextState = nextRedo.pop();
    
    if (nextState) {
      // Push current to undoStack
      setUndoStack((prev) => [
        ...prev,
        {
          placedBoxes: JSON.parse(JSON.stringify(placedBoxesRef.current)),
          unpackedItems: JSON.parse(JSON.stringify(unpackedItemsRef.current))
        }
      ]);
      
      setPlacedBoxes(nextState.placedBoxes);
      setUnpackedItems(nextState.unpackedItems);
      setRedoStack(nextRedo);
      setSelectedBoxId(null);
    }
  }, [redoStack, cancelPacking]);

  // Keyboard shortcut listener for Ctrl+Z (Undo) and Ctrl+Y or Ctrl+Shift+Z (Redo)
  useEffect(() => {
    const handleKeyDown = (e: KeyboardEvent) => {
      // Check if input or textarea is active (to avoid undoing while writing in text inputs)
      const activeEl = document.activeElement;
      if (
        activeEl && 
        (activeEl.tagName === "INPUT" || 
         activeEl.tagName === "TEXTAREA" || 
         activeEl.getAttribute("contenteditable") === "true")
      ) {
        return;
      }

      const isCtrl = e.ctrlKey || e.metaKey; // supports Mac Cmd key
      
      if (isCtrl && e.key.toLowerCase() === "z") {
        e.preventDefault();
        if (e.shiftKey) {
          // Ctrl+Shift+Z: Redo
          handleRedo();
        } else {
          // Ctrl+Z: Undo
          handleUndo();
        }
      } else if (isCtrl && e.key.toLowerCase() === "y") {
        e.preventDefault();
        handleRedo();
      }
    };

    window.addEventListener("keydown", handleKeyDown);
    return () => {
      window.removeEventListener("keydown", handleKeyDown);
    };
  }, [handleUndo, handleRedo]);

  // Save changes to localStorage
  useEffect(() => {
    localStorage.setItem("container_items", JSON.stringify(items));
  }, [items]);

  useEffect(() => {
    localStorage.setItem("container_placed_boxes", JSON.stringify(placedBoxes));
  }, [placedBoxes]);

  useEffect(() => {
    localStorage.setItem("container_preset_id", selectedPresetId);
  }, [selectedPresetId]);

  // Handle manual movement of a carton (callback from Three.js ContainerViewer)
  const handleMoveBox = (
    boxId: string, 
    x: number, 
    y: number, 
    z: number, 
    w: number, 
    h: number, 
    d: number
  ) => {
    saveToHistory();
    setPlacedBoxes((prev) => 
      prev.map((box) => 
        box.id === boxId 
          ? { ...box, x, y, z, w, h, d } 
          : box
      )
    );
  };

  // Run the Corner Fitting Auto Loading Plan with realistic loading states
  const handleGenerateLoadingPlan = () => {
    if (isPacking) return;
    saveToHistory();
    setIsPacking(true);
    setSelectedBoxId(null);

    const steps = [
      "Menginisialisasi kontainer dan volume batas...",
      "Menganalisis berat & dimensi item cargo...",
      "Mengurutkan item berdasarkan volume terbesar (plot presisi)...",
      "Menghitung penempatan optimal (Zero-Collision Tetris Solver)...",
      "Memvalidasi aturan gravitasi & dukungan tumpukan...",
      "Menyelesaikan layout hemat tempat..."
    ];

    let stepIdx = 0;
    const stepInterval = setInterval(() => {
      if (stepIdx < steps.length) {
        setPackingStepText(steps[stepIdx]);
        stepIdx++;
      } else {
        clearInterval(stepInterval);
        packingStepIntervalRef.current = null;
        
        const result = generateLoadingPlan(
          items, 
          sortingStrategy, 
          requireSupport,
          currentPreset.w,
          currentPreset.h,
          currentPreset.d
        );

        if (packingSpeed === "instant") {
          setPlacedBoxes(result.packedBoxes);
          setUnpackedItems(result.unpackedItems);
          setIsPacking(false);
        } else {
          // Animated step-by-step loading
          setPlacedBoxes([]);
          setUnpackedItems([]);
          
          let index = 0;
          const intervalTime = Math.max(12, Math.min(80, 2000 / result.packedBoxes.length));

          const timer = setInterval(() => {
            if (index < result.packedBoxes.length) {
              const nextBox = result.packedBoxes[index];
              setPlacedBoxes((prev) => [...prev, nextBox]);
              index++;
            } else {
              clearInterval(timer);
              packingIntervalRef.current = null;
              setUnpackedItems(result.unpackedItems);
              setIsPacking(false);
            }
          }, intervalTime);
          packingIntervalRef.current = timer;
        }
      }
    }, 250);
    packingStepIntervalRef.current = stepInterval;
  };

  // Add one unit of an item to the container manually
  const handleAddSingleCartonManually = (item: ItemType) => {
    setSelectedBoxId(null);
    const corners = getValidCandidateCorners(placedBoxes, currentPreset.w, currentPreset.h, currentPreset.d);
    const orientations = getOrientations(item.length, item.width, item.height);
    
    let bestCorner = null;
    let bestOrientation = null;
    let bestScore = Infinity;

    for (const corner of corners) {
      for (const orient of orientations) {
        const { w, h, d } = orient;
        if (!isInsideContainer(corner.x, corner.y, corner.z, w, h, d, currentPreset.w, currentPreset.h, currentPreset.d)) continue;
        if (checkCollision(corner.x, corner.y, corner.z, w, h, d, placedBoxes)) continue;
        if (requireSupport && !isSupported(corner.x, corner.y, corner.z, w, h, d, placedBoxes)) continue;

        const score = (corner.z * 1000000) + (corner.y * 1000) + corner.x;
        if (score < bestScore) {
          bestScore = score;
          bestCorner = corner;
          bestOrientation = orient;
        }
      }
    }

    if (bestCorner && bestOrientation) {
      saveToHistory();
      const boxId = `${item.id}_manual_${Date.now()}`;
      const newBox: PlacedBox = {
        id: boxId,
        itemId: item.id,
        name: item.name,
        color: item.color,
        w: bestOrientation.w,
        h: bestOrientation.h,
        d: bestOrientation.d,
        p: item.length,
        l: item.width,
        t: item.height,
        x: bestCorner.x,
        y: bestCorner.y,
        z: bestCorner.z,
      };
      setPlacedBoxes((prev) => [...prev, newBox]);
      setSelectedBoxId(boxId);
    } else {
      alert(`Gagal meletakkan box! Tidak ada candidate corner yang valid dan stabil untuk ukuran ${item.length}x${item.width}x${item.height} cm.`);
    }
  };

  // Handle manual rotation of a selected box inside the container
  const handleRotateSelectedBox = () => {
    const box = placedBoxes.find((b) => b.id === selectedBoxId);
    if (!box) return;

    // Get all 6 orientations
    const orientations = getOrientations(box.p, box.l, box.t);
    
    // Find current orientation index based on current (w, h, d)
    let currentIndex = orientations.findIndex(
      (o) => o.w === box.w && o.h === box.h && o.d === box.d
    );

    // Get next orientation in cycle
    const nextIndex = (currentIndex + 1) % orientations.length;
    const nextOrient = orientations[nextIndex];

    // Verify if it fits in the current position (x, y, z) with next orientation
    const fits = isInsideContainer(
      box.x, box.y, box.z, 
      nextOrient.w, nextOrient.h, nextOrient.d, 
      currentPreset.w, currentPreset.h, currentPreset.d
    );

    // Collision check, excluding the box itself
    const collides = checkCollision(
      box.x, box.y, box.z, 
      nextOrient.w, nextOrient.h, nextOrient.d, 
      placedBoxes, 
      box.id
    );

    // Stack stability support rule check
    const supported = !requireSupport || isSupported(
      box.x, box.y, box.z, 
      nextOrient.w, nextOrient.h, nextOrient.d, 
      placedBoxes, 
      box.id
    );

    if (fits && !collides && supported) {
      saveToHistory();
      setPlacedBoxes((prev) =>
        prev.map((b) =>
          b.id === box.id
            ? { ...b, w: nextOrient.w, h: nextOrient.h, d: nextOrient.d }
            : b
        )
      );
    } else {
      alert(
        `Tidak dapat memutar box ke orientasi baru di posisi ini!\n` +
        `Alasan: ${!fits ? "Keluar dari kontainer" : collides ? "Menabrak box lain" : "Tidak didukung tumpukan stabil (aturan gravitasi)"}.`
      );
    }
  };

  // Add a duplicate of the selected carton in a specific direction
  const handleAddBoxInDirection = (direction: "kanan" | "kiri" | "atas") => {
    const box = placedBoxes.find((b) => b.id === selectedBoxId);
    if (!box) return;

    // Clone the box's original size and orientation
    let newX = box.x;
    let newY = box.y;
    let newZ = box.z;

    if (direction === "kanan") {
      newX = box.x + box.w;
    } else if (direction === "kiri") {
      newX = box.x - box.w;
    } else if (direction === "atas") {
      newY = box.y + box.h;
    }

    // Validate container boundary fit
    const fits = isInsideContainer(
      newX, newY, newZ,
      box.w, box.h, box.d,
      currentPreset.w,
      currentPreset.h,
      currentPreset.d
    );

    if (!fits) {
      alert(`Gagal menambah box ke ${direction}!\nAlasan: Melebihi batas ukuran kontainer.`);
      return;
    }

    // Check collision with other existing boxes
    const collides = checkCollision(
      newX, newY, newZ,
      box.w, box.h, box.d,
      placedBoxes
    );

    if (collides) {
      alert(`Gagal menambah box ke ${direction}!\nAlasan: Menabrak box lain yang sudah ada.`);
      return;
    }

    // Check support stability if required
    const supported = !requireSupport || isSupported(
      newX, newY, newZ,
      box.w, box.h, box.d,
      placedBoxes
    );

    if (!supported) {
      alert(`Gagal menambah box ke ${direction}!\nAlasan: Tidak ada tumpukan penahan di bawahnya (aturan gravitasi).`);
      return;
    }

    saveToHistory();

    // Auto-increment quantity of the item type in the catalog if it reaches or exceeds limit
    const itemType = items.find((it) => it.id === box.itemId);
    if (itemType) {
      const currentPackedCount = placedBoxes.filter((b) => b.itemId === box.itemId).length;
      if (currentPackedCount >= itemType.qty) {
        setItems((prev) =>
          prev.map((it) =>
            it.id === itemType.id ? { ...it, qty: it.qty + 1 } : it
          )
        );
      }
    }

    const newBoxId = `${box.itemId}_manual_${Date.now()}`;
    const newBox: PlacedBox = {
      id: newBoxId,
      itemId: box.itemId,
      name: box.name,
      color: box.color,
      w: box.w,
      h: box.h,
      d: box.d,
      p: box.p,
      l: box.l,
      t: box.t,
      x: newX,
      y: newY,
      z: newZ,
    };

    setPlacedBoxes((prev) => [...prev, newBox]);
    setSelectedBoxId(newBoxId); // Focus the newly added box
  };

  // Delete a specific carton from container
  const handleDeleteCarton = (boxId: string) => {
    saveToHistory();
    let didAnyFall = false;
    const remaining = placedBoxes.filter((b) => b.id !== boxId);
    
    let changed = true;
    let finalBoxes = remaining.map(b => ({ ...b })); // clone

    while (changed) {
      changed = false;
      // Sort by y ascending so that lower boxes fall first, and higher boxes fall onto them.
      finalBoxes.sort((a, b) => a.y - b.y);

      for (let i = 0; i < finalBoxes.length; i++) {
        const box = finalBoxes[i];
        if (box.y === 0) continue; // On the floor already

        let maxUnderY = 0;

        for (let j = 0; j < finalBoxes.length; j++) {
          if (i === j) continue;
          const other = finalBoxes[j];
          
          if (other.y + other.h <= box.y) {
            // X overlap
            const xOverlap = Math.max(box.x, other.x) < Math.min(box.x + box.w, other.x + other.w);
            // Z overlap
            const zOverlap = Math.max(box.z, other.z) < Math.min(box.z + box.d, other.z + other.d);

            if (xOverlap && zOverlap) {
              const supportTop = other.y + other.h;
              if (supportTop > maxUnderY) {
                maxUnderY = supportTop;
              }
            }
          }
        }

        if (maxUnderY < box.y) {
          box.y = maxUnderY;
          changed = true;
          didAnyFall = true;
        }
      }
    }

    setPlacedBoxes(finalBoxes);
    if (selectedBoxId === boxId) setSelectedBoxId(null);

    if (didAnyFall) {
      setFallingAlert("Boks dikeluarkan & boks lain menyesuaikan tumpukan! 📦💥");
    } else {
      setFallingAlert("Karton berhasil dikeluarkan dari kontainer! 📦👋");
    }
    // Autoclose after 3 seconds
    setTimeout(() => {
      setFallingAlert(null);
    }, 3000);
  };

  // Add custom new item to catalog
  const handleAddNewItem = (e: React.FormEvent) => {
    e.preventDefault();
    if (!newItemName.trim()) return;

    const newItem: ItemType = {
      id: `custom_item_${Date.now()}`,
      name: newItemName.trim(),
      length: newItemLength,
      width: newItemWidth,
      height: newItemHeight,
      qty: newItemQty,
      color: newItemColor,
    };

    setItems((prev) => [...prev, newItem]);
    
    // Reset form states
    setNewItemName("");
    setNewItemLength(100);
    setNewItemWidth(100);
    setNewItemHeight(100);
    setNewItemQty(5);
    setNewItemColor("#" + Math.floor(Math.random()*16777215).toString(16)); // randomize color for next item
  };

  // Save edited catalog item
  const handleSaveItemEdit = (e: React.FormEvent) => {
    e.preventDefault();
    if (!editingItem) return;

    setItems((prev) =>
      prev.map((it) => (it.id === editingItem.id ? editingItem : it))
    );
    setEditingItem(null);
  };

  // Remove item type from catalog
  const handleDeleteItemType = (itemId: string) => {
    saveToHistory();
    setItems((prev) => prev.filter((it) => it.id !== itemId));
    setPlacedBoxes((prev) => prev.filter((b) => b.itemId !== itemId));
    if (editingItem?.id === itemId) setEditingItem(null);
  };

  // Reset to original 4 user items
  const handleResetCatalog = () => {
    if (confirm("Reset katalog item ke furniture bawaan? Semua custom item akan dihapus.")) {
      saveToHistory();
      setItems(DEFAULT_ITEMS);
      setPlacedBoxes([]);
      setUnpackedItems([]);
      setSelectedBoxId(null);
    }
  };

  // Clear all packed boxes inside container
  const handleClearContainer = () => {
    saveToHistory();
    setPlacedBoxes([]);
    setUnpackedItems([]);
    setSelectedBoxId(null);
  };

  // Handle changing container type
  const handleSelectContainerPreset = (presetId: string) => {
    if (placedBoxes.length > 0) {
      if (confirm("Mengganti ukuran kontainer akan mengosongkan box yang sudah disusun. Lanjutkan?")) {
        saveToHistory();
        setSelectedPresetId(presetId);
        setPlacedBoxes([]);
        setUnpackedItems([]);
        setSelectedBoxId(null);
      }
    } else {
      setSelectedPresetId(presetId);
    }
  };

  // Stats summaries
  const containerVolM3 = (currentPreset.w * currentPreset.h * currentPreset.d) / 1000000;
  
  let totalVolumePacked = 0;
  placedBoxes.forEach((box) => {
    totalVolumePacked += (box.w * box.h * box.d) / 1000000;
  });
  
  const utilizationPercent = containerVolM3 > 0 ? (totalVolumePacked / containerVolM3) * 100 : 0;
  const remainingVolume = Math.max(0, containerVolM3 - totalVolumePacked);
  const totalCartonsPacked = placedBoxes.length;
  const totalCartonsInCatalog = items.reduce((sum, item) => sum + item.qty, 0);

  // Selected Box Data
  const selectedBox = placedBoxes.find((b) => b.id === selectedBoxId);

  return (
    <div className="min-h-screen bg-slate-50 font-sans text-slate-800" id="container_loading_app">
      
      {/* Pristine Modern Laravel-Themed Header */}
      <header className="bg-slate-950 border-b border-red-500/20 py-4.5 px-6 text-white sticky top-0 z-30 shadow-xl">
        <div className="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-center gap-4">
          <div className="flex items-center gap-3">
            <div className="bg-red-600/10 p-2 rounded-lg border border-red-500/30">
              {/* Laravel Red SVG Logo */}
              <svg className="w-7 h-7 text-[#ff2d20] fill-current" viewBox="0 0 262 262">
                <path d="M225 15.6c-13.5-7.7-30.7-3-38.4 10.5L163.4 67c-13.8-1.5-27.4 3-37.4 12.8L12.5 193c-9.7 10-14.3 23.6-12.8 37.4l4.5 40.5c1.5 13.5 11.2 24.3 24.7 27.5l40.5 9c13.8 3 27.4-1.5 37.4-11.3l113.5-113.5c10-9.7 14.3-23.6 12.8-37.4l-4.5-40.5c-1.5-13.8-11.2-24.3-24.7-27.5l-2.4-.5 23-40.3c7.7-13.5 3-30.7-10.5-38.4l-1-.5zm-47.5 73.1l2.3 20.3c.5 4.5-.9 9-4.2 12.3L62.1 234.8c-3.2 3.2-7.8 4.7-12.3 4.2l-20.3-2.3c-4.5-.5-9 .9-12.3 4.2l-3.3 3.3c-1.5 1.5-3.8 1.8-5.6.8l-1.5-1c-1.8-1.1-2.4-3.4-1.4-5.3l3.3-3.3c3.2-3.2 4.7-7.8 4.2-12.3l-2.3-20.3c-.5-4.5.9-9 4.2-12.3L135.2 67.2c3.2-3.2 7.8-4.7 12.3-4.2l20.3 2.3c4.5.5 9-.9 12.3-4.2l1.6-1.6c1.5-1.5 3.8-1.8 5.6-.8l1.5 1c1.8 1.1 2.4 3.4 1.4 5.3l-1.6 1.6c-3.2 3.2-4.7 7.8-4.2 12.3z" />
              </svg>
            </div>
            <div>
              <div className="flex items-center gap-2">
                <span className="text-[10px] bg-red-600/20 text-[#ff2d20] font-bold px-2 py-0.5 rounded-full border border-red-500/20 font-mono">
                  LARAVEL 11 & REACT
                </span>
                <span className="text-slate-500 text-xs font-semibold">•</span>
                <span className="text-slate-400 text-xs font-semibold">Ready to Integrate</span>
              </div>
              <h1 className="text-lg font-extrabold tracking-tight text-white flex items-center gap-1.5 mt-0.5">
                Laravel Admin <span className="text-red-500">Container Packing Optimizer</span>
              </h1>
            </div>
          </div>
          
          {/* Top Tabs */}
          <div className="flex items-center gap-2.5">
            <div className="bg-slate-900/90 border border-slate-800 p-1 rounded-lg flex">
              <button
                onClick={() => setActiveTab("simulator")}
                className={`flex items-center gap-1.5 px-4 py-1.5 rounded-md text-xs font-bold transition-all ${
                  activeTab === "simulator"
                    ? "bg-[#ff2d20] text-white shadow-md"
                    : "text-slate-400 hover:text-white"
                }`}
              >
                <Truck className="w-3.5 h-3.5" />
                <span>Packing Simulator</span>
              </button>
              <button
                onClick={() => setActiveTab("laravel")}
                className={`flex items-center gap-1.5 px-4 py-1.5 rounded-md text-xs font-bold transition-all ${
                  activeTab === "laravel"
                    ? "bg-[#ff2d20] text-white shadow-md"
                    : "text-slate-400 hover:text-white"
                }`}
              >
                <Code className="w-3.5 h-3.5" />
                <span>Laravel Code</span>
              </button>
            </div>

            <button
              onClick={handleResetCatalog}
              className="px-3.5 py-1.5 rounded-lg border border-slate-800 hover:border-slate-700 bg-slate-900 text-slate-300 hover:text-white text-xs font-semibold transition-colors"
              id="btn_reset_catalog"
            >
              Reset Catalog
            </button>
          </div>
        </div>
      </header>

      {activeTab === "laravel" ? (
        /* LARAVEL INTEGRATION GUIDES PANEL */
        <div className="max-w-7xl mx-auto px-4 py-8 animate-fade-in">
          <div className="bg-white rounded-2xl shadow-xl border border-slate-200 p-6 md:p-8 flex flex-col gap-6">
            <div className="border-b border-slate-200 pb-4">
              <div className="flex items-center gap-2 text-red-600 mb-1">
                <Code className="w-5 h-5" />
                <span className="text-xs uppercase tracking-wider font-extrabold font-mono">Panduan Integrasi Laravel v11</span>
              </div>
              <h2 className="text-2xl font-black text-slate-900 tracking-tight">
                Integrasi 3D Simulator ke Project Laravel Anda
              </h2>
              <p className="text-slate-500 text-sm mt-1">
                Ikuti langkah-langkah mudah di bawah ini untuk merender visualizer Three.js container loading optimizer di blade template Laravel.
              </p>
            </div>

            {/* Laravel Route */}
            <div className="flex flex-col gap-2">
              <div className="flex items-center justify-between">
                <span className="text-xs font-bold text-slate-700 bg-slate-100 px-2.5 py-1 rounded">Langkah 1: Definisikan Web Route</span>
                <span className="text-xs text-slate-400 font-mono font-medium">routes/web.php</span>
              </div>
              <pre className="bg-slate-950 text-slate-300 p-4 rounded-xl text-xs font-mono overflow-x-auto border border-slate-800 leading-relaxed">
{`use App\\Http\\Controllers\\ContainerLoadingController;

Route::get('/container-packing', [ContainerLoadingController::class, 'index'])->name('container.packing');
Route::post('/container-packing/calculate', [ContainerLoadingController::class, 'calculate'])->name('container.packing.calculate');`}
              </pre>
            </div>

            {/* Laravel Controller */}
            <div className="flex flex-col gap-2">
              <div className="flex items-center justify-between">
                <span className="text-xs font-bold text-slate-700 bg-slate-100 px-2.5 py-1 rounded">Langkah 2: Buat Controller</span>
                <span className="text-xs text-slate-400 font-mono font-medium">app/Http/Controllers/ContainerLoadingController.php</span>
              </div>
              <pre className="bg-slate-950 text-slate-300 p-4 rounded-xl text-xs font-mono overflow-x-auto border border-slate-800 leading-relaxed max-h-[350px]">
{`<?php

namespace App\\Http\\Controllers;

use Illuminate\\Http\\Request;

class ContainerLoadingController extends Controller
{
    public function index()
    {
        // Data furniture bawaan atau diambil dari database Eloquent
        $items = [
            ['id' => 'bilbao', 'name' => 'Mirror Bilbao', 'length' => 129, 'width' => 101, 'height' => 16, 'qty' => 30, 'color' => '#ff2d20'],
            ['id' => 'boudoir', 'name' => 'Headboard Boudoir', 'length' => 252, 'width' => 12, 'height' => 152, 'qty' => 10, 'color' => '#eab308'],
            ['id' => 'ewan', 'name' => 'Dining Chair Ewan', 'length' => 68, 'width' => 63, 'height' => 83, 'qty' => 72, 'color' => '#10b981'],
            ['id' => 'tiago', 'name' => 'Dining Table Tiago', 'length' => 83, 'width' => 83, 'height' => 82, 'qty' => 32, 'color' => '#3b82f6'],
        ];

        return view('container.simulator', compact('items'));
    }

    public function calculate(Request $request)
    {
        $items = $request->input('items', []);
        $containerSize = $request->input('container_size', '40ft_hc');

        // Panggil helper/algorithm packing solver PHP
        // Anda dapat menulis ulang packingSolver.ts menjadi service class di Laravel
        return response()->json([
            'status' => 'success',
            'container_size' => $containerSize,
            'packed_boxes' => [], // koordinat penempatan XYZ
            'unpacked_boxes' => [],
        ]);
    }
}`}
              </pre>
            </div>

            {/* Laravel Blade View */}
            <div className="flex flex-col gap-2">
              <div className="flex items-center justify-between">
                <span className="text-xs font-bold text-slate-700 bg-slate-100 px-2.5 py-1 rounded">Langkah 3: Blade View dengan Three.js</span>
                <span className="text-xs text-slate-400 font-mono font-medium">resources/views/container/simulator.blade.php</span>
              </div>
              <pre className="bg-slate-950 text-slate-300 p-4 rounded-xl text-xs font-mono overflow-x-auto border border-slate-800 leading-relaxed max-h-[300px]">
{`@extends('layouts.app')

@section('content')
<div className="container-fluid py-4 bg-slate-50">
    <div className="row">
        <div className="col-lg-8">
            <div className="card shadow-sm">
                <div className="card-header bg-slate-900 text-white d-flex justify-content-between align-items-center">
                    <h5 className="mb-0">3D Interactive Container Packing</h5>
                    <span className="badge bg-danger">Laravel Integrated</span>
                </div>
                <div className="card-body">
                    <!-- Target element untuk canvas Three.js -->
                    <div id="container-3d-canvas" style="height: 500px; background: #f8fafc;"></div>
                </div>
            </div>
        </div>
        <div className="col-lg-4">
            <div className="card shadow-sm mb-4">
                <div className="card-header">
                    <h6 className="mb-0">Daftar Item Cargo</h6>
                </div>
                <div className="card-body">
                    <ul className="list-group">
                        @foreach($items as $item)
                        <li className="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong>{{ $item['name'] }}</strong>
                                <small className="text-muted d-block">{{ $item['length'] }}x{{ $item['width'] }}x{{ $item['height'] }} cm</small>
                            </div>
                            <span className="badge bg-primary rounded-pill">{{ $item['qty'] }} Pcs</span>
                        </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Sertakan library Three.js CDN -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<script>
    // Inisialisasi scene 3D, camera, renderer, dan OrbitControls di Laravel
    const containerDiv = document.getElementById('container-3d-canvas');
    const scene = new THREE.Scene();
    scene.background = new THREE.Color('#f8fafc');
    // ... logic visualisasi dilanjutkan ...
</script>
@endsection`}
              </pre>
            </div>

            <div className="flex gap-4 items-center justify-end border-t border-slate-100 pt-5">
              <button
                onClick={() => setActiveTab("simulator")}
                className="px-6 py-2.5 bg-slate-900 hover:bg-slate-800 text-white font-bold text-xs rounded-lg transition-colors flex items-center gap-1.5 shadow"
              >
                <span>Kembali ke Simulator</span>
                <ArrowRight className="w-4 h-4" />
              </button>
            </div>
          </div>
        </div>
      ) : (
        /* CORE PACKING SIMULATOR PORT */
        <main className="max-w-7xl mx-auto px-4 py-6 md:py-8 flex flex-col gap-6">
          
          {/* STEP 1: CHOOSE CONTAINER PRESET (LANGKAH AWAL) */}
          <div className="bg-white rounded-xl shadow-md border border-slate-200 p-5 flex flex-col gap-4 animate-fade-in">
            <div className="flex flex-col sm:flex-row sm:items-center justify-between border-b border-slate-100 pb-3 gap-2">
              <div>
                <h2 className="font-extrabold text-slate-950 flex items-center gap-2 text-base">
                  <span className="bg-red-500 text-white w-5 h-5 rounded-full flex items-center justify-center text-[11px] font-mono font-bold shadow-sm">1</span>
                  Langkah Awal: Pilih Jenis & Ukuran Container
                </h2>
                <p className="text-xs text-slate-500 leading-snug mt-0.5">
                  Tentukan spesifikasi dimensi container yang akan diisi untuk menampung seluruh item cargo Anda secara presisi.
                </p>
              </div>
              <span className="text-xs bg-red-50 text-red-600 font-bold border border-red-500/20 px-3 py-1 rounded-md self-start">
                Container Terpilih: {currentPreset.name.split(" - ")[0]}
              </span>
            </div>

            {/* Container Cards Presets Grid */}
            <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
              {CONTAINER_PRESETS.map((preset) => {
                const isSelected = preset.id === selectedPresetId;
                const volume = (preset.w * preset.h * preset.d) / 1000000;
                
                return (
                  <button
                    key={preset.id}
                    onClick={() => handleSelectContainerPreset(preset.id)}
                    className={`text-left p-4 rounded-xl border transition-all duration-200 flex flex-col gap-2 relative overflow-hidden group ${
                      isSelected 
                        ? "bg-red-50/40 border-red-500 shadow-md ring-1 ring-red-500/20" 
                        : "bg-white border-slate-200 hover:border-slate-300 hover:bg-slate-50/50 hover:shadow"
                    }`}
                  >
                    {/* Selected badge overlay */}
                    {isSelected && (
                      <div className="absolute top-3 right-3 bg-red-600 text-white p-1 rounded-full shadow-sm">
                        <Check className="w-3 h-3 stroke-[3]" />
                      </div>
                    )}

                    <div className="flex items-center gap-2">
                      <Truck className={`w-5 h-5 ${isSelected ? "text-red-600" : "text-slate-400 group-hover:text-slate-600"}`} />
                      <span className={`font-black text-sm ${isSelected ? "text-red-950" : "text-slate-700"}`}>
                        {preset.id.replace("_", " ").toUpperCase()}
                      </span>
                    </div>

                    <div className="flex flex-col font-mono text-xs text-slate-500">
                      <span className="font-semibold text-slate-700">{preset.name.split(" - ")[1]}</span>
                      <span className="text-[10px] text-slate-400 mt-0.5">Lebar: {preset.w} cm | Tinggi: {preset.h} cm</span>
                      <span className="text-[10px] text-slate-400">Panjang (Depth): {preset.d} cm</span>
                    </div>

                    <div className="border-t border-slate-100 pt-2 mt-1 flex justify-between items-center">
                      <span className="text-[10px] uppercase font-bold text-slate-400 tracking-wider">Kapasitas Maks</span>
                      <span className="font-extrabold text-xs text-slate-700 font-mono bg-slate-100 px-2 py-0.5 rounded">
                        {volume.toFixed(1)} m³
                      </span>
                    </div>
                  </button>
                );
              })}
            </div>
          </div>

          {/* MAIN SIMULATION AREA */}
          <div className="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            
            {/* COLUMN 1: Item Catalog & Add New (cols: 4) */}
            <div className="lg:col-span-4 flex flex-col gap-6" id="panel_catalog">
              
              {/* Step 2 Label Header inside panel */}
              <div className="bg-white rounded-xl shadow-md border border-slate-200 p-5 flex flex-col gap-4">
                <div className="flex justify-between items-center border-b border-slate-100 pb-3">
                  <h2 className="font-extrabold text-slate-950 flex items-center gap-2 text-sm sm:text-base">
                    <span className="bg-red-500 text-white w-5 h-5 rounded-full flex items-center justify-center text-[11px] font-mono font-bold shadow-sm">2</span>
                    Kelola Item Cargo Anda
                  </h2>
                  <span className="text-xs bg-slate-100 text-slate-600 px-2.5 py-0.5 rounded-full font-mono font-semibold">
                    {items.length} Tipe Item
                  </span>
                </div>

                {/* Catalog list */}
                <div className="flex flex-col gap-3 max-h-[320px] overflow-y-auto pr-1">
                  {items.map((item) => {
                    const packedCount = placedBoxes.filter((b) => b.itemId === item.id).length;
                    const isCatalogHovered = highlightedItemId === item.id;
                    
                    return (
                      <div
                        key={item.id}
                        onMouseEnter={() => setHighlightedItemId(item.id)}
                        onMouseLeave={() => setHighlightedItemId(null)}
                        className={`p-3 rounded-xl border transition-all duration-150 ${
                          isCatalogHovered 
                            ? "bg-slate-50 border-red-300 shadow-sm" 
                            : "bg-white border-slate-200"
                        }`}
                      >
                        <div className="flex justify-between items-start mb-2">
                          <div className="flex items-center gap-2">
                            <span className="w-3 h-3 rounded-full border border-black/10" style={{ backgroundColor: item.color }} />
                            <h3 className="font-extrabold text-slate-800 text-xs sm:text-sm">{item.name}</h3>
                          </div>
                          <div className="flex gap-1">
                            <button
                              onClick={() => handleAddSingleCartonManually(item)}
                              className="p-1 hover:bg-slate-100 rounded text-[#ff2d20] hover:text-red-700 transition-colors"
                              title="Masukkan 1 box manual ke kontainer"
                            >
                              <Plus className="w-4 h-4" />
                            </button>
                            <button
                              onClick={() => setEditingItem(item)}
                              className="p-1 hover:bg-slate-100 rounded text-slate-500 hover:text-slate-800 transition-colors text-xs font-bold px-1.5"
                              title="Edit dimensi / qty"
                            >
                              Edit
                            </button>
                            <button
                              onClick={() => handleDeleteItemType(item.id)}
                              className="p-1 hover:bg-red-50 rounded text-red-500 hover:text-red-700 transition-colors"
                              title="Hapus tipe item"
                            >
                              <Trash2 className="w-3.5 h-3.5" />
                            </button>
                          </div>
                        </div>

                        {/* Dimensions and Quantity */}
                        <div className="grid grid-cols-3 gap-2 text-[11px] font-mono text-slate-500 mb-2 bg-slate-50/70 p-2 rounded-lg">
                          <div>
                            <span className="block text-[9px] uppercase tracking-wider text-slate-400">P × L × T</span>
                            <span className="text-slate-800 font-bold">{item.length}×{item.width}×{item.height}</span> <span className="text-[9px]">cm</span>
                          </div>
                          <div>
                            <span className="block text-[9px] uppercase tracking-wider text-slate-400">Total Qty</span>
                            <span className="text-slate-800 font-bold">{item.qty}</span> <span className="text-[9px]">pcs</span>
                          </div>
                          <div>
                            <span className="block text-[9px] uppercase tracking-wider text-slate-400">Tersusun</span>
                            <span className={`font-black ${packedCount === item.qty ? 'text-emerald-600' : packedCount > 0 ? 'text-red-600' : 'text-slate-500'}`}>
                              {packedCount}/{item.qty}
                            </span>
                          </div>
                        </div>

                        {/* Item progress bar */}
                        <div className="w-full bg-slate-100 rounded-full h-1 overflow-hidden">
                          <div 
                            className="h-full bg-red-500 transition-all duration-300" 
                            style={{ width: `${Math.min(100, (packedCount / item.qty) * 100)}%` }}
                          />
                        </div>
                      </div>
                    );
                  })}
                  {items.length === 0 && (
                    <div className="text-center py-8 text-slate-400 text-xs">
                      Katalog kosong. Silakan tambahkan item baru di bawah.
                    </div>
                  )}
                </div>
              </div>

              {/* Add Item Form or Edit Form Card */}
              {editingItem ? (
                <div className="bg-amber-50/50 rounded-xl shadow-md border border-amber-200 p-5 flex flex-col gap-3">
                  <h3 className="font-bold text-amber-950 text-sm flex items-center gap-1.5 border-b border-amber-200/50 pb-2">
                    <Sliders className="w-4 h-4" />
                    Edit Item: {editingItem.name}
                  </h3>
                  <form onSubmit={handleSaveItemEdit} className="flex flex-col gap-3">
                    <div className="flex flex-col gap-1">
                      <label className="text-[11px] font-bold text-amber-800">Nama Item</label>
                      <input
                        type="text"
                        required
                        value={editingItem.name}
                        onChange={(e) => setEditingItem({ ...editingItem, name: e.target.value })}
                        className="w-full px-3 py-1.5 rounded bg-white border border-amber-200 text-xs font-semibold focus:outline-none focus:ring-1 focus:ring-amber-500"
                      />
                    </div>
                    <div className="grid grid-cols-3 gap-2">
                      <div className="flex flex-col gap-1">
                        <label className="text-[11px] font-bold text-amber-800">Panjang (cm)</label>
                        <input
                          type="number"
                          required
                          min="1"
                          value={editingItem.length}
                          onChange={(e) => setEditingItem({ ...editingItem, length: parseInt(e.target.value) || 0 })}
                          className="w-full px-2 py-1.5 rounded bg-white border border-amber-200 text-xs font-mono font-bold focus:outline-none"
                        />
                      </div>
                      <div className="flex flex-col gap-1">
                        <label className="text-[11px] font-bold text-amber-800">Lebar (cm)</label>
                        <input
                          type="number"
                          required
                          min="1"
                          value={editingItem.width}
                          onChange={(e) => setEditingItem({ ...editingItem, width: parseInt(e.target.value) || 0 })}
                          className="w-full px-2 py-1.5 rounded bg-white border border-amber-200 text-xs font-mono font-bold focus:outline-none"
                        />
                      </div>
                      <div className="flex flex-col gap-1">
                        <label className="text-[11px] font-bold text-amber-800">Tinggi (cm)</label>
                        <input
                          type="number"
                          required
                          min="1"
                          value={editingItem.height}
                          onChange={(e) => setEditingItem({ ...editingItem, height: parseInt(e.target.value) || 0 })}
                          className="w-full px-2 py-1.5 rounded bg-white border border-amber-200 text-xs font-mono font-bold focus:outline-none"
                        />
                      </div>
                    </div>
                    <div className="grid grid-cols-2 gap-2">
                      <div className="flex flex-col gap-1">
                        <label className="text-[11px] font-bold text-amber-800">Qty (Jumlah)</label>
                        <input
                          type="number"
                          required
                          min="1"
                          value={editingItem.qty}
                          onChange={(e) => setEditingItem({ ...editingItem, qty: parseInt(e.target.value) || 0 })}
                          className="w-full px-2 py-1.5 rounded bg-white border border-amber-200 text-xs font-mono font-bold focus:outline-none"
                        />
                      </div>
                      <div className="flex flex-col gap-1">
                        <label className="text-[11px] font-bold text-amber-800">Hex Warna</label>
                        <div className="flex gap-1.5">
                          <input
                            type="color"
                            value={editingItem.color}
                            onChange={(e) => setEditingItem({ ...editingItem, color: e.target.value })}
                            className="w-8 h-7 border-0 p-0 rounded cursor-pointer bg-transparent"
                          />
                          <input
                            type="text"
                            required
                            value={editingItem.color}
                            onChange={(e) => setEditingItem({ ...editingItem, color: e.target.value })}
                            className="w-full px-2 py-1 rounded bg-white border border-amber-200 text-xs font-mono focus:outline-none"
                          />
                        </div>
                      </div>
                    </div>
                    <div className="flex gap-2 justify-end mt-2">
                      <button
                        type="button"
                        onClick={() => setEditingItem(null)}
                        className="px-3 py-1.5 bg-slate-200 hover:bg-slate-300 rounded text-slate-700 text-xs font-bold"
                      >
                        Batal
                      </button>
                      <button
                        type="submit"
                        className="px-4 py-1.5 bg-amber-600 hover:bg-amber-700 rounded text-white text-xs font-bold shadow-sm"
                      >
                        Simpan
                      </button>
                    </div>
                  </form>
                </div>
              ) : (
                <div className="bg-white rounded-xl shadow-md border border-slate-200 p-5 flex flex-col gap-3">
                  <h3 className="font-extrabold text-slate-950 text-sm border-b border-slate-100 pb-2 flex items-center gap-1.5">
                    <Plus className="w-4 h-4 text-[#ff2d20]" />
                    Tambah Tipe Item Baru
                  </h3>
                  <form onSubmit={handleAddNewItem} className="flex flex-col gap-3.5">
                    <div className="flex flex-col gap-1">
                      <label className="text-[11px] font-bold text-slate-500">Nama Item</label>
                      <input
                        type="text"
                        required
                        placeholder="Contoh: Meja Kerja Kayu Jati"
                        value={newItemName}
                        onChange={(e) => setNewItemName(e.target.value)}
                        className="w-full px-3 py-2 rounded-lg border border-slate-200 text-xs focus:outline-none focus:ring-1 focus:ring-red-500 font-bold"
                      />
                    </div>
                    
                    <div className="grid grid-cols-3 gap-2">
                      <div className="flex flex-col gap-1">
                        <label className="text-[11px] font-bold text-slate-500" title="Panjang (cm)">Panjang</label>
                        <input
                          type="number"
                          required
                          min="1"
                          value={newItemLength}
                          onChange={(e) => setNewItemLength(parseInt(e.target.value) || 0)}
                          className="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 text-xs font-mono font-bold focus:outline-none focus:ring-1 focus:ring-red-500"
                        />
                      </div>
                      <div className="flex flex-col gap-1">
                        <label className="text-[11px] font-bold text-slate-500" title="Lebar (cm)">Lebar</label>
                        <input
                          type="number"
                          required
                          min="1"
                          value={newItemWidth}
                          onChange={(e) => setNewItemWidth(parseInt(e.target.value) || 0)}
                          className="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 text-xs font-mono font-bold focus:outline-none focus:ring-1 focus:ring-red-500"
                        />
                      </div>
                      <div className="flex flex-col gap-1">
                        <label className="text-[11px] font-bold text-slate-500" title="Tinggi (cm)">Tinggi</label>
                        <input
                          type="number"
                          required
                          min="1"
                          value={newItemHeight}
                          onChange={(e) => setNewItemHeight(parseInt(e.target.value) || 0)}
                          className="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 text-xs font-mono font-bold focus:outline-none focus:ring-1 focus:ring-red-500"
                        />
                      </div>
                    </div>

                    <div className="grid grid-cols-2 gap-2">
                      <div className="flex flex-col gap-1">
                        <label className="text-[11px] font-bold text-slate-500">Qty (Pcs)</label>
                        <input
                          type="number"
                          required
                          min="1"
                          value={newItemQty}
                          onChange={(e) => setNewItemQty(parseInt(e.target.value) || 0)}
                          className="w-full px-2.5 py-1.5 rounded-lg border border-slate-200 text-xs font-mono font-bold focus:outline-none focus:ring-1 focus:ring-red-500"
                        />
                      </div>
                      <div className="flex flex-col gap-1">
                        <label className="text-[11px] font-bold text-slate-500">Warna</label>
                        <div className="flex gap-1.5">
                          <input
                            type="color"
                            value={newItemColor}
                            onChange={(e) => setNewItemColor(e.target.value)}
                            className="w-8 h-8 p-0 border-0 rounded cursor-pointer bg-transparent"
                          />
                          <input
                            type="text"
                            required
                            value={newItemColor}
                            onChange={(e) => setNewItemColor(e.target.value)}
                            className="w-full px-2 py-1.5 rounded-lg border border-slate-200 text-xs font-mono focus:outline-none focus:ring-1 focus:ring-red-500"
                          />
                        </div>
                      </div>
                    </div>

                    <button
                      type="submit"
                      className="w-full py-2 bg-slate-950 hover:bg-slate-800 text-white text-xs font-bold rounded-lg shadow-sm hover:scale-[1.01] active:scale-[0.99] transition-all"
                    >
                      Tambahkan ke Katalog
                    </button>
                  </form>
                </div>
              )}
            </div>

            {/* COLUMN 2: 3D Viewport with Step 3 & Auto Solver (cols: 5) */}
            <div className="lg:col-span-5 flex flex-col gap-4">
              
              <div className="bg-white px-4 py-3 rounded-xl border border-slate-200 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <div className="flex items-center gap-2">
                  <span className="bg-red-500 text-white w-5 h-5 rounded-full flex items-center justify-center text-[11px] font-mono font-bold shadow-sm">3</span>
                  <h2 className="font-extrabold text-slate-950 text-sm sm:text-base">
                    Simulasi 3D & Auto Loading
                  </h2>
                </div>

                <div className="flex items-center gap-2">
                  <button
                    onClick={() => setShowLabels(!showLabels)}
                    className="p-1.5 text-slate-400 hover:text-slate-700 hover:bg-slate-100 rounded flex items-center gap-1 text-xs font-bold"
                    title="Tampilkan/sembunyikan label box"
                  >
                    {showLabels ? <Eye className="w-4 h-4 text-red-600" /> : <EyeOff className="w-4 h-4" />}
                    <span>{showLabels ? "Label On" : "Label Off"}</span>
                  </button>
                </div>
              </div>

              {/* Render the 3D WebGL Three.js Component */}
              <div className="h-[460px] md:h-[500px] w-full relative">
                {fallingAlert && (
                  <div className="absolute top-16 left-1/2 -translate-x-1/2 z-30 bg-red-600/95 backdrop-blur text-white px-6 py-3 rounded-full shadow-2xl flex items-center gap-2 border border-red-500 animate-bounce pointer-events-none text-center">
                    <span className="text-xs sm:text-sm font-black uppercase tracking-widest">{fallingAlert}</span>
                  </div>
                )}

                <ContainerViewer
                  placedBoxes={placedBoxes}
                  onMoveBox={handleMoveBox}
                  onSelectBox={setSelectedBoxId}
                  onDeleteBox={handleDeleteCarton}
                  onRotateBox={handleRotateSelectedBox}
                  selectedBoxId={selectedBoxId}
                  requireSupport={requireSupport}
                  showLabels={showLabels}
                  highlightedItemId={highlightedItemId}
                  containerW={currentPreset.w}
                  containerH={currentPreset.h}
                  containerD={currentPreset.d}
                />

                {/* HUD Overlay for Selected Carton */}
                {selectedBox && (
                  <div className="absolute top-4 left-4 z-10 bg-slate-900/95 backdrop-blur border border-slate-800 rounded-xl p-3.5 shadow-2xl text-white w-[260px] sm:w-[280px] animate-fade-in flex flex-col gap-2">
                    <div className="flex items-center justify-between border-b border-slate-800 pb-2">
                      <div className="flex items-center gap-1.5 min-w-0">
                        <span className="w-2.5 h-2.5 rounded-full shrink-0 animate-pulse" style={{ backgroundColor: selectedBox.color }} />
                        <span className="font-extrabold text-[10px] uppercase tracking-wider text-slate-300 truncate">Aksi Box Terpilih</span>
                      </div>
                      <button 
                        type="button"
                        onClick={() => setSelectedBoxId(null)}
                        className="text-[9px] text-slate-400 hover:text-white font-bold bg-slate-800 hover:bg-slate-700 px-1.5 py-0.5 rounded transition-colors"
                      >
                        Batal
                      </button>
                    </div>
                    
                    <div className="text-[11px] font-black text-white truncate bg-slate-950 px-2 py-1.5 rounded border border-slate-800">
                      {selectedBox.name}
                    </div>

                    <div className="grid grid-cols-2 gap-1.5">
                      {/* ROTATE */}
                      <button
                        type="button"
                        onClick={handleRotateSelectedBox}
                        className="flex items-center justify-center gap-1 py-1.5 px-2 bg-red-600 hover:bg-red-700 text-white text-[10px] font-black rounded-lg transition-colors shadow-md group"
                        title="Putar Orientasi Box"
                      >
                        <Rotate3d className="w-3.5 h-3.5 group-hover:rotate-45 transition-transform" />
                        <span>PUTAR (ROTATE)</span>
                      </button>

                      {/* HAPUS */}
                      <button
                        type="button"
                        onClick={() => handleDeleteCarton(selectedBox.id)}
                        className="flex items-center justify-center gap-1 py-1.5 px-2 bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white text-[10px] font-black rounded-lg transition-colors border border-slate-700"
                        title="Hapus dari Container"
                      >
                        <Trash2 className="w-3.5 h-3.5" />
                        <span>HAPUS</span>
                      </button>
                    </div>

                    <div className="flex flex-col gap-1.5 mt-1 border-t border-slate-800/80 pt-2">
                      <span className="text-[9px] uppercase font-bold tracking-wider text-slate-400">Tambah Box Di Samping:</span>
                      <div className="grid grid-cols-3 gap-1">
                        <button
                          type="button"
                          onClick={() => handleAddBoxInDirection("kiri")}
                          className="bg-slate-800 hover:bg-slate-700 hover:border-red-500 border border-slate-700 text-white py-1 px-1 rounded text-[10px] font-black flex flex-col items-center gap-0.5 transition-all shadow-sm"
                          title="Tambah box sejenis di sebelah kiri (-X)"
                        >
                          <span className="text-slate-400 text-[8px]">← Kiri</span>
                        </button>
                        <button
                          type="button"
                          onClick={() => handleAddBoxInDirection("atas")}
                          className="bg-slate-800 hover:bg-slate-700 hover:border-red-500 border border-slate-700 text-white py-1 px-1 rounded text-[10px] font-black flex flex-col items-center gap-0.5 transition-all shadow-sm"
                          title="Tambah box sejenis di sebelah atas (+Y)"
                        >
                          <span className="text-slate-400 text-[8px]">↑ Atas</span>
                        </button>
                        <button
                          type="button"
                          onClick={() => handleAddBoxInDirection("kanan")}
                          className="bg-slate-800 hover:bg-slate-700 hover:border-red-500 border border-slate-700 text-white py-1 px-1 rounded text-[10px] font-black flex flex-col items-center gap-0.5 transition-all shadow-sm"
                          title="Tambah box sejenis di sebelah kanan (+X)"
                        >
                          <span className="text-slate-400 text-[8px]">Kanan →</span>
                        </button>
                      </div>
                    </div>
                  </div>
                )}

                {/* Simulated Loading Overlay */}
                {isPacking && (
                  <div className="absolute inset-0 bg-slate-900/80 backdrop-blur-sm z-20 rounded-xl flex flex-col items-center justify-center gap-4 text-white animate-fade-in">
                    <div className="relative">
                      <div className="w-12 h-12 border-4 border-red-500/20 border-t-red-500 rounded-full animate-spin" />
                      <Sparkles className="w-5 h-5 text-red-400 absolute inset-0 m-auto animate-pulse" />
                    </div>
                    <div className="flex flex-col items-center text-center gap-1">
                      <h4 className="font-black text-sm tracking-wide">MEMASANG CARGO DENGAN PRESISI...</h4>
                      <p className="text-[11px] text-red-200 font-mono tracking-tight animate-pulse max-w-xs">
                        {packingStepText || "Mengotomatisasi plot hemat tempat..."}
                      </p>
                    </div>
                  </div>
                )}
              </div>
              
              {/* Quick Stats Grid under viewport */}
              <div className="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div className="bg-white rounded-xl p-3 shadow-sm border border-slate-200 text-center">
                  <span className="block text-[10px] uppercase tracking-wider font-extrabold text-slate-400">Total Terisi</span>
                  <span className="text-base font-black text-slate-800 font-mono">{totalCartonsPacked} / {totalCartonsInCatalog}</span>
                  <span className="block text-[9px] text-slate-400 font-semibold">Box Carton</span>
                </div>
                
                <div className="bg-white rounded-xl p-3 shadow-sm border border-slate-200 text-center">
                  <span className="block text-[10px] uppercase tracking-wider font-extrabold text-slate-400">Volume Terisi</span>
                  <span className="text-base font-black text-red-600 font-mono">{totalVolumePacked.toFixed(2)} m³</span>
                  <span className="block text-[9px] text-slate-400 font-semibold">dari {containerVolM3.toFixed(2)} m³</span>
                </div>

                <div className="bg-white rounded-xl p-3 shadow-sm border border-slate-200 text-center">
                  <span className="block text-[10px] uppercase tracking-wider font-extrabold text-slate-400">Utilisasi Ruang</span>
                  <span className={`text-base font-black font-mono ${utilizationPercent > 80 ? 'text-emerald-600' : utilizationPercent > 50 ? 'text-red-600' : 'text-slate-600'}`}>
                    {utilizationPercent.toFixed(1)}%
                  </span>
                  <span className="block text-[9px] text-slate-400 font-semibold">Tingkat Hemat</span>
                </div>

                <div className="bg-white rounded-xl p-3 shadow-sm border border-slate-200 text-center">
                  <span className="block text-[10px] uppercase tracking-wider font-extrabold text-slate-400">Sisa Ruang</span>
                  <span className="text-base font-black text-slate-600 font-mono">{remainingVolume.toFixed(2)} m³</span>
                  <span className="block text-[9px] text-slate-400 font-semibold">Sisa Kosong</span>
                </div>
              </div>
            </div>

            {/* COLUMN 3: Auto Loading Triggers, Manual Rotation & Unpacked reports (cols: 3) */}
            <div className="lg:col-span-3 flex flex-col gap-6" id="panel_controls_stats">
              
              {/* STEP 3 Trigger Card */}
              <div className="bg-slate-900 text-white rounded-xl shadow-md border border-slate-800 p-5 flex flex-col gap-4">
                <div className="flex flex-col gap-1">
                  <h3 className="font-extrabold text-sm flex items-center gap-1.5 text-white">
                    <Sparkles className="w-4 h-4 text-red-500" />
                    Plot Otomatis Presisi
                  </h3>
                  <p className="text-[10px] text-slate-400 leading-tight">
                    Menggunakan algoritma Tetris Corner-Fitting untuk menyusun cargo secara padat, hemat ruang, dan aman.
                  </p>
                </div>

                <button
                  onClick={handleGenerateLoadingPlan}
                  disabled={isPacking}
                  className="w-full flex items-center justify-center gap-2 px-5 py-3 rounded-lg text-xs font-black transition-all duration-200 shadow-md bg-gradient-to-r from-red-600 to-orange-600 hover:from-red-500 hover:to-orange-500 text-white hover:scale-[1.02] active:scale-[0.98]"
                  id="btn_generate_loading_large"
                >
                  <Play className="w-3.5 h-3.5 stroke-[3]" />
                  <span>AUTO GENERATE LOADING</span>
                </button>

                <div className="grid grid-cols-3 gap-2">
                  <button
                    onClick={handleUndo}
                    disabled={undoStack.length === 0}
                    className="flex items-center justify-center gap-1.5 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 disabled:opacity-30 disabled:hover:bg-slate-800 disabled:text-slate-500 text-slate-300 hover:text-white text-xs font-bold transition-all border border-slate-800"
                    id="btn_undo"
                    title="Undo (Ctrl+Z)"
                  >
                    <Undo className="w-3.5 h-3.5" />
                    <span>Undo</span>
                  </button>

                  <button
                    onClick={handleRedo}
                    disabled={redoStack.length === 0}
                    className="flex items-center justify-center gap-1.5 py-2 rounded-lg bg-slate-800 hover:bg-slate-700 disabled:opacity-30 disabled:hover:bg-slate-800 disabled:text-slate-500 text-slate-300 hover:text-white text-xs font-bold transition-all border border-slate-800"
                    id="btn_redo"
                    title="Redo (Ctrl+Y)"
                  >
                    <Redo className="w-3.5 h-3.5" />
                    <span>Redo</span>
                  </button>

                  <button
                    onClick={handleClearContainer}
                    className="flex items-center justify-center gap-1.5 py-2 rounded-lg bg-red-950/30 hover:bg-red-950/60 border border-red-900/40 text-red-400 hover:text-red-200 text-xs font-bold transition-all"
                    id="btn_clear_quick"
                    title="Kosongkan kontainer"
                  >
                    <RotateCcw className="w-3.5 h-3.5" />
                    <span>Clear</span>
                  </button>
                </div>
                <p className="text-[10px] text-slate-400 text-center select-none font-medium -mt-1 leading-normal">
                  Tips: Gunakan shortcut <kbd className="bg-slate-850 text-slate-300 px-1 py-0.5 rounded font-mono text-[9px] border border-slate-800">Ctrl + Z</kbd> (Undo) & <kbd className="bg-slate-850 text-slate-300 px-1 py-0.5 rounded font-mono text-[9px] border border-slate-800">Ctrl + Y</kbd> (Redo)
                </p>
              </div>

              {/* Packing Parameter Settings Card */}
              <div className="bg-white rounded-xl shadow-md border border-slate-200 p-5 flex flex-col gap-4">
                <h3 className="font-bold text-slate-950 border-b border-slate-100 pb-2 text-xs sm:text-sm flex items-center gap-1.5">
                  <Settings className="w-4 h-4 text-slate-500" />
                  Parameter Optimasi
                </h3>

                <div className="flex flex-col gap-3 text-xs">
                  {/* Gravity Stability Support */}
                  <div className="flex flex-col gap-1">
                    <span className="font-bold text-slate-700">Aturan Tumpukan Gravitasi</span>
                    <label className="flex items-center gap-2 cursor-pointer select-none bg-slate-50 p-2.5 rounded-lg border border-slate-200">
                      <input
                        type="checkbox"
                        checked={requireSupport}
                        onChange={(e) => setRequireSupport(e.target.checked)}
                        className="rounded border-slate-300 text-red-600 focus:ring-red-500"
                      />
                      <div>
                        <span className="font-bold text-slate-800 block text-[11px]">Aktifkan Gravitasi</span>
                        <p className="text-[9px] text-slate-400 leading-tight">Mencegah box melayang di udara</p>
                      </div>
                    </label>
                  </div>

                  {/* Sorting Heuristics */}
                  <div className="flex flex-col gap-1">
                    <span className="font-bold text-slate-700">Heuristik Pengurutan</span>
                    <select
                      value={sortingStrategy}
                      onChange={(e: any) => setSortingStrategy(e.target.value)}
                      className="w-full p-2 bg-white rounded-lg border border-slate-200 text-xs font-bold focus:outline-none focus:ring-1 focus:ring-red-500"
                    >
                      <option value="volume">Volume Terbesar Dahulu</option>
                      <option value="length">Dimensi Terpanjang Dahulu</option>
                      <option value="qty">Jumlah (Qty) Terbanyak Dahulu</option>
                      <option value="none">Sesuai Input (Tanpa Urutan)</option>
                    </select>
                  </div>

                  {/* Speed controls */}
                  <div className="flex flex-col gap-1">
                    <span className="font-bold text-slate-700">Gaya Simulasi</span>
                    <div className="grid grid-cols-2 gap-1.5 bg-slate-50 p-1 rounded-lg border border-slate-200">
                      <button
                        type="button"
                        onClick={() => setPackingSpeed("animated")}
                        className={`py-1 rounded-md text-[10px] font-black transition-all ${packingSpeed === "animated" ? 'bg-white shadow text-red-600' : 'text-slate-500'}`}
                      >
                        Animasi Box
                      </button>
                      <button
                        type="button"
                        onClick={() => setPackingSpeed("instant")}
                        className={`py-1 rounded-md text-[10px] font-black transition-all ${packingSpeed === "instant" ? 'bg-white shadow text-red-600' : 'text-slate-500'}`}
                      >
                        Instan (Cepat)
                      </button>
                    </div>
                  </div>
                </div>
              </div>

              {/* MANUAL ROTATION & SELECTION DETAILS CARD */}
              {selectedBox ? (
                <div className="bg-red-50/60 rounded-xl shadow-md border border-red-200 p-5 flex flex-col gap-3.5 animate-fade-in">
                  <div className="flex justify-between items-center border-b border-red-200/50 pb-2">
                    <h3 className="font-extrabold text-red-950 text-xs flex items-center gap-1.5">
                      <span className="w-2.5 h-2.5 rounded-full" style={{ backgroundColor: selectedBox.color }} />
                      Karton Terpilih
                    </h3>
                    <button
                      onClick={() => setSelectedBoxId(null)}
                      className="text-xs text-red-600 hover:text-red-800 font-bold"
                    >
                      Tutup
                    </button>
                  </div>

                  <div className="flex flex-col gap-1.5 text-xs text-slate-800 font-medium">
                    <div className="text-xs sm:text-sm font-black text-slate-900">{selectedBox.name}</div>
                    <div><span className="text-slate-500 font-bold">ID Box:</span> <span className="font-mono text-[10px] bg-white px-1.5 py-0.5 rounded border border-slate-200">{selectedBox.id}</span></div>
                    <div><span className="text-slate-500 font-bold">Dimensi Asli:</span> {selectedBox.p} × {selectedBox.l} × {selectedBox.t} cm</div>
                    <div><span className="text-slate-500 font-bold">Dimensi Placed:</span> {selectedBox.d} × {selectedBox.w} × {selectedBox.h} cm</div>
                    
                    {/* ROTATE ACTION BUTTON ("items nya bisa dirotate") */}
                    <div className="border-t border-red-200/50 pt-2.5 mt-1">
                      <span className="block text-[10px] uppercase tracking-wider font-extrabold text-red-800 mb-1.5">
                        Rotasi & Posisi 3D
                      </span>
                      <button
                        onClick={handleRotateSelectedBox}
                        className="w-full flex items-center justify-center gap-2 py-2 px-3 bg-red-600 hover:bg-red-700 text-white text-xs font-black rounded-lg transition-all shadow-sm group"
                        title="Putar orientasi box di ruang container"
                      >
                        <Rotate3d className="w-4 h-4 group-hover:rotate-45 transition-transform duration-200" />
                        <span>PUTAR BOX (ROTATE)</span>
                      </button>
                      <p className="text-[9px] text-red-600/80 leading-tight mt-1">
                        *Klik tombol untuk menukar orientasi dimensi (Panjang, Lebar, Tinggi) secara aman di koordinat ini.
                      </p>
                    </div>

                    {/* DUP/ADD ACTION BUTTONS IN SIDEBAR */}
                    <div className="border-t border-red-200/50 pt-2.5 mt-1">
                      <span className="block text-[10px] uppercase tracking-wider font-extrabold text-red-800 mb-1.5">
                        Tambah Box Sejenis Di Samping
                      </span>
                      <div className="grid grid-cols-3 gap-2">
                        <button
                          type="button"
                          onClick={() => handleAddBoxInDirection("kiri")}
                          className="flex flex-col items-center justify-center gap-1 py-2 px-1 bg-white border border-red-200 hover:border-red-500 hover:bg-red-50/50 text-slate-850 text-[10px] font-black rounded-lg transition-all shadow-sm"
                          title="Tambah box di sebelah kiri (-X)"
                        >
                          <span className="text-red-600 font-bold">← Kiri</span>
                          <span className="text-[8px] text-slate-400 font-normal">(-X)</span>
                        </button>
                        <button
                          type="button"
                          onClick={() => handleAddBoxInDirection("atas")}
                          className="flex flex-col items-center justify-center gap-1 py-2 px-1 bg-white border border-red-200 hover:border-red-500 hover:bg-red-50/50 text-slate-850 text-[10px] font-black rounded-lg transition-all shadow-sm"
                          title="Tambah box di sebelah atas (+Y)"
                        >
                          <span className="text-red-600 font-bold">↑ Atas</span>
                          <span className="text-[8px] text-slate-400 font-normal">(+Y)</span>
                        </button>
                        <button
                          type="button"
                          onClick={() => handleAddBoxInDirection("kanan")}
                          className="flex flex-col items-center justify-center gap-1 py-2 px-1 bg-white border border-red-200 hover:border-red-500 hover:bg-red-50/50 text-slate-850 text-[10px] font-black rounded-lg transition-all shadow-sm"
                          title="Tambah box di sebelah kanan (+X)"
                        >
                          <span className="text-red-600 font-bold">Kanan →</span>
                          <span className="text-[8px] text-slate-400 font-normal">(+X)</span>
                        </button>
                      </div>
                    </div>

                    <div className="bg-white p-2.5 rounded-lg border border-slate-200 mt-2">
                      <div className="font-bold text-slate-500 text-[9px] uppercase tracking-wider mb-1">Koordinat Penempatan</div>
                      <div className="grid grid-cols-3 gap-1.5 text-center font-mono text-[11px]">
                        <div className="bg-slate-50 py-1 rounded">
                          <span className="block text-[8px] text-slate-400 uppercase">X (Lebar)</span>
                          <span className="font-bold text-slate-800">{selectedBox.x}</span>
                        </div>
                        <div className="bg-slate-50 py-1 rounded">
                          <span className="block text-[8px] text-slate-400 uppercase">Y (Tinggi)</span>
                          <span className="font-bold text-slate-800">{selectedBox.y}</span>
                        </div>
                        <div className="bg-slate-50 py-1 rounded">
                          <span className="block text-[8px] text-slate-400 uppercase">Z (Panjang)</span>
                          <span className="font-bold text-slate-800">{selectedBox.z}</span>
                        </div>
                      </div>
                    </div>
                  </div>

                  <button
                    onClick={() => handleDeleteCarton(selectedBox.id)}
                    className="w-full flex items-center justify-center gap-1.5 py-2 rounded-lg bg-slate-900 hover:bg-slate-800 text-white text-xs font-bold transition-all shadow-sm"
                  >
                    <Trash2 className="w-3.5 h-3.5" />
                    <span>Keluarkan dari Container</span>
                  </button>
                </div>
              ) : (
                <div className="bg-white rounded-xl shadow-md border border-slate-200 p-5 text-center text-slate-400 text-xs flex flex-col items-center justify-center gap-2.5 min-h-[140px]">
                  <Info className="w-6 h-6 text-slate-300" />
                  <p className="font-medium">Klik box di dalam kontainer 3D atau daftar untuk melihat detail rotasi & memindahkan koordinat secara visual.</p>
                </div>
              )}

              {/* Unpacked items card (Error / Full container indication) */}
              {unpackedItems.length > 0 && (
                <div className="bg-red-50 rounded-xl shadow-md border border-red-200 p-5 flex flex-col gap-3">
                  <h3 className="font-bold text-red-800 text-xs sm:text-sm flex items-center gap-1.5 border-b border-red-100 pb-2">
                    <AlertTriangle className="w-4 h-4 text-red-500 animate-pulse" />
                    Item Tidak Muat ({unpackedItems.reduce((sum, u) => sum + u.count, 0)} Pcs)
                  </h3>
                  <p className="text-[11px] text-red-600 leading-snug">
                    Kontainer penuh atau tidak ada tumpukan stabil yang tersisa untuk menampung item berikut:
                  </p>
                  <div className="flex flex-col gap-2 max-h-[160px] overflow-y-auto">
                    {unpackedItems.map((u) => (
                      <div key={u.item.id} className="flex justify-between items-center text-xs bg-white p-2 rounded-lg border border-red-100">
                        <div className="flex items-center gap-1.5">
                          <span className="w-2.5 h-2.5 rounded-full" style={{ backgroundColor: u.item.color }} />
                          <span className="font-bold text-slate-700">{u.item.name}</span>
                        </div>
                        <span className="font-mono bg-red-100 text-red-700 font-extrabold px-2 py-0.5 rounded">
                          {u.count} Pcs
                        </span>
                      </div>
                    ))}
                  </div>
                </div>
              )}

              {unpackedItems.length === 0 && placedBoxes.length > 0 && (
                <div className="bg-emerald-50 rounded-xl shadow-md border border-emerald-200 p-5 flex flex-col gap-2.5 text-center items-center animate-fade-in">
                  <CheckCircle2 className="w-8 h-8 text-emerald-500" />
                  <h3 className="font-black text-emerald-800 text-sm">Semua Item Muat Sempurna!</h3>
                  <p className="text-[11px] text-emerald-600 leading-snug">
                    Seluruh {totalCartonsPacked} carton dari katalog berhasil diatur secara rapat menggunakan algoritma Tetris Corner-Fitting.
                  </p>
                </div>
              )}

            </div>
          </div>
        </main>
      )}

      {/* Modern footer */}
      <footer className="bg-slate-900 border-t border-slate-800 py-8 px-6 mt-12 text-slate-400 text-xs">
        <div className="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-center gap-4">
          <div className="flex flex-col gap-1">
            <span className="font-bold text-white flex items-center gap-1.5">
              <Truck className="w-4 h-4 text-red-500" />
              Laravel Admin Container Loading Simulation
            </span>
            <p className="text-[11px] text-slate-400">
              Ubah orientasi, geser bebas dengan Drag & Drop, pilih preset container ukuran 20ft/40ft/45ft, dan optimalkan tata letak plot Anda secara instan.
            </p>
          </div>
          <div className="text-right text-[10px] font-mono text-slate-500 whitespace-nowrap">
            Powered by Three.js & Laravel Ready API Proxy
          </div>
        </div>
      </footer>
    </div>
  );
}
