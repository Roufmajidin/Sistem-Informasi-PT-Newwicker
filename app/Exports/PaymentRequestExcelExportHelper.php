<?php

namespace App\Exports;

use App\Models\InspectSchedule;
use App\Models\PaymentRequest;
use App\Models\PaymentRequestSaved;
use App\Models\ProductionTimeline;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\PageSetup;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Export Payment Request ke Excel dengan layout 2-up.
 *
 * Prinsip layout:
 * - 1 SUBKON / 1 blok print page.
 * - 2 panel berdampingan: kiri + kanan.
 * - SPK diisi ke kiri terlebih dahulu sampai batas tinggi, lalu kanan.
 * - Jika kanan tidak memiliki SPK, panel kanan tidak digambar.
 * - NO SPK tidak dipotong; Excel memakai wrap text.
 * - Timeline hanya: NO | TANGGAL | DESCRIPTION | IN | PASSED.
 * - Tanggal IN dan tanggal QC yang sama digabung menjadi satu baris.
 * - QTY IN memakai balancing component yang sama dengan Modal Mutasi.
 */
class PaymentRequestExcelExportHelper
{
    private const LEFT_START_COL = 1;   // A
    private const RIGHT_START_COL = 8;  // H
    private const PANEL_WIDTH = 6;      // A:F / H:M
    private const GUTTER_COL = 7;       // G
    private const MAX_PANEL_ROWS = 55;

    public static function build(int $draftId): Spreadsheet
    {
        $data = self::loadData($draftId);
        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0);

        foreach (($data['groups'] ?? []) as $group) {
            $supplier = self::text($group['supplier'] ?? 'SUBKON');
            $title = preg_replace('/[\\\\\/\?\*\[\]:]/', '-', $supplier);
            $title = trim($title, "' ");
            $title = $title !== '' ? mb_substr($title, 0, 31) : 'SUBKON';
            $baseTitle = $title;
            $suffix = 1;
            while ($spreadsheet->getSheetByName($title) !== null) {
                $ending = '-' . $suffix++;
                $title = mb_substr($baseTitle, 0, 31 - mb_strlen($ending)) . $ending;
            }

            $sheet = $spreadsheet->createSheet();
            $sheet->setTitle($title);
            self::configureSheet($sheet);

            $row = 1;
            $parts = self::splitGroupIntoTwoUp($group);
            foreach ($parts as $partIndex => $part) {
                if ($partIndex > 0) {
                    $row += 2;
                }

                $left = $part[0] ?? [];
                $right = $part[1] ?? [];
                $leftEnd = null;
                $rightEnd = null;

                if ($left) {
                    $leftEnd = self::drawPanel($sheet, $left, $group, $data['request_no'], $data['request_date'], self::LEFT_START_COL, $row);
                }
                if ($right) {
                    $rightEnd = self::drawPanel($sheet, $right, $group, $data['request_no'], $data['request_date'], self::RIGHT_START_COL, $row);
                }

                $endRow = max($leftEnd ?? $row, $rightEnd ?? $row);
                if ($left && $right) {
                    self::drawVerticalDivider($sheet, $row, $endRow);
                }
                $row = $endRow + 1;
            }

            self::finalizeSheet($sheet, $row - 1);
        }

        if ($spreadsheet->getSheetCount() === 0) {
            $sheet = $spreadsheet->createSheet();
            $sheet->setTitle('List Payment');
            self::configureSheet($sheet);
            self::finalizeSheet($sheet, 1);
        }

        $spreadsheet->setActiveSheetIndex(0);
        return $spreadsheet;
    }

    private static function configureSheet(Worksheet $sheet): void
    {
        $widths = [
            'A' => 5.5,
            'B' => 14,
            'C' => 29,
            'D' => 12,
            'E' => 13,
            'F' => 13,
            'G' => 2.5,
            'H' => 5.5,
            'I' => 14,
            'J' => 29,
            'K' => 12,
            'L' => 13,
            'M' => 13,
        ];

        foreach ($widths as $column => $width) {
            $sheet->getColumnDimension($column)->setWidth($width);
        }

        $sheet->getSheetView()->setZoomScale(85);
        $sheet->setShowGridlines(false);

        $sheet->getPageSetup()
            ->setOrientation(PageSetup::ORIENTATION_LANDSCAPE)
            ->setPaperSize(PageSetup::PAPERSIZE_A4)
            ->setFitToWidth(1)
            ->setFitToHeight(0);

        $sheet->getPageMargins()
            ->setTop(0.25)
            ->setRight(0.25)
            ->setBottom(0.25)
            ->setLeft(0.25)
            ->setHeader(0.1)
            ->setFooter(0.1);

        $sheet->getHeaderFooter()->setOddFooter('&CPage &P of &N');
        $sheet->getPageSetup()->setHorizontalCentered(true);
        $sheet->freezePane('A1');
    }

    private static function finalizeSheet(Worksheet $sheet, int $lastRow): void
    {
        $sheet->getPageSetup()->setPrintArea('A1:M' . max(1, $lastRow));
        $sheet->getPageSetup()->setScale(null);
        $sheet->getPageSetup()->setFitToPage(true);
    }

    private static function drawPanel(
        Worksheet $sheet,
        array $payments,
        array $group,
        string $requestNo,
        ?string $requestDate,
        int $startCol,
        int $startRow
    ): int {
        $endCol = $startCol + self::PANEL_WIDTH - 1;
        $row = $startRow;

        // Header.
        $sheet->mergeCellsByColumnAndRow($startCol, $row, $endCol, $row);
        $cell = $sheet->getCellByColumnAndRow($startCol, $row);
        $cell->setValue('LIST PAYMENT');
        self::styleTitle($cell);
        $row++;

        $sheet->mergeCellsByColumnAndRow($startCol, $row, $endCol, $row);
        $cell = $sheet->getCellByColumnAndRow($startCol, $row);
        $cell->setValue('PENGAJUAN ' . self::displayDate($requestDate));
        self::styleSubtitle($cell);
        $row++;

        $sheet->mergeCellsByColumnAndRow($startCol, $row, $endCol, $row);
        $cell = $sheet->getCellByColumnAndRow($startCol, $row);
        $cell->setValue('REQUEST : ' . self::text($requestNo));
        self::styleMeta($cell);
        $row++;

        $sheet->mergeCellsByColumnAndRow($startCol, $row, $endCol, $row);
        $cell = $sheet->getCellByColumnAndRow($startCol, $row);
        $cell->setValue('SUBKON : ' . self::text($group['supplier'] ?? '-'));
        self::styleMeta($cell);
        $row += 1;

        // Logo perusahaan pada awal setiap panel.
        $logoPath = public_path('assets/images/logo.png');
        if (is_file($logoPath)) {
            $drawing = new \PhpOffice\PhpSpreadsheet\Worksheet\Drawing();
            $drawing->setName('Logo Newwicker');
            $drawing->setDescription('PT Newwicker Indonesia');
            $drawing->setPath($logoPath);
            $drawing->setHeight(24);
            $drawing->setCoordinates(\PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($startCol) . $startRow);
            $drawing->setOffsetX(2);
            $drawing->setOffsetY(1);
            $drawing->setWorksheet($sheet);
        }

        // Payment list.
        $headers = ['NO', 'PO', 'NO SPK', 'PAYMENT', 'AMOUNT', ''];
        $row = self::writeHeaderRow($sheet, $startCol, $row, $headers);

        foreach ($payments as $index => $payment) {
            $values = [
                $index + 1,
                self::text($payment['no_po'] ?? '-'),
                self::text($payment['spk_no'] ?? '-'),
                self::text($payment['payment_note'] ?? '-'),
                (float) ($payment['payment_amount_effective'] ?? 0),
                '',
            ];

            self::writePaymentRow($sheet, $startCol, $row, $values, $index % 2 === 1);
            $row++;
        }

        $row++;

        foreach ($payments as $payment) {
            $row = self::writeSpkBlock($sheet, $startCol, $row, $payment);
            $row++;
        }

        // Total payment untuk panel.
        $total = array_sum(array_map(
            static fn ($payment) => (float) ($payment['payment_amount_effective'] ?? 0),
            $payments
        ));

        $sheet->mergeCellsByColumnAndRow($startCol, $row, $endCol - 1, $row);
        $label = $sheet->getCellByColumnAndRow($startCol, $row);
        $label->setValue('TOTAL PAYMENT');
        self::styleTotalLabel($label);

        $amount = $sheet->getCellByColumnAndRow($endCol, $row);
        $amount->setValue($total);
        self::styleCurrency($amount, true);
        self::applyBorder($sheet, $startCol, $row, $endCol, $row);

        return $row + 1;
    }

    private static function writeSpkBlock(Worksheet $sheet, int $startCol, int $row, array $payment): int
    {
        $endCol = $startCol + self::PANEL_WIDTH - 1;
        $spkNo = self::text($payment['spk_no'] ?? '-');
        $noPo = self::text($payment['no_po'] ?? '-');

        $sheet->mergeCellsByColumnAndRow($startCol, $row, $endCol, $row);
        $cell = $sheet->getCellByColumnAndRow($startCol, $row);
        $cell->setValue('NO SPK  ' . $spkNo);
        self::styleSpkTitle($cell);
        $sheet->getRowDimension($row)->setRowHeight(20);
        $row++;

        $headers = ['NO', 'NAME', 'PO', 'QTY', 'QTY IN', ''];
        $row = self::writeHeaderRow($sheet, $startCol, $row, $headers);

        $items = is_array($payment['spk_items'] ?? null) ? $payment['spk_items'] : [];

        if (!$items) {
            $values = [1, 'Item detail tidak tersedia', $noPo, 0, 0, ''];
            self::writeItemRow($sheet, $startCol, $row, $values, true);
            $row++;
        } else {
            foreach ($items as $index => $item) {
                $values = [
                    $index + 1,
                    self::text($item['nama'] ?? '-'),
                    $noPo,
                    (float) ($item['qty'] ?? 0),
                    (float) ($item['qty_in'] ?? 0),
                    '',
                ];
                self::writeItemRow($sheet, $startCol, $row, $values, $index % 2 === 1);
                $row++;

                $row = self::writeCombinedTimeline($sheet, $startCol, $row, $item);
                $row++;
            }
        }

        return $row;
    }

    private static function writeCombinedTimeline(Worksheet $sheet, int $startCol, int $row, array $item): int
    {
        $endCol = $startCol + self::PANEL_WIDTH - 1;

        $sheet->mergeCellsByColumnAndRow($startCol, $row, $endCol, $row);
        $title = $sheet->getCellByColumnAndRow($startCol, $row);
        $title->setValue('TIMELINE PEMASUKAN & QC');
        self::styleTimelineTitle($title);
        $title->getStyle()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $title->getStyle()->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $row++;

        // Subtitle sengaja dihilangkan; header tabel langsung di bawah judul.
        $headers = ['NO', 'TANGGAL', 'DESCRIPTION', 'IN', 'PASSED', ''];
        $row = self::writeHeaderRow($sheet, $startCol, $row, $headers, true);

        $timeline = is_array($item['combined_timeline'] ?? null)
            ? $item['combined_timeline']
            : self::combineTimeline($item);

        $totalIn = 0;
        $totalPassed = 0;

        foreach ($timeline as $index => $timelineRow) {
            $in = (float) ($timelineRow['in'] ?? 0);
            $passed = (float) ($timelineRow['passed'] ?? 0);
            $totalIn += $in;
            $totalPassed += $passed;

            $values = [
                $index + 1,
                self::displayDate($timelineRow['date'] ?? ''),
                self::cleanDescription($timelineRow['description'] ?? null),
                $in > 0 ? $in : null,
                $passed > 0 ? $passed : null,
                '',
            ];

            self::writeTimelineRow($sheet, $startCol, $row, $values, $index % 2 === 1);
            $row++;
        }

        $sheet->mergeCellsByColumnAndRow($startCol, $row, $startCol + 3, $row);
        $label = $sheet->getCellByColumnAndRow($startCol, $row);
        $label->setValue('TOTAL');
        self::styleTotalLabel($label);

        $inCell = $sheet->getCellByColumnAndRow($startCol + 3, $row);
        $inCell->setValue($totalIn > 0 ? $totalIn : null);
        self::styleQuantity($inCell, true);

        $passedCell = $sheet->getCellByColumnAndRow($startCol + 4, $row);
        $passedCell->setValue($totalPassed > 0 ? $totalPassed : null);
        self::styleQuantity($passedCell, true);

        self::applyBorder($sheet, $startCol, $row, $endCol, $row);

        return $row + 1;
    }

    private static function writeHeaderRow(Worksheet $sheet, int $startCol, int $row, array $headers, bool $timeline = false): int
    {
        $endCol = $startCol + self::PANEL_WIDTH - 1;

        foreach ($headers as $index => $header) {
            $cell = $sheet->getCellByColumnAndRow($startCol + $index, $row);
            $cell->setValue($header);
            $cell->getStyle()->getFont()->setBold(true)->setSize(9)->getColor()->setARGB('FFFFFFFF');
            $cell->getStyle()->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF1E293B');
            $cell->getStyle()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $cell->getStyle()->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        }

        self::applyBorder($sheet, $startCol, $row, $endCol, $row);
        $sheet->getRowDimension($row)->setRowHeight(20);

        return $row + 1;
    }

    private static function writePaymentRow(Worksheet $sheet, int $startCol, int $row, array $values, bool $zebra): void
    {
        for ($i = 0; $i < self::PANEL_WIDTH; $i++) {
            $cell = $sheet->getCellByColumnAndRow($startCol + $i, $row);
            $cell->setValue($values[$i] ?? '');
            self::styleBodyCell($cell, $zebra);

            if ($i === 4) {
                self::styleCurrency($cell);
            } elseif ($i === 0) {
                $cell->getStyle()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            }
        }

        $sheet->getRowDimension($row)->setRowHeight(22);
        self::applyBorder($sheet, $startCol, $row, $startCol + self::PANEL_WIDTH - 1, $row);
    }

    private static function writeItemRow(Worksheet $sheet, int $startCol, int $row, array $values, bool $zebra): void
    {
        for ($i = 0; $i < self::PANEL_WIDTH; $i++) {
            $cell = $sheet->getCellByColumnAndRow($startCol + $i, $row);
            $cell->setValue($values[$i] ?? '');
            self::styleBodyCell($cell, $zebra);

            if ($i === 3 || $i === 4) {
                self::styleQuantity($cell, $i === 4);
            }

            if ($i === 0) {
                $cell->getStyle()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            }
        }

        // QTY IN highlight.
        $qtyIn = $sheet->getCellByColumnAndRow($startCol + 4, $row);
        $qtyIn->getStyle()->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFEAF7EE');
        $qtyIn->getStyle()->getFont()->setBold(true)->getColor()->setARGB('FF166534');

        $sheet->getRowDimension($row)->setRowHeight(32);
        self::applyBorder($sheet, $startCol, $row, $startCol + self::PANEL_WIDTH - 1, $row);
    }

    private static function writeTimelineRow(Worksheet $sheet, int $startCol, int $row, array $values, bool $zebra): void
    {
        for ($i = 0; $i < self::PANEL_WIDTH; $i++) {
            $cell = $sheet->getCellByColumnAndRow($startCol + $i, $row);
            $cell->setValue($values[$i] ?? '');
            self::styleBodyCell($cell, $zebra, 9);

            if ($i === 0) {
                $cell->getStyle()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            }

            if ($i === 3 || $i === 4) {
                self::styleQuantity($cell);
            }
        }

        $sheet->getRowDimension($row)->setRowHeight(24);
        self::applyBorder($sheet, $startCol, $row, $startCol + self::PANEL_WIDTH - 1, $row);
    }

    private static function splitGroupIntoTwoUp(array $group): array
    {
        $payments = $group['payments'] ?? [];
        $parts = [];
        $current = [[], []];
        $column = 0;
        $height = [0, 0];

        foreach ($payments as $payment) {
            $estimated = self::estimatePaymentRows($payment);

            if (!empty($current[$column]) && $height[$column] + $estimated > self::MAX_PANEL_ROWS) {
                if ($column === 0) {
                    $column = 1;
                } else {
                    $parts[] = $current;
                    $current = [[], []];
                    $height = [0, 0];
                    $column = 0;
                }
            }

            $current[$column][] = $payment;
            $height[$column] += $estimated;
        }

        if (!empty($current[0]) || !empty($current[1])) {
            $parts[] = $current;
        }

        return $parts ?: [[[], []]];
    }

    private static function estimatePaymentRows(array $payment): int
    {
        $rows = 3; // SPK title + table header + spacing.
        $items = is_array($payment['spk_items'] ?? null) ? $payment['spk_items'] : [];

        if (!$items) {
            return $rows + 3;
        }

        foreach ($items as $item) {
            $timeline = is_array($item['combined_timeline'] ?? null)
                ? $item['combined_timeline']
                : self::combineTimeline($item);

            // item + timeline title/subtitle/header + timeline rows + total + spacing
            $rows += 1 + 3 + count($timeline) + 2;
        }

        return $rows + 2;
    }

    private static function estimatePartHeight(array $part): int
    {
        $height = 6;
        foreach ($part as $column) {
            foreach ($column as $payment) {
                $height = max($height, self::estimatePaymentRows($payment) + 6);
            }
        }
        return $height;
    }

    private static function drawVerticalDivider(Worksheet $sheet, int $startRow, int $endRow): void
    {
        $sheet->getColumnDimension('G')->setWidth(2.5);
        for ($row = max(1, $startRow); $row <= $endRow; $row++) {
            $cell = $sheet->getCell('G' . $row);
            $cell->getStyle()->getBorders()->getLeft()->setBorderStyle(Border::BORDER_DASHED)->getColor()->setARGB('FFD1D5DB');
        }
    }

    private static function styleTitle($cell): void
    {
        $cell->getStyle()->getFont()->setBold(true)->setSize(14)->getColor()->setARGB('FF1E293B');
        $cell->getStyle()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $cell->getStyle()->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $cell->getStyle()->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFFFFFFF');
    }

    private static function styleSubtitle($cell): void
    {
        $cell->getStyle()->getFont()->setSize(9)->getColor()->setARGB('FF64748B');
        $cell->getStyle()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    }

    private static function styleMeta($cell): void
    {
        $cell->getStyle()->getFont()->setBold(true)->setSize(9)->getColor()->setARGB('FF334155');
        $cell->getStyle()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
    }

    private static function styleSpkTitle($cell): void
    {
        $cell->getStyle()->getFont()->setBold(true)->setSize(10)->getColor()->setARGB('FFFFFFFF');
        $cell->getStyle()->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF1E293B');
        $cell->getStyle()->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $cell->getStyle()->getAlignment()->setWrapText(true);
    }

    private static function styleTimelineTitle($cell): void
    {
        $cell->getStyle()->getFont()->setBold(true)->setSize(9)->getColor()->setARGB('FF166534');
        $cell->getStyle()->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFEAF7EE');
        $cell->getStyle()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $cell->getStyle()->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
    }

    private static function styleTimelineSubtitle($cell): void
    {
        $cell->getStyle()->getFont()->setItalic(true)->setSize(8)->getColor()->setARGB('FF64748B');
    }

    private static function styleBodyCell($cell, bool $zebra = false, int $fontSize = 9): void
    {
        $cell->getStyle()->getFont()->setSize($fontSize)->getColor()->setARGB('FF334155');
        $cell->getStyle()->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $cell->getStyle()->getAlignment()->setWrapText(true);
        $cell->getStyle()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);

        if ($zebra) {
            $cell->getStyle()->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF8FAFC');
        }
    }

    private static function styleQuantity($cell, bool $bold = false): void
    {
        // Angka quantity ditampilkan tanpa titik desimal untuk nilai bulat.
        $cell->getStyle()->getNumberFormat()->setFormatCode('#,##0');
        $cell->getStyle()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        if ($bold) {
            $cell->getStyle()->getFont()->setBold(true);
        }
    }

    private static function styleCurrency($cell, bool $bold = false): void
    {
        $cell->getStyle()->getNumberFormat()->setFormatCode('"Rp "#,##0');
        $cell->getStyle()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        if ($bold) {
            $cell->getStyle()->getFont()->setBold(true)->getColor()->setARGB('FF166534');
        }
    }

    private static function styleTotalLabel($cell): void
    {
        $cell->getStyle()->getFont()->setBold(true)->setSize(9)->getColor()->setARGB('FF334155');
        $cell->getStyle()->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF1F5F9');
        $cell->getStyle()->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $cell->getStyle()->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
    }

    private static function applyBorder(Worksheet $sheet, int $startCol, int $startRow, int $endCol, int $endRow): void
    {
        $sheet->getStyleByColumnAndRow($startCol, $startRow, $endCol, $endRow)
            ->getBorders()
            ->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN)
            ->getColor()
            ->setARGB('FFD1D5DB');
    }

    private static function loadData(int $draftId): array
    {
        $draft = PaymentRequestSaved::findOrFail($draftId);

        $requestIds = is_array($draft->payment_request_ids)
            ? $draft->payment_request_ids
            : (json_decode((string) $draft->payment_request_ids, true) ?: []);

        $requests = PaymentRequest::with('spk')
            ->whereIn('id', $requestIds)
            ->get();

        $payments = $requests->map(function ($request) {
            if (!$request->spk) {
                return [
                    'supplier' => '-',
                    'spk_id' => $request->spk_id,
                    'no_po' => '-',
                    'spk_no' => '-',
                    'payment_note' => '-',
                    'payment_id' => $request->payment_id,
                    'adjustment' => 0,
                    'payment_amount' => 0,
                    'payment_amount_effective' => 0,
                    'spk_items' => [],
                ];
            }

            $spkData = is_string($request->spk->data)
                ? json_decode($request->spk->data, true)
                : ($request->spk->data ?? []);

            $spkData = is_array($spkData) ? $spkData : [];

            $payment = collect($spkData['payments'] ?? [])
                ->firstWhere('payment_id', $request->payment_id);

            $payment = is_array($payment) ? $payment : [];

            $items = self::buildItems($request->spk_id, $spkData['items'] ?? []);
            $adjustment = $payment['adjustment'] ?? 0;
            $amount = (float) ($payment['amount'] ?? 0);
            $effective = (
                $adjustment !== null
                && $adjustment !== ''
                && is_numeric($adjustment)
                && (float) $adjustment != 0
            ) ? (float) $adjustment : $amount;

            return [
                'supplier' => $spkData['sup'] ?? '-',
                'spk_id' => $request->spk_id,
                'no_po' => $spkData['no_po'] ?? '-',
                'spk_no' => $spkData['no_spk'] ?? '-',
                'payment_note' => $payment['note'] ?? '-',
                'payment_id' => $request->payment_id,
                'adjustment' => $adjustment,
                'payment_amount' => $amount,
                'payment_amount_effective' => $effective,
                'spk_items' => $items,
            ];
        })->values()->all();

        $groups = [];
        foreach ($payments as $payment) {
            $supplier = self::text($payment['supplier'] ?? '-');
            if (!isset($groups[$supplier])) {
                $groups[$supplier] = [
                    'supplier' => $supplier,
                    'payments' => [],
                ];
            }
            $groups[$supplier]['payments'][] = $payment;
        }

        return [
            'request_no' => (string) ($draft->request_no ?? '-'),
            'request_date' => $draft->request_date,
            'groups' => array_values($groups),
        ];
    }

    private static function buildItems($spkId, array $rawItems): array
    {
        return collect($rawItems)
            ->filter(static fn ($value) => is_array($value))
            ->map(function (array $item) use ($spkId) {
                $detailPoId = $item['detail_po_id'] ?? $item['detail_id'] ?? null;
                $qtySpk = (float) ($item['qty'] ?? 0);

                $timeline = $detailPoId !== null
                    ? ProductionTimeline::query()
                        ->where('spk_id', $spkId)
                        ->where('detail_po_id', $detailPoId)
                        ->orderBy('id')
                        ->get()
                    : collect();

                $inspect = $detailPoId !== null
                    ? InspectSchedule::query()
                        ->where('spk_id', $spkId)
                        ->where('detail_po_id', $detailPoId)
                        ->orderBy('batch')
                        ->orderBy('tanggal_inspect')
                        ->orderBy('id')
                        ->get()
                    : collect();

                $customColumns = $item['custom_columns'] ?? [];
                if (is_string($customColumns)) {
                    $customColumns = json_decode($customColumns, true) ?? [];
                }
                $customColumns = is_array($customColumns) ? $customColumns : [];

                $components = [];
                foreach ($customColumns as $componentIndex => $component) {
                    if (!is_array($component)) {
                        continue;
                    }

                    $componentName = self::componentName($component, $componentIndex);
                    $componentQtySpk = isset($component['pcs']) && $component['pcs'] !== '' && is_numeric($component['pcs'])
                        ? (float) $component['pcs']
                        : $qtySpk;

                    $componentQtyIn = 0;
                    foreach ($timeline as $timelineRow) {
                        $type = strtolower(trim((string) ($timelineRow->type ?? '')));
                        if ($type !== 'in' && $type !== 'service_masuk') {
                            continue;
                        }

                        $qty = (float) ($timelineRow->qty ?? 0);
                        if ($qty == 0) {
                            continue;
                        }

                        $remark = self::normalizeText($timelineRow->remark ?? '');
                        $target = self::normalizeText($componentName);

                        if ($remark === '') {
                            if ((int) $componentIndex === 0) {
                                $componentQtyIn += $qty;
                            }
                        } elseif ($remark === $target) {
                            $componentQtyIn += $qty;
                        }
                    }

                    $components[] = [
                        'name' => $componentName,
                        'qty_spk' => $componentQtySpk,
                        'qty_in' => $componentQtyIn,
                    ];
                }

                $balancedQtyIn = self::balancedQtyIn($qtySpk, $components, $timeline);

                $productionRows = $timeline->map(function ($row) {
                    return [
                        'type' => $row->type,
                        'qty' => (float) ($row->qty ?? 0),
                        'remark' => $row->remark,
                        'date' => $row->date,
                    ];
                })->all();

                $inspectRows = $inspect->map(function ($row) {
                    return [
                        'tanggal_inspect' => $row->tanggal_inspect,
                        'passed' => (float) ($row->passed ?? 0),
                    ];
                })->all();

                $combined = self::combineTimeline([
                    'production_timeline' => $productionRows,
                    'inspect_timeline' => $inspectRows,
                ]);

                return [
                    'kode' => $item['kode'] ?? '-',
                    'nama' => $item['nama'] ?? '-',
                    'qty' => $qtySpk,
                    'qty_in' => $balancedQtyIn,
                    'satuan' => $item['satuan'] ?? $item['sat'] ?? 'pcs',
                    'detail_po_id' => $detailPoId,
                    'custom_columns' => $customColumns,
                    'components' => $components,
                    'production_timeline' => $productionRows,
                    'inspect_timeline' => $inspectRows,
                    'combined_timeline' => $combined,
                ];
            })
            ->values()
            ->all();
    }

    private static function balancedQtyIn(float $qtySpk, array $components, $timeline): float
    {
        if ($qtySpk <= 0) {
            return 0;
        }

        if ($components) {
            $progress = [];
            foreach ($components as $component) {
                $qty = (float) ($component['qty_spk'] ?? 0);
                $in = (float) ($component['qty_in'] ?? 0);
                if ($qty > 0) {
                    $progress[] = max(0, $in / $qty);
                }
            }

            if ($progress) {
                return min($qtySpk, $qtySpk * min($progress));
            }
        }

        $total = 0;
        foreach ($timeline as $row) {
            $type = strtolower(trim((string) ($row->type ?? '')));
            if ($type === 'in' || $type === 'service_masuk') {
                $total += (float) ($row->qty ?? 0);
            }
        }

        return min($qtySpk, $total);
    }

    private static function combineTimeline(array $item): array
    {
        $map = [];

        foreach (($item['production_timeline'] ?? []) as $row) {
            $type = strtolower(trim((string) ($row['type'] ?? '')));
            if ($type !== 'in' && $type !== 'service_masuk') {
                continue;
            }

            $date = self::normalizeDate($row['date'] ?? '');
            $key = $date !== '' ? $date : '__NO_DATE__';
            if (!isset($map[$key])) {
                $map[$key] = [
                    'date' => $date,
                    'description' => [],
                    'in' => 0,
                    'passed' => 0,
                ];
            }

            $remark = trim((string) ($row['remark'] ?? ''));
            if ($remark !== '') {
                $map[$key]['description'][] = $remark;
            }

            $map[$key]['in'] += (float) ($row['qty'] ?? 0);
        }

        foreach (($item['inspect_timeline'] ?? []) as $row) {
            $date = self::normalizeDate($row['tanggal_inspect'] ?? '');
            $key = $date !== '' ? $date : '__NO_DATE__';
            if (!isset($map[$key])) {
                $map[$key] = [
                    'date' => $date,
                    'description' => [],
                    'in' => 0,
                    'passed' => 0,
                ];
            }

            $map[$key]['description'][] = 'QC Inspect';
            $map[$key]['passed'] += (float) ($row['passed'] ?? 0);
        }

        uasort($map, static function ($a, $b) {
            $ad = $a['date'] ?: '9999-12-31';
            $bd = $b['date'] ?: '9999-12-31';
            return strcmp($ad, $bd);
        });

        return array_values(array_map(static function ($row) {
            $descriptions = array_values(array_unique(array_filter(array_map('trim', $row['description']))));
            if (!$descriptions) {
                $descriptions[] = $row['passed'] > 0 ? 'QC Inspect' : 'Barang masuk';
            }

            return [
                'date' => $row['date'],
                'description' => implode(', ', $descriptions),
                'in' => $row['in'],
                'passed' => $row['passed'],
            ];
        }, $map));
    }

    private static function componentName(array $component, int $index): string
    {
        $preferred = [
            'nama', 'name', 'nama_material', 'nama_bahan', 'bahan',
            'triplek', 'finishing', 'komponen', 'component', 'description',
        ];

        foreach ($preferred as $key) {
            $value = $component[$key] ?? null;
            if (is_string($value) && trim($value) !== '' && !in_array(strtolower(trim($value)), ['-', 'null', 'undefined', 'n/a', 'na'], true)) {
                return trim($value);
            }
        }

        $technical = ['harga', 'material', 'pcs', 'set', 'total', 'p', 'l', 't', 'qty', 'kode', 'id'];
        foreach ($component as $key => $value) {
            if (!is_string($value) || trim($value) === '') {
                continue;
            }
            if (in_array(strtolower((string) $key), $technical, true)) {
                continue;
            }
            if (in_array(strtolower(trim($value)), ['-', 'null', 'undefined', 'n/a', 'na'], true)) {
                continue;
            }
            return trim($value);
        }

        return 'Komponen ' . ($index + 1);
    }

    private static function cleanDescription($value): string
    {
        if ($value === null) {
            return '-';
        }

        $value = trim((string) $value);
        if ($value === '' || strtolower($value) === 'null' || strtolower($value) === 'undefined') {
            return '-';
        }

        // Bersihkan kata null jika ikut tersimpan di dalam description.
        $value = preg_replace('/\bnull\b/i', '', $value);
        $value = trim(preg_replace('/\s*,\s*,+/', ', ', $value), " \t\n\r\0\x0B,");

        return $value === '' ? '-' : $value;
    }

    private static function normalizeText($value): string
    {
        return trim(preg_replace('/\s+/', ' ', preg_replace('/[^a-z0-9]+/i', ' ', strtolower((string) ($value ?? '')))));
    }

    private static function normalizeDate($value): string
    {
        if ($value instanceof Carbon) {
            return $value->format('Y-m-d');
        }

        $raw = trim((string) $value);
        if ($raw === '') {
            return '';
        }

        try {
            return Carbon::parse($raw)->format('Y-m-d');
        } catch (\Throwable $e) {
            if (preg_match('/^(\d{1,2})[\/-](\d{1,2})[\/-](\d{4})$/', $raw, $m)) {
                return sprintf('%04d-%02d-%02d', $m[3], $m[2], $m[1]);
            }
            return $raw;
        }
    }

    private static function displayDate($value): string
    {
        $normalized = self::normalizeDate($value);
        if ($normalized === '') {
            return '-';
        }

        try {
            return Carbon::parse($normalized)->format('d/m/Y');
        } catch (\Throwable $e) {
            return (string) $value;
        }
    }

    private static function text($value): string
    {
        $text = trim((string) ($value ?? ''));
        return $text === '' ? '-' : $text;
    }
}
