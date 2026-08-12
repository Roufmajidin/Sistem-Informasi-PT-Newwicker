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

class BarangJadiExport implements WithEvents, WithCustomStartCell, WithTitle
{
    protected $timelines;

    protected $from;
    protected $to;
    public function title(): string
    {
        return 'Barang Jadi';
    }
    public function __construct($timelines, $from, $to)
    {
        $this->timelines = $timelines;
        $this->from = $from;
        $this->to = $to;
    }

    //utama
    public function sheets(): array
    {
        return [

            new BarangJadiExport(
                $this->timelines,
                $this->from,
                $this->to,
            ),

            new QcExports(
                $this->inspection,
                $this->from,
                $this->to
            ),

        ];
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

                // =========================
// HEADER
// =========================
    
                // Judul
                $sheet->mergeCells('A1:K1');
                $sheet->setCellValue(
                    'A1',
                    'REKAPITULASI PEMASUKAN BARANG JADI SUB'
                );

                // Periode
                $sheet->mergeCells('A2:K2');
                $sheet->setCellValue(
                    'A2',
                    'TANGGAL : '
                    . Carbon::parse($this->from)->format('d/m/Y')
                    . ' s/d '
                    . Carbon::parse($this->to)->format('d/m/Y')
                );
                // Download info
                $sheet->mergeCells('A3:K3');
                $sheet->setCellValue(
                    'A3',
                    'Downloaded by system on : ' . now()->format('d/m/Y H:i:s')
                );

                $sheet->getStyle('A3:K3')->getFont()
                    ->setItalic(true)
                    ->setSize(9);

                $sheet->getStyle('A3:K3')->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                // Style Judul
                $sheet->getStyle('A1:K1')->getFont()
                    ->setBold(true)
                    ->setSize(16);

                $sheet->getStyle('A1:K2')->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);

                // Tinggi baris
                $sheet->getRowDimension(1)->setRowHeight(24);
                $sheet->getRowDimension(2)->setRowHeight(20);

                $row = 6;

                $sheet->fromArray([
                    [
                        'NO',
                        'DATE',
                        'PO',
                        'ARTICLE',
                        'DESCRIPTION',
                        'KATEGORI',
                        'SUB',
                        'NO SPK',
                        'QTY SPK',
                        'QTY MASUK',
                        'TYPE',
                        'REMARK'
                    ]
                ], null, "A{$row}");

                $row++;
                foreach ($this->timelines as $i => $item) {

                    $spkItem = collect($item->spk->data['items'] ?? [])
                        ->firstWhere('detail_po_id', $item->detail_po_id);

                    $sheet->fromArray([
                        [
                            $i + 1,
                            $item->date,
                            optional($item->po)->order_no,
                            $item->detailPo->detail['article_nr_'] ?? '-',
                            $item->detailPo->detail['description'] ?? '-',
                            $item->spk->data['sup'] ?? '-',
                            $item->spk->data['no_spk'] ?? '-',
                            $item->spk->data['kategori'] ?? '-',
                            $spkItem['qty'] ?? 0,
                            $item->qty,
                            $item->type,
                            $item->remark
                        ]
                    ], null, "A{$row}");

                    $row++;
                }

                $dataEndRow = $row - 1;
                $row = $dataEndRow + 3;
                // Inventory By
                $sheet->mergeCells("A{$row}:C{$row}");
                $sheet->setCellValue("A{$row}", "Inventory By,");

                $sheet->getStyle("A{$row}")
                    ->getFont()
                    ->setBold(true)
                    ->setSize(10);

                $sheet->getStyle("A{$row}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Beri ruang untuk tanda tangan
                $row += 4;

                // Garis tanda tangan
                $sheet->mergeCells("A{$row}:C{$row}");
                $sheet->setCellValue("A{$row}", "________________________");

                $sheet->getStyle("A{$row}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Nama
                $row++;

                $sheet->mergeCells("A{$row}:C{$row}");
                $sheet->setCellValue("A{$row}", "Siti Maryanti");

                $sheet->getStyle("A{$row}")
                    ->getFont()
                    ->setBold(true);

                $sheet->getStyle("A{$row}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Jabatan
                $row++;

                $sheet->mergeCells("A{$row}:C{$row}");
                $sheet->setCellValue("A{$row}", "Adm. Produksi");

                $sheet->getStyle("A{$row}")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);
                // Border seluruh tabel
                $sheet->getStyle("A6:L{$dataEndRow}")

                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);

                // Header warna biru
                $sheet->getStyle("A5:L6")
                    ->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB('FF1E2B45');

                $sheet->getStyle("A6:L6")
                    ->getFont()
                    ->getColor()
                    ->setARGB('FFFFFFFF');

                $sheet->getStyle("A6:K6")
                    ->getFont()
                    ->setBold(true);

                // Header rata tengah
                $sheet->getStyle("A5:K5")
                    ->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                    ->setVertical(Alignment::VERTICAL_CENTER);

                // Semua data rata tengah secara vertikal
                $sheet->getStyle("A7:L{$dataEndRow}")
                    ->getAlignment()
                    ->setVertical(Alignment::VERTICAL_CENTER);

                // Lebar kolom
                $sheet->getColumnDimension('A')->setWidth(5);
                $sheet->getColumnDimension('B')->setWidth(18);
                $sheet->getColumnDimension('C')->setWidth(15);
                $sheet->getColumnDimension('D')->setWidth(18);
                $sheet->getColumnDimension('E')->setWidth(45);
                $sheet->getColumnDimension('F')->setWidth(15);
                $sheet->getColumnDimension('G')->setWidth(22);
                $sheet->getColumnDimension('H')->setWidth(10);
                $sheet->getColumnDimension('I')->setWidth(10);
                $sheet->getColumnDimension('J')->setWidth(12);
                $sheet->getColumnDimension('K')->setWidth(30);

            }

        ];
    }
}