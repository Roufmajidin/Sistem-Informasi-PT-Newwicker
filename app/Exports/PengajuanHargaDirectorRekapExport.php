<?php

namespace App\Exports;

use App\Models\PengajuanHargaPekerjaan;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithCustomStartCell;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class PengajuanHargaDirectorRekapExport implements
    FromCollection,
    WithHeadings,
    WithStyles,
    ShouldAutoSize,
    WithCustomStartCell,
    WithDrawings,
    WithEvents
{
    private ?Collection $records = null;
    private array $imagePaths = [];

    public function __construct(
        private readonly string $startDate,
        private readonly string $endDate
    ) {
    }

    public function startCell(): string
    {
        return 'A5';
    }

    public function headings(): array
    {
        return [
            'Gambar',
            'Qty',
            'Item',
            'Pekerjaan',
            'Sub Name',
            'Kalkulasi PPIC',
            'Director Price',
            'Pengajuan Sub',
            'PPIC Remark',
            'Sub Remark',
            'Director Remark',
            'Status',
            'Tanggal Pengajuan',
            'Updated Director',
        ];
    }

    private function records(): Collection
    {
        return $this->records ??= PengajuanHargaPekerjaan::with(['po', 'detailPo', 'kategori'])
            ->whereDate('created_at', '>=', $this->startDate)
            ->whereDate('created_at', '<=', $this->endDate)
            ->orderBy('created_at')
            ->orderBy('id')
            ->get();
    }

    private function latestPrice($item, string $field): ?float
    {
        $history = $item->price_history;
        if (is_string($history)) {
            $history = json_decode($history, true) ?: [];
        }
        $entries = data_get($history ?: [], $field, []);
        // Entry terakhir merupakan revisi terbaru.
        if (is_array($entries) && count($entries)) {
            $last = end($entries);
            if (
                is_array($last) && array_key_exists('price', $last)
                && $last['price'] !== null && $last['price'] !== ''
            ) {
                return (float) $last['price'];
            }
        }
        $current = $item->{$field};
        return $current === null || $current === '' ? null : (float) $current;
    }

    private function detailOf($p): array
    {
        $detail = $p->detailPo?->detail ?? [];
        if (is_string($detail)) {
            $detail = json_decode($detail, true) ?: [];
        }
        return is_array($detail) ? $detail : [];
    }

    private function photoPath($photo): ?string
    {
        if (!is_string($photo) || trim($photo) === '') {
            return null;
        }
        $photo = trim($photo);
        // Gambar remote tidak diunduh; hanya file lokal yang aman dan tersedia.
        if (preg_match('#^https?://#i', $photo)) {
            $parts = parse_url($photo);
            $host = strtolower($parts['host'] ?? '');
            $appHost = strtolower(parse_url(config('app.url'), PHP_URL_HOST) ?: '');
            if ($host === '' || $appHost === '' || $host !== $appHost) {
                return null;
            }
            $photo = $parts['path'] ?? '';
        }
        $photo = rawurldecode(str_replace('\\', '/', $photo));
        $photo = ltrim($photo, '/');
        if (str_contains($photo, '..') || str_contains($photo, "\0")) {
            return null;
        }
        $photo = preg_replace('#^storage/#i', '', $photo);
        $candidates = [
            storage_path('app/public/' . $photo),
            public_path('storage/' . $photo),
            public_path($photo),
        ];
        foreach ($candidates as $candidate) {
            if (is_file($candidate) && is_readable($candidate)) {
                $extension = strtolower(pathinfo($candidate, PATHINFO_EXTENSION));
                if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif'], true)) {
                    return $candidate;
                }
            }
        }
        return null;
    }

    public function collection(): Collection
    {
        $this->imagePaths = [];
        return $this->records()->values()->map(function ($p, $index) {
            $detail = $this->detailOf($p);
            $code = collect([
                $detail['nw_code'] ?? null,
                $detail['nw_code_'] ?? null,
                $detail['nwCode'] ?? null,
                $detail['article_code'] ?? null,
                $detail['article_nr_'] ?? null,
                $detail['article_nr'] ?? null,
                $detail['article'] ?? null,
                $p->article_code,
            ])->first(fn($v) => is_scalar($v) && trim((string) $v) !== '' && trim((string) $v) !== '-');

            $photo = $detail['photo'] ?? $detail['image'] ?? $detail['picture'] ?? null;
            $this->imagePaths[$index] = $this->photoPath($photo);

            return [
                '', // Gambar ditanam pada sel A menggunakan WithDrawings.
                $detail['qty'] ?? $detail['quantity'] ?? $p->detailPo?->qty ?? '',
                (string) ($code ?? '-'),
                $p->kategori?->name ?? '-',
                $p->sub_name ?: '-',
                $this->latestPrice($p, 'ppic_price'),
                $this->latestPrice($p, 'director_price'),
                $this->latestPrice($p, 'pengajuan_sub_price'),
                $p->ppic_remark ?: '-',
                $p->sub_remark ?: '-',
                $p->director_remark ?: '-',
                $p->closed ? 'Closed' : 'Open',
                $p->created_at?->format('Y-m-d H:i:s') ?? '',
                $p->directur_date ? Carbon::parse($p->directur_date)->format('Y-m-d H:i:s') : '',
            ];
        });
    }

    public function drawings(): array
    {
        // WithDrawings dapat dipanggil sebelum collection(), jadi bangun path bila perlu.
        if (count($this->imagePaths) !== $this->records()->count()) {
            foreach ($this->records()->values() as $index => $p) {
                $detail = $this->detailOf($p);
                $this->imagePaths[$index] = $this->photoPath(
                    $detail['photo'] ?? $detail['image'] ?? $detail['picture'] ?? null
                );
            }
        }

        $drawings = [];
        foreach ($this->imagePaths as $index => $path) {
            if (!$path)
                continue;
            $drawing = new Drawing();
            $drawing->setName('Foto Item ' . ($index + 1));
            $drawing->setDescription('Foto item pengajuan harga');
            $drawing->setPath($path);
            $drawing->setHeight(65);
            $drawing->setCoordinates('A' . ($index + 6));
            $drawing->setOffsetX(8);
            $drawing->setOffsetY(5);
            $drawings[] = $drawing;
        }
        return $drawings;
    }

    public function styles(Worksheet $sheet): array
    {
        $lastDataRow = max(5, 5 + $this->records()->count());
        $sheet->mergeCells('A1:N1');
        $sheet->mergeCells('A2:N2');
        $sheet->mergeCells('A3:N3');
        $sheet->setCellValue('A1', 'REKAP PENGAJUAN & APPROVAL PENGAJUAN HARGA SUBKON');
        $sheet->setCellValue('A2', 'PT. NEWWICKER INDONESIA');
        $sheet->setCellValue('A3', 'Periode ' . Carbon::parse($this->startDate)->format('d-m-Y')
            . ' - ' . Carbon::parse($this->endDate)->format('d-m-Y'));
        $sheet->getStyle('A1:N3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A1:N2')->getFont()->setBold(true);
        $sheet->getStyle('A1')->getFont()->setSize(15);
        $sheet->getStyle('A2')->getFont()->setSize(12);
        $sheet->getStyle('A3')->getFont()->setSize(11);
        $sheet->getRowDimension(1)->setRowHeight(26);
        $sheet->getRowDimension(2)->setRowHeight(21);
        $sheet->getRowDimension(3)->setRowHeight(20);

        $sheet->getStyle('A5:N5')->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle('A5:N5')->getFill()->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('203C5C');
        $sheet->getRowDimension(5)->setRowHeight(24);
        $sheet->freezePane('A6');
        $sheet->setAutoFilter('A5:N' . $lastDataRow);
        if ($lastDataRow >= 6) {
            $sheet->getStyle('F6:H' . $lastDataRow)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->getStyle('A6:N' . $lastDataRow)->getAlignment()->setWrapText(true);
            for ($row = 6; $row <= $lastDataRow; $row++) {
                $sheet->getRowDimension($row)->setRowHeight(78);
            }
        }
        $sheet->getColumnDimension('A')->setWidth(20);
        $sheet->getColumnDimension('I')->setWidth(45);
        $sheet->getColumnDimension('J')->setWidth(45);
        $sheet->getColumnDimension('K')->setWidth(45);
        $sheet->getColumnDimension('M')->setWidth(23);
        $sheet->getColumnDimension('N')->setWidth(23);
        $sheet->getStyle('A5:N' . $lastDataRow)->getAlignment()->setVertical(Alignment::VERTICAL_TOP);
        $sheet->getStyle('A5:N' . $lastDataRow)->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN)
            ->getColor()->setRGB('64748B');
        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                // Border tabel dibuat pada tahap terakhir agar tetap terlihat
                // setelah seluruh data, heading, dan gambar selesai ditulis.
                $lastDataRow = max(5, 5 + $this->records()->count());
                $tableRange = 'A5:N' . $lastDataRow;
                $sheet->getStyle($tableRange)->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN)
                    ->getColor()->setRGB('64748B');
                $sheet->getStyle($tableRange)->getBorders()->getOutline()
                    ->setBorderStyle(Border::BORDER_MEDIUM)
                    ->getColor()->setRGB('334155');
                $sheet->getStyle('A5:N5')->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN)
                    ->getColor()->setRGB('CBD5E1');

                $signatureRow = max(9, 5 + $this->records()->count() + 4);
                $sheet->mergeCells("L{$signatureRow}:N{$signatureRow}");
                $sheet->mergeCells('L' . ($signatureRow + 3) . ':N' . ($signatureRow + 3));
                $sheet->setCellValue("L{$signatureRow}", 'Best regards,');
                $sheet->setCellValue('L' . ($signatureRow + 3), 'Stanley Tans');
                $sheet->getStyle("L{$signatureRow}:N" . ($signatureRow + 3))
                    ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                $sheet->getStyle('L' . ($signatureRow + 3))->getFont()->setBold(true);
            },
        ];
    }
}
