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
     * =========================================================================
     * EXPORT SATU DRAFT REQUEST / DR
     * =========================================================================
     *
     * Controller cukup:
     *
     * return ExportPengajuanSpk::export($saved);
     *
     * Semua proses:
     *
     * - mengambil Payment Request
     * - mengambil snapshot
     * - mengambil nama_sub
     * - grouping / sorting nama_sub
     * - adjustment finance
     * - adjustment by finance
     * - membuat Excel
     *
     * dilakukan di helper ini.
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
        |
        | Hanya Payment Request yang masuk ke DR ini.
        |
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
        | PREPARE DATA UNTUK GROUPING NAMA SUB
        |--------------------------------------------------------------------------
        |
        | Contoh data awal:
        |
        | Pak Waya
        | Pak Waya
        | Pak Sobana
        | Pak Sobana
        | Pak Sobana
        | Pak Yanto
        |
        | Akan diurutkan:
        |
        | Pak Sobana
        | Pak Sobana
        | Pak Sobana
        | Pak Waya
        | Pak Waya
        | Pak Yanto
        |
        */

        $sortedRequests = [];


        foreach (
            $paymentRequests
            as $request
        ) {

            /*
            |--------------------------------------------------------------------------
            | SPK CHECK
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
            | DATA SPK TERBARU
            |--------------------------------------------------------------------------
            |
            | Selain untuk fallback nama_sub,
            | data ini nanti digunakan untuk adjustment Finance.
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

                $currentSpkData = [];
            }


            /*
            |--------------------------------------------------------------------------
            | NAMA SUB
            |--------------------------------------------------------------------------
            |
            | Prioritas:
            |
            | 1. snapshot[sup]
            | 2. snapshot[nama_sub]
            | 3. snapshot[sub]
            | 4. data SPK[sup]
            | 5. data SPK[nama_sub]
            | 6. data SPK[sub]
            | 7. property model
            |
            */

            $namaSub =
                trim(
                    $snapshot['sup']
                    ?? $snapshot['nama_sub']
                    ?? $snapshot['sub']
                    ?? ''
                );


            /*
            |--------------------------------------------------------------------------
            | FALLBACK DATA SPK
            |--------------------------------------------------------------------------
            */

            if ($namaSub === '') {

                $namaSub =
                    trim(
                        $currentSpkData['sup']
                        ?? $currentSpkData['nama_sub']
                        ?? $currentSpkData['sub']
                        ?? ''
                    );
            }


            /*
            |--------------------------------------------------------------------------
            | FALLBACK PAYMENT REQUEST MODEL
            |--------------------------------------------------------------------------
            */

            if ($namaSub === '') {

                $namaSub =
                    trim(
                        $request->sup
                        ?? $request->sup
                        ?? ''
                    );
            }


            /*
            |--------------------------------------------------------------------------
            | FALLBACK SPK MODEL
            |--------------------------------------------------------------------------
            */

            if (
                $namaSub === ''
                &&
                $request->spk
            ) {

                $namaSub =
                    trim(
                        $request->spk->sup
                        ?? $request->spk->nama_sub
                        ?? ''
                    );
            }


            /*
            |--------------------------------------------------------------------------
            | JIKA BENAR-BENAR TIDAK ADA
            |--------------------------------------------------------------------------
            */

            if ($namaSub === '') {

                $namaSub =
                    'TANPA SUB';
            }


            /*
            |--------------------------------------------------------------------------
            | NORMALIZE UNTUK SORTING
            |--------------------------------------------------------------------------
            |
            | Pak Waya
            | PAK WAYA
            | pak waya
            |
            | dianggap kelompok yang sama.
            |
            */

            $namaSubKey =
                self::normalizeSub(
                    $namaSub
                );


            /*
            |--------------------------------------------------------------------------
            | SIMPAN
            |--------------------------------------------------------------------------
            */

            $sortedRequests[] = [

                'request' =>
                    $request,

                'snapshot' =>
                    $snapshot,

                'currentSpkData' =>
                    $currentSpkData,

                'namaSub' =>
                    $namaSub,

                'namaSubKey' =>
                    $namaSubKey,
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | SORT / GROUPING NAMA SUB
        |--------------------------------------------------------------------------
        |
        | Semua grouping dilakukan DI HELPER.
        |
        */

        usort(
            $sortedRequests,
            function (
                $a,
                $b
            ) {

                /*
                |--------------------------------------------------------------------------
                | SORT NAMA SUB
                |--------------------------------------------------------------------------
                */

                $compare =
                    strcasecmp(
                        $a['namaSubKey'],
                        $b['namaSubKey']
                    );


                /*
                |--------------------------------------------------------------------------
                | JIKA NAMA SUB SAMA
                |--------------------------------------------------------------------------
                |
                | Pertahankan urutan berdasarkan
                | ID Payment Request.
                |
                */

                if ($compare === 0) {

                    return (
                        $a['request']->id
                        <=>
                        $b['request']->id
                    );
                }


                return $compare;
            }
        );


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
            'A1:L1'
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

            'A7' =>
                'No.',

            'B7' =>
                'Date',

            'C7' =>
                'Nama Sub',

            'D7' =>
                'NO PO',

            'E7' =>
                'NO. INV / NO. SPK',

            'F7' =>
                'TYPE BIAYA',

            'G7' =>
                'Nama Barang/Item/Jasa',

            'H7' =>
                'QTY',

            'I7' =>
                'Diajukan',

            'J7' =>
                'Adjustment Finance',

            'K7' =>
                'Adjustment By Finance',

            'L7' =>
                'Total Harga',
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
        | TITLE STYLE
        |--------------------------------------------------------------------------
        */

        $sheet
            ->getStyle(
                'A1:L1'
            )
            ->getFont()
            ->setBold(true)
            ->setSize(14);


        $sheet
            ->getStyle(
                'A1:L1'
            )
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
            ->getStyle(
                'A5:C6'
            )
            ->getFont()
            ->setBold(true);


        /*
        |--------------------------------------------------------------------------
        | TABLE HEADER STYLE
        |--------------------------------------------------------------------------
        */

        $headerStyle =
            $sheet->getStyle(
                'A7:L7'
            );


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
        | LOOP DATA YANG SUDAH DI-GROUP
        |--------------------------------------------------------------------------
        */

        foreach (
            $sortedRequests
            as $sortedItem
        ) {

            $request =
                $sortedItem['request'];


            $snapshot =
                $sortedItem['snapshot'];


            $currentSpkData =
                $sortedItem['currentSpkData'];


            $namaSub =
                $sortedItem['namaSub'];


            /*
            |--------------------------------------------------------------------------
            | PAYMENT DARI SNAPSHOT
            |--------------------------------------------------------------------------
            |
            | Ini nominal ASLI saat Payment Request dibuat.
            |
            */

            $payment =
                collect(
                    $snapshot['payments']
                    ?? []
                )->first(
                    function (
                        $item
                    ) use (
                        $request
                    ) {

                        return
                            (string) (
                                $item['payment_id']
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
            | FALLBACK PAYMENT
            |--------------------------------------------------------------------------
            */

            if (!$payment) {

                $payment = [];
            }


            /*
            |--------------------------------------------------------------------------
            | PAYMENT TERBARU
            |--------------------------------------------------------------------------
            |
            | Dipakai untuk mengambil:
            |
            | - adjustment
            | - adjustment_by
            |
            */

            $currentPayment =
                collect(
                    $currentSpkData['payments']
                    ?? []
                )->first(
                    function (
                        $item
                    ) use (
                        $request
                    ) {

                        return
                            (string) (
                                $item['payment_id']
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
            | ADJUSTMENT FINANCE
            |--------------------------------------------------------------------------
            */

            $adjustment =
                null;


            $adjustmentBy =
                null;


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

            $adjustmentAmount =
                0;


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

            $adjustmentByName =
                '';


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
            | TOTAL HARGA
            |--------------------------------------------------------------------------
            |
            | Jika ada adjustment:
            |     pakai adjustment
            |
            | Jika tidak ada adjustment:
            |     pakai angka asli
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


            if (
                empty(
                    $spkData
                )
            ) {

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
            | C - NAMA SUB
            |--------------------------------------------------------------------------
            */

            $sheet->setCellValue(
                "C{$row}",
                $namaSub
            );


            /*
            |--------------------------------------------------------------------------
            | D - NO PO
            |--------------------------------------------------------------------------
            */

            $sheet->setCellValue(
                "D{$row}",
                $spkData['no_po']
                    ?? $request->no_po
                    ?? ''
            );


            /*
            |--------------------------------------------------------------------------
            | E - NO SPK
            |--------------------------------------------------------------------------
            */

            $sheet->setCellValue(
                "E{$row}",
                $spkData['no_spk']
                    ?? $request->no_spk
                    ?? ''
            );


            /*
            |--------------------------------------------------------------------------
            | F - TYPE BIAYA
            |--------------------------------------------------------------------------
            */

            $sheet->setCellValue(
                "F{$row}",
                $payment['note']
                    ?? ''
            );


            /*
            |--------------------------------------------------------------------------
            | G - NAMA BARANG / ITEM / JASA
            |--------------------------------------------------------------------------
            */

            $sheet->setCellValue(
                "G{$row}",
                $payment['note_tambahan']
                    ?? $payment['note']
                    ?? ''
            );


            /*
            |--------------------------------------------------------------------------
            | H - QTY
            |--------------------------------------------------------------------------
            */

            $sheet->setCellValue(
                "H{$row}",
                1
            );


            /*
            |--------------------------------------------------------------------------
            | I - DIAJUKAN / HARGA ASLI
            |--------------------------------------------------------------------------
            */

            $sheet->setCellValue(
                "I{$row}",
                $hargaAsli
            );


            /*
            |--------------------------------------------------------------------------
            | J - ADJUSTMENT FINANCE
            |--------------------------------------------------------------------------
            */

            $sheet->setCellValue(
                "J{$row}",
                $adjustmentAmount
            );


            /*
            |--------------------------------------------------------------------------
            | K - ADJUSTMENT BY FINANCE
            |--------------------------------------------------------------------------
            */

            $sheet->setCellValue(
                "K{$row}",
                $adjustmentByName
            );


            /*
            |--------------------------------------------------------------------------
            | L - TOTAL HARGA
            |--------------------------------------------------------------------------
            */

            $sheet->setCellValue(
                "L{$row}",
                "=H{$row}*"
                . "IF(J{$row}>0,J{$row},I{$row})"
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
            "A{$totalRow}:K{$totalRow}"
        );


        if (
            $row > $startRow
        ) {

            $lastDataRow =
                $row - 1;


            $sheet->setCellValue(
                "L{$totalRow}",
                "=SUM(L{$startRow}:L{$lastDataRow})"
            );

        } else {

            $sheet->setCellValue(
                "L{$totalRow}",
                0
            );
        }


        /*
        |--------------------------------------------------------------------------
        | DATA BORDER
        |--------------------------------------------------------------------------
        */

        if (
            $row > $startRow
        ) {

            $dataStyle =
                $sheet->getStyle(
                    "A{$startRow}:L"
                    . ($row - 1)
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
            | CENTER NO + DATE
            |--------------------------------------------------------------------------
            */

            $sheet
                ->getStyle(
                    "A{$startRow}:B"
                    . ($row - 1)
                )
                ->getAlignment()
                ->setHorizontal(
                    Alignment::HORIZONTAL_CENTER
                );


            /*
            |--------------------------------------------------------------------------
            | NAMA SUB
            |--------------------------------------------------------------------------
            */

            $sheet
                ->getStyle(
                    "C{$startRow}:C"
                    . ($row - 1)
                )
                ->getAlignment()
                ->setHorizontal(
                    Alignment::HORIZONTAL_LEFT
                );


            /*
            |--------------------------------------------------------------------------
            | CENTER QTY
            |--------------------------------------------------------------------------
            */

            $sheet
                ->getStyle(
                    "H{$startRow}:H"
                    . ($row - 1)
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
                    "I{$startRow}:J"
                    . ($row - 1)
                )
                ->getAlignment()
                ->setHorizontal(
                    Alignment::HORIZONTAL_RIGHT
                );


            $sheet
                ->getStyle(
                    "L{$startRow}:L"
                    . ($row - 1)
                )
                ->getAlignment()
                ->setHorizontal(
                    Alignment::HORIZONTAL_RIGHT
                );


            /*
            |--------------------------------------------------------------------------
            | WRAP TEXT
            |--------------------------------------------------------------------------
            */

            $sheet
                ->getStyle(
                    "C{$startRow}:G"
                    . ($row - 1)
                )
                ->getAlignment()
                ->setWrapText(true);


            $sheet
                ->getStyle(
                    "K{$startRow}:K"
                    . ($row - 1)
                )
                ->getAlignment()
                ->setWrapText(true);
        }


        /*
        |--------------------------------------------------------------------------
        | NUMBER FORMAT
        |--------------------------------------------------------------------------
        */

        $sheet
            ->getStyle(
                "H{$startRow}:J{$totalRow}"
            )
            ->getNumberFormat()
            ->setFormatCode(
                '#,##0'
            );


        $sheet
            ->getStyle(
                "L{$startRow}:L{$totalRow}"
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
                "A{$totalRow}:L{$totalRow}"
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
            ->setWidth(20);


        $sheet
            ->getColumnDimension('D')
            ->setWidth(16);


        $sheet
            ->getColumnDimension('E')
            ->setWidth(24);


        $sheet
            ->getColumnDimension('F')
            ->setWidth(20);


        $sheet
            ->getColumnDimension('G')
            ->setWidth(32);


        $sheet
            ->getColumnDimension('H')
            ->setWidth(8);


        $sheet
            ->getColumnDimension('I')
            ->setWidth(18);


        $sheet
            ->getColumnDimension('J')
            ->setWidth(20);


        $sheet
            ->getColumnDimension('K')
            ->setWidth(24);


        $sheet
            ->getColumnDimension('L')
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
            ->setFitToWidth(
                1
            );


        $sheet
            ->getPageSetup()
            ->setFitToHeight(
                0
            );


        /*
        |--------------------------------------------------------------------------
        | PRINT AREA
        |--------------------------------------------------------------------------
        */

        $sheet
            ->getPageSetup()
            ->setPrintArea(
                "A1:L{$totalRow}"
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
     * =========================================================================
     * NORMALIZE NAMA SUB
     * =========================================================================
     *
     * Contoh:
     *
     * Pak Waya
     * PAK WAYA
     * pak waya
     * Pak    Waya
     *
     * dianggap sebagai satu kelompok:
     *
     * PAK WAYA
     */
    private static function normalizeSub(
        $value
    ) {

        $value =
            trim(
                (string) $value
            );


        $value =
            preg_replace(
                '/\s+/',
                ' ',
                $value
            );


        return strtoupper(
            $value
        );
    }


    /**
     * =========================================================================
     * CONVERT NOMINAL KE ANGKA
     * =========================================================================
     *
     * Contoh:
     *
     * 15.415.351
     *     =>
     * 15415351
     *
     * 14.000.000
     *     =>
     * 14000000
     *
     * 15.415.351,50
     *     =>
     * 15415351.50
     */
    private static function toNumber(
        $value
    ) {

        if (
            $value === null
            ||
            $value === ''
        ) {

            return 0;
        }


        if (
            is_int($value)
            ||
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
        | HAPUS RP / SPASI
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
            strpos(
                $value,
                '.'
            ) !== false
            &&
            strpos(
                $value,
                ','
            ) !== false
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
                isset(
                    $parts[1]
                )
                &&
                strlen(
                    $parts[1]
                ) === 3
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