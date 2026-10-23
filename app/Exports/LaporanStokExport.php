
<?php

namespace App\Exports;

use App\Models\Stok;
use App\Models\TransaksiStok;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class LaporanStokExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    ShouldAutoSize,
    WithColumnFormatting,
    WithStyles
{
    protected $jenis;
    protected $search;

    protected $nomor = 0;

    public function __construct($jenis = null, $search = null)
    {
        $this->jenis = $jenis;
        $this->search = $search;
    }

    /**
     * Ambil data laporan stok.
     * Mengikuti perhitungan pada LaporanController@index.
     */
    public function collection()
    {
        $transactionSummary = TransaksiStok::query()
            ->select(
                'stok_id',
                DB::raw("
                    SUM(
                        CASE
                            WHEN tipe = 'in' THEN qty
                            ELSE 0
                        END
                    ) AS total_in
                "),
                DB::raw("
                    SUM(
                        CASE
                            WHEN tipe = 'out' THEN qty
                            ELSE 0
                        END
                    ) AS total_out
                ")
            )
            ->groupBy('stok_id');

        return Stok::query()
            ->leftJoinSub(
                $transactionSummary,
                'transaction_summary',
                function ($join) {
                    $join->on(
                        'stoks.id',
                        '=',
                        'transaction_summary.stok_id'
                    );
                }
            )
            ->select(
                'stoks.*',
                DB::raw(
                    'COALESCE(transaction_summary.total_in, 0) AS total_in'
                ),
                DB::raw(
                    'COALESCE(transaction_summary.total_out, 0) AS total_out'
                )
            )

            // Filter jenis barang
            ->when($this->jenis, function ($query) {
                $query->where('stoks.jenis', $this->jenis);
            })

            // Pencarian kode barang atau nama barang
            ->when($this->search, function ($query) {
                $search = $this->search;

                $query->where(function ($q) use ($search) {
                    $q->where(
                        'stoks.kode_barang',
                        'LIKE',
                        '%' . $search . '%'
                    )->orWhere(
                        'stoks.nama_barang',
                        'LIKE',
                        '%' . $search . '%'
                    );
                });
            })

            // Urutan sama dengan index
            ->orderBy('stoks.nama_barang')

            ->get()

            // Hitung saldo dengan presisi 3 desimal
            ->map(function ($stok) {
                $totalIn = (float) ($stok->total_in ?? 0);
                $totalOut = (float) ($stok->total_out ?? 0);

                $stok->saldo = round($totalIn - $totalOut, 3);

                return $stok;
            });
    }

    /**
     * Header Excel.
     */
    public function headings(): array
    {
        return [
            'No',
            'Kode Barang',
            'Nama Barang',
            'Jenis',
            'Satuan',
            'Harga',
            'Saldo',
            'Stok In',
            'Stok Out',
            'Tanggal',
            'Aksi',
            'Detail',
        ];
    }

    /**
     * Pemetaan setiap baris Excel.
     */
    public function map($stok): array
    {
        $this->nomor++;

        return [
            $this->nomor,
            $stok->kode_barang,
            $stok->nama_barang,
            $stok->jenis,
            $stok->satuan,
            (float) ($stok->harga ?? 0),
            (float) $stok->saldo,
            (float) $stok->total_in,
            (float) $stok->total_out,
            now()->format('Y-m-d'),
            '-',
            url('/laporan/detail/' . $stok->id),
        ];
    }

    /**
     * Format angka Excel.
     *
     * Maksimal 3 desimal, tanpa nol desimal yang tidak diperlukan.
     * Contoh:
     * 1.600 -> 1.6
     * 1.500 -> 1.5
     * 1.125 -> 1.125
     * 10.000 -> 10
     */
    public function columnFormats(): array
    {
        return [
            'F' => '#,##0.##',
            'G' => '#,##0.###',
            'H' => '#,##0.###',
            'I' => '#,##0.###',
            'J' => 'yyyy-mm-dd',
        ];
    }

    /**
     * Styling worksheet.
     */
    public function styles(Worksheet $sheet)
    {
        // Header tebal
        $sheet->getStyle('A1:L1')
            ->getFont()
            ->setBold(true);

        // Warna latar header
        $sheet->getStyle('A1:L1')
            ->getFill()
            ->setFillType(
                \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID
            );

        $sheet->getStyle('A1:L1')
            ->getFill()
            ->getStartColor()
            ->setRGB('D9EAF7');

        // Header rata tengah
        $sheet->getStyle('A1:L1')
            ->getAlignment()
            ->setHorizontal('center');

        // Aktifkan filter Excel
        $sheet->setAutoFilter(
            'A1:L' . $sheet->getHighestRow()
        );

        // Bekukan header
        $sheet->freezePane('A2');

        return [];
    }
}
