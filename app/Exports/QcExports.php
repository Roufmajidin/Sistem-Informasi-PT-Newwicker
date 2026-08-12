<?php

namespace App\Exports;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use Maatwebsite\Excel\Concerns\WithTitle;
class QcExports implements WithEvents, WithCustomStartCell, WithTitle
{
    protected $inspection;
    protected $from;
    protected $to;

    public function title(): string
    {
        return 'QC PASS';
    }
    public function __construct($inspection, $from, $to)
    {
        $this->inspection = $inspection;
        $this->from = $from;
        $this->to = $to;
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

                // ===== COMPANY HEADER =====
                $sheet->mergeCells('A1:J1');
                $sheet->setCellValue('A1', 'PT NEWWICKER INDONESIA');

                $sheet->mergeCells('A2:J2');
                $sheet->setCellValue('A2', 'QC PASS REPORT');

                $sheet->mergeCells('A3:J3');
                $sheet->setCellValue(
                    'A3',
                    'Periode : '
                    . Carbon::parse($this->from)->format('d M Y')
                    . ' - '
                    . Carbon::parse($this->to)->format('d M Y')
                );

                $sheet->getStyle('A1:J3')->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->getStyle('A1:J1')->getFont()->setBold(true)->setSize(15);
                $sheet->getStyle('A2:J2')->getFont()->setBold(true)->setSize(12);
                $sheet->getStyle('A3:J3')->getFont()->setBold(true);

                // ===== HEADER =====
                $row = 5;

                $header = [
                    'NO',
                    'DATE',
                    'PO',
                    'ITEM',
                    'BUYER',
                    'NO SPK',
                    'SUB',
                    'KATEGORI',
                    'PERSON',
                    'TOTAL',
                    'PASS',
                    'REJECT'
                ];

                $sheet->fromArray([$header], null, "A{$row}");

                $sheet->getStyle("A{$row}:J{$row}")
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB('FF1E2B45');

                $sheet->getStyle("A{$row}:J{$row}")
                    ->getFont()
                    ->getColor()
                    ->setARGB('FFFFFFFF');

                $sheet->getStyle("A{$row}:J{$row}")
                    ->getFont()
                    ->setBold(true);

                $row++;

                $totalInspect = 0;
                $totalPass = 0;
                $totalReject = 0;
                $inspection = $this->inspection
                    ->sortBy(function ($item) {

                        $spk = optional($item->spk)->data['no_spk'] ?? 'ZZZZZZ';

                        return [
                            $spk,
                            optional($item->spk)->data['sup'] ?? '',
                            optional($item->spk)->data['kategori'] ?? '',
                            $item->tanggal_inspect
                        ];

                    })
                    ->values();
                foreach ($inspection as $i => $item) {

                    $detail = optional($item->detailPo)->detail;

                    if (is_string($detail)) {
                        $detail = json_decode($detail, true);
                    }

                    if (!is_array($detail)) {
                        $detail = [];
                    }

                    $sheet->fromArray([
                        [
                            $i + 1,
                            Carbon::parse($item->tanggal_inspect)->format('d-m-Y H:i'),
                            optional($item->po)->order_no,
                            $detail['description'] ?? '-',
                            optional($item->po)->company_name,
                            optional($item->spk)->data['no_spk'] ?? '-',
                            optional($item->spk)->data['sup'] ?? 'Non-SPK',
                            optional($item->spk)->data['kategori'] ?? '-',
                            optional($item->user)->name,
                            $item->jumlah_inspect,
                            $item->passed,
                            $item->rejected
                        ]
                    ], null, "A{$row}");

                    $totalInspect += $item->jumlah_inspect;
                    $totalPass += $item->passed;
                    $totalReject += $item->rejected;

                    $row++;
                }

                $sheet->mergeCells("A{$row}:G{$row}");
                $sheet->setCellValue("A{$row}", "TOTAL");

                $sheet->setCellValue("H{$row}", $totalInspect);
                $sheet->setCellValue("I{$row}", $totalPass);
                $sheet->setCellValue("J{$row}", $totalReject);

                $sheet->getStyle("A5:L{$row}")
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);

                $sheet->getColumnDimension('A')->setWidth(5);
                $sheet->getColumnDimension('B')->setWidth(18);
                $sheet->getColumnDimension('C')->setWidth(16);
                $sheet->getColumnDimension('D')->setWidth(45);
                $sheet->getColumnDimension('E')->setWidth(25);
                $sheet->getColumnDimension('F')->setWidth(18);
                $sheet->getColumnDimension('G')->setWidth(15);
                $sheet->getColumnDimension('H')->setWidth(18);
                $sheet->getColumnDimension('I')->setWidth(20);
                $sheet->getColumnDimension('J')->setWidth(10);
                $sheet->getColumnDimension('K')->setWidth(10);
                $sheet->getColumnDimension('L')->setWidth(10);

                $row += 3;

                $sheet->mergeCells("A{$row}:C{$row}");
                $sheet->mergeCells("D{$row}:F{$row}");
                $sheet->mergeCells("G{$row}:J{$row}");

                $sheet->setCellValue("A{$row}", "Prepared By");
                $sheet->setCellValue("D{$row}", "Checked By");
                $sheet->setCellValue("G{$row}", "Approved By");

                $row += 5;

                $sheet->setCellValue(
                    "A{$row}",
                    "Generated : " . now()->format('d-m-Y H:i:s')
                );
            }
        ];
    }
}