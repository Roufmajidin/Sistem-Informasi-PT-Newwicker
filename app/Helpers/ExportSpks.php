<?php

namespace App\Helpers;

use App\Models\Spk;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExportSpks
{
    /**
     * =========================================================
     * EXPORT SPK
     * =========================================================
     */
    public static function export($spkId)
    {
        $spk = Spk::findOrFail($spkId);

        $data = $spk->data ?? [];

        /*
        |--------------------------------------------------------------------------
        | TEMPLATE
        |--------------------------------------------------------------------------
        */
        $templatePath = storage_path(
            'app/templates/SPK-TEMPLATE.xlsx'
        );

        if (!file_exists($templatePath)) {
            abort(
                500,
                'File template SPK tidak ditemukan: ' . $templatePath
            );
        }

        /*
        |--------------------------------------------------------------------------
        | LOAD TEMPLATE
        |--------------------------------------------------------------------------
        */
        $spreadsheet = IOFactory::load($templatePath);

        /*
        |--------------------------------------------------------------------------
        | GUNAKAN SHEET AKTIF
        |--------------------------------------------------------------------------
        */
        $sheet = $spreadsheet->getActiveSheet();


        /*
        |--------------------------------------------------------------------------
        | HEADER
        |--------------------------------------------------------------------------
        */

        $sheet->setCellValue(
            'B7',
            $data['no_spk'] ?? ''
        );

        $sheet->setCellValue(
            'B8',
            $data['sup'] ?? ''
        );

        $sheet->setCellValue(
            'B9',
            $data['tgl_terima'] ?? ''
        );

        $sheet->setCellValue(
            'B10',
            $data['tgl_selesai'] ?? ''
        );

        $sheet->setCellValue(
            'K7',
            $data['no_po'] ?? ''
        );


        /*
        |--------------------------------------------------------------------------
        | HAPUS DRAWING GAMBAR PRODUK LAMA
        |--------------------------------------------------------------------------
        |
        | Logo/header tetap dipertahankan.
        |
        | Gambar produk pada template berada di row >= 13.
        |
        */
        self::removeProductDrawings($sheet);


        /*
        |--------------------------------------------------------------------------
        | BUILD DISPLAY ROWS
        |--------------------------------------------------------------------------
        |
        | 1 item = 1 row
        |
        | custom_columns tambahan juga menjadi row tambahan.
        |
        */
        $displayRows = [];

        $items = is_array($data['items'] ?? null)
            ? $data['items']
            : [];


        foreach ($items as $item) {

            /*
            |--------------------------------------------------------------------------
            | ITEM UTAMA
            |--------------------------------------------------------------------------
            */
            $displayRows[] = [
                'type' => 'item',
                'data' => $item,
            ];


            /*
            |--------------------------------------------------------------------------
            | DETAIL CUSTOM
            |--------------------------------------------------------------------------
            */
            $customColumns =
                is_array($item['custom_columns'] ?? null)
                    ? $item['custom_columns']
                    : [];


            /*
            | Index 0 adalah header/kategori utama.
            */
            if (count($customColumns) > 1) {

                foreach (
                    array_slice($customColumns, 1)
                    as $detail
                ) {

                    $displayRows[] = [
                        'type' => 'detail',
                        'data' => $detail,
                    ];
                }
            }
        }


        /*
        |--------------------------------------------------------------------------
        | MINIMUM 1 ROW
        |--------------------------------------------------------------------------
        */
        if (count($displayRows) === 0) {

            $displayRows[] = [
                'type' => 'item',
                'data' => [],
            ];
        }


        /*
        |--------------------------------------------------------------------------
        | ITEM ROW TEMPLATE
        |--------------------------------------------------------------------------
        */
        $itemStartRow = 14;

        $itemCount = count($displayRows);

        $lastItemRow =
            $itemStartRow + $itemCount - 1;


        /*
        |--------------------------------------------------------------------------
        | INSERT EXTRA ROW
        |--------------------------------------------------------------------------
        |
        | Template sudah mempunyai row 14.
        |
        | Jika 5 item:
        |
        | 14
        | 15
        | 16
        | 17
        | 18
        |
        */
        if ($itemCount > 1) {

            $sheet->insertNewRowBefore(
                $itemStartRow + 1,
                $itemCount - 1
            );
        }


        /*
        |--------------------------------------------------------------------------
        | COPY STYLE ROW 14 KE SEMUA ROW TAMBAHAN
        |--------------------------------------------------------------------------
        */
        for ($i = 1; $i < $itemCount; $i++) {

            $targetRow =
                $itemStartRow + $i;

            self::copyRowStyle(
                $sheet,
                $itemStartRow,
                $targetRow
            );
        }


        /*
        |--------------------------------------------------------------------------
        | WRITE ITEMS
        |--------------------------------------------------------------------------
        */
        foreach (
            $displayRows as $index => $display
        ) {

            $row =
                $itemStartRow + $index;

            $item =
                $display['data'];


            /*
            |--------------------------------------------------------------------------
            | BORDER WAJIB
            |--------------------------------------------------------------------------
            */
            self::applyItemBorder(
                $sheet,
                $row
            );


            /*
            |--------------------------------------------------------------------------
            | ITEM UTAMA
            |--------------------------------------------------------------------------
            */
            if (
                $display['type'] === 'item'
            ) {

                self::writeMainItem(
                    $sheet,
                    $row,
                    $item
                );
            }


            /*
            |--------------------------------------------------------------------------
            | CUSTOM DETAIL
            |--------------------------------------------------------------------------
            */
            else {

                self::writeDetailItem(
                    $sheet,
                    $row,
                    $item
                );
            }


            /*
            |--------------------------------------------------------------------------
            | ROW HEIGHT
            |--------------------------------------------------------------------------
            */
            $sheet
                ->getRowDimension($row)
                ->setRowHeight(51.75);
        }


        /*
        |--------------------------------------------------------------------------
        | TOTAL
        |--------------------------------------------------------------------------
        |
        | Total berada tepat di bawah seluruh item.
        |
        | Contoh:
        |
        | item 14
        | item 15
        | item 16
        | item 17
        | item 18
        | TOTAL 19
        |
        */
        $totalRow =
            $lastItemRow + 1;


        /*
        |--------------------------------------------------------------------------
        | TOTAL FORMULA
        |--------------------------------------------------------------------------
        */
        $sheet->setCellValue(
            "K{$totalRow}",
            "=SUM(K{$itemStartRow}:K{$lastItemRow})"
        );


        /*
        |--------------------------------------------------------------------------
        | TOTAL FORMAT
        |--------------------------------------------------------------------------
        */
        self::formatCurrency(
            $sheet,
            "K{$totalRow}"
        );


        /*
        |--------------------------------------------------------------------------
        | TOTAL BORDER
        |--------------------------------------------------------------------------
        */
        self::applyTotalBorder(
            $sheet,
            $totalRow
        );


        /*
        |--------------------------------------------------------------------------
        | TERMS
        |--------------------------------------------------------------------------
        |
        | Ditulis di area A:I.
        |
        | Tidak menggunakan embedded Word object.
        |
        */
        $terms = [

            '1. Spesifikasi barang harus sesuai dengan sample.',

            '2. Harga belum termasuk transportasi sampai gudang NewWicker.',

            '3. Supplier bertanggung jawab atas ketidaksesuaian spesifikasi barang.',

            '4. Final Quality Controlling akan dilaksanakan di gudang NewWicker.',

            '5. Supplier dikenakan penalty 1% setiap harinya atas keterlambatan produksi.',

            '6. Supplier wajib melaporkan perkembangan produksi dan permasalahan yang dapat menghambat kelancaran produksi.',

            '7. Penyelesaian pembayaran dilakukan setelah supplier memenuhi semua kewajibannya.',

            '8. Supplier dilarang memberikan hadiah atau komisi dalam bentuk uang kepada karyawan dan staff PT. NewWicker.',
        ];


        /*
        |--------------------------------------------------------------------------
        | TERMS START
        |--------------------------------------------------------------------------
        |
        | Mulai pada row total.
        |
        */
        $termsStartRow =
            $totalRow;


        /*
        |--------------------------------------------------------------------------
        | TULIS TERMS
        |--------------------------------------------------------------------------
        */
        foreach (
            $terms as $index => $term
        ) {

            $termRow =
                $termsStartRow + $index;


            /*
            |--------------------------------------------------------------------------
            | MERGE A:I
            |--------------------------------------------------------------------------
            */
            self::safeMerge(
                $sheet,
                "A{$termRow}:I{$termRow}"
            );


            /*
            |--------------------------------------------------------------------------
            | VALUE
            |--------------------------------------------------------------------------
            */
            $sheet->setCellValue(
                "A{$termRow}",
                $term
            );


            /*
            |--------------------------------------------------------------------------
            | STYLE
            |--------------------------------------------------------------------------
            */
            $sheet->getStyle(
                "A{$termRow}:I{$termRow}"
            )->applyFromArray([

                'font' => [
                    'name' => 'Arial',
                    'size' => 9,
                    'bold' => false,
                ],

                'fill' => [
                    'fillType' => Fill::FILL_NONE,
                ],

                'borders' => [

                    'top' => [
                        'borderStyle' =>
                            Border::BORDER_NONE,
                    ],

                    'bottom' => [
                        'borderStyle' =>
                            Border::BORDER_NONE,
                    ],

                    'left' => [
                        'borderStyle' =>
                            Border::BORDER_NONE,
                    ],

                    'right' => [
                        'borderStyle' =>
                            Border::BORDER_NONE,
                    ],
                ],

                'alignment' => [

                    'horizontal' =>
                        Alignment::HORIZONTAL_LEFT,

                    'vertical' =>
                        Alignment::VERTICAL_CENTER,

                    'wrapText' => false,
                ],
            ]);


            /*
            |--------------------------------------------------------------------------
            | HEIGHT
            |--------------------------------------------------------------------------
            */
            $sheet
                ->getRowDimension($termRow)
                ->setRowHeight(17);
        }


        /*
        |--------------------------------------------------------------------------
        | DENGAN ANDA
        |--------------------------------------------------------------------------
        */
        $withYouRow =
            $termsStartRow + count($terms);


        self::safeMerge(
            $sheet,
            "A{$withYouRow}:I{$withYouRow}"
        );


        $sheet->setCellValue(
            "A{$withYouRow}",
            'Dengan Anda.'
        );


        $sheet->getStyle(
            "A{$withYouRow}:I{$withYouRow}"
        )->applyFromArray([

            'font' => [
                'name' => 'Arial',
                'size' => 9,
            ],

            'borders' => [

                'top' => [
                    'borderStyle' =>
                        Border::BORDER_NONE,
                ],

                'bottom' => [
                    'borderStyle' =>
                        Border::BORDER_NONE,
                ],

                'left' => [
                    'borderStyle' =>
                        Border::BORDER_NONE,
                ],

                'right' => [
                    'borderStyle' =>
                        Border::BORDER_NONE,
                ],
            ],

            'alignment' => [

                'horizontal' =>
                    Alignment::HORIZONTAL_LEFT,

                'vertical' =>
                    Alignment::VERTICAL_CENTER,
            ],
        ]);


        /*
        |--------------------------------------------------------------------------
        | PAYMENT
        |--------------------------------------------------------------------------
        |
        | Payment tetap berada di sisi kanan.
        |
        | K = Amount
        | L = Date
        | M = Note
        |
        */
        $payments =
            is_array($data['payments'] ?? null)
                ? $data['payments']
                : [];


        /*
        |--------------------------------------------------------------------------
        | PAYMENT HEADER
        |--------------------------------------------------------------------------
        |
        | Kita letakkan sejajar dengan terms pertama.
        |
        */
        $paymentHeaderRow =
            $termsStartRow;


        /*
        |--------------------------------------------------------------------------
        | PAYMENT HEADER
        |--------------------------------------------------------------------------
        */
        $sheet->setCellValue(
            "K{$paymentHeaderRow}",
            'Ammount'
        );

        $sheet->setCellValue(
            "L{$paymentHeaderRow}",
            'Date'
        );

        $sheet->setCellValue(
            "M{$paymentHeaderRow}",
            'Note'
        );


        /*
        |--------------------------------------------------------------------------
        | PAYMENT HEADER STYLE
        |--------------------------------------------------------------------------
        */
        self::applyPaymentBorder(
            $sheet,
            $paymentHeaderRow
        );


        /*
        |--------------------------------------------------------------------------
        | PAYMENT ROW
        |--------------------------------------------------------------------------
        */
        $paymentStartRow =
            $paymentHeaderRow + 1;


        foreach (
            $payments as $index => $payment
        ) {

            $paymentRow =
                $paymentStartRow + $index;


            /*
            |--------------------------------------------------------------------------
            | J = CHECKBOX
            |--------------------------------------------------------------------------
            */
            $isRequest =
                isset($payment['is_request'])
                    ? (bool) $payment['is_request']
                    : true;


            $sheet->setCellValue(
                "J{$paymentRow}",
                $isRequest ? '✓' : ''
            );


            /*
            |--------------------------------------------------------------------------
            | AMOUNT
            |--------------------------------------------------------------------------
            */
            $amount =
                self::numericValue(
                    $payment['amount'] ?? 0
                );


            $sheet->setCellValue(
                "K{$paymentRow}",
                $amount ?? 0
            );


            /*
            |--------------------------------------------------------------------------
            | DATE
            |--------------------------------------------------------------------------
            */
            $sheet->setCellValue(
                "L{$paymentRow}",
                $payment['date'] ?? ''
            );


            /*
            |--------------------------------------------------------------------------
            | NOTE
            |--------------------------------------------------------------------------
            */
            $note =
                $payment['note'] ?? '';


            if (
                trim((string) $note) === ''
            ) {

                $note =
                    $payment['note_tambahan']
                    ?? '';
            }


            $sheet->setCellValue(
                "M{$paymentRow}",
                $note
            );


            /*
            |--------------------------------------------------------------------------
            | COPY STYLE PAYMENT
            |--------------------------------------------------------------------------
            */
            if ($index > 0) {

                self::copyPaymentStyle(
                    $sheet,
                    $paymentStartRow,
                    $paymentRow
                );
            }


            /*
            |--------------------------------------------------------------------------
            | BORDER
            |--------------------------------------------------------------------------
            */
            self::applyPaymentBorder(
                $sheet,
                $paymentRow
            );


            /*
            |--------------------------------------------------------------------------
            | CURRENCY
            |--------------------------------------------------------------------------
            */
            self::formatCurrency(
                $sheet,
                "K{$paymentRow}"
            );


            /*
            |--------------------------------------------------------------------------
            | HEIGHT
            |--------------------------------------------------------------------------
            */
            $sheet
                ->getRowDimension($paymentRow)
                ->setRowHeight(17);
        }


        /*
        |--------------------------------------------------------------------------
        | SUPPLIER FOOTER
        |--------------------------------------------------------------------------
        |
        | Template:
        |
        | Know by : supplier
        |
        */
        $footerRow =
            max(
                26 + max(0, $itemCount - 1),
                $paymentStartRow + count($payments) + 1,
                $withYouRow + 2
            );


        /*
        |--------------------------------------------------------------------------
        | SUPPLIER
        |--------------------------------------------------------------------------
        */
        self::safeMerge(
            $sheet,
            "L{$footerRow}:M{$footerRow}"
        );


        $sheet->setCellValue(
            "L{$footerRow}",
            '=$B$8'
        );


        /*
        |--------------------------------------------------------------------------
        | SIGNATURE AREA
        |--------------------------------------------------------------------------
        |
        | Kalau footer template bergeser karena row tambahan,
        | isi signature tidak kita ubah.
        |
        | Kita hanya memastikan area tetap mempunyai border/style
        | template.
        |
        */


        /*
        |--------------------------------------------------------------------------
        | PRINT AREA
        |--------------------------------------------------------------------------
        */
        $printBottom =
            $footerRow + 5;


        $sheet
            ->getPageSetup()
            ->setPrintArea(
                "A1:M{$printBottom}"
            );


        /*
        |--------------------------------------------------------------------------
        | LANDSCAPE
        |--------------------------------------------------------------------------
        */
        $sheet
            ->getPageSetup()
            ->setOrientation(
                \PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE
            );


        /*
        |--------------------------------------------------------------------------
        | FIT TO PAGE
        |--------------------------------------------------------------------------
        */
        $sheet
            ->getPageSetup()
            ->setFitToWidth(1);

        $sheet
            ->getPageSetup()
            ->setFitToHeight(0);


        /*
        |--------------------------------------------------------------------------
        | CALCULATION
        |--------------------------------------------------------------------------
        */
        $spreadsheet
            ->getCalculationEngine()
            ->clearCalculationCache();


        /*
        |--------------------------------------------------------------------------
        | FILENAME
        |--------------------------------------------------------------------------
        */
        $safeNoSpk =
            preg_replace(
                '/[\/\\\\]/',
                '-',
                $data['no_spk'] ?? $spk->id
            );


        $filename =
            "SPK-{$safeNoSpk}.xlsx";


        /*
        |--------------------------------------------------------------------------
        | TEMP FILE
        |--------------------------------------------------------------------------
        */
        $temporaryFile =
            tempnam(
                storage_path('app'),
                'spk_'
            );


        if (!$temporaryFile) {

            abort(
                500,
                'Tidak dapat membuat temporary file untuk export SPK.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | WRITE
        |--------------------------------------------------------------------------
        */
        $writer =
            new Xlsx(
                $spreadsheet
            );


        /*
        |--------------------------------------------------------------------------
        | FORMULA
        |--------------------------------------------------------------------------
        */
        $writer->setPreCalculateFormulas(true);


        /*
        |--------------------------------------------------------------------------
        | SAVE
        |--------------------------------------------------------------------------
        */
        $writer->save(
            $temporaryFile
        );


        /*
        |--------------------------------------------------------------------------
        | DOWNLOAD
        |--------------------------------------------------------------------------
        */
        return response()
            ->download(
                $temporaryFile,
                $filename,
                [
                    'Content-Type' =>
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                ]
            )
            ->deleteFileAfterSend(true);
    }


    /**
     * =========================================================
     * WRITE MAIN ITEM
     * =========================================================
     */
    private static function writeMainItem(
        Worksheet $sheet,
        int $row,
        array $item
    ) {

        /*
        |--------------------------------------------------------------------------
        | A = KODE
        |--------------------------------------------------------------------------
        */
        $sheet->setCellValue(
            "A{$row}",
            $item['kode'] ?? ''
        );


        /*
        |--------------------------------------------------------------------------
        | C = NAMA
        |--------------------------------------------------------------------------
        */
        $sheet->setCellValue(
            "C{$row}",
            $item['nama'] ?? ''
        );


        /*
        |--------------------------------------------------------------------------
        | D = P
        |--------------------------------------------------------------------------
        */
        $sheet->setCellValue(
            "D{$row}",
            $item['p'] ?? ''
        );


        /*
        |--------------------------------------------------------------------------
        | E = L
        |--------------------------------------------------------------------------
        */
        $sheet->setCellValue(
            "E{$row}",
            $item['l'] ?? ''
        );


        /*
        |--------------------------------------------------------------------------
        | F = T
        |--------------------------------------------------------------------------
        */
        $sheet->setCellValue(
            "F{$row}",
            $item['t'] ?? ''
        );


        /*
        |--------------------------------------------------------------------------
        | G = MATERIAL
        |--------------------------------------------------------------------------
        */
        $sheet->setCellValue(
            "G{$row}",
            $item['material'] ?? ''
        );


        /*
        |--------------------------------------------------------------------------
        | QTY
        |--------------------------------------------------------------------------
        */
        $unit =
            strtolower(
                trim(
                    (string) (
                        $item['satuan']
                        ?? 'pcs'
                    )
                )
            );


        $qty =
            $item['qty'] ?? '';


        /*
        | Clear
        */
        $sheet->setCellValue(
            "H{$row}",
            ''
        );

        $sheet->setCellValue(
            "I{$row}",
            ''
        );


        if ($unit === 'set') {

            $sheet->setCellValue(
                "I{$row}",
                $qty
            );

        } else {

            $sheet->setCellValue(
                "H{$row}",
                $qty
            );
        }


        /*
        |--------------------------------------------------------------------------
        | HARGA
        |--------------------------------------------------------------------------
        */
        $harga =
            self::numericValue(
                $item['harga'] ?? null
            );


        $sheet->setCellValue(
            "J{$row}",
            $harga
        );


        /*
        |--------------------------------------------------------------------------
        | TOTAL
        |--------------------------------------------------------------------------
        */
        $total =
            self::numericValue(
                $item['total'] ?? null
            );


        /*
        | Fallback
        */
        if (
            $total === null &&
            $harga !== null &&
            is_numeric($qty)
        ) {

            $total =
                $harga * (float) $qty;
        }


        $sheet->setCellValue(
            "K{$row}",
            $total ?? 0
        );


        /*
        |--------------------------------------------------------------------------
        | CATATAN
        |--------------------------------------------------------------------------
        */
        $sheet->setCellValue(
            "L{$row}",
            self::extractRemark(
                $item['catatan'] ?? ''
            )
        );


        /*
        |--------------------------------------------------------------------------
        | FORMAT
        |--------------------------------------------------------------------------
        */
        self::formatCurrency(
            $sheet,
            "J{$row}"
        );

        self::formatCurrency(
            $sheet,
            "K{$row}"
        );


        /*
        |--------------------------------------------------------------------------
        | ALIGNMENT
        |--------------------------------------------------------------------------
        */
        $sheet
            ->getStyle("A{$row}:M{$row}")
            ->getAlignment()
            ->setVertical(
                Alignment::VERTICAL_CENTER
            );


        $sheet
            ->getStyle("C{$row}")
            ->getAlignment()
            ->setWrapText(true);

        $sheet
            ->getStyle("G{$row}")
            ->getAlignment()
            ->setWrapText(true);

        $sheet
            ->getStyle("L{$row}:M{$row}")
            ->getAlignment()
            ->setWrapText(true);


        /*
        |--------------------------------------------------------------------------
        | IMAGE
        |--------------------------------------------------------------------------
        */
        if (
            !empty($item['images']) &&
            is_array($item['images'])
        ) {

            $image =
                $item['images'][0]
                ?? null;


            if ($image) {

                self::insertImage(
                    $sheet,
                    $image,
                    "B{$row}",
                    80
                );
            }
        }
    }


    /**
     * =========================================================
     * WRITE DETAIL ITEM
     * =========================================================
     */
    private static function writeDetailItem(
        Worksheet $sheet,
        int $row,
        array $detail
    ) {

        /*
        |--------------------------------------------------------------------------
        | C = KATEGORI
        |--------------------------------------------------------------------------
        */
        $sheet->setCellValue(
            "C{$row}",
            $detail['kategori'] ?? ''
        );


        /*
        |--------------------------------------------------------------------------
        | UKURAN
        |--------------------------------------------------------------------------
        */
        $sheet->setCellValue(
            "D{$row}",
            $detail['p'] ?? ''
        );

        $sheet->setCellValue(
            "E{$row}",
            $detail['l'] ?? ''
        );

        $sheet->setCellValue(
            "F{$row}",
            $detail['t'] ?? ''
        );


        /*
        |--------------------------------------------------------------------------
        | MATERIAL
        |--------------------------------------------------------------------------
        */
        $sheet->setCellValue(
            "G{$row}",
            $detail['material'] ?? ''
        );


        /*
        |--------------------------------------------------------------------------
        | PCS
        |--------------------------------------------------------------------------
        */
        $pcs =
            $detail['pcs'] ?? '';


        /*
        |--------------------------------------------------------------------------
        | SET
        |--------------------------------------------------------------------------
        */
        $set =
            $detail['set'] ?? '';


        /*
        |--------------------------------------------------------------------------
        | FALLBACK QTY
        |--------------------------------------------------------------------------
        */
        if (
            $pcs === '' &&
            $set === '' &&
            isset($detail['qty'])
        ) {

            $unit =
                strtolower(
                    trim(
                        (string) (
                            $detail['satuan']
                            ?? 'pcs'
                        )
                    )
                );


            if ($unit === 'set') {

                $set =
                    $detail['qty'];

            } else {

                $pcs =
                    $detail['qty'];
            }
        }


        /*
        |--------------------------------------------------------------------------
        | QTY
        |--------------------------------------------------------------------------
        */
        $sheet->setCellValue(
            "H{$row}",
            $pcs
        );

        $sheet->setCellValue(
            "I{$row}",
            $set
        );


        /*
        |--------------------------------------------------------------------------
        | HARGA
        |--------------------------------------------------------------------------
        */
        $harga =
            self::numericValue(
                $detail['harga'] ?? null
            );


        $sheet->setCellValue(
            "J{$row}",
            $harga
        );


        /*
        |--------------------------------------------------------------------------
        | TOTAL
        |--------------------------------------------------------------------------
        */
        $total =
            self::numericValue(
                $detail['total'] ?? null
            );


        if (
            $total === null &&
            $harga !== null
        ) {

            $qty =
                is_numeric($pcs)
                    ? (float) $pcs
                    : (
                        is_numeric($set)
                            ? (float) $set
                            : null
                    );


            if ($qty !== null) {

                $total =
                    $harga * $qty;
            }
        }


        $sheet->setCellValue(
            "K{$row}",
            $total ?? 0
        );


        /*
        |--------------------------------------------------------------------------
        | CATATAN
        |--------------------------------------------------------------------------
        */
        $sheet->setCellValue(
            "L{$row}",
            self::extractRemark(
                $detail['catatan'] ?? ''
            )
        );


        /*
        |--------------------------------------------------------------------------
        | FORMAT
        |--------------------------------------------------------------------------
        */
        self::formatCurrency(
            $sheet,
            "J{$row}"
        );

        self::formatCurrency(
            $sheet,
            "K{$row}"
        );


        /*
        |--------------------------------------------------------------------------
        | WRAP
        |--------------------------------------------------------------------------
        */
        $sheet
            ->getStyle("C{$row}")
            ->getAlignment()
            ->setWrapText(true);

        $sheet
            ->getStyle("G{$row}")
            ->getAlignment()
            ->setWrapText(true);

        $sheet
            ->getStyle("L{$row}:M{$row}")
            ->getAlignment()
            ->setWrapText(true);
    }


    /**
     * =========================================================
     * COPY ROW STYLE
     * =========================================================
     */
    private static function copyRowStyle(
        Worksheet $sheet,
        int $sourceRow,
        int $targetRow
    ) {

        /*
        |--------------------------------------------------------------------------
        | COPY A:M
        |--------------------------------------------------------------------------
        */
        for (
            $column = 1;
            $column <= 13;
            $column++
        ) {

            $coordinate =
                \PhpOffice\PhpSpreadsheet\Cell\Coordinate::
                    stringFromColumnIndex(
                        $column
                    );


            $sheet->duplicateStyle(
                $sheet->getStyle(
                    "{$coordinate}{$sourceRow}"
                ),
                "{$coordinate}{$targetRow}"
            );


            /*
            | Copy value kosong.
            */
            $sheet->setCellValue(
                "{$coordinate}{$targetRow}",
                null
            );
        }


        /*
        |--------------------------------------------------------------------------
        | ROW HEIGHT
        |--------------------------------------------------------------------------
        */
        $height =
            $sheet
                ->getRowDimension($sourceRow)
                ->getRowHeight();


        if (
            $height !== null &&
            $height !== -1
        ) {

            $sheet
                ->getRowDimension($targetRow)
                ->setRowHeight(
                    $height
                );
        }
    }


    /**
     * =========================================================
     * COPY PAYMENT STYLE
     * =========================================================
     */
    private static function copyPaymentStyle(
        Worksheet $sheet,
        int $sourceRow,
        int $targetRow
    ) {

        foreach (
            ['J', 'K', 'L', 'M']
            as $column
        ) {

            $sheet->duplicateStyle(
                $sheet->getStyle(
                    "{$column}{$sourceRow}"
                ),
                "{$column}{$targetRow}"
            );
        }


        $height =
            $sheet
                ->getRowDimension($sourceRow)
                ->getRowHeight();


        if (
            $height !== null &&
            $height !== -1
        ) {

            $sheet
                ->getRowDimension($targetRow)
                ->setRowHeight(
                    $height
                );
        }
    }


    /**
     * =========================================================
     * ITEM BORDER
     * =========================================================
     */
    private static function applyItemBorder(
        Worksheet $sheet,
        int $row
    ) {

        $thin =
            Border::BORDER_THIN;


        $sheet
            ->getStyle("A{$row}:M{$row}")
            ->getBorders()
            ->getTop()
            ->setBorderStyle($thin);


        $sheet
            ->getStyle("A{$row}:M{$row}")
            ->getBorders()
            ->getBottom()
            ->setBorderStyle($thin);


        $sheet
            ->getStyle("A{$row}:M{$row}")
            ->getBorders()
            ->getLeft()
            ->setBorderStyle($thin);


        $sheet
            ->getStyle("A{$row}:M{$row}")
            ->getBorders()
            ->getRight()
            ->setBorderStyle($thin);
    }


    /**
     * =========================================================
     * TOTAL BORDER
     * =========================================================
     */
    private static function applyTotalBorder(
        Worksheet $sheet,
        int $row
    ) {

        $thin =
            Border::BORDER_THIN;


        /*
        | Hanya area J:K.
        */
        $sheet
            ->getStyle("J{$row}:K{$row}")
            ->getBorders()
            ->getTop()
            ->setBorderStyle($thin);


        $sheet
            ->getStyle("J{$row}:K{$row}")
            ->getBorders()
            ->getBottom()
            ->setBorderStyle($thin);


        $sheet
            ->getStyle("J{$row}:K{$row}")
            ->getBorders()
            ->getLeft()
            ->setBorderStyle($thin);


        $sheet
            ->getStyle("J{$row}:K{$row}")
            ->getBorders()
            ->getRight()
            ->setBorderStyle($thin);


        $sheet
            ->getStyle("K{$row}")
            ->getAlignment()
            ->setHorizontal(
                Alignment::HORIZONTAL_RIGHT
            );
    }


    /**
     * =========================================================
     * PAYMENT BORDER
     * =========================================================
     */
    private static function applyPaymentBorder(
        Worksheet $sheet,
        int $row
    ) {

        $thin =
            Border::BORDER_THIN;


        /*
        |--------------------------------------------------------------------------
        | K:M
        |--------------------------------------------------------------------------
        */
        $sheet
            ->getStyle("K{$row}:M{$row}")
            ->getBorders()
            ->getTop()
            ->setBorderStyle($thin);


        $sheet
            ->getStyle("K{$row}:M{$row}")
            ->getBorders()
            ->getBottom()
            ->setBorderStyle($thin);


        $sheet
            ->getStyle("K{$row}:M{$row}")
            ->getBorders()
            ->getLeft()
            ->setBorderStyle($thin);


        $sheet
            ->getStyle("K{$row}:M{$row}")
            ->getBorders()
            ->getRight()
            ->setBorderStyle($thin);


        /*
        |--------------------------------------------------------------------------
        | CHECKBOX
        |--------------------------------------------------------------------------
        */
        $sheet
            ->getStyle("J{$row}")
            ->getAlignment()
            ->setHorizontal(
                Alignment::HORIZONTAL_CENTER
            );

        $sheet
            ->getStyle("J{$row}")
            ->getAlignment()
            ->setVertical(
                Alignment::VERTICAL_CENTER
            );
    }


    /**
     * =========================================================
     * REMOVE PRODUCT DRAWINGS
     * =========================================================
     */
    private static function removeProductDrawings(
        Worksheet $sheet
    ) {

        $drawings =
            $sheet->getDrawingCollection();


        /*
        |--------------------------------------------------------------------------
        | Drawing collection PhpSpreadsheet adalah ArrayObject.
        |--------------------------------------------------------------------------
        */
        for (
            $i = count($drawings) - 1;
            $i >= 0;
            $i--
        ) {

            $drawing =
                $drawings[$i]
                ?? null;


            if (!$drawing) {
                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | KOORDINAT
            |--------------------------------------------------------------------------
            */
            $coordinates =
                $drawing->getCoordinates();


            if (!$coordinates) {
                continue;
            }


            /*
            |--------------------------------------------------------------------------
            | PRODUCT IMAGE
            |--------------------------------------------------------------------------
            |
            | Jangan hapus logo.
            |
            | Logo ada di row 1.
            |
            */
            if (
                preg_match(
                    '/^[A-Z]+(\d+)$/',
                    $coordinates,
                    $matches
                )
            ) {

                $row =
                    (int) $matches[1];


                if ($row >= 13) {

                    unset(
                        $drawings[$i]
                    );
                }
            }
        }
    }


    /**
     * =========================================================
     * INSERT IMAGE
     * =========================================================
     */
    private static function insertImage(
        Worksheet $sheet,
        $path,
        string $cell,
        int $height = 80
    ) {

        if (!$path) {
            return;
        }


        $realPath =
            self::resolveImagePath(
                $path
            );


        if (
            !$realPath ||
            !file_exists($realPath)
        ) {
            return;
        }


        try {

            $drawing =
                new Drawing();


            $drawing->setPath(
                $realPath
            );

            $drawing->setCoordinates(
                $cell
            );

            $drawing->setHeight(
                $height
            );

            $drawing->setOffsetX(5);

            $drawing->setOffsetY(5);

            $drawing->setWorksheet(
                $sheet
            );

        } catch (\Throwable $e) {

            /*
            |--------------------------------------------------------------------------
            | Kalau gambar error, export tetap jalan.
            |--------------------------------------------------------------------------
            */
            report($e);
        }
    }


    /**
     * =========================================================
     * RESOLVE IMAGE PATH
     * =========================================================
     */
    private static function resolveImagePath(
        $path
    ) {

        $path =
            trim(
                (string) $path
            );


        if ($path === '') {
            return null;
        }


        /*
        |--------------------------------------------------------------------------
        | LOCAL FILE
        |--------------------------------------------------------------------------
        */
        if (
            file_exists($path)
        ) {

            return $path;
        }


        /*
        |--------------------------------------------------------------------------
        | URL
        |--------------------------------------------------------------------------
        */
        if (
            filter_var(
                $path,
                FILTER_VALIDATE_URL
            )
        ) {

            $urlPath =
                parse_url(
                    $path,
                    PHP_URL_PATH
                );


            $urlPath =
                ltrim(
                    (string) $urlPath,
                    '/'
                );


            /*
            |--------------------------------------------------------------------------
            | public/storage/...
            |--------------------------------------------------------------------------
            */
            $candidate =
                public_path(
                    $urlPath
                );


            if (
                file_exists($candidate)
            ) {

                return $candidate;
            }


            /*
            |--------------------------------------------------------------------------
            | storage/app/public/...
            |--------------------------------------------------------------------------
            */
            if (
                str_starts_with(
                    $urlPath,
                    'storage/'
                )
            ) {

                $candidate =
                    storage_path(
                        'app/public/' .
                        substr(
                            $urlPath,
                            8
                        )
                    );


                if (
                    file_exists($candidate)
                ) {

                    return $candidate;
                }
            }
        }


        /*
        |--------------------------------------------------------------------------
        | /storage/...
        |--------------------------------------------------------------------------
        */
        if (
            str_starts_with(
                $path,
                '/storage/'
            )
        ) {

            $relative =
                substr(
                    $path,
                    9
                );


            $candidate =
                storage_path(
                    'app/public/' .
                    $relative
                );


            if (
                file_exists($candidate)
            ) {

                return $candidate;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | public path
        |--------------------------------------------------------------------------
        */
        $candidate =
            public_path(
                ltrim(
                    $path,
                    '/'
                )
            );


        if (
            file_exists($candidate)
        ) {

            return $candidate;
        }


        return null;
    }


    /**
     * =========================================================
     * SAFE MERGE
     * =========================================================
     */
    private static function safeMerge(
        Worksheet $sheet,
        string $range
    ) {

        /*
        |--------------------------------------------------------------------------
        | Jangan merge kalau range sudah merged.
        |--------------------------------------------------------------------------
        */
        foreach (
            $sheet->getMergeCells()
            as $existing
        ) {

            if (
                strtoupper($existing) ===
                strtoupper($range)
            ) {
                return;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | Jangan paksa merge yang overlap.
        |--------------------------------------------------------------------------
        */
        try {

            $sheet->mergeCells(
                $range
            );

        } catch (\Throwable $e) {

            /*
            |--------------------------------------------------------------------------
            | Tidak membuat export gagal.
            |--------------------------------------------------------------------------
            */
            report($e);
        }
    }


    /**
     * =========================================================
     * EXTRACT REMARK
     * =========================================================
     */
    private static function extractRemark(
        $value
    ): string {

        if (
            is_array($value)
        ) {

            return (string) (
                $value['remark']
                ??
                $value['keterangan']
                ??
                ''
            );
        }


        return (string) $value;
    }


    /**
     * =========================================================
     * NUMERIC VALUE
     * =========================================================
     */
    private static function numericValue(
        $value
    ): ?float {

        if (
            $value === null ||
            $value === ''
        ) {

            return null;
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


        if ($value === '') {
            return null;
        }


        /*
        |--------------------------------------------------------------------------
        | HAPUS RP
        |--------------------------------------------------------------------------
        */
        $value =
            str_ireplace(
                'Rp',
                '',
                $value
            );


        $value =
            str_replace(
                [
                    ' ',
                    "\xc2\xa0",
                ],
                '',
                $value
            );


        /*
        |--------------------------------------------------------------------------
        | FORMAT 1.525.000,50
        |--------------------------------------------------------------------------
        */
        if (
            str_contains($value, '.') &&
            str_contains($value, ',')
        ) {

            if (
                strrpos($value, ',') >
                strrpos($value, '.')
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

            } else {

                $value =
                    str_replace(
                        ',',
                        '',
                        $value
                    );
            }
        }


        /*
        |--------------------------------------------------------------------------
        | 450,5
        |--------------------------------------------------------------------------
        */
        elseif (
            str_contains($value, ',')
        ) {

            $parts =
                explode(
                    ',',
                    $value
                );


            if (
                count($parts) === 2 &&
                strlen($parts[1]) <= 2
            ) {

                $value =
                    str_replace(
                        ',',
                        '.',
                        $value
                    );

            } else {

                $value =
                    str_replace(
                        ',',
                        '',
                        $value
                    );
            }
        }


        /*
        |--------------------------------------------------------------------------
        | 1.525.000
        |--------------------------------------------------------------------------
        */
        elseif (
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
        }


        return is_numeric($value)
            ? (float) $value
            : null;
    }


    /**
     * =========================================================
     * CURRENCY FORMAT
     * =========================================================
     */
    private static function formatCurrency(
        Worksheet $sheet,
        string $cell
    ) {

        $sheet
            ->getStyle($cell)
            ->getNumberFormat()
            ->setFormatCode(
                '"Rp" #,##0'
            );
    }
}