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
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

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
        $filteredData = $this->filterDateRange(
            $this->data
        );

        $primaryItems = collect();
        $otherJobs = [];

        foreach ($filteredData as $item) {

            $job = trim(
                (string) ($item->pekerjaan ?? '')
            );

            $jobLower = strtolower($job);

            /*
             * Primary category tetap menjadi satu SHEET DIVISI.
             *
             * Contoh:
             * search = packing
             * => semua Packing Foam / Medium / Box / Single Face
             *    masuk satu sheet PACKING.
             */
            if ($this->matchesPrimary($jobLower)) {
                $primaryItems->push($item);
                continue;
            }

            /*
             * Pekerjaan/divisi lain tetap dibuat menjadi sheet sendiri.
             */
            if ($job === '') {
                $job = 'PEKERJAAN LAIN';
            }

            $jobKey = strtolower(trim($job));

            if (!isset($otherJobs[$jobKey])) {
                $otherJobs[$jobKey] = [
                    'title' => $job,
                    'data' => collect(),
                ];
            }

            $otherJobs[$jobKey]['data']->push($item);
        }

        $sheets = [];

        /*
         * Jika keyword/divisi dipilih, tampilkan divisi tersebut sebagai
         * sheet pertama.
         */
        if ($this->primaryCategory !== '') {

            if ($primaryItems->isNotEmpty()) {

                $sheets[] = new ReportSheet(
                    data: $primaryItems->values(),
                    reportType: $this->isPacking()
                        ? 'packing'
                        : 'normal',
                    sheetTitle: $this->primaryTitle(),
                    dateFrom: $this->dateFrom,
                    dateTo: $this->dateTo
                );
            }

        } else {

            /*
             * Jika search kosong, jangan buat sheet GENERAL.
             * Langsung pecah berdasarkan PEKERJAAN/DIVISI.
             */
            $jobs = [];

            foreach ($filteredData as $item) {

                $job = trim(
                    (string) ($item->pekerjaan ?? '')
                );

                if ($job === '') {
                    $job = 'PEKERJAAN LAIN';
                }

                $key = strtolower($job);

                if (!isset($jobs[$key])) {
                    $jobs[$key] = [
                        'title' => $job,
                        'data' => collect(),
                    ];
                }

                $jobs[$key]['data']->push($item);
            }

            uasort($jobs, function ($a, $b) {
                return strcasecmp(
                    $a['title'],
                    $b['title']
                );
            });

            foreach ($jobs as $job) {

                $isPacking = $this->collectionIsPacking(
                    $job['data']
                );

                $sheets[] = new ReportSheet(
                    data: $job['data']->values(),
                    reportType: $isPacking
                        ? 'packing'
                        : 'normal',
                    sheetTitle: strtoupper(
                        trim($job['title'])
                    ),
                    dateFrom: $this->dateFrom,
                    dateTo: $this->dateTo
                );
            }

        }

        /*
         * Divisi lain tetap dibuat sebagai sheet terpisah.
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

            $sheetTitle = $this->safeSheetTitle(
                strtoupper(
                    trim($job['title'])
                )
            );

            /*
             * Jangan sampai nama sheet sama dengan primary.
             */
            if (
                strtoupper($sheetTitle) ===
                strtoupper(
                    $this->safeSheetTitle(
                        $this->primaryTitle()
                    )
                )
            ) {
                $sheetTitle = mb_substr(
                    $sheetTitle . ' 2',
                    0,
                    31
                );
            }

            $sheets[] = new ReportSheet(
                data: $job['data']->values(),
                reportType: $this->collectionIsPacking(
                    $job['data']
                )
                    ? 'packing'
                    : 'normal',
                sheetTitle: $sheetTitle,
                dateFrom: $this->dateFrom,
                dateTo: $this->dateTo
            );
        }

        /*
         * TO PRINT harus selalu menjadi sheet pertama.
         * Isinya memakai collection dari sheet-sheet yang sama persis,
         * bukan copy worksheet/cell, sehingga layout blok tetap konsisten.
         */
        $printSheet = new ToPrintSheet($sheets);

        return array_merge(
            [$printSheet],
            $sheets
        );
    }

    protected function filterDateRange(
        Collection $data
    ): Collection {
        return $data->filter(function ($item) {

            $dateValue =
                $item->tanggal
                ?? $item->date
                ?? $item->tanggal_transaksi
                ?? $item->created_at
                ?? null;

            if (!$dateValue) {
                return true;
            }

            $timestamp = strtotime(
                (string) $dateValue
            );

            if ($timestamp === false) {
                return true;
            }

            $itemDate = date(
                'Y-m-d',
                $timestamp
            );

            if (
                $this->dateFrom &&
                $itemDate < $this->dateFrom
            ) {
                return false;
            }

            if (
                $this->dateTo &&
                $itemDate > $this->dateTo
            ) {
                return false;
            }

            return true;

        })->values();
    }

    protected function matchesPrimary(
        string $job
    ): bool {

        if ($this->primaryCategory === '') {
            return false;
        }

        if ($this->primaryCategory === 'packing') {

            return
                str_contains($job, 'packing')
                || str_contains($job, 'foam')
                || str_contains($job, 'medium')
                || str_contains($job, 'single face')
                || str_contains($job, 'box');
        }

        return str_contains(
            $job,
            $this->primaryCategory
        );
    }

    protected function isPacking(): bool
    {
        return $this->primaryCategory === 'packing';
    }

    protected function collectionIsPacking(
        Collection $data
    ): bool {
        return $data->contains(function ($item) {

            $job = strtolower(
                trim((string) ($item->pekerjaan ?? ''))
            );

            return
                str_contains($job, 'packing')
                || str_contains($job, 'foam')
                || str_contains($job, 'medium')
                || str_contains($job, 'single face')
                || str_contains($job, 'box');
        });
    }

    protected function primaryTitle(): string
    {
        if ($this->primaryCategory === '') {
            return 'BORONGAN';
        }

        return strtoupper(
            $this->primaryCategory
        );
    }

    protected function safeSheetTitle(
        string $title
    ): string {

        $title = preg_replace(
            '/[\\\\\/\?\*\[\]\:]/',
            ' ',
            $title
        );

        $title = trim($title);

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
| ReportSheet
|--------------------------------------------------------------------------
|
| SATU SHEET = SATU DIVISI / PEKERJAAN.
|
| Di dalam sheet tersebut:
| - setiap PENGERJA/person tetap dibuat sebagai form
| - tidak ada PENERIMA : xxx
| - tidak ada GENERAL
| - tidak ada tabel resume
| - TOTAL selalu hanya "TOTAL"
| - setiap form memiliki PERIODE PENARIKAN
|
*/
/**
 * --------------------------------------------------------------------------
 * TO PRINT SHEET
 * --------------------------------------------------------------------------
 *
 * Sheet pertama yang berisi seluruh form dari semua sheet/divisi.
 * Tidak menyalin worksheet Excel; kita membangun ulang row dari collection
 * sheet sumber sehingga merge, border, width, dan pagination dapat diatur
 * khusus untuk halaman print.
 */
class ToPrintSheet implements
    FromCollection,
    WithEvents,
    WithTitle
{
    protected array $sourceSheets;

    public function __construct(array $sourceSheets)
    {
        $this->sourceSheets = $sourceSheets;
    }

    public function title(): string
    {
        return 'TO PRINT';
    }

    public function collection()
    {
        $rows = collect();

        foreach ($this->sourceSheets as $sourceSheet) {
            $sourceRows = $sourceSheet->collection();

            if ($sourceRows->isEmpty()) {
                continue;
            }

            if ($rows->isNotEmpty()) {
                // Satu baris pemisah antar divisi/sheet.
                $rows->push(array_fill(0, 10, ''));
            }

            foreach ($sourceRows as $sourceRow) {
                $row = array_values((array) $sourceRow);

                // TO PRINT memakai maksimum 10 kolom agar packing dan normal
                // dapat hidup dalam satu sheet tanpa menggeser blok berikutnya.
                $row = array_pad($row, 10, '');

                if (count($row) > 10) {
                    $row = array_slice($row, 0, 10);
                }

                $rows->push($row);
            }
        }

        return $rows;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();
                $lastColumn = 'J';

                if ($highestRow < 1) {
                    return;
                }

                // ---------------------------------------------------------
                // Deteksi header masing-masing form
                // ---------------------------------------------------------
                $headerRows = [];

                for ($row = 1; $row <= $highestRow; $row++) {
                    $value = trim((string) $sheet
                        ->getCell('A' . $row)
                        ->getValue());

                    if (strtoupper($value) === 'NO') {
                        $headerRows[] = $row;
                    }
                }

                // ---------------------------------------------------------
                // Style header + tabel
                // ---------------------------------------------------------
                foreach ($headerRows as $headerRow) {
                    $isPacking = false;

                    $headerValues = [];
                    for ($col = 1; $col <= 10; $col++) {
                        $headerValues[] = strtoupper(trim((string) $sheet
                            ->getCellByColumnAndRow($col, $headerRow)
                            ->getValue()));
                    }

                    if (
                        in_array('FE FOAM', $headerValues, true) ||
                        in_array('SINGLE FACE', $headerValues, true) ||
                        in_array('BOX', $headerValues, true)
                    ) {
                        $isPacking = true;
                    }

                    $lastTableColumn = $isPacking ? 'J' : 'G';

                    $sheet->getStyle(
                        'A' . $headerRow . ':' . $lastTableColumn . $headerRow
                    )->applyFromArray([
                        'font' => [
                            'bold' => true,
                        ],
                        'fill' => [
                            'fillType' => Fill::FILL_SOLID,
                            'startColor' => [
                                'rgb' => 'D9E1F2',
                            ],
                        ],
                        'alignment' => [
                            'horizontal' => Alignment::HORIZONTAL_CENTER,
                            'vertical' => Alignment::VERTICAL_CENTER,
                            'wrapText' => true,
                        ],
                        'borders' => [
                            'allBorders' => [
                                'borderStyle' => Border::BORDER_THIN,
                            ],
                        ],
                    ]);

                    // Cari TOTAL sampai sebelum header berikutnya.
                    $totalRow = null;

                    for ($row = $headerRow + 1; $row <= $highestRow; $row++) {
                        $first = trim((string) $sheet
                            ->getCell('A' . $row)
                            ->getValue());

                        if (
                            $row !== $headerRow + 1 &&
                            strtoupper($first) === 'NO'
                        ) {
                            break;
                        }

                        for ($col = 1; $col <= ($isPacking ? 10 : 7); $col++) {
                            $value = trim((string) $sheet
                                ->getCellByColumnAndRow($col, $row)
                                ->getValue());

                            if (strtoupper($value) === 'TOTAL') {
                                $totalRow = $row;
                                break 2;
                            }
                        }
                    }

                    if ($totalRow) {
                        $sheet->getStyle(
                            'A' . $headerRow . ':' . $lastTableColumn . $totalRow
                        )->getBorders()->getAllBorders()->setBorderStyle(
                            Border::BORDER_THIN
                        );

                        $sheet->getStyle(
                            'A' . $totalRow . ':' . $lastTableColumn . $totalRow
                        )->getFont()->setBold(true);
                    }
                }

                // ---------------------------------------------------------
                // Judul + periode
                // ---------------------------------------------------------
                for ($row = 1; $row <= $highestRow; $row++) {
                    $value = trim((string) $sheet
                        ->getCell('A' . $row)
                        ->getValue());

                    $upper = strtoupper($value);

                    if (str_starts_with($upper, 'PEMBAYARAN BORONGAN')) {
                        $sheet->mergeCells(
                            'A' . $row . ':J' . $row
                        );

                        $sheet->getStyle(
                            'A' . $row . ':J' . $row
                        )->applyFromArray([
                            'font' => [
                                'bold' => true,
                                'size' => 12,
                            ],
                            'alignment' => [
                                'horizontal' => Alignment::HORIZONTAL_CENTER,
                                'vertical' => Alignment::VERTICAL_CENTER,
                            ],
                        ]);

                        $sheet->getRowDimension($row)->setRowHeight(24);
                    }

                    if (str_starts_with($upper, 'PERIODE PENARIKAN:')) {
                        $sheet->mergeCells(
                            'A' . $row . ':J' . $row
                        );

                        $sheet->getStyle(
                            'A' . $row . ':J' . $row
                        )->applyFromArray([
                            'font' => [
                                'bold' => true,
                                'size' => 10,
                            ],
                            'alignment' => [
                                'horizontal' => Alignment::HORIZONTAL_CENTER,
                                'vertical' => Alignment::VERTICAL_CENTER,
                            ],
                        ]);

                        $sheet->getRowDimension($row)->setRowHeight(20);
                    }
                }

                // ---------------------------------------------------------
                // Number format
                // ---------------------------------------------------------
                $sheet->getStyle('E1:E' . $highestRow)
                    ->getNumberFormat()
                    ->setFormatCode('0');

                $sheet->getStyle('F1:J' . $highestRow)
                    ->getNumberFormat()
                    ->setFormatCode('[$Rp-421] #,##0');

                // ---------------------------------------------------------
                // Alignment + wrapping
                // ---------------------------------------------------------
                $sheet->getStyle(
                    'A1:J' . $highestRow
                )->getAlignment()->setVertical(
                    Alignment::VERTICAL_CENTER
                );

                $sheet->getStyle(
                    'B1:B' . $highestRow
                )->getAlignment()->setWrapText(true);

                // ---------------------------------------------------------
                // Column widths khusus TO PRINT
                // ---------------------------------------------------------
                $widths = [
                    'A' => 6,
                    'B' => 34,
                    'C' => 13,
                    'D' => 14,
                    'E' => 8,
                    'F' => 12,
                    'G' => 13,
                    'H' => 13,
                    'I' => 11,
                    'J' => 14,
                ];

                foreach ($widths as $column => $width) {
                    $sheet->getColumnDimension($column)->setWidth($width);
                }

                $sheet->freezePane('A1');

                // ---------------------------------------------------------
                // PRINT A4 PORTRAIT
                // ---------------------------------------------------------
                $pageSetup = $sheet->getPageSetup();

                $pageSetup
                    ->setOrientation(PageSetup::ORIENTATION_PORTRAIT)
                    ->setPaperSize(PageSetup::PAPERSIZE_A4)
                    ->setFitToWidth(1)
                    ->setFitToHeight(0)
                    ->setFitToPage(false);

                $sheet->getPageMargins()
                    ->setTop(0.20)
                    ->setRight(0.15)
                    ->setBottom(0.20)
                    ->setLeft(0.15);

                $sheet->getPageSetup()->setPrintArea(
                    'A1:J' . $highestRow
                );

                $sheet->setShowGridLines(false);
            },
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
        $this->data = $data->values();

        $this->reportType = $reportType;

        $this->sheetTitle = $this->safeTitle(
            $sheetTitle
        );

        $this->dateFrom = $dateFrom;
        $this->dateTo = $dateTo;
    }

    public function title(): string
    {
        return $this->sheetTitle;
    }

    public function collection()
    {
        return $this->reportType === 'packing'
            ? $this->packingRows()
            : $this->normalRows();
    }

    /*
    |--------------------------------------------------------------------------
    | NORMAL
    |--------------------------------------------------------------------------
    */

    protected function normalRows(): Collection
    {
        $rows = collect();

        /*
         * Tidak ada general table.
         * Langsung form per orang.
         */
        $persons = $this->uniquePersonsFrom(
            $this->data
        );

        foreach ($persons as $person) {

            $personData = $this->dataForPerson(
                $this->data,
                $person
            );

            if ($personData->isEmpty()) {
                continue;
            }

            /*
             * Jarak antar form.
             */
            if ($rows->isNotEmpty()) {
                $rows->push(
                    array_fill(0, 7, '')
                );
            }

            /*
             * JUDUL ORANG
             */
            $rows->push([
                'PEMBAYARAN BORONGAN ' .
                strtoupper($this->sheetTitle)
            ]);

            /*
             * PERIODE SETIAP FORM
             */
            $rows->push([
                $this->periodText()
            ]);

            /*
             * HEADER
             */
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

            /*
             * TOTAL SELALU TOTAL.
             */
            $rows->push([
                '',
                '',
                '',
                '',
                '',
                'TOTAL',
                $personTotal
            ]);

            $rows->push(
                array_fill(0, 7, '')
            );
            $rows->push(
                array_fill(0, 7, '')
            );

            $rows->push([
                'Made by',
                '',
                'Finance',
                '',
                'Checked by',
                '',
                'Penerima'
            ]);

            $rows->push(
                array_fill(0, 7, '')
            );
            $rows->push(
                array_fill(0, 7, '')
            );

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
                    4,
                    '.',
                    ''
                );

            if (!isset($groups[$key])) {

                $groups[$key] = [
                    'description' => $description,
                    'article' => $article,
                    'no_po' => trim(
                        (string) (
                            $item->no_po
                            ?? $item->po
                            ?? ''
                        )
                    ),
                    'pekerjaan' => $pekerjaan,
                    'qty' => 0,
                    'harga' => $harga,
                    'total' => 0,
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

    /*
    |--------------------------------------------------------------------------
    | PACKING
    |--------------------------------------------------------------------------
    */

    protected function packingRows(): Collection
    {
        $rows = collect();

        $persons = $this->uniquePersonsFrom(
            $this->data
        );

        foreach ($persons as $person) {

            $personData = $this->dataForPerson(
                $this->data,
                $person
            );

            if ($personData->isEmpty()) {
                continue;
            }

            if ($rows->isNotEmpty()) {
                $rows->push(
                    array_fill(0, 10, '')
                );
            }

            /*
             * JUDUL FORM ORANG.
             */
            $rows->push([
                'PEMBAYARAN BORONGAN PACKING'
            ]);

            /*
             * PERIODE SETIAP FORM.
             */
            $rows->push([
                $this->periodText()
            ]);

            /*
             * HEADER.
             */
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

            /*
             * TOTAL SAJA.
             */
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
                $personTotal
            ]);

            $rows->push(
                array_fill(0, 10, '')
            );
            $rows->push(
                array_fill(0, 10, '')
            );

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

            $rows->push(
                array_fill(0, 10, '')
            );
            $rows->push(
                array_fill(0, 10, '')
            );

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
                    4,
                    '.',
                    ''
                );

            if (!isset($groups[$key])) {

                $groups[$key] = [
                    'description' => $description,
                    'article' => $article,
                    'no_po' => trim(
                        (string) (
                            $item->no_po
                            ?? $item->po
                            ?? ''
                        )
                    ),
                    'pekerjaan' => $jenis,
                    'qty' => 0,
                    'harga' => $harga,
                    'total' => 0,
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

    /*
    |--------------------------------------------------------------------------
    | PERSON
    |--------------------------------------------------------------------------
    */

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
                $person = 'BELUM ADA PENGERJA';
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

        if ($personKey === 'belum ada pengerja') {

            return $data->filter(function ($item) {

                return trim(
                    (string) (
                        $item->person ?? ''
                    )
                ) === '';

            })->values();
        }

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

    /*
    |--------------------------------------------------------------------------
    | PERIOD
    |--------------------------------------------------------------------------
    */

    protected function periodText(): string
    {
        if (
            $this->dateFrom &&
            $this->dateTo
        ) {
            return 'PERIODE PENARIKAN: '
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
            return 'PERIODE PENARIKAN: MULAI '
                . date(
                    'd F Y',
                    strtotime($this->dateFrom)
                );
        }

        if ($this->dateTo) {
            return 'PERIODE PENARIKAN: SAMPAI '
                . date(
                    'd F Y',
                    strtotime($this->dateTo)
                );
        }

        return 'PERIODE PENARIKAN: '
            . date('d F Y');
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
                        $event->sheet->getDelegate();

                    $isPacking =
                        $this->reportType === 'packing';

                    $lastColumn =
                        $isPacking
                            ? 'J'
                            : 'G';

                    $highestRow =
                        $sheet->getHighestRow();

                    /*
                     * Cari seluruh header NO.
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
                            strtoupper($value) === 'NO'
                        ) {
                            $headerRows[] = $row;
                        }
                    }

                    /*
                     * Style semua form.
                     */
                    foreach ($headerRows as $headerRow) {

                        $sheet->getStyle(
                            'A' .
                            $headerRow .
                            ':' .
                            $lastColumn .
                            $headerRow
                        )->applyFromArray([

                            'font' => [
                                'bold' => true,
                            ],

                            'fill' => [
                                'fillType' =>
                                    Fill::FILL_SOLID,

                                'startColor' => [
                                    'rgb' =>
                                        'D9E1F2',
                                ],
                            ],

                            'alignment' => [
                                'horizontal' =>
                                    Alignment::HORIZONTAL_CENTER,

                                'vertical' =>
                                    Alignment::VERTICAL_CENTER,

                                'wrapText' => true,
                            ],

                            'borders' => [
                                'allBorders' => [
                                    'borderStyle' =>
                                        Border::BORDER_THIN,
                                ],
                            ],
                        ]);

                        /*
                         * Cari TOTAL setelah header tersebut.
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
                             * Header berikutnya berarti form selesai.
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
                                    'bold' => true,
                                ],
                            ]);
                        }
                    }

                    /*
                     * Style judul form dan periode.
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

                        $upper = strtoupper($value);

                        /*
                         * Judul orang.
                         */
                        if (
                            str_starts_with(
                                $upper,
                                'PEMBAYARAN BORONGAN'
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
                                'A' . $row . ':' . $lastColumn . $row
                            )->getAlignment()
                                ->setHorizontal(Alignment::HORIZONTAL_CENTER)
                                ->setVertical(Alignment::VERTICAL_CENTER)
                                ->setWrapText(false);

                            $sheet->getStyle(
                                'A' .
                                $row .
                                ':' .
                                $lastColumn .
                                $row
                            )->applyFromArray([

                                'font' => [
                                    'bold' => true,
                                    'size' => 12,
                                ],

                                'alignment' => [
                                    'horizontal' =>
                                        Alignment::HORIZONTAL_CENTER,

                                    'vertical' =>
                                        Alignment::VERTICAL_CENTER,
                                ],
                            ]);

                            $sheet
                                ->getRowDimension($row)
                                ->setRowHeight(24);
                        }

                        /*
                         * Periode setiap form.
                         */
                        if (
                            str_starts_with(
                                $upper,
                                'PERIODE PENARIKAN:'
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
                                    'size' => 10,
                                ],

                                'alignment' => [
                                    'horizontal' =>
                                        Alignment::HORIZONTAL_CENTER,

                                    'vertical' =>
                                        Alignment::VERTICAL_CENTER,
                                ],
                            ]);

                            $sheet
                                ->getRowDimension($row)
                                ->setRowHeight(20);
                        }
                    }

                    /*
                     * NUMBER FORMAT.
                     *
                     * PENTING:
                     * "0.##" tidak menggunakan separator ribuan.
                     *
                     * 20      => 20
                     * 20.5    => 20.5
                     * 1500    => 1500
                     * 1500.25 => 1500.25
                     *
                     * Jadi tidak menjadi 20.000 / 1,500.
                     */
                    if ($isPacking) {

                        $sheet->getStyle(
                            'E1:E' .
                            $highestRow
                        )->getNumberFormat()
                            ->setFormatCode(
                                '0'
                            );

                        $sheet->getStyle(
                            'F1:J' .
                            $highestRow
                        )->getNumberFormat()
                            ->setFormatCode(
                                '[$Rp-421] #,##0'
                            );

                    } else {

                        $sheet->getStyle(
                            'E1:E' .
                            $highestRow
                        )->getNumberFormat()
                            ->setFormatCode(
                                '0'
                            );

                        $sheet->getStyle(
                            'F1:G' .
                            $highestRow
                        )->getNumberFormat()
                            ->setFormatCode(
                                '[$Rp-421] #,##0'
                            );
                    }

                    /*
                     * Alignment.
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
                     * Width.
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
                            'J' => 16,
                        ];

                    } else {

                        $widths = [
                            'A' => 6,
                            'B' => 42,
                            'C' => 14,
                            'D' => 16,
                            'E' => 10,
                            'F' => 16,
                            'G' => 18,
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
                     * Freeze.
                     */
                    $sheet->freezePane('A4');

                    /*
                     * PRINT A4 PORTRAIT.
                     *
                     * Width is fitted to one page, while height is allowed
                     * to flow naturally. We also add SMART page breaks below
                     * so the next person's form only moves when the current
                     * page has insufficient room.
                     */
                    $pageSetup = $sheet->getPageSetup();

                    $pageSetup
                        ->setOrientation(
                            PageSetup::ORIENTATION_PORTRAIT
                        )
                        ->setPaperSize(
                            PageSetup::PAPERSIZE_A4
                        )
                        ->setFitToWidth(1)
                        ->setFitToHeight(0)
                        ->setFitToPage(false);

                    $sheet->getPageMargins()
                        ->setTop(0.20)
                        ->setRight(0.20)
                        ->setBottom(0.20)
                        ->setLeft(0.20);

                    /*
                     * SMART PAGE BREAK PER FORM
                     *
                     * There is no longer a forced "one form = one page" rule.
                     * A page can contain several forms. A break is inserted
                     * only when the estimated remaining space is not enough
                     * for the next complete form.
                     */
                    $formTitleRows = [];

                    for ($r = 1; $r <= $highestRow; $r++) {

                        $cell = trim((string) $sheet
                            ->getCell('A' . $r)
                            ->getValue());

                        if (str_starts_with(
                            strtoupper($cell),
                            'PEMBAYARAN BORONGAN'
                        )) {
                            $formTitleRows[] = $r;
                        }
                    }

                    /*
                     * Approximate printable vertical capacity in rows.
                     * Portrait A4 with the compact margins above normally
                     * accommodates roughly this amount at the default Excel
                     * row height. The algorithm below uses actual row heights
                     * when they have been explicitly set.
                     */
                    $pageCapacityPoints = 760;
                    $usedPoints = 0;
                    $previousRow = 1;

                    foreach ($formTitleRows as $index => $titleRow) {

                        $nextTitleRow = $formTitleRows[$index + 1] ?? ($highestRow + 1);

                        /* Gap before the next form. */
                        $formStart = $titleRow;
                        $formEnd = $nextTitleRow - 1;

                        $formHeight = 0;

                        for ($r = $formStart; $r <= $formEnd; $r++) {

                            $height = $sheet
                                ->getRowDimension($r)
                                ->getRowHeight();

                            if ($height === -1 || $height === null || $height <= 0) {
                                $height = 15;
                            }

                            $formHeight += $height;
                        }

                        /*
                         * First form always starts on page 1. For later forms,
                         * if it cannot fit, create a break immediately before
                         * it. This keeps each person's form intact.
                         */
                        if (
                            $index > 0 &&
                            ($usedPoints + $formHeight) > $pageCapacityPoints
                        ) {
                            $sheet->setBreakByColumnAndRow(
                                1,
                                $titleRow,
                                Worksheet::BREAK_ROW
                            );

                            $usedPoints = $formHeight;
                        } else {
                            $usedPoints += $formHeight;
                        }
                    }
                },
        ];
    }

    protected function safeTitle(
        string $title
    ): string {

        $title = preg_replace(
            '/[\\\\\/\?\*\[\]\:]/',
            ' ',
            $title
        );

        $title = trim($title);

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
