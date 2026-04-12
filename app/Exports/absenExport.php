<?php

namespace App\Exports;

use App\Models\Absen;
use App\Models\User;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AbsenExport implements FromCollection, WithHeadings, WithStyles, WithEvents
{
    protected $start;
    protected $end;

    public function __construct($start, $end)
    {
        $this->start = $start;
        $this->end   = $end;
    }

    public function collection()
    {
        $data = [];
        $users = User::all();
        $period = Carbon::parse($this->start)->daysUntil(Carbon::parse($this->end)->addDay());

        foreach ($users as $user) {

            $row = [];
            $row[] = $user->name;

            $totalS = $totalI = $totalC = $totalA = 0;
            $totalJam = 0;

            foreach ($period as $d) {

                $absens = Absen::where('user_id', $user->id)
                    ->whereDate('tanggal', $d->format('Y-m-d'))
                    ->orderBy('jam_masuk')
                    ->get();

                if ($absens->isEmpty()) {

                    $row[] = '-';
                    $totalA++;

                } else {

                    $jamList = [];

                    foreach ($absens as $absen) {

                        $keterangan = strtolower(trim($absen->keterangan ?? 'hadir'));

                        if ($absen->jam_masuk) {

                            $jamMasuk  = $absen->jam_masuk;
                            $jamKeluar = $absen->jam_keluar ?? '17:00';

                            $jamList[] = $jamMasuk . ' - ' . $jamKeluar;

                            // Hitung total jam kerja
                            $totalJam += Carbon::parse($jamMasuk)
                                ->diffInMinutes(Carbon::parse($jamKeluar)) / 60;
                        }

                        // Rekap kategori
                        switch ($keterangan) {
                            case 'sakit':
                                $totalS++;
                                break;
                            case 'izin':
                            case 'i':
                                $totalI++;
                                break;
                            case 'cuti':
                                $totalC++;
                                break;
                            case 'hadir':
                            case 'masuk':
                            case 'keluar':
                                break;
                            default:
                                $totalA++;
                                break;
                        }
                    }

                    // Gabungkan multiline
                    $row[] = implode("\n", $jamList);
                }
            }

            $row[] = $totalS;
            $row[] = $totalI;
            $row[] = $totalC;
            $row[] = $totalA;
            $row[] = number_format($totalJam, 2);

            $data[] = $row;
        }

        return collect($data);
    }

    public function headings(): array
    {
        $period = Carbon::parse($this->start)->daysUntil(Carbon::parse($this->end)->addDay());
        $dates = [];

        foreach ($period as $d) {
            $dates[] = $d->format('d/m');
        }

        return array_merge(['Nama'], $dates, ['S', 'I', 'C', 'A', 'Total Jam Kerja']);
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet->getDelegate();

                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();

                // Border tabel
                $sheet->getStyle("A1:{$highestColumn}{$highestRow}")
                    ->getBorders()->getAllBorders()
                    ->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

                // Wrap text supaya multiline terlihat
                $sheet->getStyle("A1:{$highestColumn}{$highestRow}")
                    ->getAlignment()
                    ->setWrapText(true);

                // Warnai weekend
                $period = Carbon::parse($this->start)->daysUntil(Carbon::parse($this->end)->addDay());
                $colIndex = 2;

                foreach ($period as $d) {
                    if ($d->isWeekend()) {
                        $sheet->getStyleByColumnAndRow($colIndex, 1, $colIndex, $highestRow)
                            ->getFill()
                            ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                            ->getStartColor()->setRGB('FF6666');
                    }
                    $colIndex++;
                }
            },
        ];
    }
}
