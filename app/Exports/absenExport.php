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
            $absen = Absen::where('user_id', $user->id)
                           ->where('tanggal', $d->format('Y-m-d'))
                           ->first();

            if (!$absen) {
                $row[] = '-';
                $totalA++;
            } else {
                // Tampilkan jam masuk - jam keluar
                $jamMasuk = $absen->jam_masuk ?? '';
                $jamKeluar = $absen->jam_keluar ?? '17:00'; // default 17:00 jika belum keluar
                $row[] = $jamMasuk && $jamKeluar ? $jamMasuk . ' - ' . $jamKeluar : $absen->keterangan ?? '';

                // Hitung total kehadiran
                $k = $absen->keterangan ?? 'H';
                if ($k == 'sakit') $totalS++;
                elseif ($k == 'i') $totalI++;
                elseif ($k == 'cuti') $totalC++;
                else $totalA++;

                // Hitung jam kerja
                if ($absen->jam_masuk) {
                    $jamKerja = Carbon::parse($jamMasuk)
                                   ->diffInMinutes(Carbon::parse($jamKeluar)) / 60;
                    $totalJam += $jamKerja;
                }
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
        $dates  = [];
        foreach ($period as $d) {
            $dates[] = $d->format('d/m');
        }

        return array_merge(['Nama'], $dates, ['S', 'I', 'C', 'A', 'Total Jam Kerja']);
    }

    // Styling header & font
    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 12]], // header
        ];
    }

    // Styling weekend & borders
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();

                // Borders
                $sheet->getStyle("A1:{$highestColumn}{$highestRow}")
                      ->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

                // Weekend highlight
                $period = Carbon::parse($this->start)->daysUntil(Carbon::parse($this->end)->addDay());
                $colIndex = 2; // kolom tanggal mulai dari B
                foreach ($period as $d) {
                    if ($d->isWeekend()) {
                        $sheet->getStyleByColumnAndRow($colIndex, 1, $colIndex, $highestRow)
                              ->getFill()
                              ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                              ->getStartColor()->setRGB('FF6666'); // merah weekend
                    }
                    $colIndex++;
                }
            }
        ];
    }
}
