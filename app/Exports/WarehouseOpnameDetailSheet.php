<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class WarehouseOpnameDetailSheet implements FromArray, WithTitle, WithStyles
{
    public function __construct(
        private Collection $stoks,
        private Collection $transactions
    ) {
    }

    public function title(): string
    {
        return 'detail';
    }

    public function array(): array
    {
        $rows = [
            [],
            [],
            [
                'no',
                'nama barang',
                'jenis bahan',
                'sup id',
                'spk/no po id',
                'in/out',
                'qty',
                'harga',
                'satuan',
                'date',
            ],
        ];

        $stocksById = $this->stoks->keyBy('id');

        foreach ($this->transactions as $index => $transaction) {

            $stok = $stocksById->get($transaction->stok_id);

            $data = $transaction->spk?->data ?? [];

            $supplierName = $data['sup'] ?? '';

            $spkPo = trim(implode(' / ', array_filter([
                $data['no_spk'] ?? null,
                $data['no_po'] ?? $transaction->po ?? null,
            ])));

            $harga = $stok && $stok->harga !== null
                ? (float) $stok->harga
                : null;

            $rows[] = [
                $index + 1,
                $stok?->nama_barang ?? '',
                $stok?->jenis ?? '',
                $supplierName,
                $spkPo,
                strtoupper((string) $transaction->tipe),
                (float) $transaction->qty,
                $harga,
                $stok?->satuan ?? '',
                $transaction->tanggal
                ? \Carbon\Carbon::parse($transaction->tanggal)
                    ->format('d/m/Y')
                : '',
            ];
        }

        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = max(
            4,
            3 + $this->transactions->count()
        );

        // Header kuning
        $sheet->getStyle('A3:J3')
            ->getFill()
            ->setFillType(Fill::FILL_SOLID);

        $sheet->getStyle('A3:J3')
            ->getFill()
            ->getStartColor()
            ->setRGB('FFFF00');

        $sheet->getStyle('A3:J3')
            ->getFont()
            ->setBold(true);

        // Isi tabel
        $sheet->getStyle("A4:J{$lastRow}")
            ->getFont()
            ->setBold(false);

        $sheet->getStyle("A4:J{$lastRow}")
            ->getFill()
            ->setFillType(Fill::FILL_NONE);

        // Lebar kolom
        foreach ([
            'A' => 7,
            'B' => 35,
            'C' => 22,
            'D' => 26,
            'E' => 49,
            'F' => 13,
            'G' => 15,
            'H' => 19,
            'I' => 15,
            'J' => 19,
        ] as $col => $width) {
            $sheet->getColumnDimension($col)
                ->setWidth($width);
        }

        // Qty: maksimal 3 desimal
        $sheet->getStyle("G4:G{$lastRow}")
            ->getNumberFormat()
            ->setFormatCode('#,##0.###');

        // Harga: format Rupiah
        $sheet->getStyle("H4:H{$lastRow}")
            ->getNumberFormat()
            ->setFormatCode('"Rp" #,##0');

        // Freeze header
        $sheet->freezePane('A4');

        // Tidak menggunakan AutoFilter
        return [];
    }
}
