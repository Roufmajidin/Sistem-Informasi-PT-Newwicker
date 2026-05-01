<?php

namespace App\Imports;

use App\Models\Bom;
use App\Models\BomGroup;
use App\Models\BomItem;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Row;

class BomImport implements OnEachRow
{
    protected $bom;
    protected $currentGroup = null;
    protected $startReading = false;

    public function onRow(Row $row)
    {
        $rowIndex = $row->getIndex();
        $data = $row->toArray();

        // 🔥 AMBIL METADATA (HEADER ATAS)
        if ($rowIndex == 2) {
            $this->productName = $data[1] ?? null;
        }

        if ($rowIndex == 3) {
            $this->article = $data[1] ?? null;
        }

        if ($rowIndex == 4) {
            $this->orderNo = $data[1] ?? null;
        }

        if ($rowIndex == 5) {
            $this->buyer = $data[1] ?? null;

            // 🔥 BUAT BOM SETELAH HEADER KELAR
            $this->bom = Bom::create([
                'name' => $this->productName,
                'article_number' => $this->article,
                'order_no' => $this->orderNo,
                'buyer' => $this->buyer,
            ]);
        }

        // 🔥 DETEKSI MULAI DATA (baris KOMPONEN)
        if (isset($data[0]) && strtoupper($data[0]) == 'KOMPONEN') {
            $this->startReading = true;
            return;
        }

        if (!$this->startReading) return;

        $name = trim($data[0] ?? '');
        $qty  = $data[1] ?? null;
        $unit = $data[2] ?? null;

        if (!$name) return;

        // 🔥 DETEKSI GROUP
        if (empty($qty) && empty($unit)) {

            $this->currentGroup = BomGroup::create([
                'bom_id' => $this->bom->id,
                'name' => $name
            ]);

            return;
        }

        // 🔥 ITEM
        if ($this->currentGroup) {
            BomItem::create([
                'group_id' => $this->currentGroup->id,
                'name' => $name,
                'qty' => $qty,
                'unit' => $unit,
            ]);
        }
    }
}
