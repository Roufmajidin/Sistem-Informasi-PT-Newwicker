<?php

namespace App\Exports;

use App\Models\PaymentRequest;
use App\Models\PaymentRequestSaved;
use App\Models\User;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class ExportPengajuanSpk
{
    /**
     * Export satu Draft Request / DR
     */
    public static function export(PaymentRequestSaved $saved)
    {
        /*
        |--------------------------------------------------------------------------
        | CREATE WORKBOOK
        |--------------------------------------------------------------------------
        */

        $spreadsheet = new Spreadsheet();

        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setTitle('Pengajuan SPK');


        /*
        |--------------------------------------------------------------------------
        | PAYMENT REQUEST IDS
        |--------------------------------------------------------------------------
        */

        $paymentRequestIds =
            $saved->payment_request_ids ?? [];

        if (is_string($paymentRequestIds)) {

            $paymentRequestIds =
                json_decode(
                    $paymentRequestIds,
                    true
                ) ?? [];
        }

        if (!is_array($paymentRequestIds)) {

            $paymentRequestIds = [];
        }


        /*
        |--------------------------------------------------------------------------
        | LOAD PAYMENT REQUEST
        |--------------------------------------------------------------------------
        */

        $paymentRequests =
            PaymentRequest::with('spk')
                ->whereIn(
                    'id',
                    $paymentRequestIds
                )
                ->get();


        /*
        |--------------------------------------------------------------------------
        | REQUEST DATE
        |--------------------------------------------------------------------------
        */

        $requestDate = '';

        if ($saved->request_date) {

            $requestDate =
                \Carbon\Carbon::parse(
                    $saved->request_date
                )->format('d/m/Y');
        }


        /*
        |--------------------------------------------------------------------------
        | TITLE
        |--------------------------------------------------------------------------
        */

        $sheet->setCellValue(
            'A1',
            'PENGAJUAN PEMBAYARAN'
        );

        $sheet->mergeCells(
            'A1:K1'
        );


        /*
        |--------------------------------------------------------------------------
        | HEADER INFORMATION
        |--------------------------------------------------------------------------
        */

        $sheet->setCellValue(
            'A5',
            'Date'
        );

        $sheet->setCellValue(
            'C5',
            $requestDate
        );

        $sheet->setCellValue(
            'A6',
            'No.'
        );

        $sheet->setCellValue(
            'C6',
            $saved->request_no ?? ''
        );

        $sheet->setCellValue(
            'E6',
            'Transfer'
        );


        /*
        |--------------------------------------------------------------------------
        | TABLE HEADER
        |--------------------------------------------------------------------------
        */

        $headers = [

            'A7' => 'No.',

            'B7' => 'Date',

            'C7' => 'NO PO',

            'D7' => 'NO. INV / NO. SPK',

            'E7' => 'TYPE BIAYA',

            'F7' => 'Nama Barang/Item/Jasa',

            'G7' => 'QTY',

            'H7' => 'Diajukan',

            'I7' => 'Adjustment Finance',

            'J7' => 'Adjustment By Finance',

            'K7' => 'Total Harga',
        ];


        foreach ($headers as $cell => $value) {

            $sheet->setCellValue(
                $cell,
                $value
            );
        }


        /*
        |--------------------------------------------------------------------------
        | TITLE STYLE
        |--------------------------------------------------------------------------
        */

        $sheet
            ->getStyle('A1:K1')
            ->getFont()
            ->setBold(true)
            ->setSize(14);


        $sheet
            ->getStyle('A1:K1')
            ->getAlignment()
            ->setHorizontal(
                Alignment::HORIZONTAL_CENTER
            )
            ->setVertical(
                Alignment::VERTICAL_CENTER
            );


        /*
        |--------------------------------------------------------------------------
        | HEADER INFO STYLE
        |--------------------------------------------------------------------------
        */

        $sheet
            ->getStyle('A5:C6')
            ->getFont()
            ->setBold(true);


        /*
        |--------------------------------------------------------------------------
        | TABLE HEADER STYLE
        |--------------------------------------------------------------------------
        */

        $headerStyle =
            $sheet->getStyle('A7:K7');


        $headerStyle
            ->getFont()
            ->setBold(true);


        $headerStyle
            ->getAlignment()
            ->setHorizontal(
                Alignment::HORIZONTAL_CENTER
            )
            ->setVertical(
                Alignment::VERTICAL_CENTER
            )
            ->setWrapText(true);


        $headerStyle
            ->getFill()
            ->setFillType(
                Fill::FILL_SOLID
            )
            ->getStartColor()
            ->setARGB(
                'D9EAD3'
            );


        $headerStyle
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(
                Border::BORDER_THIN
            );


        /*
        |--------------------------------------------------------------------------
        | DATA START
        |--------------------------------------------------------------------------
        */

        $startRow = 8;

        $row = $startRow;

        $no = 1;


        /*
        |--------------------------------------------------------------------------
        | LOOP PAYMENT REQUEST
        |--------------------------------------------------------------------------
        */

        foreach ($paymentRequests as $request) {

            /*
            |--------------------------------------------------------------------------
            | SPK
            |--------------------------------------------------------------------------
            */

            if (!$request->spk) {

                \Log::warning(
                    'EXPORT PENGAJUAN - SPK NOT FOUND',
                    [
                        'saved_id' =>
                            $saved->id,

                        'payment_request_id' =>
                            $request->id,

                        'spk_id' =>
                            $request->spk_id,
                    ]
                );

                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | SNAPSHOT PENGAJUAN
            |--------------------------------------------------------------------------
            |
            | Ini adalah angka asli ketika Payment Request dibuat.
            |
            */

            $snapshot =
                $request->spk_snapshot;


            if (is_string($snapshot)) {

                $snapshot =
                    json_decode(
                        $snapshot,
                        true
                    );
            }


            if (!is_array($snapshot)) {

                $snapshot = [];
            }


            /*
            |--------------------------------------------------------------------------
            | PAYMENT DARI SNAPSHOT
            |--------------------------------------------------------------------------
            */

            $payment =
                collect(
                    $snapshot['payments'] ?? []
                )->first(
                    function ($item) use ($request) {

                        return (string) (
                            $item['payment_id'] ?? ''
                        )
                        ===
                        (string) $request->payment_id;
                    }
                );


            /*
            |--------------------------------------------------------------------------
            | FALLBACK PAYMENT
            |--------------------------------------------------------------------------
            */

            if (!$payment) {

                $payment =
                    [];
            }


            /*
            |--------------------------------------------------------------------------
            | DATA SPK TERBARU
            |--------------------------------------------------------------------------
            |
            | Dipakai untuk adjustment Finance.
            |
            */

            $currentSpkData =
                $request->spk->data;


            if (is_string($currentSpkData)) {

                $currentSpkData =
                    json_decode(
                        $currentSpkData,
                        true
                    );
            }


            if (!is_array($currentSpkData)) {

                $currentSpkData =
                    [];
            }


            /*
            |--------------------------------------------------------------------------
            | PAYMENT TERBARU
            |--------------------------------------------------------------------------
            */

            $currentPayment =
                collect(
                    $currentSpkData['payments'] ?? []
                )->first(
                    function ($item) use ($request) {

                        return (string) (
                            $item['payment_id'] ?? ''
                        )
                        ===
                        (string) $request->payment_id;
                    }
                );


            /*
            |--------------------------------------------------------------------------
            | HARGA ASLI
            |--------------------------------------------------------------------------
            */

            $hargaAsli =
                self::toNumber(
                    $payment['amount'] ?? 0
                );


            /*
            |--------------------------------------------------------------------------
            | ADJUSTMENT FINANCE
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


            /*
            |--------------------------------------------------------------------------
            | ADJUSTMENT NUMBER
            |--------------------------------------------------------------------------
            */

            $adjustmentAmount = 0;


            if (
                $adjustment !== null
                &&
                $adjustment !== ''
            ) {

                $adjustmentAmount =
                    self::toNumber(
                        $adjustment
                    );
            }


            /*
            |--------------------------------------------------------------------------
            | ADJUSTMENT BY FINANCE
            |--------------------------------------------------------------------------
            */

            $adjustmentByName = '';


            if (
                $adjustmentBy !== null
                &&
                $adjustmentBy !== ''
            ) {

                /*
                |--------------------------------------------------------------------------
                | Jika adjustment_by berupa ID user
                |--------------------------------------------------------------------------
                */

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
            | TOTAL HARGA
            |--------------------------------------------------------------------------
            |
            | Jika adjustment Finance > 0:
            |     gunakan adjustment
            |
            | Jika tidak:
            |     gunakan harga asli
            |
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


            /*
            |--------------------------------------------------------------------------
            | DATA SPK UNTUK IDENTITAS
            |--------------------------------------------------------------------------
            */

            $spkData =
                $snapshot;


            if (empty($spkData)) {

                $spkData =
                    $currentSpkData;
            }


            /*
            |--------------------------------------------------------------------------
            | A - NO
            |--------------------------------------------------------------------------
            */

            $sheet->setCellValue(
                "A{$row}",
                $no
            );


            /*
            |--------------------------------------------------------------------------
            | B - DATE
            |--------------------------------------------------------------------------
            */

            $sheet->setCellValue(
                "B{$row}",
                $requestDate
            );


            /*
            |--------------------------------------------------------------------------
            | C - NO PO
            |--------------------------------------------------------------------------
            */

            $sheet->setCellValue(
                "C{$row}",
                $spkData['no_po']
                    ?? $request->no_po
                    ?? ''
            );


            /*
            |--------------------------------------------------------------------------
            | D - NO SPK
            |--------------------------------------------------------------------------
            */

            $sheet->setCellValue(
                "D{$row}",
                $spkData['no_spk']
                    ?? $request->no_spk
                    ?? ''
            );


            /*
            |--------------------------------------------------------------------------
            | E - TYPE BIAYA
            |--------------------------------------------------------------------------
            */

            $sheet->setCellValue(
                "E{$row}",
                $payment['note']
                    ?? ''
            );


            /*
            |--------------------------------------------------------------------------
            | F - NAMA ITEM
            |--------------------------------------------------------------------------
            */

            $sheet->setCellValue(
                "F{$row}",
                $payment['note_tambahan']
                    ?? $payment['note']
                    ?? ''
            );


            /*
            |--------------------------------------------------------------------------
            | G - QTY
            |--------------------------------------------------------------------------
            */

            $sheet->setCellValue(
                "G{$row}",
                1
            );


            /*
            |--------------------------------------------------------------------------
            | H - HARGA ASLI
            |--------------------------------------------------------------------------
            */

            $sheet->setCellValue(
                "H{$row}",
                $hargaAsli
            );


            /*
            |--------------------------------------------------------------------------
            | I - ADJUSTMENT FINANCE
            |--------------------------------------------------------------------------
            */

            $sheet->setCellValue(
                "I{$row}",
                $adjustmentAmount
            );


            /*
            |--------------------------------------------------------------------------
            | J - ADJUSTMENT BY FINANCE
            |--------------------------------------------------------------------------
            */

            $sheet->setCellValue(
                "J{$row}",
                $adjustmentByName
            );


            /*
            |--------------------------------------------------------------------------
            | K - TOTAL HARGA
            |--------------------------------------------------------------------------
            */

            $sheet->setCellValue(
                "K{$row}",
                "=G{$row}*"
                . "IF(I{$row}>0,I{$row},H{$row})"
            );


            /*
            |--------------------------------------------------------------------------
            | NEXT
            |--------------------------------------------------------------------------
            */

            $row++;

            $no++;
        }


        /*
        |--------------------------------------------------------------------------
        | TOTAL CASH
        |--------------------------------------------------------------------------
        */

        $totalRow =
            $row;


        $sheet->setCellValue(
            "A{$totalRow}",
            'TOTAL CASH'
        );


        $sheet->mergeCells(
            "A{$totalRow}:J{$totalRow}"
        );


        if ($row > $startRow) {

            $lastDataRow =
                $row - 1;


            $sheet->setCellValue(
                "K{$totalRow}",
                "=SUM(K{$startRow}:K{$lastDataRow})"
            );

        } else {

            $sheet->setCellValue(
                "K{$totalRow}",
                0
            );
        }


        /*
        |--------------------------------------------------------------------------
        | DATA BORDER
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
                    "A{$startRow}:B" .
                    ($row - 1)
                )
                ->getAlignment()
                ->setHorizontal(
                    Alignment::HORIZONTAL_CENTER
                );


            $sheet
                ->getStyle(
                    "G{$startRow}:G" .
                    ($row - 1)
                )
                ->getAlignment()
                ->setHorizontal(
                    Alignment::HORIZONTAL_CENTER
                );


            /*
            |--------------------------------------------------------------------------
            | MONEY ALIGNMENT
            |--------------------------------------------------------------------------
            */

            $sheet
                ->getStyle(
                    "H{$startRow}:I" .
                    ($row - 1)
                )
                ->getAlignment()
                ->setHorizontal(
                    Alignment::HORIZONTAL_RIGHT
                );
        }


        /*
        |--------------------------------------------------------------------------
        | NUMBER FORMAT
        |--------------------------------------------------------------------------
        */

        $sheet
            ->getStyle(
                "G{$startRow}:I{$totalRow}"
            )
            ->getNumberFormat()
            ->setFormatCode(
                '#,##0'
            );


        $sheet
            ->getStyle(
                "K{$startRow}:K{$totalRow}"
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

        $totalStyle =
            $sheet->getStyle(
                "A{$totalRow}:K{$totalRow}"
            );


        $totalStyle
            ->getFont()
            ->setBold(true);


        $totalStyle
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(
                Border::BORDER_THIN
            );


        $totalStyle
            ->getAlignment()
            ->setVertical(
                Alignment::VERTICAL_CENTER
            );


        $totalStyle
            ->getAlignment()
            ->setHorizontal(
                Alignment::HORIZONTAL_RIGHT
            );


        /*
        |--------------------------------------------------------------------------
        | COLUMN WIDTH
        |--------------------------------------------------------------------------
        */

        $sheet
            ->getColumnDimension('A')
            ->setWidth(7);

        $sheet
            ->getColumnDimension('B')
            ->setWidth(14);

        $sheet
            ->getColumnDimension('C')
            ->setWidth(16);

        $sheet
            ->getColumnDimension('D')
            ->setWidth(24);

        $sheet
            ->getColumnDimension('E')
            ->setWidth(20);

        $sheet
            ->getColumnDimension('F')
            ->setWidth(32);

        $sheet
            ->getColumnDimension('G')
            ->setWidth(8);

        $sheet
            ->getColumnDimension('H')
            ->setWidth(18);

        $sheet
            ->getColumnDimension('I')
            ->setWidth(20);

        $sheet
            ->getColumnDimension('J')
            ->setWidth(24);

        $sheet
            ->getColumnDimension('K')
            ->setWidth(18);


        /*
        |--------------------------------------------------------------------------
        | ROW HEIGHT
        |--------------------------------------------------------------------------
        */

        $sheet
            ->getRowDimension(1)
            ->setRowHeight(25);


        $sheet
            ->getRowDimension(7)
            ->setRowHeight(40);


        /*
        |--------------------------------------------------------------------------
        | WRAP TEXT
        |--------------------------------------------------------------------------
        */

        if ($row > $startRow) {

            $sheet
                ->getStyle(
                    "C{$startRow}:F" .
                    ($row - 1)
                )
                ->getAlignment()
                ->setWrapText(true);


            $sheet
                ->getStyle(
                    "J{$startRow}:J" .
                    ($row - 1)
                )
                ->getAlignment()
                ->setWrapText(true);
        }


        /*
        |--------------------------------------------------------------------------
        | FREEZE
        |--------------------------------------------------------------------------
        */

        $sheet->freezePane(
            'A8'
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


        /*
        |--------------------------------------------------------------------------
        | FILENAME
        |--------------------------------------------------------------------------
        */

        $filename =
            ($saved->request_no ?? 'pengajuan')
            . '.xlsx';


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


    /**
     * Convert nominal ke angka.
     *
     * Contoh:
     * 15.415.351 -> 15415351
     * 14.000.000 -> 14000000
     */
    private static function toNumber($value)
    {
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