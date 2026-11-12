<?php
namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class WarehouseOpnameDetailSheet implements FromArray, WithTitle, WithStyles
{
    public function __construct(private Collection $stoks, private Collection $transactions) {}
    public function title(): string { return 'detail'; }

    public function array(): array
    {
        $rows = [[], [], ['no','nama barang','jenis bahan','sup id','spk/no po id','in/out','qty','satuan','date']];
        $stocksById = $this->stoks->keyBy('id');
        foreach ($this->transactions as $index => $transaction) {
            $stok = $stocksById->get($transaction->stok_id);
            $data = $transaction->spk?->data ?? [];
            $supplierName = $data['sup'] ?? ''; // JSON menyimpan nama supplier, bukan supplier ID.
            $spkPo = trim(implode(' / ', array_filter([$data['no_spk'] ?? null, $data['no_po'] ?? $transaction->po ?? null])));
            $rows[] = [
                $index + 1, $stok?->nama_barang ?? '', $stok?->jenis ?? '',
                $supplierName, $spkPo, strtoupper((string) $transaction->tipe),
                (float) $transaction->qty, $stok?->satuan ?? '',
                $transaction->tanggal ? \Carbon\Carbon::parse($transaction->tanggal)->format('d/m/Y') : '',
            ];
        }
        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A3:I3')->getFill()->setFillType('solid')->getStartColor()->setRGB('FFFF00');
        $sheet->getStyle('A3:I3')->getFont()->setBold(true);
        $sheet->getStyle('A4:I' . max(4, 3 + $this->transactions->count()))->getFont()->setBold(false);
        $sheet->getStyle('A4:I' . max(4, 3 + $this->transactions->count()))->getFill()->setFillType('none');
        foreach (['A'=>7,'B'=>35,'C'=>22,'D'=>26,'E'=>49,'F'=>13,'G'=>15,'H'=>15,'I'=>19] as $col=>$width) $sheet->getColumnDimension($col)->setWidth($width);
        $sheet->getStyle('G4:G' . max(4, 3 + $this->transactions->count()))->getNumberFormat()->setFormatCode('#,##0.000');
        $sheet->freezePane('A4');
        // Tidak menggunakan AutoFilter agar baris tidak terlihat disorot kuning saat file dibuka.
        return [];
    }
}
