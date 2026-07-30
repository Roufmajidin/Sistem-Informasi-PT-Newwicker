<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class CommercialInvoiceExport implements WithTitle, WithEvents
{
    protected $invoice;

    public function __construct($invoice)
    {
        $this->invoice = $invoice;
    }

    public function title(): string
    {
        return 'Commercial Invoice';
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $data = $this->invoice;

                // Set column widths
                $widths = [
                    'A' => 8,   // NO
                    'B' => 16,  // HS CODE
                    'C' => 18,  // ITEM NUMBER
                    'D' => 32,  // DESCRIPTION
                    'E' => 16,  // QUANTITY (PCS)
                    'F' => 16,  // QUANTITY (BOX)
                    'G' => 15,  // UNIT PRICE
                    'H' => 18,  // TOTAL PRICE
                    'I' => 22,  // GROS WEIGHT (KGS)
                    'J' => 20,  // TOTAL CBM VOLUME
                ];
                foreach ($widths as $col => $w) {
                    $sheet->getColumnDimension($col)->setWidth($w);
                }

                // --- 1. SHIPPER HEADER ---
                $sheet->setCellValue('A6', 'SHIPPER :');
                $sheet->getStyle('A6')->getFont();

                $sheet->setCellValue('A7', 'PT. NEWWICKER INDONESIA');
                $sheet->getStyle('A7')->getFont()->setSize(11);

                $sheet->setCellValue('A8', 'JL. KISABALANANG BLOK. SIPANCING RT. 005 RW. 002,');
                $sheet->setCellValue('A9', 'DESA MEGU CILIK, KEC. WERU, CIREBON - INDONESIA');
                $sheet->setCellValue('A10', 'PHONE: 0231-325880 - export@newwicker.com');

                // Invoice Title
                $sheet->setCellValue('H8', 'COMMERCIAL INVOICE');
                $sheet->getStyle('H8')->getFont()->setSize(14);
                $sheet->getStyle('H8')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                // Horizontal separator under header
                $sheet->getStyle('A10:J10')->getBorders()->getBottom()->setBorderStyle(Border::BORDER_MEDIUM);

                // --- 2. BUYER & METADATA ---
                $sheet->setCellValue('A11', 'BUYER :');
                $sheet->getStyle('A11')->getFont();

                $sheet->setCellValue('A12', $data->buyer ?? 'Casa Giulia');
                $sheet->getStyle('A12')->getFont();

                $sheet->setCellValue('A13', $data->buyer_address ?? '');

                // Metadata block (I & J)
                $etd = $data->etd ? date('d-M-y', strtotime($data->etd)) : '';
                $meta = [
                    ['DATE', $etd],
                    ['SALES ORDER NO', $data->sales_order ?? ''],
                    ['INVOICE NO', $data->invoice_no ?? ''],
                    ['CUSTOMER CODE', $data->customer_code ?? ''],
                    ['CUSTOMER PO NO', $data->customer_po_no ?? ''],
                ];

                foreach ($meta as $idx => $m) {
                    $r = 11 + $idx;
                    $sheet->setCellValue("I{$r}", $m[0]);
                    $sheet->setCellValue("J{$r}", $m[1]);
                    $sheet->getStyle("I{$r}")->getFont();
                    $sheet->getStyle("I{$r}:J{$r}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                }

                // --- 3. MAIN TABLE HEADERS (Row 17) ---
                $headers = [
                    'A17' => "NO",
                    'B17' => "HS CODE",
                    'C17' => "ITEM NUMBER",
                    'D17' => "DESCRIPTION",
                    'E17' => "QUANTITY (PCS)",
                    'F17' => "QUANTITY (BOX)",
                    'G17' => "UNIT PRICE",
                    'H17' => "TOTAL PRICE",
                    'I17' => "GROS WEIGHT (KGS)",
                    'J17' => "TOTAL CBM VOLUME",
                ];

                $sheet->getRowDimension(17)->setRowHeight(32);
                foreach ($headers as $cell => $text) {
                    $sheet->setCellValue($cell, $text);
                }

                $sheet->getStyle('A17:J17')->getFont();
                $sheet->getStyle('A17:J17')->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER)
                    ->setWrapText(true);
                $sheet->getStyle('A17:J17')->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

                // --- 4. DATA ITEMS ---
                $row = 18;
                $startRow = $row;
                $items = $data->items ?? [];

                foreach ($items as $idx => $item) {
                    $sheet->getRowDimension($row)->setRowHeight(24);

                    $sheet->setCellValue("A{$row}", $idx + 1);
                    $sheet->setCellValue("B{$row}", $item->hs_code ?? '');
                    $sheet->setCellValue("C{$row}", $item->article_nr ?? '');
                    $sheet->setCellValue("D{$row}", $item->description ?? '');
                    $sheet->setCellValue("E{$row}", (float)($item->qty_pcs ?? 0));
                    $sheet->setCellValue("F{$row}", (float)($item->qty_box ?? 0));
                    $sheet->setCellValue("G{$row}", (float)($item->unit_price ?? 0));
                    $sheet->setCellValue("H{$row}", "=E{$row}*G{$row}");
                    $sheet->setCellValue("I{$row}", (float)($item->gross_weight ?? 0));
                    $sheet->setCellValue("J{$row}", (float)($item->total_cbm ?? 0));

                    // Formatting & Alignment
                    $sheet->getStyle("A{$row}:C{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("D{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                    $sheet->getStyle("E{$row}:F{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("G{$row}:H{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                    $sheet->getStyle("I{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("J{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                    $sheet->getStyle("E{$row}:F{$row}")->getNumberFormat()->setFormatCode('#,##0');
                    $sheet->getStyle("G{$row}:H{$row}")->getNumberFormat()->setFormatCode('"$"\\ #,##0.00');
                    $sheet->getStyle("I{$row}")->getNumberFormat()->setFormatCode('#,##0.00');
                    $sheet->getStyle("J{$row}")->getNumberFormat()->setFormatCode('0.000');

                    $sheet->getStyle("A{$row}:J{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                    $sheet->getStyle("A{$row}:J{$row}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

                    $row++;
                }

                $endRow = $row - 1;

                // --- 5. TOTAL ROW ---
                $totalRow = $row;
                $sheet->setCellValue("D{$totalRow}", "TOTAL");
                $sheet->getStyle("D{$totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->setCellValue("E{$totalRow}", "=SUM(E{$startRow}:E{$endRow})");
                $sheet->setCellValue("F{$totalRow}", "=SUM(F{$startRow}:F{$endRow})");
                $sheet->setCellValue("I{$totalRow}", "=SUM(I{$startRow}:I{$endRow})");
                $sheet->setCellValue("J{$totalRow}", "=SUM(J{$startRow}:J{$endRow})");

                $sheet->getStyle("E{$totalRow}:F{$totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("I{$totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("J{$totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                $sheet->getStyle("E{$totalRow}:F{$totalRow}")->getNumberFormat()->setFormatCode('#,##0');
                $sheet->getStyle("I{$totalRow}")->getNumberFormat()->setFormatCode('#,##0.00');
                $sheet->getStyle("J{$totalRow}")->getNumberFormat()->setFormatCode('0.000');

                $sheet->getStyle("A{$totalRow}:J{$totalRow}")->getBorders()->getTop()->setBorderStyle(Border::BORDER_THIN);
                $sheet->getStyle("A{$totalRow}:J{$totalRow}")->getBorders()->getBottom()->setBorderStyle(Border::BORDER_DOUBLE);
                $sheet->getStyle("A{$totalRow}:J{$totalRow}")->getBorders()->getLeft()->setBorderStyle(Border::BORDER_THIN);
                $sheet->getStyle("A{$totalRow}:J{$totalRow}")->getBorders()->getRight()->setBorderStyle(Border::BORDER_THIN);

                // --- 6. AMOUNT SUMMARY BOX ---
                $rowAmt = $totalRow + 2;
                $rowDep = $rowAmt + 1;
                $rowBal = $rowDep + 1;

                $sheet->setCellValue("G{$rowAmt}", "AMOUNT");
                $sheet->setCellValue("H{$rowAmt}", "=SUM(H{$startRow}:H{$endRow})");
                $sheet->getStyle("G{$rowAmt}:H{$rowAmt}")->getFont();
                $sheet->getStyle("H{$rowAmt}")->getNumberFormat()->setFormatCode('"$"\\ #,##0.00');

                $sheet->setCellValue("G{$rowDep}", "DEPOSIT");
                $sheet->setCellValue("H{$rowDep}", 0);
                $sheet->getStyle("G{$rowDep}:H{$rowDep}")->getFont();
                $sheet->getStyle("H{$rowDep}")->getNumberFormat()->setFormatCode('"$"\\ -');

                $sheet->setCellValue("G{$rowBal}", "BALANCE USD");
                $sheet->setCellValue("H{$rowBal}", "=H{$rowAmt}-H{$rowDep}");
                $sheet->getStyle("G{$rowBal}:H{$rowBal}")->getFont();
                $sheet->getStyle("H{$rowBal}")->getNumberFormat()->setFormatCode('"$"\\ #,##0.00');

                $sheet->getStyle("G{$rowAmt}:H{$rowBal}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

                // --- 7. LOGISTICS FOOTER ---
                $shipRow = $rowBal + 3;

                $eta = $data->eta ? date('d-M-y', strtotime($data->eta)) : '';

                $sheet->setCellValue("A{$shipRow}", "ETD");
                $sheet->setCellValue("C{$shipRow}", $etd);
                $sheet->setCellValue("A" . ($shipRow + 1), "ETA");
                $sheet->setCellValue("C" . ($shipRow + 1), $eta);
                $sheet->setCellValue("A" . ($shipRow + 2), "VESSEL NAME");
                $sheet->setCellValue("C" . ($shipRow + 2), $data->vessel_name ?? '');
                $sheet->setCellValue("A" . ($shipRow + 3), "CONTAINER NO");
                $sheet->setCellValue("C" . ($shipRow + 3), $data->container_no ?? '');
                $sheet->setCellValue("A" . ($shipRow + 4), "SEAL NO");
                $sheet->setCellValue("C" . ($shipRow + 4), $data->seal_no ?? '');

                $sheet->getStyle("A{$shipRow}:A" . ($shipRow + 4))->getFont();
                $sheet->getStyle("C{$shipRow}:C" . ($shipRow + 4))->getFont();

                $sheet->setCellValue("E{$shipRow}", "COUNTRY OF ORIGIN");
                $sheet->setCellValue("H{$shipRow}", "INDONESIA");
                $sheet->setCellValue("E" . ($shipRow + 1), "CONTAINER SIZE");
                $sheet->setCellValue("H" . ($shipRow + 1), $data->container_type ?? "1x40'HC");
                $sheet->setCellValue("E" . ($shipRow + 2), "PORT OR DEPARTURE");
                $sheet->setCellValue("H" . ($shipRow + 2), $data->port_loading ?? "Jakarta, Indonesia");
                $sheet->setCellValue("E" . ($shipRow + 3), "PORT OF ENTRY");
                $sheet->setCellValue("H" . ($shipRow + 3), $data->port_discharge ?? "ANTWERP, BELGIUM");
                $sheet->setCellValue("E" . ($shipRow + 4), "FUMIGATION");
                $sheet->setCellValue("H" . ($shipRow + 4), $data->fumigation ?? "YES");

                $sheet->getStyle("E{$shipRow}:E" . ($shipRow + 4))->getFont();

                // --- 8. TERMS & BANK DETAILS ---
                $termRow = $shipRow + 6;
                $sheet->setCellValue("A{$termRow}", "Price : The prices are FOB Jakarta, Indonesia.");
                $sheet->setCellValue("A" . ($termRow + 1), "Payment Terms : 100% balance payment should be paid upon received copy B/L by email");

                $bankRow = $termRow + 3;
                $sheet->setCellValue("A{$bankRow}", "Bank Details :");
                $sheet->getStyle("A{$bankRow}")->getFont();

                $sheet->setCellValue("A" . ($bankRow + 1), "Name of Bank");
                $sheet->setCellValue("C" . ($bankRow + 1), ": MANDIRI BANK");
                $sheet->setCellValue("A" . ($bankRow + 2), "Address of Bak");
                $sheet->setCellValue("C" . ($bankRow + 2), ": 36-38 JL.GATOT SUBROTO INDONESIA");
                $sheet->setCellValue("A" . ($bankRow + 3), "Swift Code");
                $sheet->setCellValue("C" . ($bankRow + 3), ": BMRIIDJAXXX");
                $sheet->setCellValue("A" . ($bankRow + 4), "Account Name");
                $sheet->setCellValue("C" . ($bankRow + 4), ": NEWWICKER INDONESIA");
                $sheet->setCellValue("A" . ($bankRow + 5), "Account Number");
                $sheet->setCellValue("C" . ($bankRow + 5), ": 134 001 110 1729");

                $sheet->setCellValue("H" . ($bankRow - 1), "Authorized Signatured");
                $sheet->getStyle("H" . ($bankRow - 1))->getFont()->setSize(11);
            }
        ];
    }
}

