<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;

/*
|--------------------------------------------------------------------------
| ReportExport
|--------------------------------------------------------------------------
|
| Perilaku:
|
| 1. User memilih range tanggal.
| 2. User menulis keyword pekerjaan, misalnya:
|       packing
|       finishing
|       rangka
|       anyam
|       dll.
| 3. Sheet pertama berisi semua data yang match keyword tersebut.
| 4. Sheet berikutnya otomatis berisi kategori PEKERJAAN lain
|    yang juga ada pada range tanggal tersebut.
|
| Contoh:
|
| Search = packing
|
| Sheet 1 : PACKING
| Sheet 2 : FINISHING CAT
| Sheet 3 : RANGKA
| Sheet 4 : ANYAM
| Sheet 5 : ...
|
| Contoh:
|
| Search = finishing
|
| Sheet 1 : FINISHING
| Sheet 2 : PACKING
| Sheet 3 : RANGKA
| Sheet 4 : ANYAM
| Sheet 5 : ...
|
| Packing tetap memakai format matrix:
| Foam / Medium / Single Face / Box.
|
|--------------------------------------------------------------------------
*/

class ReportExport implements WithMultipleSheets
{
    protected Collection $data;
    protected string $primaryCategory;
    protected ?string $dateFrom;
    protected ?string $dateTo;

    public function __construct(
        Collection $data,
        string $primaryCategory = '',
        ?string $dateFrom = null,
        ?string $dateTo = null
    ) {
        $this->data = $data->values();

        $this->primaryCategory = strtolower(
            trim($primaryCategory)
        );

        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
    }

    public function sheets(): array
    {
        /*
        |--------------------------------------------------------------------------
        | FILTER RANGE TANGGAL
        |--------------------------------------------------------------------------
        */

        $filteredData = $this->filterDateRange(
            $this->data
        );

        /*
        |--------------------------------------------------------------------------
        | PISAHKAN PRIMARY CATEGORY DAN PEKERJAAN LAIN
        |--------------------------------------------------------------------------
        */

        $primaryItems = collect();

        $otherJobs = [];

        foreach ($filteredData as $item) {

            $job = trim(
                (string) ($item->pekerjaan ?? '')
            );

            $jobLower = strtolower($job);

            /*
            |--------------------------------------------------------------------------
            | PRIMARY MATCH
            |--------------------------------------------------------------------------
            */

            $isPrimary = $this->matchesPrimary(
                $jobLower
            );

            if ($isPrimary) {

                $primaryItems->push($item);

                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | PEKERJAAN LAIN
            |--------------------------------------------------------------------------
            |
            | Setiap nilai PEKERJAAN menjadi satu sheet.
            |
            */

            if ($job === '') {
                $job = 'PEKERJAAN LAIN';
            }

            $jobKey = strtolower(
                trim($job)
            );

            if (!isset($otherJobs[$jobKey])) {

                $otherJobs[$jobKey] = [
                    'title' => $job,
                    'data' => collect()
                ];
            }

            $otherJobs[$jobKey]['data']->push(
                $item
            );
        }

        /*
        |--------------------------------------------------------------------------
        | SHEETS
        |--------------------------------------------------------------------------
        */

        $sheets = [];

        /*
        |--------------------------------------------------------------------------
        | SHEET 1 = PRIMARY
        |--------------------------------------------------------------------------
        */

        $primaryTitle =
            $this->primaryTitle();

        $primaryMode =
            $this->isPacking()
                ? 'packing'
                : 'normal';

        /*
        |--------------------------------------------------------------------------
        | Jika keyword kosong:
        |
        | seluruh data menjadi sheet utama.
        |--------------------------------------------------------------------------
        */

        if ($this->primaryCategory === '') {

            $primaryItems =
                $filteredData;

            $primaryTitle =
                'BORONGAN';
        }

        /*
        |--------------------------------------------------------------------------
        | SHEET 1 = RESUME / RANGKUMAN
        |--------------------------------------------------------------------------
        | Resume selalu menjadi sheet pertama dan menggunakan seluruh data
        | pada range tanggal, bukan hanya kategori yang sedang dicari.
        */
        $sheets[] =
            new ResumeSheet(
                data: $filteredData->values(),
                dateFrom: $this->dateFrom,
                dateTo: $this->dateTo
            );

        /*
        |--------------------------------------------------------------------------
        | SHEET 2 = PRIMARY
        |--------------------------------------------------------------------------
        */
        $sheets[] =
            new ReportSheet(
                data: $primaryItems->values(),
                reportType: $primaryMode,
                sheetTitle: $primaryTitle,
                dateFrom: $this->dateFrom,
                dateTo: $this->dateTo
            );

        /*
        |--------------------------------------------------------------------------
        | SHEET 2+ = PEKERJAAN LAIN
        |--------------------------------------------------------------------------
        */

        /*
        | Urutkan berdasarkan nama pekerjaan.
        */
        uasort(
            $otherJobs,
            function ($a, $b) {
                return strcasecmp(
                    $a['title'],
                    $b['title']
                );
            }
        );

        foreach ($otherJobs as $job) {

            $sheetTitle =
                $this->safeSheetTitle(
                    strtoupper(
                        trim($job['title'])
                    )
                );

            /*
            |--------------------------------------------------------------------------
            | Hindari nama sheet sama dengan primary
            |--------------------------------------------------------------------------
            */

            if (
                strtoupper(
                    $sheetTitle
                ) === strtoupper(
                    $this->safeSheetTitle(
                        $primaryTitle
                    )
                )
            ) {
                $sheetTitle =
                    $sheetTitle . ' 2';

                $sheetTitle =
                    mb_substr(
                        $sheetTitle,
                        0,
                        31
                    );
            }

            $sheets[] =
                new ReportSheet(
                    data: $job['data']->values(),
                    reportType: 'normal',
                    sheetTitle: $sheetTitle,
                    dateFrom: $this->dateFrom,
                    dateTo: $this->dateTo
                );
        }

        return $sheets;
    }

    /*
    |--------------------------------------------------------------------------
    | DATE FILTER
    |--------------------------------------------------------------------------
    */

    protected function filterDateRange(
        Collection $data
    ): Collection {
        return $data->filter(
            function ($item) {

                $dateValue =
                    $item->tanggal
                    ?? $item->date
                    ?? $item->tanggal_transaksi
                    ?? $item->created_at
                    ?? null;

                /*
                |--------------------------------------------------------------------------
                | Jika data tidak memiliki tanggal,
                | jangan dibuang.
                |--------------------------------------------------------------------------
                */

                if (!$dateValue) {
                    return true;
                }

                $itemDate =
                    date(
                        'Y-m-d',
                        strtotime(
                            $dateValue
                        )
                    );

                if (
                    $this->dateFrom &&
                    $itemDate <
                    $this->dateFrom
                ) {
                    return false;
                }

                if (
                    $this->dateTo &&
                    $itemDate >
                    $this->dateTo
                ) {
                    return false;
                }

                return true;
            }
        )->values();
    }

    /*
    |--------------------------------------------------------------------------
    | MATCH PRIMARY
    |--------------------------------------------------------------------------
    */

    protected function matchesPrimary(
        string $job
    ): bool {

        if ($this->primaryCategory === '') {
            return false;
        }

        /*
        |--------------------------------------------------------------------------
        | PACKING
        |--------------------------------------------------------------------------
        |
        | Packing mencakup:
        | Packing Foam
        | Packing Medium
        | Packing Box
        | Packing Single Face
        | Foam / Medium / Box / Single Face
        |--------------------------------------------------------------------------
        */

        if (
            $this->primaryCategory === 'packing'
        ) {

            return
                str_contains(
                    $job,
                    'packing'
                )
                ||
                str_contains(
                    $job,
                    'foam'
                )
                ||
                str_contains(
                    $job,
                    'medium'
                )
                ||
                str_contains(
                    $job,
                    'single face'
                )
                ||
                str_contains(
                    $job,
                    'box'
                );
        }

        /*
        |--------------------------------------------------------------------------
        | KATEGORI LAIN
        |--------------------------------------------------------------------------
        |
        | Contoh:
        | finishing
        | rangka
        | anyam
        | unfinish
        | dll.
        |--------------------------------------------------------------------------
        */

        return str_contains(
            $job,
            $this->primaryCategory
        );
    }

    /*
    |--------------------------------------------------------------------------
    | CHECK PACKING
    |--------------------------------------------------------------------------
    */

    protected function isPacking(): bool
    {
        return
            $this->primaryCategory ===
            'packing';
    }

    /*
    |--------------------------------------------------------------------------
    | PRIMARY TITLE
    |--------------------------------------------------------------------------
    */

    protected function primaryTitle(): string
    {
        if (
            $this->primaryCategory === ''
        ) {
            return 'BORONGAN';
        }

        return strtoupper(
            $this->primaryCategory
        );
    }

    /*
    |--------------------------------------------------------------------------
    | SAFE SHEET NAME
    |--------------------------------------------------------------------------
    */

    protected function safeSheetTitle(
        string $title
    ): string {

        $title = preg_replace(
            '/[\\\\\/\?\*\[\]\:]/',
            ' ',
            $title
        );

        $title = trim(
            $title
        );

        if ($title === '') {
            $title = 'SHEET';
        }

        return mb_substr(
            $title,
            0,
            31
        );
    }
}


/*
|--------------------------------------------------------------------------
| ResumeSheet
|--------------------------------------------------------------------------
|
| Sheet pertama = rangkuman seluruh pekerjaan dalam range tanggal.
|
| Kolom:
| NO, NAMA BARANG, TANGGAL, PO NO, KODE,
| PEKERJAAN LAINNYA SESUAI YANG ADA DI RANGE,
| FE FOAM, Medium, Single Face, Box, JUMLAH, PERSON.
|
| Packing dipisahkan ke kolom matrix. Pekerjaan lain dirangkum
| pada kolom "PEKERJAAN LAINNYA SESUAI YANG ADA DI RANGE".
|--------------------------------------------------------------------------
*/

class ResumeSheet implements
    FromCollection,
    WithEvents,
    WithTitle
{
    protected Collection $data;
    protected ?string $dateFrom;
    protected ?string $dateTo;

    public function __construct(
        Collection $data,
        ?string $dateFrom = null,
        ?string $dateTo = null
    ) {
        $this->data = $data->values();
        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
    }

    public function title(): string
    {
        return 'RESUME';
    }

    public function collection()
    {
        $rows = collect();

        /*
        |--------------------------------------------------------------------------
        | JUDUL
        |--------------------------------------------------------------------------
        */

        $rows->push([
            'REKAPITULASI PEMBAYARAN BORONGAN'
        ]);

        $rows->push([
            $this->periodText()
        ]);

        $rows->push(array_fill(0, 10, ''));

        /*
        |--------------------------------------------------------------------------
        | HEADER RESUME
        |--------------------------------------------------------------------------
        |
        | Resume dibuat vertikal agar tidak terlalu lebar.
        |
        | NO
        | NAMA BARANG
        | TANGGAL
        | PO NO
        | KODE
        | JENIS PEKERJAAN
        | QTY
        | HARGA
        | JUMLAH
        | PENGERJA
        |
        */

        $rows->push([
            'NO',
            'NAMA BARANG',
            'TANGGAL',
            'PO NO',
            'KODE',
            'JENIS PEKERJAAN',
            'QTY',
            'HARGA',
            'JUMLAH',
            'PENGERJA'
        ]);

        /*
        |--------------------------------------------------------------------------
        | GROUP DATA
        |--------------------------------------------------------------------------
        |
        | Satu baris = satu kombinasi:
        |
        | Article + Description + Tanggal + PO + Pekerjaan + Harga + Person
        |
        | Jadi apabila orang yang sama mengerjakan barang dan pekerjaan yang
        | sama dengan harga yang sama beberapa kali, Qty dan Total dijumlahkan.
        |
        */

        $groups = [];

        foreach ($this->data as $item) {

            $description = trim(
                (string) (
                    $item->description
                    ?? $item->nama_barang
                    ?? ''
                )
            );

            $article = trim(
                (string) (
                    $item->article
                    ?? $item->kode
                    ?? ''
                )
            );

            $tanggalValue =
                $item->tanggal
                ?? $item->date
                ?? $item->tanggal_transaksi
                ?? $item->created_at
                ?? null;

            $tanggalKey = '';

            if ($tanggalValue) {
                $timestamp = strtotime(
                    (string) $tanggalValue
                );

                if ($timestamp !== false) {
                    $tanggalKey = date(
                        'Y-m-d',
                        $timestamp
                    );
                }
            }

            $tanggalDisplay = $tanggalKey
                ? date(
                    'd/m/Y',
                    strtotime($tanggalKey)
                )
                : '';

            $po = trim(
                (string) (
                    $item->no_po
                    ?? $item->po
                    ?? ''
                )
            );

            $job = trim(
                (string) (
                    $item->pekerjaan
                    ?? ''
                )
            );

            $person = trim(
                (string) (
                    $item->person
                    ?? ''
                )
            );

            /*
            |--------------------------------------------------------------------------
            | Jika kosong
            |--------------------------------------------------------------------------
            */

            if ($job === '') {
                $job = 'PEKERJAAN LAIN';
            }

            if ($person === '') {
                $person = 'BELUM ADA PENGERJA';
            }

            $qty = (float) (
                $item->qty ?? 0
            );

            $harga = (float) (
                $item->harga ?? 0
            );

            $total = (float) (
                $item->total ?? 0
            );

            if (
                $total == 0
                && $qty != 0
                && $harga != 0
            ) {
                $total =
                    $qty * $harga;
            }

            /*
            |--------------------------------------------------------------------------
            | KEY GROUP
            |--------------------------------------------------------------------------
            */

            $key =
                strtolower($article) . '||' .
                strtolower($description) . '||' .
                $tanggalKey . '||' .
                strtolower($po) . '||' .
                strtolower($job) . '||' .
                number_format(
                    $harga,
                    4,
                    '.',
                    ''
                ) . '||' .
                strtolower($person);

            if (!isset($groups[$key])) {

                $groups[$key] = [
                    'description' =>
                        $description,

                    'tanggal' =>
                        $tanggalDisplay,

                    'po' =>
                        $po,

                    'article' =>
                        $article,

                    'pekerjaan' =>
                        $job,

                    'qty' =>
                        0,

                    'harga' =>
                        $harga,

                    'total' =>
                        0,

                    'person' =>
                        $person
                ];
            }

            $groups[$key]['qty'] += $qty;
            $groups[$key]['total'] += $total;
        }

        /*
        |--------------------------------------------------------------------------
        | SORT
        |--------------------------------------------------------------------------
        |
        | Urutan:
        | tanggal -> pekerjaan -> person -> article
        |
        */

        uasort(
            $groups,
            function ($a, $b) {

                $dateCompare =
                    strcmp(
                        $a['tanggal'],
                        $b['tanggal']
                    );

                if ($dateCompare !== 0) {
                    return $dateCompare;
                }

                $jobCompare =
                    strcasecmp(
                        $a['pekerjaan'],
                        $b['pekerjaan']
                    );

                if ($jobCompare !== 0) {
                    return $jobCompare;
                }

                $personCompare =
                    strcasecmp(
                        $a['person'],
                        $b['person']
                    );

                if ($personCompare !== 0) {
                    return $personCompare;
                }

                return strcasecmp(
                    $a['description'],
                    $b['description']
                );
            }
        );

        /*
        |--------------------------------------------------------------------------
        | ISI BARIS RESUME
        |--------------------------------------------------------------------------
        */

        $number = 1;
        $grandTotal = 0;

        /*
        | Rekap per orang
        */
        $personSummary = [];

        foreach ($groups as $group) {

            $grandTotal +=
                $group['total'];

            $rows->push([
                $number++,
                $group['description'],
                $group['tanggal'],
                $group['po'],
                $group['article'],
                $group['pekerjaan'],
                $group['qty'],
                $group['harga'],
                $group['total'],
                $group['person']
            ]);

            $personKey =
                strtolower(
                    trim($group['person'])
                );

            if (!isset(
                $personSummary[$personKey]
            )) {

                $personSummary[$personKey] = [
                    'person' =>
                        $group['person'],

                    'jobs' =>
                        0,

                    'total' =>
                        0
                ];
            }

            $personSummary[$personKey]['jobs']++;
            $personSummary[$personKey]['total'] +=
                $group['total'];
        }

        /*
        |--------------------------------------------------------------------------
        | TOTAL RESUME
        |--------------------------------------------------------------------------
        */

        $rows->push([
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            'TOTAL',
            $grandTotal,
            ''
        ]);

        /*
        |--------------------------------------------------------------------------
        | JARAK
        |--------------------------------------------------------------------------
        */

        $rows->push(
            array_fill(0, 10, '')
        );

        $rows->push(
            array_fill(0, 10, '')
        );

        /*
        |--------------------------------------------------------------------------
        | REKAP PEMBAYARAN PER ORANG
        |--------------------------------------------------------------------------
        */

        $rows->push([
            'REKAP PEMBAYARAN PER ORANG'
        ]);

        $rows->push([
            'PENGERJA',
            'JUMLAH PEKERJAAN',
            'TOTAL'
        ]);

        uasort(
            $personSummary,
            function ($a, $b) {
                return strcasecmp(
                    $a['person'],
                    $b['person']
                );
            }
        );

        $personGrandTotal = 0;

        foreach ($personSummary as $summary) {

            $personGrandTotal +=
                $summary['total'];

            $rows->push([
                $summary['person'],
                $summary['jobs'],
                $summary['total']
            ]);
        }

        $rows->push([
            'TOTAL',
            '',
            $personGrandTotal
        ]);

        return $rows;
    }

    protected function periodText(): string
    {
        if (
            $this->dateFrom
            && $this->dateTo
        ) {

            return 'PERIODE '
                . date(
                    'd F Y',
                    strtotime($this->dateFrom)
                )
                . ' - '
                . date(
                    'd F Y',
                    strtotime($this->dateTo)
                );
        }

        if ($this->dateFrom) {

            return 'PERIODE MULAI '
                . date(
                    'd F Y',
                    strtotime($this->dateFrom)
                );
        }

        if ($this->dateTo) {

            return 'PERIODE SAMPAI '
                . date(
                    'd F Y',
                    strtotime($this->dateTo)
                );
        }

        return 'PERIODE '
            . date('d F Y');
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class =>
                function (AfterSheet $event) {

                    $sheet =
                        $event->sheet
                            ->getDelegate();

                    $highestRow =
                        $sheet->getHighestRow();

                    /*
                    |--------------------------------------------------------------------------
                    | TITLE
                    |--------------------------------------------------------------------------
                    */

                    $sheet->mergeCells(
                        'A1:J1'
                    );

                    $sheet->mergeCells(
                        'A2:J2'
                    );

                    $sheet->getStyle(
                        'A1:J1'
                    )->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'size' => 14
                        ],
                        'alignment' => [
                            'horizontal' =>
                                Alignment::HORIZONTAL_CENTER,

                            'vertical' =>
                                Alignment::VERTICAL_CENTER
                        ]
                    ]);

                    $sheet->getStyle(
                        'A2:J2'
                    )->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'size' => 12
                        ],
                        'alignment' => [
                            'horizontal' =>
                                Alignment::HORIZONTAL_CENTER,

                            'vertical' =>
                                Alignment::VERTICAL_CENTER
                        ]
                    ]);

                    /*
                    |--------------------------------------------------------------------------
                    | HEADER TABEL UTAMA
                    |--------------------------------------------------------------------------
                    */

                    $sheet->getStyle(
                        'A4:J4'
                    )->applyFromArray([

                        'font' => [
                            'bold' => true
                        ],

                        'fill' => [
                            'fillType' =>
                                Fill::FILL_SOLID,

                            'startColor' => [
                                'rgb' =>
                                    'D9E1F2'
                            ]
                        ],

                        'alignment' => [
                            'horizontal' =>
                                Alignment::HORIZONTAL_CENTER,

                            'vertical' =>
                                Alignment::VERTICAL_CENTER,

                            'wrapText' =>
                                true
                        ],

                        'borders' => [
                            'allBorders' => [
                                'borderStyle' =>
                                    Border::BORDER_THIN
                            ]
                        ]
                    ]);

                    /*
                    |--------------------------------------------------------------------------
                    | CARI BARIS TOTAL UTAMA
                    |--------------------------------------------------------------------------
                    */

                    $mainTotalRow = null;

                    for (
                        $row = 5;
                        $row <= $highestRow;
                        $row++
                    ) {

                        $value =
                            trim(
                                (string)
                                $sheet
                                    ->getCell(
                                        'H' . $row
                                    )
                                    ->getValue()
                            );

                        if (
                            strtoupper(
                                $value
                            ) === 'TOTAL'
                        ) {

                            $mainTotalRow =
                                $row;

                            break;
                        }
                    }

                    if ($mainTotalRow) {

                        $sheet->getStyle(
                            'A4:J' .
                            $mainTotalRow
                        )->getBorders()
                            ->getAllBorders()
                            ->setBorderStyle(
                                Border::BORDER_THIN
                            );

                        $sheet->getStyle(
                            'A' .
                            $mainTotalRow .
                            ':J' .
                            $mainTotalRow
                        )->applyFromArray([
                            'font' => [
                                'bold' => true
                            ]
                        ]);
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | CARI HEADER REKAP ORANG
                    |--------------------------------------------------------------------------
                    */

                    $personHeaderRow = null;

                    for (
                        $row = 1;
                        $row <= $highestRow;
                        $row++
                    ) {

                        $value =
                            trim(
                                (string)
                                $sheet
                                    ->getCell(
                                        'A' . $row
                                    )
                                    ->getValue()
                            );

                        if (
                            strtoupper($value) ===
                            'PENGERJA'
                        ) {

                            $personHeaderRow =
                                $row;

                            break;
                        }
                    }

                    if ($personHeaderRow) {

                        $sheet->getStyle(
                            'A' .
                            $personHeaderRow .
                            ':C' .
                            $personHeaderRow
                        )->applyFromArray([

                            'font' => [
                                'bold' => true
                            ],

                            'fill' => [
                                'fillType' =>
                                    Fill::FILL_SOLID,

                                'startColor' => [
                                    'rgb' =>
                                        'D9E1F2'
                                ]
                            ],

                            'alignment' => [
                                'horizontal' =>
                                    Alignment::HORIZONTAL_CENTER,

                                'vertical' =>
                                    Alignment::VERTICAL_CENTER
                            ],

                            'borders' => [
                                'allBorders' => [
                                    'borderStyle' =>
                                        Border::BORDER_THIN
                                ]
                            ]
                        ]);

                        $personTotalRow = null;

                        for (
                            $row =
                                $personHeaderRow + 1;
                            $row <= $highestRow;
                            $row++
                        ) {

                            $value =
                                trim(
                                    (string)
                                    $sheet
                                        ->getCell(
                                            'A' . $row
                                        )
                                        ->getValue()
                                );

                            if (
                                strtoupper($value) ===
                                'TOTAL'
                            ) {

                                $personTotalRow =
                                    $row;

                                break;
                            }
                        }

                        if ($personTotalRow) {

                            $sheet->getStyle(
                                'A' .
                                $personHeaderRow .
                                ':C' .
                                $personTotalRow
                            )->getBorders()
                                ->getAllBorders()
                                ->setBorderStyle(
                                    Border::BORDER_THIN
                                );

                            $sheet->getStyle(
                                'A' .
                                $personTotalRow .
                                ':C' .
                                $personTotalRow
                            )->applyFromArray([
                                'font' => [
                                    'bold' => true
                                ]
                            ]);
                        }
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | NUMBER FORMAT
                    |--------------------------------------------------------------------------
                    */

                    $sheet->getStyle(
                        'G5:I' .
                        $highestRow
                    )->getNumberFormat()
                        ->setFormatCode(
                            '#,##0.##'
                        );

                    /*
                    |--------------------------------------------------------------------------
                    | ALIGNMENT
                    |--------------------------------------------------------------------------
                    */

                    $sheet->getStyle(
                        'A4:J' .
                        $highestRow
                    )->getAlignment()
                        ->setVertical(
                            Alignment::VERTICAL_CENTER
                        );

                    $sheet->getStyle(
                        'A4:J' .
                        $highestRow
                    )->getAlignment()
                        ->setWrapText(true);

                    /*
                    |--------------------------------------------------------------------------
                    | WIDTH
                    |--------------------------------------------------------------------------
                    */

                    $widths = [

                        'A' => 6,

                        'B' => 38,

                        'C' => 13,

                        'D' => 14,

                        'E' => 16,

                        'F' => 25,

                        'G' => 12,

                        'H' => 14,

                        'I' => 16,

                        'J' => 24
                    ];

                    foreach (
                        $widths
                        as $column => $width
                    ) {

                        $sheet
                            ->getColumnDimension(
                                $column
                            )
                            ->setWidth(
                                $width
                            );
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | ROW HEIGHT
                    |--------------------------------------------------------------------------
                    */

                    $sheet
                        ->getRowDimension(1)
                        ->setRowHeight(25);

                    $sheet
                        ->getRowDimension(2)
                        ->setRowHeight(22);

                    $sheet
                        ->getRowDimension(4)
                        ->setRowHeight(32);

                    /*
                    |--------------------------------------------------------------------------
                    | FREEZE
                    |--------------------------------------------------------------------------
                    */

                    $sheet->freezePane(
                        'A5'
                    );

                    /*
                    |--------------------------------------------------------------------------
                    | PRINT
                    |--------------------------------------------------------------------------
                    */

                    $sheet->getPageSetup()
                        ->setOrientation(
                            PageSetup::ORIENTATION_LANDSCAPE
                        )
                        ->setPaperSize(
                            PageSetup::PAPERSIZE_A4
                        )
                        ->setFitToWidth(1)
                        ->setFitToHeight(0);

                    $sheet->getPageMargins()
                        ->setTop(0.25)
                        ->setRight(0.25)
                        ->setBottom(0.25)
                        ->setLeft(0.25);
                }
        ];
    }
}


class ReportSheet implements
    FromCollection,
    WithEvents,
    WithTitle
{
    protected Collection $data;
    protected string $reportType;
    protected string $sheetTitle;
    protected ?string $dateFrom;
    protected ?string $dateTo;

    public function __construct(
        Collection $data,
        string $reportType = 'normal',
        string $sheetTitle = 'BORONGAN',
        ?string $dateFrom = null,
        ?string $dateTo = null
    ) {
        $this->data =
            $data->values();

        $this->reportType =
            $reportType;

        $this->sheetTitle =
            $this->safeTitle(
                $sheetTitle
            );

        $this->dateFrom =
            $dateFrom;

        $this->dateTo =
            $dateTo;
    }

    public function title(): string
    {
        return $this->sheetTitle;
    }

    public function collection()
    {
        if (
            $this->reportType ===
            'packing'
        ) {
            return $this->packingRows();
        }

        return $this->normalRows();
    }

    /*
    |--------------------------------------------------------------------------
    | NORMAL
    |--------------------------------------------------------------------------
    */

    protected function normalRows(): Collection
    {
        $rows = collect();

        $rows->push([
            'REKAPITULASI PEMBAYARAN BORONGAN ' .
            strtoupper($this->sheetTitle)
        ]);

        $rows->push([
            $this->periodText()
        ]);

        $rows->push(array_fill(0, 7, ''));

        $rows->push([
            strtoupper($this->sheetTitle)
        ]);

        $rows->push([
            'NO',
            'NAMA BARANG',
            'PO NO',
            'KODE',
            'QTY',
            'HARGA',
            'JUMLAH'
        ]);

        $groups = $this->groupNormalData($this->data);

        $number = 1;
        $grandTotal = 0;

        foreach ($groups as $group) {
            $grandTotal += $group['total'];

            $rows->push([
                $number++,
                $group['description'],
                $group['no_po'],
                $group['article'],
                $group['qty'],
                $group['harga'],
                $group['total']
            ]);
        }

        $rows->push([
            '',
            '',
            '',
            '',
            '',
            'TOTAL',
            $grandTotal
        ]);

        $rows->push(array_fill(0, 7, ''));
        $rows->push(array_fill(0, 7, ''));

        $rows->push([
            'Made by',
            '',
            'Finance',
            '',
            'Checked by',
            '',
            'Approve by'
        ]);

        $rows->push(array_fill(0, 7, ''));
        $rows->push(array_fill(0, 7, ''));

        $rows->push([
            'Siti',
            '',
            'Ainun',
            '',
            'Didin W',
            '',
            'Mr Ley'
        ]);

        /*
        |--------------------------------------------------------------------------
        | FORM PER ORANG
        |--------------------------------------------------------------------------
        */

        $rows = $this->appendNormalPersonForms(
            $rows,
            $this->data
        );

        return $rows;
    }

    protected function groupNormalData(
        Collection $data
    ): array {
        $groups = [];

        foreach ($data as $item) {
            $description = trim(
                (string) (
                    $item->description
                    ?? $item->nama_barang
                    ?? ''
                )
            );

            $article = trim(
                (string) (
                    $item->article
                    ?? $item->kode
                    ?? ''
                )
            );

            $pekerjaan = trim(
                (string) (
                    $item->pekerjaan
                    ?? ''
                )
            );

            $harga = (float) (
                $item->harga ?? 0
            );

            $qty = (float) (
                $item->qty ?? 0
            );

            $total = (float) (
                $item->total ?? 0
            );

            if (
                $total == 0 &&
                $qty != 0 &&
                $harga != 0
            ) {
                $total = $qty * $harga;
            }

            $key =
                strtolower($article) . '||' .
                strtolower($description) . '||' .
                strtolower($pekerjaan) . '||' .
                number_format(
                    $harga,
                    2,
                    '.',
                    ''
                );

            if (!isset($groups[$key])) {
                $groups[$key] = [
                    'description' => $description,
                    'article' => $article,
                    'no_po' => trim(
                        (string) (
                            $item->no_po ?? ''
                        )
                    ),
                    'pekerjaan' => $pekerjaan,
                    'qty' => 0,
                    'harga' => $harga,
                    'total' => 0
                ];
            }

            $groups[$key]['qty'] += $qty;
            $groups[$key]['total'] += $total;

            if (
                empty($groups[$key]['no_po']) &&
                !empty($item->no_po)
            ) {
                $groups[$key]['no_po'] =
                    trim((string) $item->no_po);
            }
        }

        return $groups;
    }

    protected function appendNormalPersonForms(
        Collection $rows,
        Collection $data
    ): Collection {
        $persons = $this->uniquePersonsFrom($data);

        foreach ($persons as $person) {
            $personData = $this->dataForPerson(
                $data,
                $person
            );

            if ($personData->isEmpty()) {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | JARAK ANTAR FORM
            |--------------------------------------------------------------------------
            */

            $rows->push(array_fill(0, 7, ''));
            $rows->push(array_fill(0, 7, ''));

            $rows->push([
                'PEMBAYARAN BORONGAN - ' .
                strtoupper($person)
            ]);

            $rows->push([
                'PENERIMA : ' . $person
            ]);

            $rows->push([
                'NO',
                'NAMA BARANG',
                'PO NO',
                'KODE',
                'QTY',
                'HARGA',
                'JUMLAH'
            ]);

            $groups = $this->groupNormalData(
                $personData
            );

            $number = 1;
            $personTotal = 0;

            foreach ($groups as $group) {
                $personTotal += $group['total'];

                $rows->push([
                    $number++,
                    $group['description'],
                    $group['no_po'],
                    $group['article'],
                    $group['qty'],
                    $group['harga'],
                    $group['total']
                ]);
            }

            $rows->push([
                '',
                '',
                '',
                '',
                '',
                'TOTAL ' . strtoupper($person),
                $personTotal
            ]);

            $rows->push(array_fill(0, 7, ''));
            $rows->push(array_fill(0, 7, ''));

            $rows->push([
                'Made by',
                '',
                'Finance',
                '',
                'Checked by',
                '',
                'Penerima'
            ]);

            $rows->push(array_fill(0, 7, ''));
            $rows->push(array_fill(0, 7, ''));

            $rows->push([
                'Siti',
                '',
                'Ainun',
                '',
                'Didin W',
                '',
                $person
            ]);
        }

        return $rows;
    }


    protected function packingRows(): Collection
    {
        $rows = collect();

        $rows->push([
            'REKAPITULASI PEMBAYARAN BORONGAN PACKING'
        ]);

        $rows->push([
            $this->periodText()
        ]);

        $rows->push(array_fill(0, 10, ''));

        $rows->push([
            'PACKING'
        ]);

        $rows->push([
            'NO',
            'NAMA BARANG',
            'PO NO',
            'KODE',
            'QTY',
            'FE FOAM',
            'Medium',
            'Single Face',
            'Box',
            'JUMLAH'
        ]);

        $groups = $this->groupPackingData(
            $this->data
        );

        $number = 1;
        $grandTotal = 0;

        foreach ($groups as $group) {
            $foam = '';
            $medium = '';
            $singleFace = '';
            $box = '';

            switch ($group['pekerjaan']) {
                case 'foam':
                    $foam = $group['harga'];
                    break;

                case 'medium':
                    $medium = $group['harga'];
                    break;

                case 'single_face':
                    $singleFace = $group['harga'];
                    break;

                case 'box':
                    $box = $group['harga'];
                    break;
            }

            $grandTotal += $group['total'];

            $rows->push([
                $number++,
                $group['description'],
                $group['no_po'],
                $group['article'],
                $group['qty'],
                $foam,
                $medium,
                $singleFace,
                $box,
                $group['total']
            ]);
        }

        $rows->push([
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            '',
            'TOTAL',
            $grandTotal
        ]);

        $rows->push(array_fill(0, 10, ''));
        $rows->push(array_fill(0, 10, ''));

        $rows->push([
            'Made by',
            '',
            'Finance',
            '',
            'Checked by',
            '',
            '',
            'Approve by',
            '',
            ''
        ]);

        $rows->push(array_fill(0, 10, ''));
        $rows->push(array_fill(0, 10, ''));

        $rows->push([
            'Siti',
            '',
            'Ainun',
            '',
            'Didin W',
            '',
            '',
            'Mr Ley',
            '',
            ''
        ]);

        /*
        |--------------------------------------------------------------------------
        | FORM PER ORANG
        |--------------------------------------------------------------------------
        */

        $rows = $this->appendPackingPersonForms(
            $rows,
            $this->data
        );

        return $rows;
    }

    protected function groupPackingData(
        Collection $data
    ): array {
        $groups = [];

        foreach ($data as $item) {
            $description = trim(
                (string) (
                    $item->description
                    ?? $item->nama_barang
                    ?? ''
                )
            );

            $article = trim(
                (string) (
                    $item->article
                    ?? $item->kode
                    ?? ''
                )
            );

            $pekerjaan = strtolower(
                trim(
                    (string) (
                        $item->pekerjaan
                        ?? ''
                    )
                )
            );

            $harga = (float) (
                $item->harga ?? 0
            );

            $qty = (float) (
                $item->qty ?? 0
            );

            $total = (float) (
                $item->total ?? 0
            );

            if (
                $total == 0 &&
                $qty != 0 &&
                $harga != 0
            ) {
                $total = $qty * $harga;
            }

            if (
                str_contains(
                    $pekerjaan,
                    'single face'
                )
            ) {
                $jenis = 'single_face';
            } elseif (
                str_contains(
                    $pekerjaan,
                    'foam'
                )
            ) {
                $jenis = 'foam';
            } elseif (
                str_contains(
                    $pekerjaan,
                    'medium'
                )
            ) {
                $jenis = 'medium';
            } elseif (
                str_contains(
                    $pekerjaan,
                    'box'
                )
            ) {
                $jenis = 'box';
            } else {
                $jenis = 'medium';
            }

            $key =
                strtolower($article) . '||' .
                strtolower($description) . '||' .
                $jenis . '||' .
                number_format(
                    $harga,
                    2,
                    '.',
                    ''
                );

            if (!isset($groups[$key])) {
                $groups[$key] = [
                    'description' => $description,
                    'article' => $article,
                    'no_po' => trim(
                        (string) (
                            $item->no_po ?? ''
                        )
                    ),
                    'pekerjaan' => $jenis,
                    'qty' => 0,
                    'harga' => $harga,
                    'total' => 0
                ];
            }

            $groups[$key]['qty'] += $qty;
            $groups[$key]['total'] += $total;

            if (
                empty($groups[$key]['no_po']) &&
                !empty($item->no_po)
            ) {
                $groups[$key]['no_po'] =
                    trim((string) $item->no_po);
            }
        }

        return $groups;
    }

    protected function appendPackingPersonForms(
        Collection $rows,
        Collection $data
    ): Collection {
        $persons = $this->uniquePersonsFrom($data);

        foreach ($persons as $person) {
            $personData = $this->dataForPerson(
                $data,
                $person
            );

            if ($personData->isEmpty()) {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | JARAK
            |--------------------------------------------------------------------------
            */

            $rows->push(array_fill(0, 10, ''));
            $rows->push(array_fill(0, 10, ''));

            /*
            |--------------------------------------------------------------------------
            | JUDUL FORM
            |--------------------------------------------------------------------------
            */

            $rows->push([
                'PEMBAYARAN BORONGAN PACKING - ' .
                strtoupper($person)
            ]);

            $rows->push([
                'PENERIMA : ' . $person
            ]);

            $rows->push([
                'NO',
                'NAMA BARANG',
                'PO NO',
                'KODE',
                'QTY',
                'FE FOAM',
                'Medium',
                'Single Face',
                'Box',
                'JUMLAH'
            ]);

            $groups = $this->groupPackingData(
                $personData
            );

            $number = 1;
            $personTotal = 0;

            foreach ($groups as $group) {
                $foam = '';
                $medium = '';
                $singleFace = '';
                $box = '';

                switch ($group['pekerjaan']) {
                    case 'foam':
                        $foam = $group['harga'];
                        break;

                    case 'medium':
                        $medium = $group['harga'];
                        break;

                    case 'single_face':
                        $singleFace = $group['harga'];
                        break;

                    case 'box':
                        $box = $group['harga'];
                        break;
                }

                $personTotal += $group['total'];

                $rows->push([
                    $number++,
                    $group['description'],
                    $group['no_po'],
                    $group['article'],
                    $group['qty'],
                    $foam,
                    $medium,
                    $singleFace,
                    $box,
                    $group['total']
                ]);
            }

            $rows->push([
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                '',
                'TOTAL ' . strtoupper($person),
                $personTotal
            ]);

            $rows->push(array_fill(0, 10, ''));
            $rows->push(array_fill(0, 10, ''));

            $rows->push([
                'Made by',
                '',
                'Finance',
                '',
                'Checked by',
                '',
                '',
                'Approve by',
                '',
                'Penerima'
            ]);

            $rows->push(array_fill(0, 10, ''));
            $rows->push(array_fill(0, 10, ''));

            $rows->push([
                'Siti',
                '',
                'Ainun',
                '',
                'Didin W',
                '',
                '',
                'Mr Ley',
                '',
                $person
            ]);
        }

        return $rows;
    }

    protected function uniquePersonsFrom(
        Collection $data
    ): array {
        $persons = [];

        foreach ($data as $item) {
            $person = trim(
                (string) (
                    $item->person ?? ''
                )
            );

            if ($person === '') {
                continue;
            }

            $key = strtolower($person);

            if (!isset($persons[$key])) {
                $persons[$key] = $person;
            }
        }

        return array_values($persons);
    }

    protected function dataForPerson(
        Collection $data,
        string $person
    ): Collection {
        $personKey = strtolower(
            trim($person)
        );

        return $data->filter(
            function ($item) use ($personKey) {
                return strtolower(
                    trim(
                        (string) (
                            $item->person ?? ''
                        )
                    )
                ) === $personKey;
            }
        )->values();
    }


    protected function uniquePersons(): array
    {
        return $this->uniquePersonsFrom(
            $this->data
        );
    }

    protected function periodText(): string
    {
        if (
            $this->dateFrom &&
            $this->dateTo
        ) {

            return 'PERIODE '
                . date(
                    'd F Y',
                    strtotime(
                        $this->dateFrom
                    )
                )
                . ' - '
                . date(
                    'd F Y',
                    strtotime(
                        $this->dateTo
                    )
                );
        }

        if ($this->dateFrom) {

            return 'PERIODE MULAI '
                . date(
                    'd F Y',
                    strtotime(
                        $this->dateFrom
                    )
                );
        }

        if ($this->dateTo) {

            return 'PERIODE SAMPAI '
                . date(
                    'd F Y',
                    strtotime(
                        $this->dateTo
                    )
                );
        }

        return 'PERIODE ' .
            date('d F Y');
    }

    /*
    |--------------------------------------------------------------------------
    | EVENTS
    |--------------------------------------------------------------------------
    */

    public function registerEvents(): array
    {
        return [
            AfterSheet::class =>
                function (AfterSheet $event) {

                    $sheet =
                        $event->sheet
                            ->getDelegate();

                    $isPacking =
                        $this->reportType ===
                        'packing';

                    $lastColumn =
                        $isPacking
                            ? 'J'
                            : 'G';

                    $highestRow =
                        $sheet->getHighestRow();

                    /*
                    |--------------------------------------------------------------------------
                    | TITLE UTAMA
                    |--------------------------------------------------------------------------
                    */

                    $sheet->mergeCells(
                        'A1:' .
                        $lastColumn .
                        '1'
                    );

                    $sheet->mergeCells(
                        'A2:' .
                        $lastColumn .
                        '2'
                    );

                    $sheet->mergeCells(
                        'A4:' .
                        $lastColumn .
                        '4'
                    );

                    $sheet->getStyle(
                        'A1:' . $lastColumn . '1'
                    )->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'size' => 14
                        ],
                        'alignment' => [
                            'horizontal' =>
                                Alignment::HORIZONTAL_CENTER,
                            'vertical' =>
                                Alignment::VERTICAL_CENTER
                        ]
                    ]);

                    $sheet->getStyle(
                        'A2:' . $lastColumn . '2'
                    )->applyFromArray([
                        'font' => [
                            'bold' => true,
                            'size' => 12
                        ],
                        'alignment' => [
                            'horizontal' =>
                                Alignment::HORIZONTAL_CENTER,
                            'vertical' =>
                                Alignment::VERTICAL_CENTER
                        ]
                    ]);

                    /*
                    |--------------------------------------------------------------------------
                    | SEMUA HEADER TABEL
                    |--------------------------------------------------------------------------
                    */

                    $headerRows = [];

                    for (
                        $row = 1;
                        $row <= $highestRow;
                        $row++
                    ) {
                        $value = trim(
                            (string) $sheet
                                ->getCell(
                                    'A' . $row
                                )
                                ->getValue()
                        );

                        if (
                            strtoupper($value) ===
                            'NO'
                        ) {
                            $headerRows[] = $row;
                        }
                    }

                    foreach ($headerRows as $headerRow) {

                        $sheet->getStyle(
                            'A' .
                            $headerRow .
                            ':' .
                            $lastColumn .
                            $headerRow
                        )->applyFromArray([

                            'font' => [
                                'bold' => true
                            ],

                            'fill' => [
                                'fillType' =>
                                    Fill::FILL_SOLID,

                                'startColor' => [
                                    'rgb' =>
                                        'D9E1F2'
                                ]
                            ],

                            'alignment' => [
                                'horizontal' =>
                                    Alignment::HORIZONTAL_CENTER,

                                'vertical' =>
                                    Alignment::VERTICAL_CENTER,

                                'wrapText' => true
                            ],

                            'borders' => [
                                'allBorders' => [
                                    'borderStyle' =>
                                        Border::BORDER_THIN
                                ]
                            ]
                        ]);

                        /*
                        |--------------------------------------------------------------------------
                        | CARI TOTAL UNTUK FORM INI
                        |--------------------------------------------------------------------------
                        */

                        $totalRow = null;

                        for (
                            $row = $headerRow + 1;
                            $row <= $highestRow;
                            $row++
                        ) {
                            $found = false;

                            for (
                                $col = 1;
                                $col <= (
                                    $isPacking
                                        ? 10
                                        : 7
                                );
                                $col++
                            ) {
                                $value = trim(
                                    (string) $sheet
                                        ->getCellByColumnAndRow(
                                            $col,
                                            $row
                                        )
                                        ->getValue()
                                );

                                if (
                                    strtoupper($value) ===
                                    'TOTAL'
                                    ||
                                    str_starts_with(
                                        strtoupper($value),
                                        'TOTAL '
                                    )
                                ) {
                                    $found = true;
                                    break;
                                }
                            }

                            if ($found) {
                                $totalRow = $row;
                                break;
                            }

                            /*
                            | Jika bertemu header berikutnya,
                            | berarti form sebelumnya tidak punya total.
                            */
                            if (
                                trim(
                                    (string) $sheet
                                        ->getCell(
                                            'A' . $row
                                        )
                                        ->getValue()
                                ) === 'NO'
                            ) {
                                break;
                            }
                        }

                        if ($totalRow) {

                            $sheet->getStyle(
                                'A' .
                                $headerRow .
                                ':' .
                                $lastColumn .
                                $totalRow
                            )->getBorders()
                                ->getAllBorders()
                                ->setBorderStyle(
                                    Border::BORDER_THIN
                                );

                            $sheet->getStyle(
                                'A' .
                                $totalRow .
                                ':' .
                                $lastColumn .
                                $totalRow
                            )->applyFromArray([
                                'font' => [
                                    'bold' => true
                                ]
                            ]);
                        }
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | STYLE JUDUL FORM ORANG
                    |--------------------------------------------------------------------------
                    */

                    for (
                        $row = 1;
                        $row <= $highestRow;
                        $row++
                    ) {
                        $value = trim(
                            (string) $sheet
                                ->getCell(
                                    'A' . $row
                                )
                                ->getValue()
                        );

                        if (
                            str_starts_with(
                                strtoupper($value),
                                'PEMBAYARAN BORONGAN -'
                            )
                            ||
                            str_starts_with(
                                strtoupper($value),
                                'PEMBAYARAN BORONGAN PACKING -'
                            )
                        ) {
                            $sheet->mergeCells(
                                'A' .
                                $row .
                                ':' .
                                $lastColumn .
                                $row
                            );

                            $sheet->getStyle(
                                'A' .
                                $row .
                                ':' .
                                $lastColumn .
                                $row
                            )->applyFromArray([
                                'font' => [
                                    'bold' => true,
                                    'size' => 12
                                ],
                                'alignment' => [
                                    'horizontal' =>
                                        Alignment::HORIZONTAL_CENTER,
                                    'vertical' =>
                                        Alignment::VERTICAL_CENTER
                                ]
                            ]);

                            $sheet
                                ->getRowDimension($row)
                                ->setRowHeight(24);
                        }

                        if (
                            str_starts_with(
                                strtoupper($value),
                                'PENERIMA :'
                            )
                        ) {
                            $sheet->mergeCells(
                                'A' .
                                $row .
                                ':' .
                                $lastColumn .
                                $row
                            );

                            $sheet->getStyle(
                                'A' .
                                $row .
                                ':' .
                                $lastColumn .
                                $row
                            )->applyFromArray([
                                'font' => [
                                    'bold' => true
                                ],
                                'alignment' => [
                                    'horizontal' =>
                                        Alignment::HORIZONTAL_LEFT,
                                    'vertical' =>
                                        Alignment::VERTICAL_CENTER
                                ]
                            ]);
                        }
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | NUMBER FORMAT SEMUA FORM
                    |--------------------------------------------------------------------------
                    */

                    if ($isPacking) {

                        $sheet->getStyle(
                            'E1:E' .
                            $highestRow
                        )->getNumberFormat()
                            ->setFormatCode(
                                '#,##0.##'
                            );

                        $sheet->getStyle(
                            'F1:J' .
                            $highestRow
                        )->getNumberFormat()
                            ->setFormatCode(
                                '#,##0'
                            );

                    } else {

                        $sheet->getStyle(
                            'E1:E' .
                            $highestRow
                        )->getNumberFormat()
                            ->setFormatCode(
                                '#,##0.##'
                            );

                        $sheet->getStyle(
                            'F1:G' .
                            $highestRow
                        )->getNumberFormat()
                            ->setFormatCode(
                                '#,##0'
                            );
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | ALIGNMENT
                    |--------------------------------------------------------------------------
                    */

                    $sheet->getStyle(
                        'A1:' .
                        $lastColumn .
                        $highestRow
                    )->getAlignment()
                        ->setVertical(
                            Alignment::VERTICAL_CENTER
                        );

                    /*
                    |--------------------------------------------------------------------------
                    | WIDTH
                    |--------------------------------------------------------------------------
                    */

                    if ($isPacking) {

                        $widths = [
                            'A' => 6,
                            'B' => 42,
                            'C' => 14,
                            'D' => 16,
                            'E' => 10,
                            'F' => 12,
                            'G' => 12,
                            'H' => 14,
                            'I' => 12,
                            'J' => 16
                        ];

                    } else {

                        $widths = [
                            'A' => 6,
                            'B' => 42,
                            'C' => 14,
                            'D' => 16,
                            'E' => 10,
                            'F' => 16,
                            'G' => 18
                        ];
                    }

                    foreach (
                        $widths as $column => $width
                    ) {
                        $sheet
                            ->getColumnDimension(
                                $column
                            )
                            ->setWidth($width);
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | HEIGHT
                    |--------------------------------------------------------------------------
                    */

                    $sheet
                        ->getRowDimension(1)
                        ->setRowHeight(25);

                    $sheet
                        ->getRowDimension(2)
                        ->setRowHeight(22);

                    $sheet
                        ->getRowDimension(4)
                        ->setRowHeight(25);

                    /*
                    |--------------------------------------------------------------------------
                    | FREEZE
                    |--------------------------------------------------------------------------
                    */

                    $sheet->freezePane('A6');

                    /*
                    |--------------------------------------------------------------------------
                    | PRINT
                    |--------------------------------------------------------------------------
                    */

                    $sheet->getPageSetup()
                        ->setOrientation(
                            PageSetup::ORIENTATION_LANDSCAPE
                        );

                    $sheet->getPageSetup()
                        ->setPaperSize(
                            PageSetup::PAPERSIZE_A4
                        );

                    $sheet->getPageSetup()
                        ->setFitToWidth(1)
                        ->setFitToHeight(0);

                    $sheet->getPageMargins()
                        ->setTop(0.25)
                        ->setRight(0.25)
                        ->setBottom(0.25)
                        ->setLeft(0.25);
                }
        ];
    }

    protected function findTotalRow(
        $sheet,
        int $highestRow,
        string $lastColumn
    ): ?int {

        for (
            $row = 1;
            $row <= $highestRow;
            $row++
        ) {

            $values =
                $sheet->rangeToArray(
                    'A' .
                    $row .
                    ':' .
                    $lastColumn .
                    $row,
                    null,
                    true,
                    false
                )[0];

            foreach (
                $values
                as $value
            ) {

                if (
                    strtoupper(
                        trim(
                            (string) $value
                        )
                    ) === 'TOTAL'
                ) {
                    return $row;
                }
            }
        }

        return null;
    }

    /*
    |--------------------------------------------------------------------------
    | SAFE TITLE
    |--------------------------------------------------------------------------
    */

    protected function safeTitle(
        string $title
    ): string {

        $title = preg_replace(
            '/[\\\\\/\?\*\[\]\:]/',
            ' ',
            $title
        );

        $title = trim(
            $title
        );

        if ($title === '') {
            $title = 'BORONGAN';
        }

        return mb_substr(
            $title,
            0,
            31
        );
    }
}
