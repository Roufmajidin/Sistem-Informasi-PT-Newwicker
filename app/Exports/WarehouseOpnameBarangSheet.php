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
    public function __construct(
        private Collection $stoks,
        private Collection $transactions
    ) {
    }

    public function title(): string
    {
        return 'barang';
    }

    public function array(): array
    {
        $now = now();

        $rows = [
            [
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                'downloaded at ' . $now->format('d/m/Y H:i:s'),
                '',
                ''
            ],
            [
                '',
                '',
                '',
                '',
                'PT NEWWICKER INDONESIA',
                '',
                '',
                '',
                '',
                '',
                '',
                ''
            ],
            [
                '',
                '',
                '',
                '',
                'WAREHOUSE REPORT OPNAME',
                '',
                '',
                '',
                '',
                '',
                '',
                ''
            ],
            [],
            [
                'no',
                'nama',
                'kode',
                'jenis',
                'id_barang',
                'harga',
                'opname saldo',
                'date_opname',
                'AFTER OPNAME',
                '',
                'saldo',
                'date_saldo'
            ],
            [
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                'in',
                'out',
                '',
                ''
            ],
        ];

        $grouped = $this->transactions->groupBy('stok_id');

        foreach ($this->stoks as $index => $stok) {

            $items = $grouped->get($stok->id, collect());

            $in = (float) $items
                ->where('tipe', 'in')
                ->sum('qty');

            $out = (float) $items
                ->where('tipe', 'out')
                ->sum('qty');

            $opname = (float) ($stok->stok_awal ?? 0);

            $harga = $stok->harga !== null
                ? (float) $stok->harga
                : null;

            $saldo = $opname + $in - $out;

            $rows[] = [
                $index + 1,
                $stok->nama_barang,
                $stok->kode_barang,
                $stok->jenis,
                (int) $stok->id,
                $harga,
                $opname,
                $stok->opname_at
                ? \Carbon\Carbon::parse($stok->opname_at)
                    ->format('d/m/Y H:i:s')
                : '',
                $in,
                $out,
                $saldo,
                $now->format('d/m/Y H:i:s'),
            ];
        }

        $rows[] = [];

        $rows[] = [
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            'Cirebon, ' . $now->format('d/m/Y'),
            '',
            ''
        ];

        $rows[] = [
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            'Warehouse',
            '',
            ''
        ];

        return $rows;
    }

    public function styles(Worksheet $sheet)
    {
        $lastDataRow = max(7, 6 + $this->stoks->count());

        // Header perusahaan
        $sheet->mergeCells('E2:H2');
        $sheet->mergeCells('E3:H3');

        // Header transaksi IN / OUT
        $sheet->mergeCells('I5:J5');

        // Format header
        $sheet->getStyle('A5:L6')
            ->getFont()
            ->setBold(true);

        $sheet->getStyle('A5:L6')
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->getStyle('A5:L6')
            ->getAlignment()
            ->setVertical(Alignment::VERTICAL_CENTER);

        $sheet->getStyle('A5:L6')
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle('thin');

        // Judul
        $sheet->getStyle('E2:H3')
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Isi tabel
        $sheet->getStyle("A7:L{$lastDataRow}")
            ->getFont()
            ->setBold(false);

        $sheet->getStyle("A7:L{$lastDataRow}")
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_LEFT);

        // Kolom angka
        $sheet->getStyle("E7:G{$lastDataRow}")
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        $sheet->getStyle("I7:K{$lastDataRow}")
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        // Harga: angka Rupiah tanpa desimal
        $sheet->getStyle("F7:F{$lastDataRow}")
            ->getNumberFormat()
            ->setFormatCode('"Rp" #,##0');

        // Qty opname
        $sheet->getStyle("G7:G{$lastDataRow}")
            ->getNumberFormat()
            ->setFormatCode('#,##0.000');

        // Qty masuk, keluar, saldo
        $sheet->getStyle("I7:K{$lastDataRow}")
            ->getNumberFormat()
            ->setFormatCode('#,##0.000');

        // Lebar kolom
        foreach ([
            'A' => 7,
            'B' => 34,
            'C' => 20,
            'D' => 22,
            'E' => 15,
            'F' => 19,
            'G' => 19,
            'H' => 23,
            'I' => 15,
            'J' => 15,
            'K' => 18,
            'L' => 23,
        ] as $col => $width) {
            $sheet->getColumnDimension($col)
                ->setWidth($width);
        }

        // Freeze header
        $sheet->freezePane('A7');

        return [];
    }
}
