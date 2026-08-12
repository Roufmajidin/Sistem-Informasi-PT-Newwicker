<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class WarehouseHistoryExport implements
    WithEvents,
    WithCustomStartCell,
    WithTitle
{
    protected $histories;
    protected $from;
    protected $to;

    public function __construct($histories, $from, $to)
    {
        $this->histories = $histories;
        $this->from = $from;
        $this->to = $to;
    }

    public function title(): string
    {
        return 'Warehouse History';
    }

    public function startCell(): string
    {
        return 'A1';
    }

    public function registerEvents(): array
    {
        return [

            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet->getDelegate();

                /*
                |--------------------------------------------------------------------------
                | HEADER
                |--------------------------------------------------------------------------
                */

                $sheet->mergeCells('A1:L1');

                $sheet->setCellValue(
                    'A1',
                    'REKAPITULASI HISTORY MUTASI WAREHOUSE'
                );

                $sheet->mergeCells('A2:L2');

                $sheet->setCellValue(
                    'A2',
                    'TANGGAL : '
                    . Carbon::parse($this->from)->format('d/m/Y')
                    . ' s/d ' .
                    Carbon::parse($this->to)->format('d/m/Y')
                );

                $sheet->mergeCells('A3:L3');

                $sheet->setCellValue(
                    'A3',
                    'Downloaded by system on : '
                    . now()->format('d/m/Y H:i:s')
                );

                /*
                |--------------------------------------------------------------------------
                | STYLE HEADER
                |--------------------------------------------------------------------------
                */

                $sheet->getStyle('A1:L3')
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->getStyle('A1:L1')
                    ->getFont()
                    ->setBold(true)
                    ->setSize(16);

                $sheet->getStyle('A2:L2')
                    ->getFont()
                    ->setBold(true);

                $sheet->getStyle('A3:L3')
                    ->getFont()
                    ->setItalic(true)
                    ->setSize(9);

                $sheet->getStyle('A3:L3')
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                $sheet->getRowDimension(1)
                    ->setRowHeight(24);

                /*
                |--------------------------------------------------------------------------
                | TABLE HEADER
                |--------------------------------------------------------------------------
                */

                $row = 5;

                $sheet->fromArray([
                    [
                        'NO',
                        'DESCRIPTION',
                        'KODE BARANG',
                        'JENIS',
                        'TANGGAL',
                        'QTY',
                        'IN / OUT',
                        'SATUAN',
                        'SPK / INV',
                        'PO NUMBER',
                        'REMARK',
                        'CREATED AT'
                    ]
                ], null, "A{$row}");

                /*
                |--------------------------------------------------------------------------
                | STYLE HEADER TABLE
                |--------------------------------------------------------------------------
                */

                $sheet->getStyle("A{$row}:L{$row}")
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB('FF1E2B45');

                $sheet->getStyle("A{$row}:L{$row}")
                    ->getFont()
                    ->getColor()
                    ->setARGB('FFFFFFFF');

                $sheet->getStyle("A{$row}:L{$row}")
                    ->getFont()
                    ->setBold(true);

                $sheet->getStyle("A{$row}:L{$row}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);

                $row++;

                $totalIn = 0;
                $totalOut = 0;

                foreach ($this->histories as $i => $history) {

                    $stok = $history->stok;

                    $sheet->fromArray([
                        [
                            $i + 1,

                            optional($stok)->nama_barang,

                            optional($stok)->kode_barang,

                            optional($stok)->jenis,

                            Carbon::parse($history->tanggal)->format('d/m/Y'),

                            $history->qty,

                            strtoupper($history->tipe),

                            optional($stok)->satuan,

                            optional($history->spk)->data['no_spk']
                            ?? $history->inv_no
                            ?? '-',

                            $history->po,

                            $history->keterangan,

                            Carbon::parse($history->created_at)
                                ->format('d/m/Y H:i')

                        ]
                    ], null, "A{$row}");

                    /*
                    |--------------------------------------------------------------------------
                    | Warna IN / OUT
                    |--------------------------------------------------------------------------
                    */

                    if (strtolower($history->tipe) == 'in') {

                        $sheet->getStyle("G{$row}")
                            ->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()
                            ->setARGB('FF28A745');

                        $totalIn += $history->qty;

                    } else {

                        $sheet->getStyle("G{$row}")
                            ->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()
                            ->setARGB('FFDC3545');

                        $totalOut += $history->qty;

                    }

                    $sheet->getStyle("G{$row}")
                        ->getFont()
                        ->getColor()
                        ->setARGB('FFFFFFFF');

                    $sheet->getStyle("G{$row}")
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    $row++;
                }
                $dataEndRow = $row - 1;
                $row++;

                $sheet->mergeCells("A{$row}:E{$row}");
                $sheet->setCellValue("A{$row}", "TOTAL MASUK");

                $sheet->setCellValue("F{$row}", $totalIn);

                $row++;

                $sheet->mergeCells("A{$row}:E{$row}");
                $sheet->setCellValue("A{$row}", "TOTAL KELUAR");

                $sheet->setCellValue("F{$row}", $totalOut);
                $sheet->getStyle("A5:L{$dataEndRow}")
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);
                $sheet->getStyle("A6:L{$dataEndRow}")
                    ->getAlignment()
                    ->setVertical(Alignment::VERTICAL_CENTER);

                $sheet->getStyle("A6:A{$dataEndRow}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->getStyle("F6:F{$dataEndRow}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getColumnDimension('A')->setWidth(6);
                $sheet->getColumnDimension('B')->setWidth(35);
                $sheet->getColumnDimension('C')->setWidth(15);
                $sheet->getColumnDimension('D')->setWidth(18);
                $sheet->getColumnDimension('E')->setWidth(15);
                $sheet->getColumnDimension('F')->setWidth(10);
                $sheet->getColumnDimension('G')->setWidth(10);
                $sheet->getColumnDimension('H')->setWidth(10);
                $sheet->getColumnDimension('I')->setWidth(22);
                $sheet->getColumnDimension('J')->setWidth(18);
                $sheet->getColumnDimension('K')->setWidth(35);
                $sheet->getColumnDimension('L')->setWidth(18);
                $row += 4;

                $sheet->mergeCells("I{$row}:L{$row}");
                $sheet->setCellValue("I{$row}", "Inventory By,");

                $sheet->getStyle("I{$row}:L{$row}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $row += 4;

                $sheet->getStyle("I{$row}:L{$row}")
                    ->getBorders()
                    ->getTop()
                    ->setBorderStyle(Border::BORDER_THIN);

                $row++;

                $sheet->mergeCells("I{$row}:L{$row}");
                $sheet->setCellValue("I{$row}", "Sumanti");

                $sheet->getStyle("I{$row}:L{$row}")
                    ->getFont()
                    ->setBold(true);

                $sheet->getStyle("I{$row}:L{$row}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $row++;

                $sheet->mergeCells("I{$row}:L{$row}");
                $sheet->setCellValue("I{$row}", "Adm. Warehouse");

                $sheet->getStyle("I{$row}:L{$row}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                ;
            }
        ];
    }
}