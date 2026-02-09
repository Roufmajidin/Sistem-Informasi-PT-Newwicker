<?php

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Events\BeforeExport;

class SpkExport implements FromCollection, WithEvents, WithStartRow
{
    protected $spk;

    public function __construct($spk)
    {
        $this->spk = $spk;
    }

    /**
     * ==========================
     * DATA ITEMS (TABLE)
     * ==========================
     */
    public function collection()
    {
        $rows = collect();

        foreach ($this->spk->items as $item) {

            // 🔥 mulai dari kolom A sampai H
            $rows->push([
                $item['kode'] ?? '',
                $item['nama'] ?? '',
                $item['p'] ?? '',
                $item['l'] ?? '',
                $item['t'] ?? '',
                $item['qty'] ?? '',
                $item['satuan'] ?? '',
                $item['harga'] ?? '',
            ]);
        }

        return $rows;
    }

    /**
     * ==========================
     * ITEMS MULAI ROW 15
     * ==========================
     */
    public function startRow(): int
    {
        return 15;
    }

    /**
     * ==========================
     * LOAD TEMPLATE + HEADER
     * ==========================
     */
    public function registerEvents(): array
    {
        return [

            /**
             * 🔥 LOAD TEMPLATE
             */
            BeforeExport::class => function (BeforeExport $event) {

                $template = \PhpOffice\PhpSpreadsheet\IOFactory::load(
                    storage_path('app/templates/SPK-TEMPLATE.xlsx')
                );

                // 🔥 get spreadsheet dari writer
                $spreadsheet = $event->writer->getDelegate();

                // hapus sheet bawaan
                $spreadsheet->disconnectWorksheets();

                // inject sheet template
                $spreadsheet->addExternalSheet(
                    $template->getActiveSheet()
                );
            },

            /**
             * 🔥 ISI CELL HEADER
             */
            AfterSheet::class   => function (AfterSheet $event) {

                $sheet = $event->sheet;

                // 🔥 HEADER (B7 - B10)
                $sheet->setCellValue('B7', $this->spk->data['no_spk'] ?? '');
                $sheet->setCellValue('B8', $this->spk->data['sup'] ?? '');
                $sheet->setCellValue('B9', $this->spk->data['tgl_terima'] ?? '');
                $sheet->setCellValue('B10', $this->spk->data['tgl_selesai'] ?? '');

                // 🔥 NO PO
                $sheet->setCellValue('K7', $this->spk->data['no_po'] ?? '');
            },
        ];
    }
}
