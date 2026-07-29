<?php

namespace App\Exports;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
class PackingListExport implements WithEvents
{
    protected $ipl;

    public function __construct($ipl)
    {
        $this->ipl = $ipl;
    }

    /**
     * Register events to build the complete custom Excel layout
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $this->buildExcelSheet($sheet);
            },
        ];
    }

    /**
     * Build sheet using PhpSpreadsheet
     */
    public function buildExcelSheet($sheet)
    {
        $sheet->setShowGridLines(true);

        // --- COLUMN WIDTHS ---
        $sheet->getColumnDimension('A')->setWidth(6);   // NO
        $sheet->getColumnDimension('B')->setWidth(18);  // PO NUMBER
        $sheet->getColumnDimension('C')->setWidth(16);  // HTS CODE
        $sheet->getColumnDimension('D')->setWidth(18);  // ARTICLE NO. CLIENT
        $sheet->getColumnDimension('E')->setWidth(16);  // PHOTOS
        $sheet->getColumnDimension('F')->setWidth(30);  // DESCRIPTION
        $sheet->getColumnDimension('G')->setWidth(26);  // REFLECTIVE DIMENSION
        $sheet->getColumnDimension('H')->setWidth(12);  // QTY CTN
        $sheet->getColumnDimension('I')->setWidth(10);  // CTNS
        $sheet->getColumnDimension('J')->setWidth(16);  // NETT WEIGHT
        $sheet->getColumnDimension('K')->setWidth(16);  // GROSS WEIGHT
        $sheet->getColumnDimension('L')->setWidth(14);  // TOTAL CBM

        // --- COMPANY HEADER ---
        $sheet->mergeCells('A1:L1');
        $sheet->setCellValue('A1', 'PT. NEWWICKER INDONESIA');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A2:L2');
        $sheet->setCellValue('A2', 'JL. KISABA LANANG RT. 019 RW. 002,');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A3:L3');
        $sheet->setCellValue('A3', 'BODELOR, PLUMBON, CIREBON 45155');
        $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A4:L4');
        $sheet->setCellValue('A4', 'INDONESIA');
        $sheet->getStyle('A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->mergeCells('A5:L5');
        $sheet->setCellValue('A5', 'PHONE : 0231 - 325880 - export@newwicker.com');
        $sheet->getStyle('A5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Border line under company info
        $sheet->getStyle('A5:L5')->getBorders()->getBottom()->setBorderStyle(Border::BORDER_MEDIUM);

        // --- TITLE ---
        $sheet->mergeCells('A6:L6');
        $sheet->setCellValue('A6', 'PACKING LIST');
        $sheet->getStyle('A6')->getFont()->setBold(true)->setSize(16)->setUnderline(true);
        $sheet->getStyle('A6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $containerNo = $this->ipl->container_no ?? '-';
        $sheet->mergeCells('A7:L7');
        $sheet->setCellValue('A7', 'PL-' . $this->ipl->invoice_no);
        $sheet->getStyle('A7')->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle('A7')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // --- SHIPPER & BUYER (LEFT) vs CONTAINER & VESSEL DETAILS (RIGHT) ---
        // Shipper Header
        $sheet->mergeCells('A8:D8');
        $sheet->setCellValue('A8', 'SHIPPER :');
        $sheet->getStyle('A8')->getFont()->setBold(true);
        $sheet->getStyle('A8:D8')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D9D9D9');

        $sheet->mergeCells('A9:D9');
        $sheet->setCellValue('A9', 'PT. NEWWICKER INDONESIA');
        $sheet->getStyle('A9')->getFont()->setBold(true);

        $sheet->mergeCells('A10:D10');
        $sheet->setCellValue('A10', 'JL. KISABA LANANG RT. 019 RW. 002,');
        $sheet->mergeCells('A11:D11');
        $sheet->setCellValue('A11', 'BODELOR, PLUMBON, CIREBON 45155');
        $sheet->mergeCells('A12:D12');
        $sheet->setCellValue('A12', 'INDONESIA');
        $sheet->mergeCells('A13:D13');
        $sheet->setCellValue('A13', 'Tel: +62 231 325880');

        // Buyer Header
        $sheet->mergeCells('A14:D14');
        $sheet->setCellValue('A14', 'BUYER :');
        $sheet->getStyle('A14')->getFont()->setBold(true);
        $sheet->getStyle('A14:D14')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D9D9D9');

        $sheet->mergeCells('A15:D15');
        $sheet->setCellValue('A15', strtoupper($this->ipl->buyer ?? ''));
        $sheet->getStyle('A15')->getFont()->setBold(true);

        $sheet->mergeCells('A16:D17');
        $sheet->setCellValue('A16', $this->ipl->buyer_address ?? '');
        $sheet->getStyle('A16')->getAlignment()->setWrapText(true);

        $sheet->mergeCells('A18:D18');
        $sheet->setCellValue('A18', 'TEL: ' . ($this->ipl->customer_code ?? ''));

        // Right details
        $etdFormatted = $this->ipl->etd ? date('d M Y', strtotime($this->ipl->etd)) : '';
        $etaFormatted = $this->ipl->eta ? date('d M Y', strtotime($this->ipl->eta)) : '';
        $dateFormatted = $this->ipl->created_at ? date('d M Y', strtotime($this->ipl->created_at)) : '';

        $rightDetails = [
            9  => ['Date', $dateFormatted],
            10 => ['Vessel Name', $this->ipl->vessel_name ?? ''],
            11 => ['Connecting Vessel', ''],
            12 => ['Container Type', $this->ipl->container_type ?? ''],
            13 => ['Container No.', $this->ipl->container_no ?? ''],
            14 => ['Seal No.', $this->ipl->seal_no ?? ''],
            15 => ['Port of Loading', $this->ipl->port_loading ?? ''],
            16 => ['Port of Discharge', $this->ipl->port_discharge ?? ''],
            17 => ['Commodity', $this->ipl->commodity ?? ''],
            18 => ['ETD', $etdFormatted],
            19 => ['ETA', $etaFormatted],
            20 => ['Shipment no #', $this->ipl->invoice_no ?? ''],
        ];

        foreach ($rightDetails as $row => $info) {
            $sheet->setCellValue("H{$row}", $info[0] . ' :');
            $sheet->getStyle("H{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("H{$row}")->getFont()->setBold(true);
            $sheet->setCellValue("I{$row}", $info[1]);
            $sheet->mergeCells("I{$row}:L{$row}");
        }

        // --- TABLE HEADERS (Row 21) ---
        $headers = [
            'A' => "NO.",
            'B' => "PO\nNUMBER",
            'C' => "HTS CODE",
            'D' => "ARTICLE\nNO. CLIENT",
            'E' => "PHOTOS",
            'F' => "DESCRIPTION",
            'G' => "REFLECTIVE\nDIMENSION",
            'H' => "QTY CTN",
            'I' => "",
            'J' => "NETT\nWEIGHT\n(KGS)",
            'K' => "GROSS\nWEIGHT\n(KGS)",
            'L' => "TOTAL\nCBM"
        ];

        $sheet->getRowDimension(21)->setRowHeight(36);
        foreach ($headers as $col => $text) {
            $sheet->setCellValue("{$col}21", $text);
            $sheet->getStyle("{$col}21")->getFont()->setBold(true);
            $sheet->getStyle("{$col}21")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("{$col}21")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle("{$col}21")->getAlignment()->setWrapText(true);
            $sheet->getStyle("{$col}21")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        }

        // --- DATA ROWS (Row 22 onwards) ---
        $currentRow = 22;

        foreach ($this->ipl->items as $idx => $item) {
            $sheet->getRowDimension($currentRow)->setRowHeight(60);

            $sheet->setCellValue("A{$currentRow}", $idx + 1);
            $sheet->setCellValue("B{$currentRow}", $item->po_no ?? $this->ipl->sales_order);
            $sheet->setCellValue("C{$currentRow}", $item->hs_code);
            $sheet->setCellValue("D{$currentRow}", $item->article_nr);

            // Download & attach product image
            if (!empty($item->photo)) {
                try {
                    $imgContent = @file_get_contents($item->photo);
                    if ($imgContent !== false) {
                        $tempPath = sys_get_temp_dir() . '/item_img_' . $item->id . '.png';
                        file_put_contents($tempPath, $imgContent);

                        $drawing = new Drawing();
                        $drawing->setName($item->description ?? 'Photo');
                        $drawing->setPath($tempPath);
                        $drawing->setCoordinates("E{$currentRow}");
                        $drawing->setHeight(50);
                        $drawing->setOffsetX(10);
                        $drawing->setOffsetY(5);
                        $drawing->setWorksheet($sheet);
                    }
                } catch (Exception $e) {
                    $sheet->setCellValue("E{$currentRow}", '-');
                }
            }

            $sheet->setCellValue("F{$currentRow}", $item->description);
            $sheet->setCellValue("G{$currentRow}", $item->box_dimension);
            $sheet->setCellValue("H{$currentRow}", $item->qty_box ?? $item->qty_pcs);
            $sheet->setCellValue("I{$currentRow}", 'CTNS');
            $sheet->setCellValue("J{$currentRow}", (float)$item->net_weight);
            $sheet->setCellValue("K{$currentRow}", (float)$item->gross_weight);
            $sheet->setCellValue("L{$currentRow}", (float)$item->total_cbm);

            // Alignments
            $sheet->getStyle("A{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle("B{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle("C{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle("D{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle("F{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle("G{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle("H{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle("I{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);

            // Numbers
            $sheet->getStyle("J{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT)->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle("J{$currentRow}")->getNumberFormat()->setFormatCode('#,##0.00');

            $sheet->getStyle("K{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT)->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle("K{$currentRow}")->getNumberFormat()->setFormatCode('#,##0.00');

            $sheet->getStyle("L{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT)->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle("L{$currentRow}")->getNumberFormat()->setFormatCode('#,##0.000');

            // Borders
            $sheet->getStyle("A{$currentRow}:L{$currentRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

            $currentRow++;
        }

        // --- TOTAL ROW ---
        $sheet->getRowDimension($currentRow)->setRowHeight(24);
        $sheet->mergeCells("A{$currentRow}:G{$currentRow}");
        $sheet->setCellValue("A{$currentRow}", 'TOTAL');
        $sheet->getStyle("A{$currentRow}")->getFont()->setBold(true);
        $sheet->getStyle("A{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT)->setVertical(Alignment::VERTICAL_CENTER);

        $startDataRow = 22;
        $endDataRow = $currentRow - 1;

        $sheet->setCellValue("H{$currentRow}", "=SUM(H{$startDataRow}:H{$endDataRow})");
        $sheet->getStyle("H{$currentRow}")->getFont()->setBold(true);
        $sheet->getStyle("H{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->setCellValue("I{$currentRow}", 'CTNS');
        $sheet->getStyle("I{$currentRow}")->getFont()->setBold(true);
        $sheet->getStyle("I{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->setCellValue("J{$currentRow}", "=SUM(J{$startDataRow}:J{$endDataRow})");
        $sheet->getStyle("J{$currentRow}")->getFont()->setBold(true);
        $sheet->getStyle("J{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle("J{$currentRow}")->getNumberFormat()->setFormatCode('#,##0.00');

        $sheet->setCellValue("K{$currentRow}", "=SUM(K{$startDataRow}:K{$endDataRow})");
        $sheet->getStyle("K{$currentRow}")->getFont()->setBold(true);
        $sheet->getStyle("K{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle("K{$currentRow}")->getNumberFormat()->setFormatCode('#,##0.00');

        $sheet->setCellValue("L{$currentRow}", "=SUM(L{$startDataRow}:L{$endDataRow})");
        $sheet->getStyle("L{$currentRow}")->getFont()->setBold(true);
        $sheet->getStyle("L{$currentRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT)->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle("L{$currentRow}")->getNumberFormat()->setFormatCode('#,##0.000');

        $sheet->getStyle("A{$currentRow}:L{$currentRow}")->getBorders()->getTop()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("A{$currentRow}:L{$currentRow}")->getBorders()->getBottom()->setBorderStyle(Border::BORDER_DOUBLE);
        $sheet->getStyle("A{$currentRow}:L{$currentRow}")->getBorders()->getLeft()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("A{$currentRow}:L{$currentRow}")->getBorders()->getRight()->setBorderStyle(Border::BORDER_THIN);
    }
}
