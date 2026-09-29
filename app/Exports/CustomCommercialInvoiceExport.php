<?php

namespace App\Exports;

use App\Models\ExportIpl;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
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
         * PENTING:
         * Tidak ada freezePane().
         */

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
}
