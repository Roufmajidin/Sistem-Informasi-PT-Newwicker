<?php

namespace App\Exports;

use App\Models\PaymentRequest;
use App\Models\User;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class ExportAllPaymentRequest
{
    public static function export()
    {
        /*
        |--------------------------------------------------------------------------
        | AMBIL SEMUA PAYMENT REQUEST
        |--------------------------------------------------------------------------
        */

        $paymentRequests = PaymentRequest::with('spk')
            ->orderBy('id')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | GROUP BERDASARKAN NO SPK
        |--------------------------------------------------------------------------
        */

        $groups = [];


        foreach ($paymentRequests as $request) {

            /*
            |--------------------------------------------------------------------------
            | SNAPSHOT
            |--------------------------------------------------------------------------
            */

            $snapshot = $request->spk_snapshot;

            if (is_string($snapshot)) {

                $snapshot = json_decode(
                    $snapshot,
                    true
                );
            }

            if (!is_array($snapshot)) {

                $snapshot = [];
            }


            /*
            |--------------------------------------------------------------------------
            | NO SPK
            |--------------------------------------------------------------------------
            */

            $noSpk =
                trim(
                    $snapshot['no_spk']
                    ?? ''
                );


            /*
            |--------------------------------------------------------------------------
            | FALLBACK KE SPK
            |--------------------------------------------------------------------------
            */

            if ($noSpk === '' && $request->spk) {

                $spkData = $request->spk->data;

                if (is_string($spkData)) {

                    $spkData = json_decode(
                        $spkData,
                        true
                    );
                }

                if (is_array($spkData)) {

                    $noSpk =
                        trim(
                            $spkData['no_spk']
                            ?? ''
                        );
                }
            }


            /*
            |--------------------------------------------------------------------------
            | KALAU TIDAK ADA NO SPK
            |--------------------------------------------------------------------------
            */

            if ($noSpk === '') {

                $noSpk =
                    'TANPA-SPK';
            }


            /*
            |--------------------------------------------------------------------------
            | GROUP
            |--------------------------------------------------------------------------
            */

            $groupKey =
                strtoupper(
                    preg_replace(
                        '/\s+/',
                        ' ',
                        $noSpk
                    )
                );


            if (!isset($groups[$groupKey])) {

                $groups[$groupKey] = [
                    'no_spk' => $noSpk,
                    'items' => [],
                ];
            }


            /*
            |--------------------------------------------------------------------------
            | MASUKKAN PAYMENT REQUEST
            |--------------------------------------------------------------------------
            */

            $groups[$groupKey]['items'][] = [
                'request' => $request,
                'snapshot' => $snapshot,
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | CREATE WORKBOOK
        |--------------------------------------------------------------------------
        */

        $spreadsheet =
            new Spreadsheet();


        /*
        |--------------------------------------------------------------------------
        | HAPUS SHEET DEFAULT
        |--------------------------------------------------------------------------
        */

        $defaultSheet =
            $spreadsheet->getActiveSheet();

        $spreadsheet->removeSheetByIndex(
            $spreadsheet->getIndex(
                $defaultSheet
            )
        );


        /*
        |--------------------------------------------------------------------------
        | BUAT SHEET PER SPK
        |--------------------------------------------------------------------------
        */

        foreach ($groups as $group) {

            self::createSheet(
                $spreadsheet,
                $group['no_spk'],
                $group['items']
            );
        }


        /*
        |--------------------------------------------------------------------------
        | KALAU TIDAK ADA DATA
        |--------------------------------------------------------------------------
        */

        if (
            $spreadsheet->getSheetCount()
            === 0
        ) {

            $sheet =
                $spreadsheet->createSheet();

            $sheet->setTitle(
                'Tidak Ada Data'
            );

            $sheet->setCellValue(
                'A1',
                'Tidak ada Payment Request.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | FILE NAME
        |--------------------------------------------------------------------------
        */

        $filename =
            'All-Payment-Request-' .
            now()->format('YmdHis') .
            '.xlsx';


        /*
        |--------------------------------------------------------------------------
        | WRITER
        |--------------------------------------------------------------------------
        */

        $writer =
            new Xlsx(
                $spreadsheet
            );


        /*
        |--------------------------------------------------------------------------
        | DOWNLOAD
        |--------------------------------------------------------------------------
        */

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

                'Cache-Control' =>
                    'max-age=0',
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | CREATE SHEET
    |--------------------------------------------------------------------------
    */

    private static function createSheet(
        Spreadsheet $spreadsheet,
        string $noSpk,
        array $items
    ) {

        /*
        |--------------------------------------------------------------------------
        | SHEET NAME
        |--------------------------------------------------------------------------
        */

        $sheetName =
            self::makeSheetName(
                $noSpk
            );


        /*
        |--------------------------------------------------------------------------
        | CREATE
        |--------------------------------------------------------------------------
        */

        $sheet =
            $spreadsheet->createSheet();

        $sheet->setTitle(
            $sheetName
        );


        /*
        |--------------------------------------------------------------------------
        | TITLE
        |--------------------------------------------------------------------------
        */

        $sheet->setCellValue(
            'A1',
            'PAYMENT REQUEST'
        );

        $sheet->mergeCells(
            'A1:K1'
        );


        /*
        |--------------------------------------------------------------------------
        | NO SPK
        |--------------------------------------------------------------------------
        */

        $sheet->setCellValue(
            'A3',
            'NO. SPK'
        );

        $sheet->setCellValue(
            'C3',
            $noSpk
        );

        $sheet->mergeCells(
            'C3:K3'
        );


        /*
        |--------------------------------------------------------------------------
        | SUPPLIER
        |--------------------------------------------------------------------------
        */

        $firstItem =
            $items[0] ?? null;

        $firstSnapshot =
            $firstItem['snapshot'] ?? [];


        $supplier =
            $firstSnapshot['sup']
            ?? '';


        $sheet->setCellValue(
            'A4',
            'SUPPLIER'
        );

        $sheet->setCellValue(
            'C4',
            $supplier
        );

        $sheet->mergeCells(
            'C4:K4'
        );


        /*
        |--------------------------------------------------------------------------
        | HEADER
        |--------------------------------------------------------------------------
        */

        $headers = [

            'A6' => 'No.',

            'B6' => 'No. PR',

            'C6' => 'Date',

            'D6' => 'No. PO',

            'E6' => 'No. SPK',

            'F6' => 'Ket.',

            'G6' => 'Harga Asli',

            'H6' => 'Adjustment Finance',

            'I6' => 'Adjustment By Finance',

            'J6' => 'Total Harga',

            'K6' => 'Status',
        ];


        foreach (
            $headers
            as $cell => $value
        ) {

            $sheet->setCellValue(
                $cell,
                $value
            );
        }


        /*
        |--------------------------------------------------------------------------
        | STYLE HEADER
        |--------------------------------------------------------------------------
        */

        $sheet
            ->getStyle('A6:K6')
            ->getFont()
            ->setBold(true);


        $sheet
            ->getStyle('A6:K6')
            ->getAlignment()
            ->setHorizontal(
                Alignment::HORIZONTAL_CENTER
            )
            ->setVertical(
                Alignment::VERTICAL_CENTER
            )
            ->setWrapText(true);


        $sheet
            ->getStyle('A6:K6')
            ->getFill()
            ->setFillType(
                Fill::FILL_SOLID
            )
            ->getStartColor()
            ->setARGB(
                'D9EAD3'
            );


        $sheet
            ->getStyle('A6:K6')
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(
                Border::BORDER_THIN
            );


        /*
        |--------------------------------------------------------------------------
        | DATA
        |--------------------------------------------------------------------------
        */

        $startRow = 7;

        $row = $startRow;

        $no = 1;

        $grandTotal = 0;


        foreach ($items as $item) {

            $request =
                $item['request'];

            $snapshot =
                $item['snapshot'];


            /*
            |--------------------------------------------------------------------------
            | PAYMENT SNAPSHOT
            |--------------------------------------------------------------------------
            */

            $payment =
                collect(
                    $snapshot['payments'] ?? []
                )->first(
                    function ($payment) use ($request) {

                        return (string) (
                            $payment['payment_id']
                            ?? ''
                        )
                        ===
                        (string) (
                            $request->payment_id
                        );
                    }
                );


            if (!$payment) {

                $payment = [];
            }


            /*
            |--------------------------------------------------------------------------
            | SPK DATA TERBARU
            |--------------------------------------------------------------------------
            */

            $currentSpkData = [];

            if ($request->spk) {

                $currentSpkData =
                    $request->spk->data;

                if (
                    is_string(
                        $currentSpkData
                    )
                ) {

                    $currentSpkData =
                        json_decode(
                            $currentSpkData,
                            true
                        );
                }

                if (
                    !is_array(
                        $currentSpkData
                    )
                ) {

                    $currentSpkData = [];
                }
            }


            /*
            |--------------------------------------------------------------------------
            | PAYMENT TERBARU
            |--------------------------------------------------------------------------
            */

            $currentPayment =
                collect(
                    $currentSpkData['payments']
                    ?? []
                )->first(
                    function ($payment) use ($request) {

                        return (string) (
                            $payment['payment_id']
                            ?? ''
                        )
                        ===
                        (string) (
                            $request->payment_id
                        );
                    }
                );


            /*
            |--------------------------------------------------------------------------
            | HARGA ASLI
            |--------------------------------------------------------------------------
            */

            $hargaAsli =
                self::toNumber(
                    $payment['amount']
                    ?? 0
                );


            /*
            |--------------------------------------------------------------------------
            | ADJUSTMENT
            |--------------------------------------------------------------------------
            */

            $adjustment = null;

            $adjustmentBy = null;


            if ($currentPayment) {

                $adjustment =
                    $currentPayment['adjustment']
                    ?? null;

                $adjustmentBy =
                    $currentPayment['adjustment_by']
                    ?? null;
            }


            $adjustmentAmount =
                self::toNumber(
                    $adjustment
                );


            /*
            |--------------------------------------------------------------------------
            | ADJUSTMENT BY
            |--------------------------------------------------------------------------
            */

            $adjustmentByName = '';


            if (
                $adjustmentBy !== null
                &&
                $adjustmentBy !== ''
            ) {

                $user =
                    User::find(
                        $adjustmentBy
                    );


                if ($user) {

                    $adjustmentByName =
                        $user->name
                        ?? $user->username
                        ?? $user->email
                        ?? $adjustmentBy;

                } else {

                    $adjustmentByName =
                        $adjustmentBy;
                }
            }


            /*
            |--------------------------------------------------------------------------
            | TOTAL
            |--------------------------------------------------------------------------
            */

            if (
                $adjustmentAmount > 0
            ) {

                $totalHarga =
                    $adjustmentAmount;

            } else {

                $totalHarga =
                    $hargaAsli;
            }


            $grandTotal +=
                $totalHarga;


            /*
            |--------------------------------------------------------------------------
            | KETERANGAN
            |--------------------------------------------------------------------------
            |
            | Contoh:
            | DP
            | Pelunasan
            |
            */

            $keterangan =
                trim(
                    $payment['note']
                    ?? ''
                );


            /*
            |--------------------------------------------------------------------------
            | DATE
            |--------------------------------------------------------------------------
            */

            $paymentDate =
                $payment['date']
                ?? '';


            /*
            |--------------------------------------------------------------------------
            | STATUS
            |--------------------------------------------------------------------------
            */

            $status =
                $request->status
                ?? '';


            /*
            |--------------------------------------------------------------------------
            | WRITE
            |--------------------------------------------------------------------------
            */

            $sheet->setCellValue(
                "A{$row}",
                $no
            );


            $sheet->setCellValue(
                "B{$row}",
                $request->request_no
                    ?? ''
            );


            $sheet->setCellValue(
                "C{$row}",
                $paymentDate
            );


            $sheet->setCellValue(
                "D{$row}",
                $snapshot['no_po']
                    ?? ''
            );


            $sheet->setCellValue(
                "E{$row}",
                $snapshot['no_spk']
                    ?? $noSpk
            );


            $sheet->setCellValue(
                "F{$row}",
                $keterangan
            );


            $sheet->setCellValue(
                "G{$row}",
                $hargaAsli
            );


            $sheet->setCellValue(
                "H{$row}",
                $adjustmentAmount
            );


            $sheet->setCellValue(
                "I{$row}",
                $adjustmentByName
            );


            $sheet->setCellValue(
                "J{$row}",
                $totalHarga
            );


            $sheet->setCellValue(
                "K{$row}",
                $status
            );


            $row++;

            $no++;
        }


        /*
        |--------------------------------------------------------------------------
        | GRAND TOTAL
        |--------------------------------------------------------------------------
        */

        $totalRow =
            $row;


        $sheet->setCellValue(
            "A{$totalRow}",
            'TOTAL'
        );


        $sheet->mergeCells(
            "A{$totalRow}:I{$totalRow}"
        );


        $sheet->setCellValue(
            "J{$totalRow}",
            $grandTotal
        );


        /*
        |--------------------------------------------------------------------------
        | STYLE DATA
        |--------------------------------------------------------------------------
        */

        if ($row > $startRow) {

            $dataStyle =
                $sheet->getStyle(
                    "A{$startRow}:K" .
                    ($row - 1)
                );


            $dataStyle
                ->getBorders()
                ->getAllBorders()
                ->setBorderStyle(
                    Border::BORDER_THIN
                );


            $dataStyle
                ->getAlignment()
                ->setVertical(
                    Alignment::VERTICAL_CENTER
                );


            /*
            |--------------------------------------------------------------------------
            | CENTER
            |--------------------------------------------------------------------------
            */

            $sheet
                ->getStyle(
                    "A{$startRow}:E" .
                    ($row - 1)
                )
                ->getAlignment()
                ->setHorizontal(
                    Alignment::HORIZONTAL_CENTER
                );


            $sheet
                ->getStyle(
                    "K{$startRow}:K" .
                    ($row - 1)
                )
                ->getAlignment()
                ->setHorizontal(
                    Alignment::HORIZONTAL_CENTER
                );


            /*
            |--------------------------------------------------------------------------
            | WRAP
            |--------------------------------------------------------------------------
            */

            $sheet
                ->getStyle(
                    "A{$startRow}:K" .
                    ($row - 1)
                )
                ->getAlignment()
                ->setWrapText(true);
        }


        /*
        |--------------------------------------------------------------------------
        | MONEY FORMAT
        |--------------------------------------------------------------------------
        */

        $sheet
            ->getStyle(
                "G{$startRow}:H{$totalRow}"
            )
            ->getNumberFormat()
            ->setFormatCode(
                '#,##0'
            );


        $sheet
            ->getStyle(
                "J{$startRow}:J{$totalRow}"
            )
            ->getNumberFormat()
            ->setFormatCode(
                '#,##0'
            );


        /*
        |--------------------------------------------------------------------------
        | TOTAL STYLE
        |--------------------------------------------------------------------------
        */

        $sheet
            ->getStyle(
                "A{$totalRow}:K{$totalRow}"
            )
            ->getFont()
            ->setBold(true);


        $sheet
            ->getStyle(
                "A{$totalRow}:K{$totalRow}"
            )
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(
                Border::BORDER_THIN
            );


        /*
        |--------------------------------------------------------------------------
        | COLUMN WIDTH
        |--------------------------------------------------------------------------
        */

        $widths = [

            'A' => 7,

            'B' => 22,

            'C' => 14,

            'D' => 16,

            'E' => 24,

            'F' => 18,

            'G' => 18,

            'H' => 22,

            'I' => 24,

            'J' => 18,

            'K' => 15,
        ];


        foreach ($widths as $column => $width) {

            $sheet
                ->getColumnDimension($column)
                ->setWidth($width);
        }


        /*
        |--------------------------------------------------------------------------
        | ROW HEIGHT
        |--------------------------------------------------------------------------
        */

        $sheet
            ->getRowDimension(1)
            ->setRowHeight(25);


        $sheet
            ->getRowDimension(6)
            ->setRowHeight(40);


        /*
        |--------------------------------------------------------------------------
        | FREEZE
        |--------------------------------------------------------------------------
        */

        $sheet->freezePane(
            'A7'
        );


        /*
        |--------------------------------------------------------------------------
        | GRIDLINES
        |--------------------------------------------------------------------------
        */

        $sheet->setShowGridlines(
            false
        );


        /*
        |--------------------------------------------------------------------------
        | PAGE SETUP
        |--------------------------------------------------------------------------
        */

        $sheet
            ->getPageSetup()
            ->setOrientation(
                PageSetup::ORIENTATION_LANDSCAPE
            );


        $sheet
            ->getPageSetup()
            ->setPaperSize(
                PageSetup::PAPERSIZE_A4
            );


        $sheet
            ->getPageSetup()
            ->setFitToWidth(1);


        $sheet
            ->getPageSetup()
            ->setFitToHeight(0);


        /*
        |--------------------------------------------------------------------------
        | PRINT AREA
        |--------------------------------------------------------------------------
        */

        $sheet
            ->getPageSetup()
            ->setPrintArea(
                "A1:K{$totalRow}"
            );
    }


    /*
    |--------------------------------------------------------------------------
    | SHEET NAME
    |--------------------------------------------------------------------------
    */

    private static function makeSheetName(
        string $name
    ) {

        /*
        |--------------------------------------------------------------------------
        | HILANGKAN KARAKTER TERLARANG EXCEL
        |--------------------------------------------------------------------------
        */

        $name =
            preg_replace(
                '/[\\\\\/\?\*\[\]\:]/',
                '-',
                $name
            );


        /*
        |--------------------------------------------------------------------------
        | TRIM
        |--------------------------------------------------------------------------
        */

        $name =
            trim($name);


        if ($name === '') {

            $name =
                'Sheet';
        }


        /*
        |--------------------------------------------------------------------------
        | MAX 31 CHAR
        |--------------------------------------------------------------------------
        */

        return mb_substr(
            $name,
            0,
            31
        );
    }


    /*
    |--------------------------------------------------------------------------
    | NUMBER FORMAT
    |--------------------------------------------------------------------------
    */

    private static function toNumber(
        $value
    ) {

        if (
            $value === null ||
            $value === ''
        ) {

            return 0;
        }


        if (
            is_int($value) ||
            is_float($value)
        ) {

            return (float) $value;
        }


        $value =
            trim(
                (string) $value
            );


        /*
        |--------------------------------------------------------------------------
        | HAPUS Rp / SPASI
        |--------------------------------------------------------------------------
        */

        $value =
            preg_replace(
                '/[^\d,.\-]/',
                '',
                $value
            );


        if ($value === '') {

            return 0;
        }


        /*
        |--------------------------------------------------------------------------
        | 15.415.351
        |--------------------------------------------------------------------------
        */

        if (
            substr_count(
                $value,
                '.'
            ) > 1
        ) {

            $value =
                str_replace(
                    '.',
                    '',
                    $value
                );


            if (
                strpos(
                    $value,
                    ','
                ) !== false
            ) {

                $value =
                    str_replace(
                        ',',
                        '.',
                        $value
                    );
            }


            return (float) $value;
        }


        /*
        |--------------------------------------------------------------------------
        | 15.415.351,50
        |--------------------------------------------------------------------------
        */

        if (
            strpos($value, '.') !== false
            &&
            strpos($value, ',') !== false
        ) {

            $value =
                str_replace(
                    '.',
                    '',
                    $value
                );


            $value =
                str_replace(
                    ',',
                    '.',
                    $value
                );


            return (float) $value;
        }


        /*
        |--------------------------------------------------------------------------
        | 15415351,50
        |--------------------------------------------------------------------------
        */

        if (
            strpos(
                $value,
                ','
            ) !== false
        ) {

            $value =
                str_replace(
                    ',',
                    '.',
                    $value
                );


            return (float) $value;
        }


        /*
        |--------------------------------------------------------------------------
        | 15.415.351
        |--------------------------------------------------------------------------
        */

        if (
            substr_count(
                $value,
                '.'
            ) === 1
        ) {

            $parts =
                explode(
                    '.',
                    $value
                );


            if (
                isset($parts[1])
                &&
                strlen($parts[1]) === 3
            ) {

                $value =
                    str_replace(
                        '.',
                        '',
                        $value
                    );
            }
        }


        return (float) $value;
    }
}