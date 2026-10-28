<?php

namespace App\Exports;

use App\Models\Absen;
use App\Models\User;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

class AbsenExport implements FromCollection, WithHeadings, WithEvents
{
    protected $start;
    protected $end;

    public function __construct($start, $end)
    {
        $this->start = Carbon::parse($start)->startOfDay();
        $this->end = Carbon::parse($end)->startOfDay();
    }

    private function dates()
    {
        return collect(Carbon::parse($this->start)->daysUntil($this->end->copy()->addDay()));
    }

    public function collection()
    {
        $dates = $this->dates();
        $users = User::orderBy('name')->get(['id', 'name']);
        $absens = Absen::whereIn('user_id', $users->pluck('id'))
            ->whereDate('tanggal', '>=', $this->start->toDateString())
            ->whereDate('tanggal', '<=', $this->end->toDateString())
            ->orderBy('jam_masuk')->get()->groupBy(function ($a) {
                return $a->user_id . '|' . Carbon::parse($a->tanggal)->format('Y-m-d');
            });

        $rows = collect();
        foreach ($users as $index => $user) {
            $row = [$index + 1, $user->name];
            $totalS = $totalI = $totalC = $totalA = 0;
            $totalMinutes = 0;

            foreach ($dates as $date) {
                $dayAbsens = $absens->get($user->id . '|' . $date->format('Y-m-d'), collect());
                if ($dayAbsens->isEmpty()) {
                    $row[] = '-';
                    $totalA++;
                    continue;
                }

                $lines = [];
                foreach ($dayAbsens as $absen) {
                    $status = strtolower(trim($absen->keterangan ?? 'hadir'));
                    switch ($status) {
                        case 'sakit': $totalS++; break;
                        case 'izin':
                        case 'i': $totalI++; break;
                        case 'cuti': $totalC++; break;
                        case 'hadir':
                        case 'masuk':
                        case 'keluar': break;
                        default: $totalA++; break;
                    }
                    if ($absen->jam_masuk) {
                        $in = Carbon::parse($absen->jam_masuk);
                        // Tetap mengikuti fallback jam keluar dari implementasi lama.
                        $out = Carbon::parse($absen->jam_keluar ?: '17:00');
                        $lines[] = $in->format('H:i') . ' - ' . $out->format('H:i');
                        $totalMinutes += abs($in->diffInMinutes($out));
                    } elseif (in_array($status, ['sakit', 'izin', 'i', 'cuti'])) {
                        $lines[] = strtoupper($status === 'izin' ? 'I' : ($status === 'sakit' ? 'S' : ($status === 'cuti' ? 'C' : $status)));
                    }
                }
                $row[] = $lines ? implode("\n", $lines) : '-';
            }
            array_push($row, $totalS, $totalI, $totalC, $totalA, round($totalMinutes / 60, 2));
            $rows->push($row);
        }
        return $rows;
    }

    public function headings(): array
    {
        return [array_merge(['No', 'Nama Karyawan'], $this->dates()->map(fn ($d) => $d->format('d/m'))->all(), ['S', 'I', 'C', 'A', 'Total Jam Kerja'])];
    }

    public function registerEvents(): array
    {
        return [AfterSheet::class => function (AfterSheet $event) {
            $sheet = $event->sheet->getDelegate();
            $dateCount = $this->dates()->count();
            $lastColIndex = $dateCount + 7;
            $lastCol = Coordinate::stringFromColumnIndex($lastColIndex);
            $lastDataRow = $sheet->getHighestRow();

            // Sisipkan header laporan di atas heading hasil export.
            $sheet->insertNewRowBefore(1, 5);
            $headerRow = 6;
            $firstDataRow = 7;
            $lastDataRow += 5;

            foreach ([1, 2, 3, 4] as $r) {
                $sheet->mergeCells("A{$r}:{$lastCol}{$r}");
            }
            $sheet->setCellValue('A1', 'PT. NEWWICKER INDONESIA');
            $sheet->setCellValue('A2', 'REKAPITULASI / TARIKAN DATA ABSENSI');
            $sheet->setCellValue('A3', 'PERIODE ' . $this->start->translatedFormat('d F Y') . ' s.d. ' . $this->end->translatedFormat('d F Y'));
            $sheet->setCellValue('A4', '');
            $sheet->getRowDimension(1)->setRowHeight(28);
            $sheet->getRowDimension(2)->setRowHeight(26);
            $sheet->getRowDimension(3)->setRowHeight(23);
            $sheet->getRowDimension(4)->setRowHeight(20);
            $sheet->getRowDimension(5)->setRowHeight(12);

            $sheet->getStyle("A1:{$lastCol}2")->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
            $sheet->getStyle("A1:{$lastCol}2")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('172A43');
            $sheet->getStyle("A3:{$lastCol}4")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('EAF0F6');
            $sheet->getStyle('A1')->getFont()->setSize(15);
            $sheet->getStyle('A2')->getFont()->setSize(13);
            $sheet->getStyle('A3')->getFont()->setBold(true)->setSize(11);
            $sheet->getStyle('A4')->getFont()->setItalic(true)->setSize(9);
            $sheet->getStyle("A1:{$lastCol}4")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER)->setVertical(Alignment::VERTICAL_CENTER);

            $sheet->getStyle("A{$headerRow}:{$lastCol}{$headerRow}")->applyFromArray([
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '315D8B']],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            ]);
            $sheet->getRowDimension($headerRow)->setRowHeight(32);
            $sheet->getColumnDimension('A')->setWidth(7);
            $sheet->getColumnDimension('B')->setWidth(29);
            for ($i = 3; $i <= $dateCount + 2; $i++) {
                $col = Coordinate::stringFromColumnIndex($i);
                $sheet->getColumnDimension($col)->setWidth(18);
            }
            for ($i = $dateCount + 3; $i <= $lastColIndex - 1; $i++) {
                $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($i))->setWidth(7);
            }
            $sheet->getColumnDimension($lastCol)->setWidth(18);

            if ($lastDataRow >= $firstDataRow) {
                $sheet->getStyle("A{$headerRow}:{$lastCol}{$lastDataRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
                $sheet->getStyle("A{$headerRow}:{$lastCol}{$lastDataRow}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER)->setWrapText(true);
                $sheet->getStyle("C{$firstDataRow}:{$lastCol}{$lastDataRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("{$lastCol}{$firstDataRow}:{$lastCol}{$lastDataRow}")->getNumberFormat()->setFormatCode('0.00');
                for ($r = $firstDataRow; $r <= $lastDataRow; $r++) {
                    $sheet->getRowDimension($r)->setRowHeight(32);
                    if (($r - $firstDataRow) % 2 === 1) {
                        $sheet->getStyle("A{$r}:{$lastCol}{$r}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F3F6FA');
                    }
                }
            }
            foreach ($this->dates() as $index => $date) {
                if ($date->isWeekend()) {
                    $col = Coordinate::stringFromColumnIndex($index + 3);
                    $sheet->getStyle("{$col}{$headerRow}:{$col}{$lastDataRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FCE2E2');
                    $sheet->getStyle("{$col}{$headerRow}")->getFont()->getColor()->setRGB('9F1239');
                }
            }

            $signRow = $lastDataRow + 3;
            $rightStart = Coordinate::stringFromColumnIndex(max(3, $lastColIndex - 5));
            $rightEnd = $lastCol;
            $sheet->mergeCells("A{$signRow}:D{$signRow}");
            $sheet->setCellValue("A{$signRow}", '');
            $sheet->mergeCells("{$rightStart}{$signRow}:{$rightEnd}{$signRow}");
            $sheet->setCellValue("{$rightStart}{$signRow}", '');
            $nameRow = $signRow + 4;
            $sheet->mergeCells("A{$nameRow}:D{$nameRow}");
            $sheet->setCellValue("A{$nameRow}", '(........................................)');
            $sheet->mergeCells("{$rightStart}{$nameRow}:{$rightEnd}{$nameRow}");
            $sheet->setCellValue("{$rightStart}{$nameRow}", '(........................................)');
            $sheet->getStyle("A{$signRow}:{$lastCol}{$nameRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $generatedRow = $nameRow + 2;
            $sheet->mergeCells("A{$generatedRow}:{$lastCol}{$generatedRow}");
            $sheet->setCellValue("A{$generatedRow}", 'Generated on: ' . Carbon::now()->translatedFormat('d F Y, H:i') . ' WIB');
            $sheet->getStyle("A{$generatedRow}")->getFont()->setItalic(true)->getColor()->setRGB('718096');
            $sheet->getStyle("A{$generatedRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            $sheet->freezePane('C7');
            $sheet->getPageSetup()->setOrientation(PageSetup::ORIENTATION_LANDSCAPE);
            $sheet->getPageSetup()->setPaperSize(PageSetup::PAPERSIZE_A3);
            $sheet->getPageSetup()->setFitToWidth(1)->setFitToHeight(0);
            $sheet->getPageSetup()->setFitToPage(true);
            $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(6, 6);
            $sheet->getPageMargins()->setTop(0.4)->setBottom(0.4)->setLeft(0.3)->setRight(0.3);
            $sheet->getPageSetup()->setPrintArea("A1:{$lastCol}{$generatedRow}");
        }];
    }
}
