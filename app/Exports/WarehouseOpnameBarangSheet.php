<?php
namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class WarehouseOpnameBarangSheet implements FromArray, WithTitle, WithStyles
{
    public function __construct(private Collection $stoks, private Collection $transactions) {}
    public function title(): string { return 'barang'; }

    public function array(): array
    {
        $now = now();
        $rows = [
            ['', '', '', '', '', '', '', '', 'downloaded at ' . $now->format('d/m/Y H:i:s'), '', ''],
            ['', '', '', '', 'PT NEWWICKER INDONESIA', '', '', '', '', '', ''],
            ['', '', '', '', 'WAREHOUSE REPORT OPNAME', '', '', '', '', '', ''],
            [],
            ['no','nama','kode','jenis','id_barang','opname saldo','date_opname','AFTER OPNAME','','saldo','date_saldo'],
            ['','','','','','','','in','out','',''],
        ];
        $grouped = $this->transactions->groupBy('stok_id');
        foreach ($this->stoks as $index => $stok) {
            $items = $grouped->get($stok->id, collect());
            $in = (float) $items->where('tipe', 'in')->sum('qty');
            $out = (float) $items->where('tipe', 'out')->sum('qty');
            $opname = (float) ($stok->stok_awal ?? 0);
            $rows[] = [
                $index + 1, $stok->nama_barang, $stok->kode_barang, $stok->jenis,
                (int) $stok->id, $opname,
                $stok->opname_at ? \Carbon\Carbon::parse($stok->opname_at)->format('d/m/Y H:i:s') : '',
                $in, $out, $opname + $in - $out, $now->format('d/m/Y H:i:s'),
            ];
        }
        $rows[] = [];
        $rows[] = ['', '', '', '', '', '', '', '', 'Cirebon, ' . $now->format('d/m/Y'), '', ''];
        $rows[] = ['', '', '', '', '', '', '', '', 'Warehouse', '', ''];
        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->mergeCells('E2:G2');
        $sheet->mergeCells('E3:G3');
        $sheet->mergeCells('H5:I5');
        $sheet->getStyle('A5:K6')->getFont()->setBold(true);
        $sheet->getStyle('A5:K6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A5:K6')->getBorders()->getAllBorders()->setBorderStyle('thin');
        $sheet->getStyle('E2:G3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A7:K' . max(7, 6 + $this->stoks->count()))->getFont()->setBold(false);
        $sheet->getStyle('A7:K' . max(7, 6 + $this->stoks->count()))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
        $sheet->getStyle('E7:F' . max(7, 6 + $this->stoks->count()))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('H7:J' . max(7, 6 + $this->stoks->count()))->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle('H7:J' . max(7, 6 + $this->stoks->count()))->getNumberFormat()->setFormatCode('#,##0.000');
        $sheet->getStyle('F7:F' . max(7, 6 + $this->stoks->count()))->getNumberFormat()->setFormatCode('#,##0.000');
        foreach (['A'=>7,'B'=>34,'C'=>20,'D'=>22,'E'=>15,'F'=>19,'G'=>23,'H'=>15,'I'=>15,'J'=>18,'K'=>23] as $col=>$width) $sheet->getColumnDimension($col)->setWidth($width);
        $sheet->freezePane('A7');
        return [];
    }
}
