<?php

namespace App\Exports;

use App\Models\ExportIpl;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CustomCommercialInvoiceExport
{
    public function download(ExportIpl $invoice)
    {
        $template = public_path(
            'templates/template custome-invoice(2).xls'
        );

        if (!file_exists($template)) {
            abort(
                404,
                'Template Custom Invoice tidak ditemukan: ' . $template
            );
        }

        /*
         * ============================================================
         * LOAD TEMPLATE
         * ============================================================
         */
        $spreadsheet = IOFactory::load($template);
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Invoice');

        /*
         * ============================================================
         * GROUP ITEM BERDASARKAN HS CODE
         * ============================================================
         */
        $groups = collect($invoice->items)
            ->groupBy(function ($item) {
                return trim((string) ($item->hs_code ?? ''));
            })
            ->map(function ($items, $hsCode) {

                $customDescription = $items
                    ->map(function ($item) {
                        return trim(
                            (string) ($item->desc_custome ?? '')
                        );
                    })
                    ->filter()
                    ->first();

                $normalDescription = $items
                    ->map(function ($item) {
                        return trim(
                            (string) ($item->description ?? '')
                        );
                    })
                    ->filter()
                    ->first();

                $qtyPcs = $items->sum(function ($item) {
                    return (float) ($item->qty_pcs ?? 0);
                });

                $qtyBox = $items->sum(function ($item) {
                    return (float) ($item->qty_box ?? 0);
                });

                $netWeight = $items->sum(function ($item) {
                    return (float) ($item->net_weight ?? 0);
                });

                $grossWeight = $items->sum(function ($item) {
                    return (float) ($item->gross_weight ?? 0);
                });

                $cbm = $items->sum(function ($item) {
                    return (float) ($item->total_cbm ?? 0);
                });

                $totalValue = $items->sum(function ($item) {
                    return (float) ($item->total_price ?? 0);
                });

                $unitPrice = $qtyPcs > 0
                    ? $totalValue / $qtyPcs
                    : 0;

                return [
                    'hs_code' => $hsCode !== ''
                        ? $hsCode
                        : '-',

                    'description' => $customDescription
                        ?: ($normalDescription ?: '-'),

                    'qty_pcs' => $qtyPcs,
                    'qty_box' => $qtyBox,
                    'net_weight' => $netWeight,
                    'gross_weight' => $grossWeight,
                    'cbm' => $cbm,
                    'total_value' => $totalValue,
                    'unit_price' => $unitPrice,
                ];
            })
            ->values();

        if ($groups->isEmpty()) {
            $groups = collect([
                [
                    'hs_code' => '-',
                    'description' => '-',
                    'qty_pcs' => 0,
                    'qty_box' => 0,
                    'net_weight' => 0,
                    'gross_weight' => 0,
                    'cbm' => 0,
                    'total_value' => 0,
                    'unit_price' => 0,
                ],
            ]);
        }

        /*
         * ============================================================
         * HEADER
         * ============================================================
         */

        // Invoice Number
        $sheet->setCellValue(
            'A8',
            $invoice->invoice_no ?? ''
        );

        // Buyer
        $sheet->setCellValue(
            'A17',
            $invoice->buyer ?? ''
        );

        /*
         * Buyer Address
         * Maksimal 3 baris mengikuti template.
         */
        $address = trim(
            (string) ($invoice->buyer_address ?? '')
        );

        $addressLines = preg_split(
            "/\r\n|\n|\r/",
            $address
        );

        $addressLines = array_values(
            array_filter(
                $addressLines,
                function ($line) {
                    return trim((string) $line) !== '';
                }
            )
        );

        for ($i = 0; $i < 3; $i++) {
            $addressValue = $addressLines[$i] ?? '';

            $sheet->setCellValue(
                'A' . (18 + $i),
                $addressValue
            );

            $sheet
                ->getStyle('A' . (18 + $i))
                ->getAlignment()
                ->setWrapText(true);
        }

        /*
         * Release Date
         *
         * Template memiliki Date pada G11 dan G12.
         * Keduanya diisi release_date agar tidak ada tanggal kosong.
         */
        $releasedDate = '';

        if (!empty($invoice->release_date)) {
            $releasedDate = Carbon::parse(
                $invoice->release_date
            )->format('d/m/Y');
        }

        $sheet->setCellValue(
            'G11',
            $releasedDate
        );

        $sheet->setCellValue(
            'G12',
            $releasedDate
        );

        // Container Type
        $sheet->setCellValue(
            'G13',
            $invoice->container_type ?? ''
        );

        // Vessel Name
        $sheet->setCellValue(
            'G14',
            $invoice->vessel_name ?? ''
        );

        // Mother Vessel
        $sheet->setCellValue(
            'G15',
            $invoice->mother_vessel
            ?? $invoice->vessel_name
            ?? ''
        );

        /*
         * Container / Seal
         */
        $containerSeal = implode(
            ' / ',
            array_filter([
                trim(
                    (string) (
                        $invoice->container_no ?? ''
                    )
                ),
                trim(
                    (string) (
                        $invoice->seal_no ?? ''
                    )
                ),
            ])
        );

        $sheet->setCellValue(
            'G16',
            $containerSeal
        );

        // Port of Loading
        $sheet->setCellValue(
            'G17',
            $invoice->port_loading ?? ''
        );

        // Port of Discharge
        $sheet->setCellValue(
            'G18',
            $invoice->port_discharge ?? ''
        );

        // ETD
        $sheet->setCellValue(
            'G19',
            !empty($invoice->etd)
            ? Carbon::parse($invoice->etd)
                ->format('d/m/Y')
            : ''
        );

        // ETA
        $sheet->setCellValue(
            'G20',
            !empty($invoice->eta)
            ? Carbon::parse($invoice->eta)
                ->format('d/m/Y')
            : ''
        );

        /*
         * ============================================================
         * TABLE
         * ============================================================
         *
         * Template:
         *
         * A = NO.
         * B = HS CODES
         * C = DESCRIPTION
         * D = KEMASAN
         * E:F = QUANTITY
         * G = UNIT PRICE
         * H = TOTAL VALUE
         * I = CBM
         *
         * Tidak menambah kolom J/K.
         */

        $firstDataRow = 23;
        $originalTotalRow = 24;
        $itemCount = $groups->count();

        /*
         * Jika item lebih dari satu, tambahkan baris
         * sebelum TOTAL.
         */
        if ($itemCount > 1) {
            $sheet->insertNewRowBefore(
                $originalTotalRow,
                $itemCount - 1
            );

            /*
             * Copy style dari row template 23
             * ke setiap row tambahan.
             */
            for (
                $row = $firstDataRow + 1;
                $row < $firstDataRow + $itemCount;
                $row++
            ) {
                for ($col = 1; $col <= 9; $col++) {

                    $source = $sheet->getCellByColumnAndRow(
                        $col,
                        $firstDataRow
                    );

                    $target = $sheet->getCellByColumnAndRow(
                        $col,
                        $row
                    );

                    $sheet->duplicateStyle(
                        $sheet->getStyle(
                            $source->getCoordinate()
                        ),
                        $target->getCoordinate()
                    );
                }

                $templateHeight = $sheet
                    ->getRowDimension($firstDataRow)
                    ->getRowHeight();

                if ($templateHeight !== null) {
                    $sheet
                        ->getRowDimension($row)
                        ->setRowHeight($templateHeight);
                }
            }
        }

        /*
         * ============================================================
         * ISI DATA
         * ============================================================
         */
        foreach ($groups as $index => $item) {

            $row = $firstDataRow + $index;

            /*
             * NO
             */
            $sheet->setCellValue(
                "A{$row}",
                $index + 1
            );

            /*
             * HS CODE
             */
            $sheet->setCellValue(
                "B{$row}",
                $item['hs_code']
            );

            /*
             * DESCRIPTION
             */
            $sheet->setCellValue(
                "C{$row}",
                $item['description']
            );

            /*
             * KEMASAN
             */
            $qtyBox = (float) $item['qty_box'];

            /*
             * Jika angka bulat, jangan tampilkan .0
             */
            $qtyBoxDisplay = fmod($qtyBox, 1.0) === 0.0
                ? number_format(
                    $qtyBox,
                    0,
                    '',
                    ''
                )
                : number_format(
                    $qtyBox,
                    2,
                    '.',
                    ''
                );

            $sheet->setCellValue(
                "D{$row}",
                $qtyBoxDisplay . ' CTNS'
            );

            /*
             * QUANTITY
             *
             * Pakai integer supaya:
             * 71.  -> 71
             * 105. -> 105
             * 176. -> 176
             */
            $qtyPcs = (float) $item['qty_pcs'];

            if (fmod($qtyPcs, 1.0) === 0.0) {
                $qtyPcs = (int) $qtyPcs;
            }

            $sheet->setCellValue(
                "E{$row}",
                $qtyPcs
            );

            /*
             * PCS
             */
            $sheet->setCellValue(
                "F{$row}",
                'PCS'
            );

            /*
             * UNIT PRICE
             *
             * Formula:
             * TOTAL VALUE / QUANTITY
             */
            $sheet->setCellValue(
                "G{$row}",
                "=IFERROR(H{$row}/E{$row},0)"
            );

            /*
             * TOTAL VALUE
             */
            $sheet->setCellValue(
                "H{$row}",
                $item['total_value']
            );

            /*
             * CBM
             */
            $sheet->setCellValue(
                "I{$row}",
                $item['cbm']
            );

            /*
             * Wrap description
             */
            $sheet
                ->getStyle("C{$row}")
                ->getAlignment()
                ->setWrapText(true);
        }

        /*
         * ============================================================
         * TOTAL
         * ============================================================
         */
        $totalRow =
            $firstDataRow +
            $groups->count();

        $totalBox = $groups->sum(
            'qty_box'
        );

        $totalPcs = $groups->sum(
            'qty_pcs'
        );

        $totalNet = $groups->sum(
            'net_weight'
        );

        $totalGross = $groups->sum(
            'gross_weight'
        );

        $totalCbm = $groups->sum(
            'cbm'
        );

        $totalValue = $groups->sum(
            'total_value'
        );

        /*
         * TOTAL label
         */
        $sheet->setCellValue(
            "A{$totalRow}",
            'TOTAL'
        );

        /*
         * TOTAL KEMASAN
         */
        if (fmod($totalBox, 1.0) === 0.0) {
            $totalBoxDisplay = number_format(
                $totalBox,
                0,
                '',
                ''
            );
        } else {
            $totalBoxDisplay = number_format(
                $totalBox,
                2,
                '.',
                ''
            );
        }

        $sheet->setCellValue(
            "D{$totalRow}",
            $totalBoxDisplay . ' CTNS'
        );

        /*
         * TOTAL QUANTITY
         *
         * Jangan tampilkan titik/desimal.
         */
        if (fmod($totalPcs, 1.0) === 0.0) {
            $totalPcs = (int) $totalPcs;
        }

        $sheet->setCellValue(
            "E{$totalRow}",
            $totalPcs
        );

        /*
         * PCS
         */
        $sheet->setCellValue(
            "F{$totalRow}",
            'PCS'
        );

        /*
         * TOTAL UNIT PRICE
         */
        $sheet->setCellValue(
            "G{$totalRow}",
            "=IFERROR(H{$totalRow}/E{$totalRow},0)"
        );

        /*
         * TOTAL VALUE
         */
        $sheet->setCellValue(
            "H{$totalRow}",
            $totalValue
        );

        /*
         * TOTAL CBM
         */
        $sheet->setCellValue(
            "I{$totalRow}",
            $totalCbm
        );

        /*
         * ============================================================
         * NUMBER FORMAT
         * ============================================================
         */

        /*
         * Quantity:
         * 71
         * 105
         * 176
         *
         * Tidak boleh:
         * 71.
         * 105.
         * 176.
         */
        $sheet
            ->getStyle(
                "E{$firstDataRow}:E{$totalRow}"
            )
            ->getNumberFormat()
            ->setFormatCode('0');

        /*
         * Unit Price
         *
         * Menggunakan symbol $
         *
         * Contoh:
         * $118.30
         */
        $sheet
            ->getStyle(
                "G{$firstDataRow}:G{$totalRow}"
            )
            ->getNumberFormat()
            ->setFormatCode(
                '$#,##0.00'
            );

        /*
         * Total Value
         *
         * Menggunakan symbol $
         *
         * Contoh:
         * $8,399.00
         */
        $sheet
            ->getStyle(
                "H{$firstDataRow}:H{$totalRow}"
            )
            ->getNumberFormat()
            ->setFormatCode(
                '$#,##0.00'
            );

        /*
         * CBM
         *
         * 3 angka desimal:
         * 1.430
         * 1.740
         * 3.170
         */
        $sheet
            ->getStyle(
                "I{$firstDataRow}:I{$totalRow}"
            )
            ->getNumberFormat()
            ->setFormatCode(
                '#,##0.000'
            );

        /*
         * ============================================================
         * ALIGNMENT
         * ============================================================
         */
        $sheet
            ->getStyle(
                "A{$firstDataRow}:I{$totalRow}"
            )
            ->getAlignment()
            ->setVertical(
                \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER
            );

        $sheet
            ->getStyle(
                "D{$firstDataRow}:I{$totalRow}"
            )
            ->getAlignment()
            ->setHorizontal(
                \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_RIGHT
            );

        $sheet
            ->getStyle(
                "C{$firstDataRow}:C{$totalRow}"
            )
            ->getAlignment()
            ->setWrapText(true);

        /*
         * ============================================================
         * PAGE SETUP
         * ============================================================
         */
        $pageSetup = $sheet->getPageSetup();

        $pageSetup->setPaperSize(
            PageSetup::PAPERSIZE_A4
        );

        $pageSetup->setOrientation(
            PageSetup::ORIENTATION_PORTRAIT
        );

        $pageSetup->setFitToWidth(1);
        $pageSetup->setFitToHeight(0);

        $pageSetup->setHorizontalCentered(true);

        /*
         * Header table tetap mengikuti template.
         * TIDAK menggunakan freeze pane.
         */
        $pageSetup->setRowsToRepeatAtTopByStartAndEnd(
            22,
            22
        );

        /*
         * Print area hanya A:I.
         */
        $pageSetup->setPrintArea(
            "A1:I" . ($totalRow + 8)
        );

        /*
         * Margin
         */
        $sheet
            ->getPageMargins()
            ->setTop(0.25)
            ->setBottom(0.25)
            ->setLeft(0.25)
            ->setRight(0.25);

        /*
         * ============================================================
         * ADD PACKING LIST SHEET
         * ============================================================
         *
         * PL dibuat langsung pada workbook CI.
         * Tidak menggunakan PackingListExport::buildExcelSheet()
         * agar worksheet template CI tidak ikut terpengaruh.
         */
        $plSheet = $spreadsheet->createSheet();
        $plSheet->setTitle('PL');

        $this->buildPackingListSheet($plSheet, $invoice);

        // CI tetap menjadi sheet aktif saat file dibuka.
        $spreadsheet->setActiveSheetIndex(
            $spreadsheet->getIndex($sheet)
        );

        /*
         * ============================================================
         * FILE NAME
         * ============================================================
         */
        $fileName =
            'Custom_Commercial_Invoice_' .
            str_replace(
                ['/', '\\', ' '],
                '_',
                $invoice->invoice_no
                ?? $invoice->id
            ) .
            '.xlsx';

        /*
         * ============================================================
         * DOWNLOAD
         * ============================================================
         */
        $writer = new Xlsx(
            $spreadsheet
        );

        return new StreamedResponse(
            function () use ($writer) {
                $writer->save(
                    'php://output'
                );
            },
            200,
            [
                'Content-Type' =>
                    'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',

                'Content-Disposition' =>
                    'attachment; filename="' .
                    $fileName .
                    '"',

                'Cache-Control' =>
                    'max-age=0',

                'Pragma' =>
                    'public',
            ]
        );
    }

    /**
     * Build Packing List sheet for the Custom Commercial Invoice workbook.
     *
     * Layout mengikuti PL yang digunakan di Export Department:
     * - header perusahaan
     * - shipper / buyer
     * - shipment information
     * - NO / HS NO. / DESCRIPTION / KEMASAN / QUANTITY / NETT WEIGHT / GROSS WEIGHT
     * - total
     * - signature Export Department
     */
    protected function buildPackingListSheet($sheet, ExportIpl $invoice)
    {
        $sheet->setShowGridLines(false);

        // ---------------------------------------------------------
        // COLUMN WIDTHS
        // ---------------------------------------------------------
        $widths = [
            'A' => 7,   // NO
            'B' => 18,  // HS NO.
            'C' => 34,  // DESCRIPTION
            'D' => 14,  // KEMASAN
            'E' => 11,  // QUANTITY
            'F' => 10,  // UNIT
            'G' => 16,  // NETT WEIGHT
            'H' => 17,  // GROSS WEIGHT
        ];

        foreach ($widths as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }

        // ---------------------------------------------------------
        // GENERAL STYLE
        // ---------------------------------------------------------
        $font = $sheet->getParent()->getDefaultStyle()->getFont();
        $font->setName('Arial')->setSize(9);

        // ---------------------------------------------------------
        // COMPANY HEADER
        // ---------------------------------------------------------
        $sheet->mergeCells('A1:H1');
        $sheet->setCellValue('A1', 'PACKING LIST');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getRowDimension(1)->setRowHeight(24);

        $sheet->mergeCells('A2:H2');
        $sheet->setCellValue(
            'A2',
            'PL-' . ($invoice->invoice_no ?? $invoice->id)
        );
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(11);
        $sheet->getStyle('A2')->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $logoPath = public_path('assets/images/logo.png');
        if (file_exists($logoPath)) {
            $logo = new Drawing();
            $logo->setName('PT Newwicker Indonesia');
            $logo->setDescription('PT Newwicker Indonesia Logo');
            $logo->setPath($logoPath);
            $logo->setCoordinates('A1');
            $logo->setHeight(52);
            $logo->setOffsetX(4);
            $logo->setOffsetY(2);
            $logo->setWorksheet($sheet);
        }

        // ---------------------------------------------------------
        // SHIPPER / SHIPMENT INFORMATION
        // ---------------------------------------------------------
        $sheet->mergeCells('A4:C4');
        $sheet->setCellValue('A4', 'SHIPPER :');
        $sheet->getStyle('A4:C4')->getFont()->setBold(true);
        $sheet->getStyle('A4:C4')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('D9D9D9');

        $sheet->mergeCells('A5:C5');
        $sheet->setCellValue('A5', 'PT. NEWWICKER INDONESIA');
        $sheet->getStyle('A5')->getFont()->setBold(true);

        $sheet->mergeCells('A6:C6');
        $sheet->setCellValue('A6', 'JL. KISABA LANANG RT. 019 RW. 002,');
        $sheet->mergeCells('A7:C7');
        $sheet->setCellValue('A7', 'BODELOR, PLUMBON, CIREBON 45155');
        $sheet->mergeCells('A8:C8');
        $sheet->setCellValue('A8', 'INDONESIA');
        $sheet->mergeCells('A9:C9');
        $sheet->setCellValue('A9', 'PHONE : 0231-325880');

        // Buyer
        $sheet->mergeCells('A10:C10');
        $sheet->setCellValue('A10', 'BUYER :');
        $sheet->getStyle('A10:C10')->getFont()->setBold(true);
        $sheet->getStyle('A10:C10')->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('D9D9D9');

        $sheet->mergeCells('A11:C11');
        $sheet->setCellValue('A11', strtoupper((string) ($invoice->buyer ?? '')));
        $sheet->getStyle('A11')->getFont()->setBold(true);

        $buyerAddress = trim((string) ($invoice->buyer_address ?? ''));
        $addressLines = preg_split('/\r\n|\n|\r/', $buyerAddress);
        $addressLines = array_values(array_filter(
            $addressLines,
            fn ($line) => trim($line) !== ''
        ));

        for ($i = 0; $i < 3; $i++) {
            $row = 12 + $i;
            $sheet->mergeCells("A{$row}:C{$row}");
            $sheet->setCellValue("A{$row}", $addressLines[$i] ?? '');
        }

        // Right-side shipment details
        $formatDate = function ($value) {
            if (empty($value)) {
                return '';
            }

            try {
                return Carbon::parse($value)->format('d F Y');
            } catch (\Throwable $e) {
                return (string) $value;
            }
        };

        $createdDate = $formatDate($invoice->created_at ?? null);
        $etd = $formatDate($invoice->etd ?? null);
        $eta = $formatDate($invoice->eta ?? null);

        $rightDetails = [
            5  => ['Date', $createdDate],
            6  => ['Date', $createdDate],
            7  => ['Container Type', $invoice->container_type ?? ''],
            8  => ['Vessel Name', $invoice->vessel_name ?? ''],
            9  => ['Mother Vessel', $invoice->mother_vessel ?? ''],
            10 => ['Container 20\'FT / Seal #.',
                trim(($invoice->container_no ?? '') . ' / ' . ($invoice->seal_no ?? ''))],
            11 => ['Port of Loading', $invoice->port_loading ?? ''],
            12 => ['Port of Discharge', $invoice->port_discharge ?? ''],
            13 => ['ETD', $etd],
            14 => ['ETA', $eta],
        ];

        foreach ($rightDetails as $row => [$label, $value]) {
            $sheet->mergeCells("D{$row}:E{$row}");
            $sheet->setCellValue("D{$row}", $label);
            $sheet->getStyle("D{$row}")->getFont()->setBold(true);
            $sheet->getStyle("D{$row}")->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            $sheet->mergeCells("F{$row}:H{$row}");
            $sheet->setCellValue("F{$row}", $value);
            $sheet->getStyle("F{$row}")->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_LEFT)
                ->setWrapText(true);
        }

        // ---------------------------------------------------------
        // PREPARE PL DATA
        // ---------------------------------------------------------
        // Satu HS code = satu baris PL, sama seperti grouping CI.
        $plGroups = collect($invoice->items)
            ->groupBy(function ($item) {
                return trim((string) ($item->hs_code ?? ''));
            })
            ->map(function ($items, $hsCode) {
                // PL wajib menggunakan Custom Description.
                $description = $items
                    ->map(function ($item) {
                        return trim((string) ($item->desc_custome ?? ''));
                    })
                    ->filter()
                    ->first();

                $qtyBox = $items->sum(function ($item) {
                    return (float) ($item->qty_box ?? $item->qty_pcs ?? 0);
                });

                $netWeight = $items->sum(function ($item) {
                    $qty = (float) ($item->qty_box ?? $item->qty_pcs ?? 0);
                    return (float) ($item->net_weight ?? 0) * $qty;
                });

                $grossWeight = $items->sum(function ($item) {
                    $qty = (float) ($item->qty_box ?? $item->qty_pcs ?? 0);
                    return (float) ($item->gross_weight ?? 0) * $qty;
                });

                return [
                    'hs_code' => $hsCode ?: '-',
                    'description' => $description ?: '-',
                    'qty_box' => $qtyBox,
                    'net_weight' => $netWeight,
                    'gross_weight' => $grossWeight,
                ];
            })
            ->values();

        if ($plGroups->isEmpty()) {
            $plGroups = collect([
                [
                    'hs_code' => '-',
                    'description' => '-',
                    'qty_box' => 0,
                    'net_weight' => 0,
                    'gross_weight' => 0,
                ],
            ]);
        }

        // ---------------------------------------------------------
        // TABLE HEADER
        // ---------------------------------------------------------
        $headerRow = 17;
        $headers = [
            'A' => 'NO.',
            'B' => 'HS NO.',
            'C' => 'DESCRIPTION',
            'D' => 'KEMASAN',
            'E' => 'QUANTITY',
            'F' => '',
            'G' => 'NETT WEIGHT',
            'H' => 'GROSS WEIGHT',
        ];

        foreach ($headers as $column => $value) {
            $sheet->setCellValue("{$column}{$headerRow}", $value);
            $sheet->getStyle("{$column}{$headerRow}")
                ->getFont()->setBold(true);
            $sheet->getStyle("{$column}{$headerRow}")
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ->setVertical(Alignment::VERTICAL_CENTER)
                ->setWrapText(true);
            $sheet->getStyle("{$column}{$headerRow}")
                ->getFill()
                ->setFillType(Fill::FILL_SOLID)
                ->getStartColor()->setRGB('D9D9D9');
        }

        $sheet->mergeCells("E{$headerRow}:F{$headerRow}");
        $sheet->setCellValue("E{$headerRow}", 'QUANTITY');

        // ---------------------------------------------------------
        // TABLE DATA
        // ---------------------------------------------------------
        $row = $headerRow + 1;

        foreach ($plGroups as $index => $item) {
            $sheet->setCellValue("A{$row}", $index + 1);
            $sheet->setCellValue("B{$row}", $item['hs_code']);
            $sheet->setCellValue("C{$row}", $item['description']);
            $sheet->setCellValue(
                "D{$row}",
                number_format($item['qty_box'], 0, '.', '') . ' CTNS'
            );
            $sheet->setCellValue("E{$row}", $item['qty_box']);
            $sheet->setCellValue("F{$row}", 'PCS');
            $sheet->setCellValue("G{$row}", $item['net_weight']);
            $sheet->setCellValue("H{$row}", $item['gross_weight']);

            $sheet->getStyle("A{$row}:H{$row}")
                ->getAlignment()
                ->setVertical(Alignment::VERTICAL_CENTER);

            $sheet->getStyle("A{$row}")
                ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("B{$row}")
                ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("C{$row}")
                ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT)
                ->setWrapText(true);
            $sheet->getStyle("D{$row}:F{$row}")
                ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("G{$row}:H{$row}")
                ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            $sheet->getStyle("E{$row}")
                ->getNumberFormat()->setFormatCode('0');
            $sheet->getStyle("G{$row}:H{$row}")
                ->getNumberFormat()->setFormatCode('#,##0.00');

            $sheet->getStyle("A{$row}:H{$row}")
                ->getBorders()->getAllBorders()
                ->setBorderStyle(Border::BORDER_THIN);

            $sheet->getRowDimension($row)->setRowHeight(30);
            $row++;
        }

        // ---------------------------------------------------------
        // TOTAL
        // ---------------------------------------------------------
        $totalRow = $row;
        $sheet->mergeCells("A{$totalRow}:C{$totalRow}");
        $sheet->setCellValue("A{$totalRow}", 'TOTAL');
        $sheet->getStyle("A{$totalRow}")->getFont()->setBold(true);
        $sheet->getStyle("A{$totalRow}")->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        $firstDataRow = $headerRow + 1;
        $lastDataRow = $totalRow - 1;

        $sheet->setCellValue("D{$totalRow}",
            '=SUM(E' . $firstDataRow . ':E' . $lastDataRow . ')&" CTNS"'
        );
        $sheet->setCellValue("E{$totalRow}",
            '=SUM(E' . $firstDataRow . ':E' . $lastDataRow . ')'
        );
        $sheet->setCellValue("F{$totalRow}", 'PCS');
        $sheet->setCellValue("G{$totalRow}",
            '=SUM(G' . $firstDataRow . ':G' . $lastDataRow . ')'
        );
        $sheet->setCellValue("H{$totalRow}",
            '=SUM(H' . $firstDataRow . ':H' . $lastDataRow . ')'
        );

        $sheet->getStyle("A{$totalRow}:H{$totalRow}")
            ->getFont()->setBold(true);
        $sheet->getStyle("A{$totalRow}:H{$totalRow}")
            ->getBorders()->getTop()
            ->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("A{$totalRow}:H{$totalRow}")
            ->getBorders()->getBottom()
            ->setBorderStyle(Border::BORDER_DOUBLE);
        $sheet->getStyle("D{$totalRow}:F{$totalRow}")
            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("G{$totalRow}:H{$totalRow}")
            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle("G{$totalRow}:H{$totalRow}")
            ->getNumberFormat()->setFormatCode('#,##0.00');

        // ---------------------------------------------------------
        // PREPARED BY
        // ---------------------------------------------------------
        $preparedRow = $totalRow + 3;
        $sheet->mergeCells("A{$preparedRow}:C{$preparedRow}");
        $sheet->setCellValue("A{$preparedRow}", 'Dibuat Oleh,');
        $sheet->getStyle("A{$preparedRow}")->getFont()->setBold(true);

        $signaturePath = public_path('assets/images/sofian.jpg');
        if (file_exists($signaturePath)) {
            $signature = new Drawing();
            $signature->setName('Sofian Signature');
            $signature->setDescription('Prepared By - Sofian');
            $signature->setPath($signaturePath);
            $signature->setCoordinates("A" . ($preparedRow + 1));
            $signature->setHeight(60);
            $signature->setOffsetX(10);
            $signature->setOffsetY(4);
            $signature->setWorksheet($sheet);
        }

        $nameRow = $preparedRow + 6;
        $sheet->mergeCells("A{$nameRow}:C{$nameRow}");
        $sheet->setCellValue("A{$nameRow}", 'Sofian');
        $sheet->getStyle("A{$nameRow}")->getFont()->setBold(true);

        $departmentRow = $nameRow + 1;
        $sheet->mergeCells("A{$departmentRow}:C{$departmentRow}");
        $sheet->setCellValue("A{$departmentRow}", 'Export Department');

        // ---------------------------------------------------------
        // PAGE SETUP
        // ---------------------------------------------------------
        $pageSetup = $sheet->getPageSetup();
        $pageSetup->setPaperSize(PageSetup::PAPERSIZE_A4);
        $pageSetup->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
        $pageSetup->setFitToWidth(1);
        $pageSetup->setFitToHeight(0);
        $pageSetup->setHorizontalCentered(true);
        $pageSetup->setPrintArea('A1:H' . ($departmentRow + 2));

        $sheet->getPageMargins()
            ->setTop(0.25)
            ->setBottom(0.25)
            ->setLeft(0.25)
            ->setRight(0.25);
    }

}
