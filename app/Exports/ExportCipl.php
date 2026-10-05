<?php



namespace App\Exports;



use App\Models\ExportIpl;
use App\Models\ExportAr;

use App\Models\ExportIplItem;

use App\Models\Loberon;



use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\IOFactory;

use PhpOffice\PhpSpreadsheet\Spreadsheet;

use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

use PhpOffice\PhpSpreadsheet\Writer\Xlsx;



use Symfony\Component\HttpFoundation\StreamedResponse;



class ExportCipl
{





    public function download(ExportIpl $invoice)
    {





        $templatePath = storage_path(

            'app/templates/cipl-templates.xlsx'

        );



        if (!file_exists($templatePath)) {

            abort(

                500,

                'Template CIPL tidak ditemukan: ' . $templatePath

            );

        }









        $spreadsheet = IOFactory::load(

            $templatePath

        );





        $sheet = $spreadsheet->getSheetByName('INV');



        if (!$sheet) {

            $sheet = $spreadsheet->getActiveSheet();

        }









        $items = collect(

            $invoice->items ?? []

        )->values();









        $articleCodes = $items

            ->pluck('article_nr')

            ->filter(function ($value) {



                return $value !== null

                    && trim((string) $value) !== '';



            })

            ->map(function ($value) {



                return trim(

                    (string) $value

                );



            })

            ->unique()

            ->values();









        $loberonMap = collect();



        if ($articleCodes->isNotEmpty()) {



            $loberonMap = Loberon::whereIn(

                'article_code',

                $articleCodes

            )

                ->get()

                ->keyBy(function ($row) {



                    return trim(

                        (string) $row->article_code

                    );



                });



        }









        $this->writeHeader(

            $sheet,

            $invoice

        );









        $firstItemRow = 16;





        $templateItemRow = 16;

        // Capture the Bankdetails text from the original template BEFORE
        // item rows are inserted. The template stores it in A18:G28.
        $bankDetailsText = $sheet->getCell('A18')->getValue();










        if ($items->count() > 1) {





            $sheet->insertNewRowBefore(

                $firstItemRow + 1,

                $items->count() - 1

            );







            for (

                $i = 1;

                $i < $items->count();

                $i++

            ) {



                $targetRow =

                    $firstItemRow + $i;



                $this->copyRowStyle(

                    $sheet,

                    $templateItemRow,

                    $targetRow

                );



            }



        }









        foreach (

            $items as $index => $item

        ) {



            $row =

                $firstItemRow + $index;









            $articleCode = trim(

                (string) (

                    $item->article_nr ?? ''

                )

            );





            $loberon = $loberonMap->get(

                $articleCode

            );









            $this->writeItem(

                $sheet,

                $row,

                $item,

                $index + 1,

                $loberon

            );



        }









        $totalRow =

            $firstItemRow + $items->count();









        $this->copyRowStyle(

            $sheet,

            17,

            $totalRow

        );





        $this->writeTotal(

            $sheet,

            $totalRow,

            $firstItemRow,

            $totalRow - 1

        );









        $paymentStartRow =

            $totalRow + 1;





        $this->writePaymentSection(
            $sheet,
            $invoice,
            $paymentStartRow,
            $bankDetailsText
        );









        $this->setupPage(

            $sheet,

            $paymentStartRow + 12

        );









        $fileName = $this->makeFilename(

            $invoice

        );









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



            ]

        );

    }









    protected function writeHeader(

        Worksheet $sheet,

        ExportIpl $invoice

    ) {







        $this->setValue(

            $sheet,

            'H3',

            $invoice->invoice_no

        );









        $this->setValue(

            $sheet,

            'Q3',

            $invoice->buyer

        );









        $this->setValue(

            $sheet,

            'Q4',

            $invoice->buyer_address

        );









        $this->setDate(

            $sheet,

            'H4',

            $invoice->date

        );









        $customerPo =

            $invoice->customer_po_no

            ?? $invoice->sales_order

            ?? '';





        $this->setValue(

            $sheet,

            'H5',

            $customerPo

        );









        $this->setValue(

            $sheet,

            'H6',

            $invoice->container_no

        );









        $this->setValue(

            $sheet,

            'H7',

            $invoice->vessel_name

        );









        $this->setValue(

            $sheet,

            'H8',

            $invoice->port_loading

        );









        $this->setValue(

            $sheet,

            'H9',

            $invoice->port_discharge

        );









        $this->setValue(

            $sheet,

            'H10',

            $invoice->incoterm

        );









        $this->setValue(

            $sheet,

            'H11',

            $invoice->country_of_origin

        );



    }









    protected function writeItem(

        Worksheet $sheet,

        int $row,

        ExportIplItem $item,

        int $number,

        $loberon = null

    ) {







        $articleCode =

            trim(

                (string) (

                    $item->article_nr ?? ''

                )

            );









        $colorId =

            data_get(

                $loberon,

                'color_id',

                ''

            );









        $sizeId =

            data_get(

                $loberon,

                'size_id',

                ''

            );









        $ean =

            data_get(

                $loberon,

                'ean_code',

                ''

            );









        $eudr =

            data_get(

                $loberon,

                'eudr_dds_code',

                ''

            );









        $description =

            data_get(

                $loberon,

                'article_description'

            )

            ?? $item->desc_custome

            ?? $item->description

            ?? '';









        $hsCode =

            data_get(

                $loberon,

                'hts_code'

            )

            ?? $item->hs_code

            ?? '';









        $bulky =

            data_get(

                $loberon,

                'bulky_goods_class'

            )

            ?? 'Normal Item';









        $netWt =

            $this->firstValue(

                $item->net_weight,

                data_get(

                    $loberon,

                    'net_wt_crt'

                )

            );





        $grossWt =

            $this->firstValue(

                $item->gross_weight,

                data_get(

                    $loberon,

                    'gross_wt_crt'

                )

            );









        $cartons =

            $this->firstValue(

                $item->qty_box,

                data_get(

                    $loberon,

                    'total_number_cartons'

                )

            );









        $dimension =

            $this->parseBoxDimension(

                $item->box_dimension

            );





        $cartonL =

            $this->firstValue(

                $dimension['l'],

                data_get(

                    $loberon,

                    'carton_l'

                )

            );





        $cartonW =

            $this->firstValue(

                $dimension['w'],

                data_get(

                    $loberon,

                    'carton_w'

                )

            );





        $cartonH =

            $this->firstValue(

                $dimension['h'],

                data_get(

                    $loberon,

                    'carton_h'

                )

            );









        $volumeCarton =

            $this->firstValue(

                $item->cbm,

                data_get(

                    $loberon,

                    'volume_carton'

                )

            );









        $qty =

            $this->firstValue(

                $item->qty_pcs,

                data_get(

                    $loberon,

                    'qty'

                )

            );









        $price =

            $this->firstValue(

                $item->unit_price,

                data_get(

                    $loberon,

                    'price_per_article'

                )

            );









        $totalNet =

            (float) $netWt *

            (float) $cartons;





        $totalGross =

            (float) $grossWt *

            (float) $cartons;









        $totalVolume =

            (float) $item->total_cbm;





        if (

            $totalVolume <= 0

            && $cartonL > 0

            && $cartonW > 0

            && $cartonH > 0

        ) {



            $totalVolume =

                (

                    $cartonL

                    * $cartonW

                    * $cartonH

                )

                / 1000000

                * (float) $cartons;



        }









        $amount =

            (float) $qty *

            (float) $price;









        $this->setValue(

            $sheet,

            'A' . $row,

            $number

        );





        $this->setValue(

            $sheet,

            'B' . $row,

            $item->blde

            ?? $item->po_no

            ?? ''

        );





        $this->setValue(

            $sheet,

            'C' . $row,

            $articleCode

        );









        $this->setValue(

            $sheet,

            'D' . $row,

            $colorId

        );





        $this->setValue(

            $sheet,

            'E' . $row,

            $sizeId

        );





        $ean = $this->cleanInvisible((string) $ean);

        $sheet->setCellValueExplicit(
            'F' . $row,
            $ean,
            DataType::TYPE_STRING
        );

        $sheet->getStyle('F' . $row)
            ->getNumberFormat()
            ->setFormatCode('@');





        $this->setValue(

            $sheet,

            'G' . $row,

            $eudr

        );









        $this->setValue(

            $sheet,

            'H' . $row,

            $description

        );





        $this->setValue(

            $sheet,

            'I' . $row,

            $hsCode

        );





        $this->setValue(

            $sheet,

            'J' . $row,

            $bulky

        );





        $this->setValue(

            $sheet,

            'K' . $row,

            $netWt

        );





        $this->setValue(

            $sheet,

            'L' . $row,

            $grossWt

        );





        $this->setValue(

            $sheet,

            'M' . $row,

            $cartons

        );









        $marks =

            $item->blde

            ?? $item->po_no

            ?? '';





        $this->setValue(

            $sheet,

            'N' . $row,

            $marks

        );









        $sheet->setCellValue(

            'Q' . $row,

            '=K' . $row . '*M' . $row

        );









        $sheet->setCellValue(

            'R' . $row,

            '=L' . $row . '*M' . $row

        );









        $this->setValue(

            $sheet,

            'S' . $row,

            $cartonL

        );





        $this->setValue(

            $sheet,

            'T' . $row,

            $cartonW

        );





        $this->setValue(

            $sheet,

            'U' . $row,

            $cartonH

        );









        $sheet->setCellValue(

            'V' . $row,

            '=S' . $row .

            '*T' . $row .

            '*U' . $row .

            '/1000000'

        );









        $sheet->setCellValue(

            'W' . $row,

            '=V' . $row .

            '*M' . $row

        );









        $this->setValue(

            $sheet,

            'X' . $row,

            $qty

        );









        $this->setValue(

            $sheet,

            'Y' . $row,

            $price

        );









        $sheet->setCellValue(

            'Z' . $row,

            '=X' . $row .

            '*Y' . $row

        );









        $sheet

            ->getStyle(

                'K' . $row . ':L' . $row

            )

            ->getNumberFormat()

            ->setFormatCode(

                '0.00'

            );





        $sheet

            ->getStyle(

                'Q' . $row . ':R' . $row

            )

            ->getNumberFormat()

            ->setFormatCode(

                '0.00'

            );





        $sheet

            ->getStyle(

                'S' . $row . ':V' . $row

            )

            ->getNumberFormat()

            ->setFormatCode(

                '0.0'

            );





        $sheet

            ->getStyle(

                'W' . $row

            )

            ->getNumberFormat()

            ->setFormatCode(

                '0.000'

            );





        $sheet

            ->getStyle(

                'X' . $row

            )

            ->getNumberFormat()

            ->setFormatCode(

                '0.0'

            );





        $sheet

            ->getStyle(

                'Y' . $row . ':Z' . $row

            )

            ->getNumberFormat()

            ->setFormatCode(

                '$#,##0.00'

            );



    }









    protected function writeTotal(

        Worksheet $sheet,

        int $row,

        int $firstItemRow,

        int $lastItemRow

    ) {







        $this->setValue(

            $sheet,

            'A' . $row,

            ''

        );





        $this->setValue(

            $sheet,

            'B' . $row,

            'TOTAL'

        );









        $sheet->setCellValue(

            'Q' . $row,

            '=SUM(Q' .

            $firstItemRow .

            ':Q' .

            $lastItemRow .

            ')'

        );









        $sheet->setCellValue(

            'R' . $row,

            '=SUM(R' .

            $firstItemRow .

            ':R' .

            $lastItemRow .

            ')'

        );









        $sheet->setCellValue(

            'W' . $row,

            '=SUM(W' .

            $firstItemRow .

            ':W' .

            $lastItemRow .

            ')'

        );









        $sheet->setCellValue(

            'X' . $row,

            '=SUM(X' .

            $firstItemRow .

            ':X' .

            $lastItemRow .

            ')'

        );









        $sheet->setCellValue(

            'Z' . $row,

            '=SUM(Z' .

            $firstItemRow .

            ':Z' .

            $lastItemRow .

            ')'

        );









        $sheet

            ->getStyle(

                'Q' . $row . ':R' . $row

            )

            ->getNumberFormat()

            ->setFormatCode(

                '0.00'

            );





        $sheet

            ->getStyle(

                'W' . $row

            )

            ->getNumberFormat()

            ->setFormatCode(

                '0.00'

            );





        $sheet

            ->getStyle(

                'Z' . $row

            )

            ->getNumberFormat()

            ->setFormatCode(

                '$#,##0.00'

            );



    }









    protected function writePaymentSection(
        Worksheet $sheet,
        ExportIpl $invoice,
        int $startRow,
        $bankDetailsText = null
    ) {
        $ar = ExportAr::with([
            'payments' => function ($query) {
                $query
                    ->orderBy('payment_date')
                    ->orderBy('id');
            },
        ])
            ->where('export_ipl_id', $invoice->id)
            ->first();

        $payments = $ar
            ? $ar->payments
            : collect();

        $toNumber = function ($value) {
            if ($value === null || $value === '') {
                return 0.0;
            }

            if (is_numeric($value)) {
                return (float) $value;
            }

            $value = str_replace([',', ' '], '', (string) $value);

            return is_numeric($value) ? (float) $value : 0.0;
        };

        $normalize = function ($value) {
            $value = (string) ($value ?? '');
            $value = preg_replace(
                '/[\x{00AD}\x{200B}-\x{200D}\x{FEFF}]/u',
                '',
                $value
            );
            $value = preg_replace('/\s+/u', ' ', $value);
            return trim($value);
        };

        $depositByPo = [];
        $depositWithoutPo = [];
        $pelunasanTotal = 0.0;
        $surchargeTotals = [
            1 => 0.0,
            2 => 0.0,
            3 => 0.0,
        ];

        foreach ($payments as $payment) {
            $type = strtolower(trim((string) ($payment->payment_type ?? '')));
            $amount = $toNumber($payment->amount ?? 0);

            if ($amount <= 0) {
                continue;
            }

            if ($type === 'deposit') {
                $refPo = $normalize($payment->ref_po ?? '');

                if ($refPo === '') {
                    $source = trim(
                        (string) (
                            $payment->keterangan
                            ?? $payment->reference
                            ?? ''
                        )
                    );

                    if (preg_match('/\bBLDE[\s\-]*[A-Z0-9]+\b/i', $source, $m)) {
                        $refPo = $normalize($m[0]);
                    }
                }

                if ($refPo !== '') {
                    $depositByPo[$refPo] =
                        ($depositByPo[$refPo] ?? 0.0) + $amount;
                } else {
                    $depositWithoutPo[] = $amount;
                }

                continue;
            }

            if ($type === 'pelunasan') {
                $pelunasanTotal += $amount;
                continue;
            }

            if ($type === 'surcharge') {
                $text = trim(
                    (string) (
                        $payment->keterangan
                        ?? $payment->reference
                        ?? ''
                    )
                );

                $slot = 1;

                if (preg_match('/surcharge\s*([123])/i', $text, $m)) {
                    $slot = (int) $m[1];
                }

                if (!isset($surchargeTotals[$slot])) {
                    $slot = 1;
                }

                $surchargeTotals[$slot] += $amount;
            }
        }

        $bldeNumbers = collect($invoice->items ?? [])
            ->map(function ($item) use ($normalize) {
                return $normalize(
                    $item->blde
                    ?? $item->po_no
                    ?? ''
                );
            })
            ->filter()
            ->unique()
            ->values();

        $depositRows = [];
        foreach ($bldeNumbers as $blde) {
            $depositRows[] = [
                'label' => './. Deposit ' . $blde,
                'amount' => (float) ($depositByPo[$blde] ?? 0),
            ];
        }

        foreach ($depositWithoutPo as $amount) {
            if (count($depositRows) >= 5) {
                break;
            }

            $depositRows[] = [
                'label' => './. Deposit',
                'amount' => $amount,
            ];
        }

        $depositTemplateCount = 5;
        $depositCount = count($depositRows);
        $extraDepositRows = max(0, $depositCount - $depositTemplateCount);

        if ($extraDepositRows > 0) {
            $finalAmountTemplateRow = $startRow + 9;
            $styleSourceRow = $startRow + 8;

            $sheet->insertNewRowBefore(
                $finalAmountTemplateRow,
                $extraDepositRows
            );

            for ($i = 1; $i <= $extraDepositRows; $i++) {
                $targetRow = $styleSourceRow + $i;

                $sheet->duplicateStyle(
                    $sheet->getStyle("A{$styleSourceRow}:Z{$styleSourceRow}"),
                    "A{$targetRow}:Z{$targetRow}"
                );

                $sourceHeight = $sheet
                    ->getRowDimension($styleSourceRow)
                    ->getRowHeight();

                if ($sourceHeight !== null) {
                    $sheet
                        ->getRowDimension($targetRow)
                        ->setRowHeight($sourceHeight);
                }
            }
        }

        $tradeDiscountRow = $startRow;
        $surcharge1Row = $startRow + 1;
        $surcharge2Row = $startRow + 2;
        $surcharge3Row = $startRow + 3;
        $finalInvoiceRow = $startRow + 3;
        $depositStartRow = $startRow + 4;
        $finalAmountRow = $startRow + 9 + $extraDepositRows;
        $paymentTermsRow = $finalAmountRow + 1;
        $totalOrderRow = $finalAmountRow;

        // Restore the Bankdetails content from the original template into
        // the top-left cell of the shifted payment/bank area.
        if ($bankDetailsText !== null && $bankDetailsText !== '') {
            $sheet->setCellValue(
                'A' . $startRow,
                $bankDetailsText
            );
        }

        if ($ar) {
            $this->setValue(
                $sheet,
                'H' . ($startRow + 2),
                'Payment Information for Accounting:'
            );

            $this->setValue(
                $sheet,
                'I' . ($startRow + 2),
                $invoice->invoice_no
            );

            $this->setDate(
                $sheet,
                'H' . ($startRow + 3),
                $invoice->date
            );
        }

        $totalRow = $startRow - 1;

        $sheet->setCellValue(
            'Z' . $tradeDiscountRow,
            '=Z' . $totalRow . '*8%'
        );

        $sheet->setCellValue(
            'Z' . $surcharge1Row,
            $surchargeTotals[1]
        );

        $sheet->setCellValue(
            'Z' . $surcharge2Row,
            $surchargeTotals[2]
        );

        $sheet->setCellValue(
            'Z' . $surcharge3Row,
            $surchargeTotals[3]
        );

        for ($i = 0; $i < max($depositTemplateCount, $depositCount); $i++) {
            $row = $depositStartRow + $i;

            if (isset($depositRows[$i])) {
                $this->setValue(
                    $sheet,
                    'H' . $row,
                    $depositRows[$i]['label']
                );

                $sheet->setCellValue(
                    'K' . $row,
                    $depositRows[$i]['amount']
                );
            } else {
                $this->setValue(
                    $sheet,
                    'H' . $row,
                    ''
                );

                $sheet->setCellValue(
                    'K' . $row,
                    0
                );
            }
        }

        $sheet->setCellValue(
            'K' . $finalInvoiceRow,
            '=Z' . $totalOrderRow
        );

        $finalFormula = '=K' . $finalInvoiceRow;

        for ($i = 0; $i < max($depositTemplateCount, $depositCount); $i++) {
            $finalFormula .= '-K' . ($depositStartRow + $i);
        }

        if ($pelunasanTotal > 0) {
            $finalFormula .= '-' . $pelunasanTotal;
        }

        $sheet->setCellValue(
            'K' . $finalAmountRow,
            $finalFormula
        );

        $sheet->setCellValue(
            'Z' . $totalOrderRow,
            '=Z' . $totalRow
            . '-Z' . $tradeDiscountRow
            . '+Z' . $surcharge1Row
            . '+Z' . $surcharge2Row
            . '+Z' . $surcharge3Row
        );

        $sheet->getStyle(
            'Z' . $tradeDiscountRow . ':Z' . $totalOrderRow
        )
            ->getNumberFormat()
            ->setFormatCode('$#,##0.00');

        $sheet->getStyle(
            'K' . $depositStartRow . ':K' . ($finalAmountRow + 1)
        )
            ->getNumberFormat()
            ->setFormatCode('$#,##0.00');

        $sheet->getStyle(
            'Z' . $surcharge1Row . ':Z' . $surcharge3Row
        )
            ->getNumberFormat()
            ->setFormatCode('$#,##0.00');

        $sheet->setCellValue(
            'H' . $paymentTermsRow,
            'Payment Terms:'
        );

        $sheet->setCellValue(
            'H' . $paymentTermsRow,
            'Payment Terms:'
        );
    }


    protected function writePaymentFormulas(

        Worksheet $sheet,

        int $row

    ) {







        $sheet->setCellValue(

            'Q' . $row,

            '=SUM(Q16:Q' .

            ($row - 1) .

            ')'

        );









        $sheet->setCellValue(

            'R' . $row,

            '=SUM(R16:R' .

            ($row - 1) .

            ')'

        );









        $sheet->setCellValue(

            'W' . $row,

            '=SUM(W16:W' .

            ($row - 1) .

            ')'

        );









        $sheet->setCellValue(

            'X' . $row,

            '=SUM(X16:X' .

            ($row - 1) .

            ')'

        );









        $sheet->setCellValue(

            'Z' . $row,

            '=SUM(Z16:Z' .

            ($row - 1) .

            ')'

        );





        $sheet

            ->getStyle(

                'Z' . $row

            )

            ->getNumberFormat()

            ->setFormatCode(

                '$#,##0.00'

            );



    }









    protected function copyRowStyle(

        Worksheet $sheet,

        int $sourceRow,

        int $targetRow

    ) {



        $sourceDimension =

            $sheet->getRowDimension(

                $sourceRow

            );





        $targetDimension =

            $sheet->getRowDimension(

                $targetRow

            );





        if (

            $sourceDimension->getRowHeight()

            !== null

        ) {



            $targetDimension->setRowHeight(

                $sourceDimension->getRowHeight()

            );



        }









        for (

            $column = 1;

            $column <= 26;

            $column++

        ) {



            $source =

                $sheet->getCellByColumnAndRow(

                    $column,

                    $sourceRow

                );





            $target =

                $sheet->getCellByColumnAndRow(

                    $column,

                    $targetRow

                );









            $target

                ->getStyle()

                ->applyFromArray(

                    $source

                        ->getStyle()

                        ->exportArray()

                );









            $target

                ->getStyle()

                ->getNumberFormat()

                ->setFormatCode(

                    $source

                        ->getStyle()

                        ->getNumberFormat()

                        ->getFormatCode()

                );









            $target

                ->getStyle()

                ->getAlignment()

                ->setHorizontal(

                    $source

                        ->getStyle()

                        ->getAlignment()

                        ->getHorizontal()

                );





            $target

                ->getStyle()

                ->getAlignment()

                ->setVertical(

                    $source

                        ->getStyle()

                        ->getAlignment()

                        ->getVertical()

                );





            $target

                ->getStyle()

                ->getAlignment()

                ->setWrapText(

                    $source

                        ->getStyle()

                        ->getAlignment()

                        ->getWrapText()

                );



        }



    }









    protected function clearItemRow(

        Worksheet $sheet,

        int $row

    ) {



        for (

            $column = 1;

            $column <= 26;

            $column++

        ) {



            $sheet

                ->getCellByColumnAndRow(

                    $column,

                    $row

                )

                ->setValue(null);



        }



    }









    protected function parseBoxDimension(

        $value

    ): array {



        if (

            $value === null

            || trim((string) $value) === ''

        ) {



            return [

                'l' => 0,

                'w' => 0,

                'h' => 0,

            ];



        }





        $value =

            trim(

                (string) $value

            );









        $value =

            str_replace(

                ['×', 'X'],

                'x',

                $value

            );





        $parts =

            preg_split(

                '/\s*x\s*/i',

                $value

            );





        if (

            count($parts) >= 3

        ) {



            return [

                'l' => (float) $parts[0],

                'w' => (float) $parts[1],

                'h' => (float) $parts[2],

            ];



        }









        $parts =

            preg_split(

                '/\s*[-\\/]\s*/',

                $value

            );





        if (

            count($parts) >= 3

        ) {



            return [

                'l' => (float) $parts[0],

                'w' => (float) $parts[1],

                'h' => (float) $parts[2],

            ];



        }





        return [

            'l' => 0,

            'w' => 0,

            'h' => 0,

        ];



    }









    protected function firstValue(

        ...$values

    ) {



        foreach ($values as $value) {



            if (

                $value !== null

                && $value !== ''

            ) {



                return $value;



            }



        }





        return 0;

    }









    protected function addressLine(

        $value

    ): string {



        if ($value === null) {

            return '';

        }





        return trim(

            preg_replace(

                '/\s+/',

                ' ',

                (string) $value

            )

        );



    }









    protected function setDate(

        Worksheet $sheet,

        string $cell,

        $value

    ) {



        if (

            empty($value)

        ) {



            $sheet->setCellValue(

                $cell,

                ''

            );



            return;



        }





        try {



            $date =

                \Carbon\Carbon::parse(

                    $value

                );





            $sheet->setCellValue(

                $cell,

                \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel(

                    $date->copy()->startOfDay()

                )

            );





            $sheet

                ->getStyle($cell)

                ->getNumberFormat()

                ->setFormatCode(

                    'dd/mm/yyyy'

                );



        } catch (

            \Throwable $e

        ) {



            $sheet->setCellValue(

                $cell,

                (string) $value

            );



        }



    }









    protected function setValue(

        Worksheet $sheet,

        string $cell,

        $value

    ) {



        if (

            is_string($value)

        ) {



            $value =

                $this->cleanInvisible(

                    $value

                );



        }





        $sheet->setCellValue(

            $cell,

            $value

        );



    }









    protected function currency(

        $value

    ): float {



        if (

            $value === null

            || $value === ''

        ) {



            return 0;



        }





        if (

            is_numeric($value)

        ) {



            return (float) $value;



        }





        $value =

            str_replace(

                ['$', ',', ' '],

                '',

                (string) $value

            );





        return (float) $value;



    }









    protected function cleanInvisible(

        $value

    ): string {



        $value =

            (string) $value;









        $value =

            preg_replace(

                '/[\x{00AD}\x{200B}-\x{200D}\x{FEFF}]/u',

                '',

                $value

            );





        return trim(

            $value

        );



    }









    protected function makeFilename(

        ExportIpl $invoice

    ): string {



        $invoiceNo =

            $invoice->invoice_no

            ?? $invoice->id;





        $invoiceNo =

            preg_replace(

                '/[\\/\\\\\\\\:*?"<>|]/',

                '-',

                (string) $invoiceNo

            );





        $invoiceNo =

            preg_replace(

                '/\s+/',

                '-',

                $invoiceNo

            );





        return

            'CIPL-' .

            trim($invoiceNo, '- ') .

            '.xlsx';



    }









    protected function setupPage(

        Worksheet $sheet,

        int $lastRow

    ) {



        $pageSetup =

            $sheet->getPageSetup();









        $pageSetup->setPaperSize(

            \PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_A4

        );









        $pageSetup->setOrientation(

            \PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_PORTRAIT

        );









        $pageSetup->setFitToWidth(

            1

        );





        $pageSetup->setFitToHeight(

            0

        );









        $pageSetup->setHorizontalCentered(

            true

        );









        $pageSetup->setPrintArea(

            'A1:Z' . $lastRow

        );









        $sheet

            ->getPageMargins()

            ->setTop(0.25)

            ->setBottom(0.25)

            ->setLeft(0.25)

            ->setRight(0.25);



    }

}