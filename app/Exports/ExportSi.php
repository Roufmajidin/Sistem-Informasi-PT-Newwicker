<?php

namespace App\Exports;

use App\Models\ExportIpl;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExportSi
{
    public function download(ExportIpl $ipl)
    {
        $ipl->loadMissing([
            'items',
        ]);

        $items = collect($ipl->items ?? []);

        // ============================================================
        // PURCHASE ORDER
        // ============================================================
        $poNumbers = $items
            ->map(function ($item) {
                return trim(
                    (string) (
                        $item->blde
                        ?? $item->po_no
                        ?? ''
                    )
                );
            })
            ->filter()
            ->unique()
            ->values()
            ->implode(' ; ');

        // ============================================================
        // HS CODE
        // ============================================================
        $hsCodes = $items
            ->pluck('hs_code')
            ->filter(function ($v) {
                return trim((string) $v) !== '';
            })
            ->map(function ($v) {
                return trim((string) $v);
            })
            ->unique()
            ->values();

        // ============================================================
        // TOTAL QUANTITY
        // ============================================================
        $totalQty = $items->sum(function ($item) {
            return (float) ($item->qty_box ?? 0);
        });

        // ============================================================
        // TOTAL NET WEIGHT
        //
        // net_weight = berat per box
        // qty_box    = jumlah box
        // ============================================================
        $totalNet = $items->sum(function ($item) {
            return
                (float) ($item->net_weight ?? 0)
                *
                (float) ($item->qty_box ?? 0);
        });

        // ============================================================
        // TOTAL GROSS WEIGHT
        //
        // gross_weight = berat per box
        // qty_box      = jumlah box
        // ============================================================
        $totalGross = $items->sum(function ($item) {
            return
                (float) ($item->gross_weight ?? 0)
                *
                (float) ($item->qty_box ?? 0);
        });

        // ============================================================
        // TOTAL CBM
        // ============================================================
        $totalCbm = $items->sum(function ($item) {
            return (float) ($item->total_cbm ?? 0);
        });

        // ============================================================
        // CREATE SPREADSHEET
        // ============================================================
        $spreadsheet = new Spreadsheet();

        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setTitle('SI');

        // ============================================================
        // PAGE SETUP
        // ============================================================
        $sheet->getPageSetup()
            ->setOrientation(PageSetup::ORIENTATION_PORTRAIT)
            ->setPaperSize(PageSetup::PAPERSIZE_A4)
            ->setFitToWidth(1)
            ->setFitToHeight(0);

        $sheet->getPageMargins()
            ->setTop(0.35)
            ->setRight(0.35)
            ->setBottom(0.35)
            ->setLeft(0.35);

        $sheet->getPageSetup()
            ->setPrintArea('A1:H55');

        // ============================================================
        // COLUMN WIDTH
        // ============================================================
        foreach ([
            'A',
            'B',
            'C',
            'D',
            'E',
            'F',
            'G',
            'H'
        ] as $col) {
            $sheet
                ->getColumnDimension($col)
                ->setWidth(15);
        }

        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getColumnDimension('B')->setWidth(3);
        $sheet->getColumnDimension('C')->setWidth(23);
        $sheet->getColumnDimension('D')->setWidth(3);
        $sheet->getColumnDimension('E')->setWidth(20);
        $sheet->getColumnDimension('F')->setWidth(3);
        $sheet->getColumnDimension('G')->setWidth(23);
        $sheet->getColumnDimension('H')->setWidth(3);

        // ============================================================
        // COMPANY HEADER
        // ============================================================
        $sheet->mergeCells('A1:H1');
        $sheet->mergeCells('A2:H2');
        $sheet->mergeCells('A3:H3');
        $sheet->mergeCells('A4:H4');
        $sheet->mergeCells('A5:H5');

        $sheet->setCellValue(
            'A1',
            'PT. NEWWICKER INDONESIA'
        );

        $sheet->setCellValue(
            'A2',
            'JL. KISABA LANANG RT. 019 RW. 002,'
        );

        $sheet->setCellValue(
            'A3',
            'BODELOR, PLUMBON, CIREBON 45155'
        );

        $sheet->setCellValue(
            'A4',
            'INDONESIA'
        );

        $sheet->setCellValue(
            'A5',
            'PHONE : 0231 - 325880 - export@newwicker.com'
        );

        $sheet
            ->getStyle('A1:H5')
            ->getAlignment()
            ->setHorizontal(
                Alignment::HORIZONTAL_CENTER
            );

        $sheet
            ->getStyle('A1')
            ->getFont()
            ->setBold(true)
            ->setSize(15);

        $sheet
            ->getStyle('A2:H5')
            ->getFont()
            ->setSize(9);

        // ============================================================
        // LOGO
        // ============================================================
        $logo = public_path(
            'assets/images/newwicker.jpg'
        );

        if (is_file($logo)) {

            $drawing = new Drawing();

            $drawing->setName(
                'Newwicker Logo'
            );

            $drawing->setDescription(
                'PT Newwicker Indonesia'
            );

            $drawing->setPath($logo);

            $drawing->setHeight(58);

            $drawing->setCoordinates('A1');

            $drawing->setOffsetX(3);

            $drawing->setOffsetY(2);

            $drawing->setWorksheet($sheet);
        }

        // ============================================================
        // TITLE
        // ============================================================
        $sheet->mergeCells('A7:H7');

        $sheet->setCellValue(
            'A7',
            'SHIPPING INSTRUCTION'
        );

        $sheet
            ->getStyle('A7:H7')
            ->getFont()
            ->setBold(true)
            ->setSize(16);

        $sheet
            ->getStyle('A7:H7')
            ->getAlignment()
            ->setHorizontal(
                Alignment::HORIZONTAL_CENTER
            );

        $sheet
            ->getStyle('A7:H7')
            ->getBorders()
            ->getOutline()
            ->setBorderStyle(
                Border::BORDER_MEDIUM
            );

        // ============================================================
        // TOP INFORMATION
        // ============================================================
        $this->pair(
            $sheet,
            8,
            'Date',
            $ipl->date?->format('d/m/Y'),
            'Booking No.',
            $ipl->booking_no
        );

        $this->pair(
            $sheet,
            9,
            'Shipping Forwarder',
            $ipl->shipping_forwarder,
            'PEB No. & Date',
            trim(
                ($ipl->peb_no ?? '')
                .
                (
                    $ipl->peb_date
                        ? ' / ' . $ipl->peb_date->format('d/m/Y')
                        : ''
                )
            )
        );

        $this->pair(
            $sheet,
            10,
            'Attn',
            $ipl->attn,
            'KPBC No.',
            $ipl->kpbc_no
        );

        $this->pair(
            $sheet,
            11,
            'HS Code',
            $hsCodes->implode(', '),
            'Container Type',
            $ipl->container_type
        );

        // ============================================================
        // SHIPPING INSTRUCTION SECTION
        // ============================================================
        $sheet->mergeCells('A13:H13');

        $sheet->setCellValue(
            'A13',
            'SHIPPING INSTRUCTION'
        );

        $sheet
            ->getStyle('A13:H13')
            ->getFont()
            ->setBold(true)
            ->setSize(10);

        $sheet
            ->getStyle('A13:H13')
            ->getAlignment()
            ->setHorizontal(
                Alignment::HORIZONTAL_CENTER
            );

        $sheet
            ->getStyle('A13:H13')
            ->getFill()
            ->setFillType(
                Fill::FILL_SOLID
            )
            ->getStartColor()
            ->setARGB('FFEFEFEF');

        $sheet
            ->getStyle('A13:H13')
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(
                Border::BORDER_THIN
            );

        // ============================================================
        // INTRODUCTION
        // ============================================================
        $sheet->mergeCells('A14:H16');

        $sheet->setCellValue(
            'A14',
            "We would appreciate it very much if you could help us in the shipment of our export commodities rattan furniture with the below description:\n\nDocumentation Original B/L to show:"
        );

        $sheet
            ->getStyle('A14:H16')
            ->getAlignment()
            ->setWrapText(true)
            ->setVertical(
                Alignment::VERTICAL_TOP
            );

        $sheet
            ->getStyle('A14:H16')
            ->getFont()
            ->setSize(9);

        $sheet
            ->getStyle('A14:H16')
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(
                Border::BORDER_THIN
            );

        // ============================================================
        // SHIPMENT DETAILS
        // ============================================================
        $sheet->mergeCells('A18:H18');

        $sheet->setCellValue(
            'A18',
            'SHIPMENT DETAILS'
        );

        $this->sectionStyle(
            $sheet,
            'A18:H18'
        );

        $this->row(
            $sheet,
            19,
            'Quantity',
            number_format(
                $totalQty,
                0,
                '.',
                ','
            ) . ' CTNS OF Rattan Furnitures'
        );

        $this->row(
            $sheet,
            20,
            'Purchase Order No.',
            $poNumbers
        );

        $this->row(
            $sheet,
            21,
            'Port of Loading',
            $ipl->port_loading
        );

        $this->row(
            $sheet,
            22,
            'Port of Destination',
            $ipl->port_discharge
        );

        $this->row(
            $sheet,
            23,
            'L/C No.',
            $ipl->lc_no
        );

        $this->row(
            $sheet,
            24,
            'Freight',
            $ipl->freight
        );

        $this->row(
            $sheet,
            25,
            'Contract No',
            $ipl->contract_no
        );

        // ============================================================
        // SHIPPER / CONSIGNEE
        // ============================================================
        $sheet->mergeCells('A27:D27');
        $sheet->mergeCells('E27:H27');

        $sheet->setCellValue(
            'A27',
            'SHIPPER'
        );

        $sheet->setCellValue(
            'E27',
            'CONSIGNEE'
        );

        $this->sectionStyle(
            $sheet,
            'A27:D27'
        );

        $this->sectionStyle(
            $sheet,
            'E27:H27'
        );

        $sheet->mergeCells('A28:D32');
        $sheet->mergeCells('E28:H32');

        $sheet->setCellValue(
            'A28',
            "PT. NEWWICKER INDONESIA\nJL. KISABA LANANG RT. 019 RW. 002,\nBODELOR, PLUMBON, CIREBON 45155\nINDONESIA\nTel : +62 231 325880"
        );

        $sheet->setCellValue(
            'E28',
            trim(
                ($ipl->buyer ?? '')
                .
                "\n"
                .
                ($ipl->buyer_address ?? '')
            )
        );

        $sheet
            ->getStyle('A28:H32')
            ->getAlignment()
            ->setWrapText(true)
            ->setVertical(
                Alignment::VERTICAL_TOP
            );

        $sheet
            ->getStyle('A28:H32')
            ->getFont()
            ->setSize(9);

        $sheet
            ->getStyle('A28:D32')
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(
                Border::BORDER_THIN
            );

        $sheet
            ->getStyle('E28:H32')
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(
                Border::BORDER_THIN
            );

        // ============================================================
        // NOTIFY PARTY
        // ============================================================
        $this->row(
            $sheet,
            34,
            'Notify Party',
            $ipl->notify_party
        );

        // ============================================================
        // TRANSPORT AND CARGO
        // ============================================================
        $sheet->mergeCells('A36:H36');

        $sheet->setCellValue(
            'A36',
            'TRANSPORT AND CARGO INFORMATION'
        );

        $this->sectionStyle(
            $sheet,
            'A36:H36'
        );

        $this->pair(
            $sheet,
            37,
            'Vessel Name',
            $ipl->vessel_name,
            'Connect to',
            $ipl->connect_to
        );

        $this->pair(
            $sheet,
            38,
            'ETD',
            $ipl->etd?->format('d/m/Y'),
            'Bill of Lading',
            $ipl->bill_of_lading
        );

        $this->pair(
            $sheet,
            39,
            'TOTAL Nett Weight',
            number_format(
                $totalNet,
                2,
                '.',
                ''
            ) . ' kgs',
            'TOTAL Gross Weight',
            number_format(
                $totalGross,
                2,
                '.',
                ''
            ) . ' kgs'
        );

        $this->pair(
            $sheet,
            40,
            'TARE',
            ($ipl->tare ?? '') . ' kgs',
            'VGM',
            ($ipl->vgm ?? '') . ' kgs'
        );

        $this->pair(
            $sheet,
            41,
            'TOTAL Volume',
            number_format(
                $totalCbm,
                2,
                '.',
                ''
            ) . ' m3',
            'Stuffing Date',
            $ipl->stuffing_date?->format('d/m/Y')
        );

        $this->pair(
            $sheet,
            42,
            'Location',
            $ipl->location,
            'EMKL',
            $ipl->emkl
        );

        $this->pair(
            $sheet,
            43,
            'Fumigation',
            $ipl->fumigation,
            'Container Type',
            $ipl->container_type
        );

        $this->pair(
            $sheet,
            44,
            'Container No.',
            $ipl->container_no,
            'Seal No.',
            $ipl->seal_no
        );

        // ============================================================
        // FOOTER
        // ============================================================
        $sheet->mergeCells('A46:H46');

        $sheet->setCellValue(
            'A46',
            'Appreciate your kind cooperation'
        );

        $sheet
            ->getStyle('A46:H46')
            ->getFont()
            ->setSize(9);

        $sheet->mergeCells('A48:H48');

        $sheet->setCellValue(
            'A48',
            'Best Regards'
        );

        $sheet->mergeCells('A50:H50');

        $sheet->setCellValue(
            'A50',
            'PT NEWWICKER INDONESIA'
        );

        $sheet->mergeCells('A53:H53');

        $sheet->setCellValue(
            'A53',
            'Sofian'
        );

        $sheet
            ->getStyle('A50:H50')
            ->getFont()
            ->setBold(true);

        // ============================================================
        // GENERAL STYLE
        // ============================================================
        $sheet
            ->getStyle('A8:H44')
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(
                Border::BORDER_THIN
            );

        $sheet
            ->getStyle('A8:H44')
            ->getAlignment()
            ->setVertical(
                Alignment::VERTICAL_CENTER
            );

        $sheet
            ->getStyle('A8:H44')
            ->getFont()
            ->setSize(9);

        $sheet
            ->getStyle('A8:H44')
            ->getAlignment()
            ->setWrapText(true);

        // ============================================================
        // ROW HEIGHT
        // ============================================================
        for ($r = 8; $r <= 53; $r++) {

            $sheet
                ->getRowDimension($r)
                ->setRowHeight(18);
        }

        $sheet
            ->getRowDimension(14)
            ->setRowHeight(28);

        $sheet
            ->getRowDimension(15)
            ->setRowHeight(28);

        $sheet
            ->getRowDimension(16)
            ->setRowHeight(28);

        $sheet
            ->getRowDimension(28)
            ->setRowHeight(22);

        $sheet
            ->getRowDimension(29)
            ->setRowHeight(22);

        $sheet
            ->getRowDimension(30)
            ->setRowHeight(22);

        $sheet
            ->getRowDimension(31)
            ->setRowHeight(22);

        $sheet
            ->getRowDimension(32)
            ->setRowHeight(22);

        // ============================================================
        // FOOTER PAGE NUMBER
        // ============================================================
        $sheet
            ->getHeaderFooter()
            ->setOddFooter('&CPage &P of &N');

        // ============================================================
        // AMANKAN NAMA FILE
        //
        // invoice_no bisa mengandung:
        // SI/0135
        // SI\0135
        // SI:0135
        //
        // Karakter tersebut tidak boleh ada pada Content-Disposition.
        // ============================================================
        $invoiceNo = $ipl->invoice_no ?: $ipl->id;

        $safeInvoiceNo = preg_replace(
            '/[\/\\:*?"<>|]+/',
            '-',
            (string) $invoiceNo
        );

        $safeInvoiceNo = trim(
            $safeInvoiceNo,
            '.- '
        );

        if ($safeInvoiceNo === '') {
            $safeInvoiceNo = (string) $ipl->id;
        }

        $filename = 'SI_' . $safeInvoiceNo . '.xlsx';

        // ============================================================
        // WRITE EXCEL
        // ============================================================
        $writer = new Xlsx(
            $spreadsheet
        );

        return response()->streamDownload(
            function () use ($writer) {

                $writer->save(
                    'php://output'
                );
            },
            $filename,
            [
                'Content-Type' =>
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            ]
        );
    }

    // ============================================================
    // PAIR
    // ============================================================
    private function pair(
        $sheet,
        $row,
        $label1,
        $value1,
        $label2,
        $value2
    ) {
        $sheet->mergeCells(
            "A{$row}:B{$row}"
        );

        $sheet->mergeCells(
            "C{$row}:D{$row}"
        );

        $sheet->mergeCells(
            "E{$row}:F{$row}"
        );

        $sheet->mergeCells(
            "G{$row}:H{$row}"
        );

        $sheet->setCellValue(
            "A{$row}",
            $label1
        );

        $sheet->setCellValue(
            "C{$row}",
            $value1 ?? ''
        );

        $sheet->setCellValue(
            "E{$row}",
            $label2
        );

        $sheet->setCellValue(
            "G{$row}",
            $value2 ?? ''
        );

        $sheet
            ->getStyle("A{$row}:H{$row}")
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(
                Border::BORDER_THIN
            );

        $sheet
            ->getStyle("A{$row}:H{$row}")
            ->getAlignment()
            ->setVertical(
                Alignment::VERTICAL_CENTER
            )
            ->setWrapText(true);

        $sheet
            ->getStyle("A{$row}:B{$row}")
            ->getFont()
            ->setBold(true);

        $sheet
            ->getStyle("E{$row}:F{$row}")
            ->getFont()
            ->setBold(true);
    }

    // ============================================================
    // ROW
    // ============================================================
    private function row(
        $sheet,
        $row,
        $label,
        $value
    ) {
        $sheet->mergeCells(
            "A{$row}:B{$row}"
        );

        $sheet->mergeCells(
            "C{$row}:H{$row}"
        );

        $sheet->setCellValue(
            "A{$row}",
            $label
        );

        $sheet->setCellValue(
            "C{$row}",
            $value ?? ''
        );

        $sheet
            ->getStyle("A{$row}:H{$row}")
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(
                Border::BORDER_THIN
            );

        $sheet
            ->getStyle("A{$row}:H{$row}")
            ->getAlignment()
            ->setVertical(
                Alignment::VERTICAL_CENTER
            )
            ->setWrapText(true);

        $sheet
            ->getStyle("A{$row}:B{$row}")
            ->getFont()
            ->setBold(true);
    }

    // ============================================================
    // SECTION STYLE
    // ============================================================
    private function sectionStyle(
        $sheet,
        $range
    ) {
        $sheet
            ->getStyle($range)
            ->getFont()
            ->setBold(true)
            ->setSize(10);

        $sheet
            ->getStyle($range)
            ->getAlignment()
            ->setHorizontal(
                Alignment::HORIZONTAL_CENTER
            )
            ->setVertical(
                Alignment::VERTICAL_CENTER
            );

        $sheet
            ->getStyle($range)
            ->getFill()
            ->setFillType(
                Fill::FILL_SOLID
            )
            ->getStartColor()
            ->setARGB('FFEFEFEF');

        $sheet
            ->getStyle($range)
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(
                Border::BORDER_THIN
            );
    }
}