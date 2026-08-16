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
        | AMBIL SEMUA PAYMENT REQUEST SAVED
        |--------------------------------------------------------------------------
        |
        | SUMBER TGL PENGAJUAN
        |
        | payment_request_saveds.request_date
        |
        */

        $savedRequests = PaymentRequestSaved::query()
            ->orderBy('request_date')
            ->orderBy('id')
            ->get();


        /*
        |--------------------------------------------------------------------------
        | MAPPING PAYMENT REQUEST ID -> SAVED
        |--------------------------------------------------------------------------
        |
        | Contoh:
        |
        | payment_request_saveds
        |
        | id                  = 25
        | request_date        = 2026-08-14
        | payment_request_ids = ["384"]
        |
        | Maka:
        |
        | 384 => 2026-08-14
        |
        */

        $savedRequestMap = [];


        foreach (
            $savedRequests
            as $saved
        ) {

            $paymentRequestIds =
                $saved->payment_request_ids
                ?? [];


            /*
            |--------------------------------------------------------------------------
            | JIKA JSON STRING
            |--------------------------------------------------------------------------
            */

            if (
                is_string(
                    $paymentRequestIds
                )
            ) {

                $paymentRequestIds =
                    json_decode(
                        $paymentRequestIds,
                        true
                    )
                    ?? [];
            }


            /*
            |--------------------------------------------------------------------------
            | VALIDASI ARRAY
            |--------------------------------------------------------------------------
            */

            if (
                !is_array(
                    $paymentRequestIds
                )
            ) {

                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | MAP SETIAP PAYMENT REQUEST
            |--------------------------------------------------------------------------
            */

            foreach (
                $paymentRequestIds
                as $paymentRequestId
            ) {

                /*
                |--------------------------------------------------------------------------
                | RAW REQUEST DATE
                |--------------------------------------------------------------------------
                |
                | Sengaja menggunakan getRawOriginal()
                | supaya tidak terkena pergeseran timezone.
                |
                */

                $rawRequestDate =
                    $saved->getRawOriginal(
                        'request_date'
                    );


                $savedRequestMap[
                    (string) $paymentRequestId
                ] = [

                    'saved_id' =>
                        $saved->id,

                    'request_no' =>
                        $saved->request_no,

                    'request_date' =>
                        $rawRequestDate,
                ];
            }
        }


        /*
        |--------------------------------------------------------------------------
        | GROUP PAYMENT REQUEST BERDASARKAN NO SPK
        |--------------------------------------------------------------------------
        */

        $groups = [];


        foreach (
            $paymentRequests
            as $request
        ) {

            /*
            |--------------------------------------------------------------------------
            | SNAPSHOT
            |--------------------------------------------------------------------------
            */

            $snapshot =
                $request->spk_snapshot;


            if (
                is_string(
                    $snapshot
                )
            ) {

                $snapshot =
                    json_decode(
                        $snapshot,
                        true
                    );
            }


            if (
                !is_array(
                    $snapshot
                )
            ) {

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
            | FALLBACK KE PAYMENT REQUEST
            |--------------------------------------------------------------------------
            */

            if (
                $noSpk === ''
            ) {

                $noSpk =
                    trim(
                        $request->no_spk
                        ?? ''
                    );
            }


            /*
            |--------------------------------------------------------------------------
            | FALLBACK KE SPK
            |--------------------------------------------------------------------------
            */

            if (
                $noSpk === ''
                &&
                $request->spk
            ) {

                $spkData =
                    $request->spk->data;


                if (
                    is_string(
                        $spkData
                    )
                ) {

                    $spkData =
                        json_decode(
                            $spkData,
                            true
                        );
                }


                if (
                    is_array(
                        $spkData
                    )
                ) {

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

            if (
                $noSpk === ''
            ) {

                $noSpk =
                    'TANPA-SPK';
            }


            /*
            |--------------------------------------------------------------------------
            | NORMALIZE GROUP KEY
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


            /*
            |--------------------------------------------------------------------------
            | CREATE GROUP
            |--------------------------------------------------------------------------
            */

            if (
                !isset(
                    $groups[$groupKey]
                )
            ) {

                $groups[$groupKey] = [

                    'no_spk' =>
                        $noSpk,

                    'items' =>
                        [],
                ];
            }


            /*
            |--------------------------------------------------------------------------
            | MASUKKAN PAYMENT REQUEST
            |--------------------------------------------------------------------------
            */

            $groups[$groupKey]['items'][] = [

                'request' =>
                    $request,

                'snapshot' =>
                    $snapshot,
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

        foreach (
            $groups
            as $group
        ) {

            self::createSheet(
                $spreadsheet,
                $group['no_spk'],
                $group['items'],
                $savedRequestMap
            );
        }


        /*
        |--------------------------------------------------------------------------
        | JIKA TIDAK ADA DATA
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
            'All-Payment-Request-'
            . now()->format('YmdHis')
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


    /*
    |--------------------------------------------------------------------------
    | CREATE SHEET
    |--------------------------------------------------------------------------
    */

    private static function createSheet(
        Spreadsheet $spreadsheet,
        string $noSpk,
        array $items,
        array $savedRequestMap
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
        | CREATE SHEET
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
            'A1:L1'
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
            'C3:L3'
        );


        /*
        |--------------------------------------------------------------------------
        | SUPPLIER
        |--------------------------------------------------------------------------
        */

        $firstItem =
            $items[0]
            ?? null;


        $firstSnapshot =
            $firstItem['snapshot']
            ?? [];


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
            'C4:L4'
        );


        /*
        |--------------------------------------------------------------------------
        | HEADER
        |--------------------------------------------------------------------------
        */

        $headers = [

            'A6' =>
                'No.',

            'B6' =>
                'No. PR',

            'C6' =>
                'Tgl Pengajuan',

            'D6' =>
                'Date',

            'E6' =>
                'No. PO',

            'F6' =>
                'No. SPK',

            'G6' =>
                'Ket.',

            'H6' =>
                'Harga Asli',

            'I6' =>
                'Adjustment Finance',

            'J6' =>
                'Adjustment By Finance',

            'K6' =>
                'Total Harga',

            'L6' =>
                'Status',
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
        | HEADER STYLE
        |--------------------------------------------------------------------------
        */

        $headerStyle =
            $sheet->getStyle(
                'A6:L6'
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
        | DATA
        |--------------------------------------------------------------------------
        */

        $startRow =
            7;


        $row =
            $startRow;


        $no =
            1;


        $grandTotal =
            0;


        foreach (
            $items
            as $item
        ) {

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
                    $snapshot['payments']
                    ?? []
                )->first(
                    function (
                        $payment
                    ) use (
                        $request
                    ) {

                        return
                            (string) (
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
            | PAYMENT TIDAK DITEMUKAN
            |--------------------------------------------------------------------------
            */

            if (
                !$payment
            ) {

                $payment = [];
            }


            /*
            |--------------------------------------------------------------------------
            | SPK DATA TERBARU
            |--------------------------------------------------------------------------
            */

            $currentSpkData =
                [];


            if (
                $request->spk
            ) {

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

                    $currentSpkData =
                        [];
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
                    function (
                        $payment
                    ) use (
                        $request
                    ) {

                        return
                            (string) (
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

            $adjustment =
                null;


            $adjustmentBy =
                null;


            if (
                $currentPayment
            ) {

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
                self::toNumber(
                    $adjustment
                );


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


                if (
                    $user
                ) {

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
            | Jika adjustment > 0:
            |     pakai adjustment
            |
            | Jika tidak:
            |     pakai harga asli
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


            $grandTotal +=
                $totalHarga;


            /*
            |--------------------------------------------------------------------------
            | KETERANGAN
            |--------------------------------------------------------------------------
            */

            $keterangan =
                trim(
                    $payment['note']
                    ?? ''
                );


            /*
            |--------------------------------------------------------------------------
            | DATE PEMBAYARAN
            |--------------------------------------------------------------------------
            |
            | Tetap dari:
            |
            | spk_snapshot.payments[].date
            |
            */

            $paymentDate =
                $payment['date']
                ?? '';


            /*
            |--------------------------------------------------------------------------
            | TANGGAL PENGAJUAN
            |--------------------------------------------------------------------------
            |
            | PENTING:
            |
            | BUKAN:
            |
            | $request->created_at
            | $request->request_date
            |
            |
            | SUMBER:
            |
            | payment_request_saveds.request_date
            |
            | berdasarkan:
            |
            | payment_request_saveds.payment_request_ids
            |
            */

            $tanggalPengajuan =
                self::getTanggalPengajuan(
                    $request->id,
                    $savedRequestMap
                );


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
            | WRITE DATA
            |--------------------------------------------------------------------------
            */

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
            | B - NO PR
            |--------------------------------------------------------------------------
            */

            $sheet->setCellValue(
                "B{$row}",
                $request->request_no
                ?? ''
            );


            /*
            |--------------------------------------------------------------------------
            | C - TGL PENGAJUAN
            |--------------------------------------------------------------------------
            */

            $sheet->setCellValue(
                "C{$row}",
                $tanggalPengajuan
            );


            /*
            |--------------------------------------------------------------------------
            | D - DATE PEMBAYARAN
            |--------------------------------------------------------------------------
            */

            $sheet->setCellValue(
                "D{$row}",
                $paymentDate
            );


            /*
            |--------------------------------------------------------------------------
            | E - NO PO
            |--------------------------------------------------------------------------
            */

            $sheet->setCellValue(
                "E{$row}",
                $snapshot['no_po']
                ?? $request->no_po
                ?? ''
            );


            /*
            |--------------------------------------------------------------------------
            | F - NO SPK
            |--------------------------------------------------------------------------
            */

            $sheet->setCellValue(
                "F{$row}",
                $snapshot['no_spk']
                ?? $request->no_spk
                ?? $noSpk
            );


            /*
            |--------------------------------------------------------------------------
            | G - KETERANGAN
            |--------------------------------------------------------------------------
            */

            $sheet->setCellValue(
                "G{$row}",
                $keterangan
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
                $totalHarga
            );


            /*
            |--------------------------------------------------------------------------
            | L - STATUS
            |--------------------------------------------------------------------------
            */

            $sheet->setCellValue(
                "L{$row}",
                $status
            );


            /*
            |--------------------------------------------------------------------------
            | NEXT ROW
            |--------------------------------------------------------------------------
            */

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


        /*
        |--------------------------------------------------------------------------
        | MERGE TOTAL
        |--------------------------------------------------------------------------
        */

        $sheet->mergeCells(
            "A{$totalRow}:J{$totalRow}"
        );


        /*
        |--------------------------------------------------------------------------
        | GRAND TOTAL DI KOLOM K
        |--------------------------------------------------------------------------
        */

        $sheet->setCellValue(
            "K{$totalRow}",
            $grandTotal
        );


        /*
        |--------------------------------------------------------------------------
        | STYLE DATA
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
            | CENTER
            |--------------------------------------------------------------------------
            */

            $sheet
                ->getStyle(
                    "A{$startRow}:F"
                    . ($row - 1)
                )
                ->getAlignment()
                ->setHorizontal(
                    Alignment::HORIZONTAL_CENTER
                );


            $sheet
                ->getStyle(
                    "L{$startRow}:L"
                    . ($row - 1)
                )
                ->getAlignment()
                ->setHorizontal(
                    Alignment::HORIZONTAL_CENTER
                );


            /*
            |--------------------------------------------------------------------------
            | WRAP TEXT
            |--------------------------------------------------------------------------
            */

            $sheet
                ->getStyle(
                    "A{$startRow}:L"
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
                "H{$startRow}:I{$totalRow}"
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


        /*
        |--------------------------------------------------------------------------
        | COLUMN WIDTH
        |--------------------------------------------------------------------------
        */

        $widths = [

            'A' => 7,

            'B' => 22,

            'C' => 16,

            'D' => 14,

            'E' => 16,

            'F' => 24,

            'G' => 18,

            'H' => 18,

            'I' => 22,

            'J' => 24,

            'K' => 18,

            'L' => 15,
        ];


        foreach (
            $widths
            as $column => $width
        ) {

            $sheet
                ->getColumnDimension(
                    $column
                )
                ->setWidth(
                    $width
                );
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
    }


    /*
    |--------------------------------------------------------------------------
    | GET TANGGAL PENGAJUAN
    |--------------------------------------------------------------------------
    |
    | paymentRequestId
    |        ↓
    | payment_request_saveds.payment_request_ids
    |        ↓
    | payment_request_saveds.request_date
    |
    */

    private static function getTanggalPengajuan(
        $paymentRequestId,
        array $savedRequestMap
    ) {

        $key =
            (string) $paymentRequestId;


        /*
        |--------------------------------------------------------------------------
        | TIDAK ADA DATA SAVED
        |--------------------------------------------------------------------------
        */

        if (
            !isset(
                $savedRequestMap[$key]
            )
        ) {

            return '';
        }


        $rawDate =
            $savedRequestMap[$key]['request_date']
            ?? null;


        if (
            !$rawDate
        ) {

            return '';
        }


        /*
        |--------------------------------------------------------------------------
        | AMBIL TANGGAL RAW SAJA
        |--------------------------------------------------------------------------
        |
        | Contoh:
        |
        | 2026-08-14
        |
        | 2026-08-14 00:00:00
        |
        | 2026-08-13 17:00:00
        |
        | semuanya diambil bagian:
        |
        | YYYY-MM-DD
        |
        */

        $dateOnly =
            substr(
                (string) $rawDate,
                0,
                10
            );


        /*
        |--------------------------------------------------------------------------
        | VALIDASI
        |--------------------------------------------------------------------------
        */

        if (
            !preg_match(
                '/^\d{4}-\d{2}-\d{2}$/',
                $dateOnly
            )
        ) {

            return '';
        }


        /*
        |--------------------------------------------------------------------------
        | FORMAT INDONESIA
        |--------------------------------------------------------------------------
        */

        try {

            return
                \Carbon\Carbon::createFromFormat(
                    'Y-m-d',
                    $dateOnly
                )->format(
                    'd/m/Y'
                );

        } catch (
            \Throwable $e
        ) {

            return '';
        }
    }


    /*
    |--------------------------------------------------------------------------
    | MAKE SHEET NAME
    |--------------------------------------------------------------------------
    */

    private static function makeSheetName(
        string $name
    ) {

        /*
        |--------------------------------------------------------------------------
        | HAPUS KARAKTER TERLARANG EXCEL
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
            trim(
                $name
            );


        if (
            $name === ''
        ) {

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
    | TO NUMBER
    |--------------------------------------------------------------------------
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


        if (
            $value === ''
        ) {

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