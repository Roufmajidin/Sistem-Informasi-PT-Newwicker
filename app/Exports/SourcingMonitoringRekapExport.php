<?php

namespace App\Exports;

use App\Models\MonitoringIssue;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class SourcingMonitoringRekapExport implements FromCollection, WithHeadings, WithStyles, WithEvents
{
    public function __construct(
        private readonly string $startDate,
        private readonly string $endDate,
        private readonly int $userId,
        private readonly bool $isDirector
    ) {}

    public function headings(): array
    {
        return [
            ['REKAP SOURCING MONITORING ISSUE'],
            ['PT. NEWWICKER INDONESIA'],
            ['Periode ' . Carbon::parse($this->startDate)->format('d-m-Y') . ' - ' . Carbon::parse($this->endDate)->format('d-m-Y')],
            [],
            ['No', 'Report By', 'Tanggal Issue', 'Problem', 'Potential Impact',
             'Comments Director', 'Comments (Per User)', 'Status', 'Tanggal Done', 'Done By'],
        ];
    }

    public function collection(): Collection
    {
        $query = MonitoringIssue::query()
            ->with(['reporter', 'completedBy', 'userComments.user'])
            ->whereDate('created_at', '>=', $this->startDate)
            ->whereDate('created_at', '<=', $this->endDate)
            ->orderBy('created_at')->orderBy('id');

        // Sama seperti aturan visibilitas index: director tidak melihat draft;
        // user biasa melihat issue released dan draft miliknya sendiri.
        if ($this->isDirector) {
            $query->where('status', '!=', 'draft');
        } else {
            $query->where(function ($q) {
                $q->where('status', '!=', 'draft')->orWhere('user_id', $this->userId);
            });
        }

        return $query->get()->values()->map(function ($issue, $index) {
            $comments = $issue->userComments->map(function ($comment) {
                $name = $comment->user?->name ?: 'User';
                return $name . ': ' . trim((string) $comment->comments);
            })->filter()->implode("\n\n");

            if (filled($issue->comments)) {
                $comments = ($comments ? $comments . "\n\n" : '')
                    . 'Legacy comment: ' . $issue->comments;
            }

            return [
                $index + 1,
                $issue->reporter?->name ?: '-',
                $issue->created_at?->format('d/m/Y H:i') ?: '-',
                $issue->problem ?: '-',
                $issue->potential_impact ?: '-',
                $issue->follow_director ?: '-',
                $comments ?: '-',
                ucfirst((string) $issue->status),
                $issue->tanggal_done?->format('d/m/Y H:i') ?: '-',
                $issue->completedBy?->name ?: '-',
            ];
        });
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->mergeCells('A1:J1');
        $sheet->mergeCells('A2:J2');
        $sheet->mergeCells('A3:J3');
        $sheet->getStyle('A1:J3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('A1:J2')->getFont()->setBold(true);
        $sheet->getStyle('A1')->getFont()->setSize(15);
        $sheet->getStyle('A2')->getFont()->setSize(12);
        $sheet->getRowDimension(1)->setRowHeight(25);
        $sheet->getRowDimension(2)->setRowHeight(21);
        $sheet->getRowDimension(3)->setRowHeight(20);
        $sheet->getStyle('A5:J5')->getFont()->setBold(true)->getColor()->setRGB('FFFFFF');
        $sheet->getStyle('A5:J5')->getFill()->setFillType(Fill::FILL_SOLID)
            ->getStartColor()->setRGB('203C5C');
        $sheet->getStyle('A5:J5')->getAlignment()->setWrapText(true);
        $sheet->getRowDimension(5)->setRowHeight(30);
        $widths = ['A'=>7,'B'=>23,'C'=>21,'D'=>48,'E'=>48,'F'=>45,'G'=>58,'H'=>15,'I'=>22,'J'=>23];
        foreach ($widths as $col => $width) $sheet->getColumnDimension($col)->setWidth($width);
        $sheet->freezePane('D6');
        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $last = max(5, $sheet->getHighestDataRow());
                $sheet->getStyle('A5:J' . $last)->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN)->getColor()->setRGB('8C99A6');
                $sheet->getStyle('A5:J' . $last)->getAlignment()
                    ->setVertical(Alignment::VERTICAL_TOP)->setWrapText(true);
                $sheet->setAutoFilter('A5:J' . $last);
                for ($row = 6; $row <= $last; $row++) {
                    $sheet->getRowDimension($row)->setRowHeight(55);
                }
                $sheet->getPageSetup()->setOrientation('landscape');
                $sheet->getPageSetup()->setFitToWidth(1);
                $sheet->getPageSetup()->setFitToHeight(0);
            },
        ];
    }
}
