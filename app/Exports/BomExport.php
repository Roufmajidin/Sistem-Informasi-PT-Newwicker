<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
class BomExport implements WithEvents, WithCustomStartCell
{
    protected $bom;

    public function __construct($bom)
    {
        // Mengubah array dari controller menjadi full nested object secara rekursif
        $this->bom = json_decode(json_encode($bom));
    }

    public function startCell(): string
    {
        return 'A1';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $bom = $this->bom;

                // --- Page Setup ---
                $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_PORTRAIT);
                $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4);
                $sheet->getPageSetup()->setFitToPage(true);
                $sheet->getPageSetup()->setFitToWidth(1);
                $sheet->getPageSetup()->setFitToHeight(0);
                $sheet->setShowGridlines(true);

                // --- Column Widths ---
                $widths = ['A' => 28, 'B' => 4, 'C' => 12, 'D' => 12, 'E' => 15, 'F' => 18, 'G' => 8, 'H' => 12];
                foreach ($widths as $col => $width) {
                    $sheet->getColumnDimension($col)->setWidth($width);
                }

                // --- Styles Definition ---
                $fontFamily = 'Arial';
                $boldFont = ['name' => $fontFamily, 'size' => 10, 'bold' => true];
                $regularFont = ['name' => $fontFamily, 'size' => 10];
                $italicFont = ['name' => $fontFamily, 'size' => 10, 'italic' => true];
                $monoFont = ['name' => $fontFamily, 'size' => 9];

                $borderThin = [
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFD0D0D0']]
                    ]
                ];

                $borderCellBox = [
                    'borders' => [
                        'outline' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['argb' => 'FF000000']]
                    ]
                ];

                $alignCenter = ['vertical' => Alignment::VERTICAL_CENTER, 'horizontal' => Alignment::HORIZONTAL_CENTER];
                $alignLeft   = ['vertical' => Alignment::VERTICAL_CENTER, 'horizontal' => Alignment::HORIZONTAL_LEFT];
                $alignRight  = ['vertical' => Alignment::VERTICAL_CENTER, 'horizontal' => Alignment::HORIZONTAL_RIGHT];

                $numFormatCurrency = '#,##0.00';
                $numFormatQty = '#,##0.00';

                // --- ROW 1: HEADER ITEM ---
                $sheet->getRowDimension(1)->setRowHeight(25);
                $sheet->setCellValue('A1', 'ITEM')->getStyle('A1')->getFont()->applyFromArray($boldFont);
                $sheet->setCellValue('B1', ':')->getStyle('B1')->getFont()->applyFromArray($boldFont);
                $sheet->getStyle('B1')->getAlignment()->applyFromArray($alignCenter);

                $sheet->setCellValue('C1', $bom->name);
                $sheet->getStyle('C1')->getFont()->applyFromArray(array_merge($boldFont, ['size' => 12]));
                $sheet->mergeCells('C1:E1');
                $sheet->setCellValue('C2', $bom->article_number);

                $sheet->getStyle('A2')->getFont()->setBold(true);
                $sheet->getStyle('B2')->getFont()->setBold(true);
                $sheet->getStyle('C2')->getFont()->setBold(true);
                // Date Right Side
                $formattedDate = Carbon::parse($bom->date)->isoFormat('dddd, MMMM D, YYYY');
                $sheet->setCellValue('G1', $formattedDate);
                $sheet->getStyle('G1')->getFont()->applyFromArray(['name' => $fontFamily, 'size' => 9, 'bold' => true]);
                $sheet->getStyle('G1')->getAlignment()->applyFromArray($alignRight);
                $sheet->mergeCells('G1:H1');

                // --- ROW 2: DIMENSION ---
                $sheet->getRowDimension(2)->setRowHeight(22);
                $sheet->setCellValue('A2', 'DIMENSION')->getStyle('A2')->getFont()->applyFromArray($boldFont);
                $sheet->setCellValue('B2', ':')->getStyle('B2')->getAlignment()->applyFromArray($alignCenter);
                $sheet->getStyle('B2')->getFont()->applyFromArray($boldFont);

                foreach (['C' => $bom->panjang, 'D' => $bom->lebar, 'E' => $bom->tinggi] as $col => $val) {
                    $sheet->setCellValue($col.'2', $val);
                    $sheet->getStyle($col.'2')->getAlignment()->applyFromArray($alignCenter);
                    $sheet->getStyle($col.'2')->getFont()->applyFromArray($boldFont);
                    $sheet->getStyle($col.'2')->applyFromArray($borderCellBox);
                }
                $sheet->setCellValue('F2', 'cm')->getStyle('F2')->getFont()->applyFromArray($regularFont);
                $sheet->getStyle('F2')->getAlignment()->applyFromArray($alignLeft);

                // --- ROW 3: CARTON SIZE ---
                $sheet->getRowDimension(3)->setRowHeight(22);
                $sheet->setCellValue('A3', 'CARTON SIZE')->getStyle('A3')->getFont()->applyFromArray($boldFont);
                $sheet->setCellValue('B3', ':')->getStyle('B3')->getAlignment()->applyFromArray($alignCenter);
                $sheet->getStyle('B3')->getFont()->applyFromArray($boldFont);

                foreach (['C' => $bom->carton_panjang, 'D' => $bom->carton_lebar, 'E' => $bom->carton_tinggi] as $col => $val) {
                    $sheet->setCellValue($col.'3', $val);
                    $sheet->getStyle($col.'3')->getAlignment()->applyFromArray($alignCenter);
                    $sheet->getStyle($col.'3')->getFont()->applyFromArray($boldFont);
                    $sheet->getStyle($col.'3')->applyFromArray($borderCellBox);
                }
                $sheet->setCellValue('F3', 'cm')->getStyle('F3')->getFont()->applyFromArray($regularFont);
                $sheet->getStyle('F3')->getAlignment()->applyFromArray($alignLeft);

                // --- ROW 4: LOADABILITY ---
                $sheet->getRowDimension(4)->setRowHeight(22);
                $sheet->setCellValue('A4', 'LOADABILITY')->getStyle('A4')->getFont()->applyFromArray($boldFont);
                $sheet->setCellValue('B4', ':')->getStyle('B4')->getAlignment()->applyFromArray($alignCenter);
                $sheet->getStyle('B4')->getFont()->applyFromArray($boldFont);

                $sheet->setCellValue('C4', $bom->loadability_pcs);
                $sheet->getStyle('C4')->getAlignment()->applyFromArray($alignCenter);
                $sheet->getStyle('C4')->getFont()->applyFromArray($boldFont);
                $sheet->getStyle('C4')->applyFromArray($borderCellBox);

                $sheet->setCellValue('D4', 'pcs')->getStyle('D4')->getFont()->applyFromArray($regularFont);
                $sheet->getStyle('D4')->getAlignment()->applyFromArray($alignLeft);

                $sheet->setCellValue('E4', $bom->loadability_cbm);
                $sheet->getStyle('E4')->getAlignment()->applyFromArray($alignCenter);
                $sheet->getStyle('E4')->getFont()->applyFromArray($boldFont);
                $sheet->getStyle('E4')->applyFromArray($borderCellBox);
                $sheet->getStyle('E4')->getNumberFormat()->setFormatCode('0.000');

                $sheet->setCellValue('F4', 'cbm')->getStyle('F4')->getFont()->applyFromArray($regularFont);
                $sheet->getStyle('F4')->getAlignment()->applyFromArray($alignLeft);
// Spacing Row 5
$sheet->getRowDimension(5)->setRowHeight(15);

// ============================
// INSERT PRODUCT IMAGE
// ============================
if (!empty($bom->image)) {

    $imagePath = storage_path('app/public/' . $bom->image);

    if (file_exists($imagePath)) {

        $drawing = new Drawing();

        $drawing->setName('Product Image');
        $drawing->setDescription('Product Image');

        $drawing->setPath($imagePath);

        // Posisi gambar
        $drawing->setCoordinates('G2');

        // Ukuran
        $drawing->setHeight(120);

        $drawing->setResizeProportional(true);

        $drawing->setWorksheet($sheet);
    }
}

// --- ROW 6: MASTER HEADER ---
$sheet->getRowDimension(6)->setRowHeight(28);
$sheet->mergeCells('A6:F6');
                // Spacing Row 5
                $sheet->getRowDimension(5)->setRowHeight(15);

                // --- ROW 6: MASTER HEADER ---
                $sheet->getRowDimension(6)->setRowHeight(28);
                $sheet->mergeCells('A6:F6');
                $sheet->setCellValue('A6', 'LABOUR & MATERIAL');
                $sheet->getStyle('A6')->getFont()->applyFromArray(['name' => $fontFamily, 'size' => 11, 'bold' => true, 'color' => ['argb' => 'FFFFFFFF']]);
                $sheet->getStyle('A6')->getAlignment()->applyFromArray($alignCenter);
                $sheet->getStyle('A6:F6')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF374151');

                $currentRow = 7;
                $groupRowsInfo = [];

                // --- GROUPS LOOP ---
                foreach ($bom->groups as $group) {
                    $group = is_array($group) ? (object) $group : $group;

                    $sheet->getRowDimension($currentRow)->setRowHeight(24);
                    $sheet->mergeCells("A{$currentRow}:E{$currentRow}");
                    $sheet->setCellValue("A{$currentRow}", 'UPAH ' . strtoupper($group->name));
                    $sheet->getStyle("A{$currentRow}")->getFont()->applyFromArray($boldFont);
                    $sheet->getStyle("A{$currentRow}")->getAlignment()->applyFromArray($alignLeft);

                    // Group Background Gray
                    $sheet->getStyle("A{$currentRow}:F{$currentRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFE5E7EB');

                    // Labor Sub Price
                    $laborPrice = count($group->sub_prices) > 0 ? (is_array($group->sub_prices[0]) ? $group->sub_prices[0]['price'] : $group->sub_prices[0]->price) : 0;
                    $sheet->setCellValue("F{$currentRow}", $laborPrice);
                    $sheet->getStyle("F{$currentRow}")->getFont()->applyFromArray($boldFont);
                    $sheet->getStyle("F{$currentRow}")->getAlignment()->applyFromArray($alignRight);
                    $sheet->getStyle("F{$currentRow}")->getNumberFormat()->setFormatCode($numFormatCurrency);

                    $groupTitleRowIndex = $currentRow;
                    $currentRow++;

                    $itemStartRowIndex = $currentRow;

                  if (count($group->items) > 0) {

    foreach ($group->items as $item) {

        $item = is_array($item) ? (object) $item : $item;

        $sheet->getRowDimension($currentRow)->setRowHeight(20);

        // =========================
        // Nama Material
        // =========================
        $sheet->setCellValue("A{$currentRow}", $item->name);
        $sheet->getStyle("A{$currentRow}")
            ->getFont()
            ->applyFromArray($regularFont);

        $sheet->getStyle("A{$currentRow}")
            ->getAlignment()
            ->applyFromArray($alignLeft);

        // =========================
        // Qty
        // =========================
        $sheet->setCellValue("C{$currentRow}", (float) $item->qty);

        $sheet->getStyle("C{$currentRow}")
            ->getFont()
            ->applyFromArray($regularFont);

        $sheet->getStyle("C{$currentRow}")
            ->getAlignment()
            ->applyFromArray($alignCenter);

        $sheet->getStyle("C{$currentRow}")
            ->getNumberFormat()
            ->setFormatCode($numFormatQty);

        // =========================
        // Unit
        // =========================
        $sheet->setCellValue("D{$currentRow}", $item->unit);

        $sheet->getStyle("D{$currentRow}")
            ->getFont()
            ->applyFromArray($regularFont);

        $sheet->getStyle("D{$currentRow}")
            ->getAlignment()
            ->applyFromArray($alignCenter);

        // =========================
        // PRICE
        // =========================
        $price = $item->price;

        if (is_string($price)) {

            $price = trim($price);

            // jika ada koma desimal (5,13)
            if (strpos($price, ',') !== false && strpos($price, '.') === false) {

                $price = str_replace(',', '.', $price);

            }

            $price = (float) $price;

        }

        $sheet->setCellValue("E{$currentRow}", $price);

        $sheet->getStyle("E{$currentRow}")
            ->getFont()
            ->applyFromArray($regularFont);

        $sheet->getStyle("E{$currentRow}")
            ->getAlignment()
            ->applyFromArray($alignRight);

        $sheet->getStyle("E{$currentRow}")
            ->getNumberFormat()
            ->setFormatCode($numFormatCurrency);

        // =========================
        // TOTAL
        // =========================
        $sheet->setCellValue("F{$currentRow}", "=C{$currentRow}*E{$currentRow}");

        $sheet->getStyle("F{$currentRow}")
            ->getFont()
            ->applyFromArray($regularFont);

        $sheet->getStyle("F{$currentRow}")
            ->getAlignment()
            ->applyFromArray($alignRight);

        $sheet->getStyle("F{$currentRow}")
            ->getNumberFormat()
            ->setFormatCode($numFormatCurrency);

        // =========================
        // Category
        // =========================
        $catCode = ($item->material_type ?? '') === 'raw' ? 'R' : 'S';

        $sheet->getStyle("G{$currentRow}")
            ->getAlignment()
            ->applyFromArray($alignCenter);

        // =========================
        // Notes
        // =========================
        if (!empty($item->notes)) {

            $sheet->setCellValue("H{$currentRow}", $item->notes);

            $sheet->getStyle("H{$currentRow}")
                ->getFont()
                ->applyFromArray($italicFont);

            $sheet->getStyle("H{$currentRow}")
                ->getAlignment()
                ->applyFromArray($alignLeft);

        }

        // =========================
        // Border
        // =========================
        foreach (['A','C','D','E','F','G'] as $col) {

            $sheet->getStyle("{$col}{$currentRow}")
                ->applyFromArray($borderThin);

        }

        $currentRow++;
    }

                        // Spacing Row
                        $sheet->getRowDimension($currentRow)->setRowHeight(12);
                        $currentRow++;

                        // Sub Total Material Row
                        $sheet->getRowDimension($currentRow)->setRowHeight(22);
                        $sheet->mergeCells("A{$currentRow}:E{$currentRow}");
                        $sheet->setCellValue("A{$currentRow}", '    Sub Total Material');
                        $sheet->getStyle("A{$currentRow}")->getFont()->applyFromArray($boldFont);
                        $sheet->getStyle("A{$currentRow}")->getAlignment()->applyFromArray($alignLeft);

                        $sheet->setCellValue("F{$currentRow}", "=SUM(F{$itemStartRowIndex}:F" . ($currentRow - 2) . ")");
                        $sheet->getStyle("F{$currentRow}")->getFont()->applyFromArray($boldFont);
                        $sheet->getStyle("F{$currentRow}")->getAlignment()->applyFromArray($alignRight);
                        $sheet->getStyle("F{$currentRow}")->getNumberFormat()->setFormatCode($numFormatCurrency);

                        // Fill Soft Green
                        $sheet->getStyle("A{$currentRow}:F{$currentRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFECFDF5');

                        // Border Green Mint
                        $borderSubTotal = [
                            'borders' => [
                                'top' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF10B981']],
                                'bottom' => ['borderStyle' => Border::BORDER_DOUBLE, 'color' => ['argb' => 'FF10B981']]
                            ]
                        ];
                        $sheet->getStyle("A{$currentRow}")->applyFromArray($borderSubTotal);
                        $sheet->getStyle("F{$currentRow}")->applyFromArray($borderSubTotal);

                        $groupRowsInfo[] = [
                            'groupName' => $group->name,
                            'startRow' => $itemStartRowIndex,
                            'endRow' => $currentRow - 2,
                            'laborRow' => $groupTitleRowIndex,
                        ];

                        $currentRow++;
                    } else {
                        $groupRowsInfo[] = [
                            'groupName' => $group->name,
                            'startRow' => 0,
                            'endRow' => 0,
                            'laborRow' => $groupTitleRowIndex,
                        ];
                    }

                    $sheet->getRowDimension($currentRow)->setRowHeight(12);
                    $currentRow++;
                }

                // --- GRAND TOTAL CALCULATIONS SECTIONS ---
                $borderBottomBlack = ['borders' => ['bottom' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF000000']]]];

                // Row: LABOUR
                $labourRowIndex = $currentRow;
                $sheet->getRowDimension($currentRow)->setRowHeight(24);
                $sheet->mergeCells("A{$currentRow}:E{$currentRow}");
                $sheet->setCellValue("A{$currentRow}", 'LABOUR')->getStyle("A{$currentRow}")->getFont()->applyFromArray($boldFont);

                $laborFormulaParts = collect($groupRowsInfo)->map(fn($info) => "F".$info['laborRow'])->implode('+');
                $sheet->setCellValue("F{$currentRow}", "=" . ($laborFormulaParts ?: '0'));
                $sheet->getStyle("F{$currentRow}")->getFont()->applyFromArray($boldFont);
                $sheet->getStyle("F{$currentRow}")->getAlignment()->applyFromArray($alignRight);
                $sheet->getStyle("F{$currentRow}")->getNumberFormat()->setFormatCode($numFormatCurrency);
                $sheet->getStyle("A{$currentRow}")->applyFromArray($borderBottomBlack);
                $sheet->getStyle("F{$currentRow}")->applyFromArray($borderBottomBlack);
                $currentRow++;

                // Row: MATERIAL
                $materialRowIndex = $currentRow;
                $sheet->getRowDimension($currentRow)->setRowHeight(24);
                $sheet->mergeCells("A{$currentRow}:E{$currentRow}");
                $sheet->setCellValue("A{$currentRow}", 'MATERIAL')->getStyle("A{$currentRow}")->getFont()->applyFromArray($boldFont);

                $materialSubtotals = collect($groupRowsInfo)->filter(fn($info) => $info['startRow'] > 0)->map(fn($info) => "F".($info['endRow'] + 2))->implode('+');
                $sheet->setCellValue("F{$currentRow}", "=" . ($materialSubtotals ?: '0'));
                $sheet->getStyle("F{$currentRow}")->getFont()->applyFromArray($boldFont);
                $sheet->getStyle("F{$currentRow}")->getAlignment()->applyFromArray($alignRight);
                $sheet->getStyle("F{$currentRow}")->getNumberFormat()->setFormatCode($numFormatCurrency);
                $sheet->getStyle("A{$currentRow}")->applyFromArray($borderBottomBlack);
                $sheet->getStyle("F{$currentRow}")->applyFromArray($borderBottomBlack);
                $currentRow++;

                // // Indented Row: RAW MATERIAL
                // $sheet->getRowDimension($currentRow)->setRowHeight(22);
                // $sheet->mergeCells("A{$currentRow}:E{$currentRow}");
                // $sheet->setCellValue("A{$currentRow}", '    RAW MATERIAL')->getStyle("A{$currentRow}")->getFont()->applyFromArray($italicFont);

                // $rawGroupSubtotals = collect($groupRowsInfo)
                //     ->filter(fn($info) => $info['startRow'] > 0 && (str_contains(strtoupper($info['groupName']), 'DECOR') || str_contains(strtoupper($info['groupName']), 'FINISHING') || str_contains(strtoupper($info['groupName']), 'KAYU')))
                //     ->map(fn($info) => "F".($info['endRow'] + 2))->implode('+');
                // $sheet->setCellValue("F{$currentRow}", "=" . ($rawGroupSubtotals ?: '0'));
                // $sheet->getStyle("F{$currentRow}")->getFont()->applyFromArray($italicFont);
                // $sheet->getStyle("F{$currentRow}")->getAlignment()->applyFromArray($alignRight);
                // $sheet->getStyle("F{$currentRow}")->getNumberFormat()->setFormatCode($numFormatCurrency);
                // $currentRow++;

                // // Indented Row: SUPPORT MATERIAL
                // $sheet->getRowDimension($currentRow)->setRowHeight(22);
                // $sheet->mergeCells("A{$currentRow}:E{$currentRow}");
                // $sheet->setCellValue("A{$currentRow}", '    SUPPORT MATERIAL')->getStyle("A{$currentRow}")->getFont()->applyFromArray($italicFont);

                $supportGroupSubtotals = collect($groupRowsInfo)
                    ->filter(fn($info) => $info['startRow'] > 0 && str_contains(strtoupper($info['groupName']), 'PACKING'))
                    ->map(fn($info) => "F".($info['endRow'] + 2))->implode('+');
                // $sheet->setCellValue("F{$currentRow}", "=" . ($supportGroupSubtotals ?: '0'));
                $sheet->getStyle("F{$currentRow}")->getFont()->applyFromArray($italicFont);
                $sheet->getStyle("F{$currentRow}")->getAlignment()->applyFromArray($alignRight);
                $sheet->getStyle("F{$currentRow}")->getNumberFormat()->setFormatCode($numFormatCurrency);
                $currentRow++;

                // --- ROW: SUMMARY BAR ---
                $summaryRowIndex = $currentRow;
                $sheet->getRowDimension($currentRow)->setRowHeight(26);
                $sheet->mergeCells("A{$currentRow}:E{$currentRow}");
                $sheet->setCellValue("A{$currentRow}", 'SUMMARY');
                $sheet->getStyle("A{$currentRow}")->getFont()->applyFromArray(array_merge($boldFont, ['color' => ['argb' => 'FFFFFFFF']]));

                $sheet->setCellValue("F{$currentRow}", "=F{$labourRowIndex}+F{$materialRowIndex}");
                $sheet->getStyle("F{$currentRow}")->getFont()->applyFromArray(array_merge($boldFont, ['color' => ['argb' => 'FFFFFFFF']]));
                $sheet->getStyle("F{$currentRow}")->getAlignment()->applyFromArray($alignRight);
                $sheet->getStyle("F{$currentRow}")->getNumberFormat()->setFormatCode($numFormatCurrency);
                $sheet->getStyle("A{$currentRow}:F{$currentRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF2E7D32');
                $currentRow++;

                // --- BOTTOM GREEN TABLE ---
                foreach (($bom->summaries ?? []) as $index => $sumItem) {
                    $sumItem = is_array($sumItem) ? (object) $sumItem : $sumItem;
                    $sheet->getRowDimension($currentRow)->setRowHeight(22);
                    $sheet->getStyle("A{$currentRow}:F{$currentRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFD9EAD3');

                    $sheet->setCellValue("A{$currentRow}", $sumItem->name)->getStyle("A{$currentRow}")->getFont()->applyFromArray($boldFont);
                    $sheet->setCellValue("C{$currentRow}", $sumItem->qty ?? 1)->getStyle("C{$currentRow}")->getFont()->applyFromArray($regularFont);
                    $sheet->getStyle("C{$currentRow}")->getAlignment()->applyFromArray($alignCenter);
                    $sheet->getStyle("C{$currentRow}")->getNumberFormat()->setFormatCode($numFormatQty);

                    $sheet->setCellValue("E{$currentRow}", $sumItem->price ?? 0)->getStyle("E{$currentRow}")->getFont()->applyFromArray($boldFont);
                    $sheet->getStyle("E{$currentRow}")->getAlignment()->applyFromArray($alignRight);
                    $sheet->getStyle("E{$currentRow}")->getNumberFormat()->setFormatCode($numFormatCurrency);

                    // Running Total Formula
                    $prevRowRef = ($index === 0) ? "F{$summaryRowIndex}" : "F" . ($currentRow - 1);
                    $sheet->setCellValue("F{$currentRow}", "={$prevRowRef}+(C{$currentRow}*E{$currentRow})");
                    $sheet->getStyle("F{$currentRow}")->getFont()->applyFromArray($boldFont);
                    $sheet->getStyle("F{$currentRow}")->getAlignment()->applyFromArray($alignRight);
                    $sheet->getStyle("F{$currentRow}")->getNumberFormat()->setFormatCode($numFormatCurrency);

                    $borderGreenBox = [
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FF93C47D']]]
                    ];
                    foreach (['A', 'C', 'E', 'F'] as $col) {
                        $sheet->getStyle("{$col}{$currentRow}")->applyFromArray($borderGreenBox);
                    }
                    $currentRow++;
                }

                // --- FINAL ROW: TOTAL HPP ---
                $sheet->getRowDimension($currentRow)->setRowHeight(28);
                $sheet->mergeCells("A{$currentRow}:E{$currentRow}");
                $sheet->setCellValue("A{$currentRow}", 'TOTAL HPP');
                $sheet->getStyle("A{$currentRow}")->getFont()->applyFromArray(['name' => $fontFamily, 'size' => 11, 'bold' => true, 'color' => ['argb' => 'FFFFFFFF']]);
                $sheet->getStyle("A{$currentRow}")->getAlignment()->applyFromArray($alignCenter);

                $sheet->setCellValue("F{$currentRow}", "=F" . ($currentRow - 1));
                $sheet->getStyle("F{$currentRow}")->getFont()->applyFromArray(['name' => $fontFamily, 'size' => 11, 'bold' => true, 'color' => ['argb' => 'FFFFFFFF']]);
                $sheet->getStyle("F{$currentRow}")->getAlignment()->applyFromArray($alignRight);
                $sheet->getStyle("F{$currentRow}")->getNumberFormat()->setFormatCode($numFormatCurrency);

                $sheet->getStyle("A{$currentRow}:F{$currentRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF1E293B');

                $borderTotalHpp = [
                    'borders' => [
                        'top' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFFFFFFF']],
                        'bottom' => ['borderStyle' => Border::BORDER_DOUBLE, 'color' => ['argb' => 'FFFFFFFF']]
                    ]
                ];
                $sheet->getStyle("A{$currentRow}")->applyFromArray($borderTotalHpp);
                $sheet->getStyle("F{$currentRow}")->applyFromArray($borderTotalHpp);
            },
        ];
    }
}
