export interface ItemType {
  id: string;
  name: string;
  length: number; // in cm (Panjang)
  width: number;  // in cm (Lebar)
  height: number; // in cm (Tinggi)
  qty: number;
  color: string;
}

export interface PlacedBox {
  id: string;
  itemId: string;
  name: string;
  color: string;
  // Placed dimensions (may be rotated)
  w: number; // Lebar (X)
  h: number; // Tinggi (Y)
  d: number; // Panjang (Z)
  // Original dimensions
  p: number; // Panjang
  l: number; // Lebar
  t: number; // Tinggi
  // Position (back-bottom-left corner)
  x: number; // in cm
  y: number; // in cm
  z: number; // in cm
}

export interface CandidateCorner {
  x: number;
  y: number;
  z: number;
}
