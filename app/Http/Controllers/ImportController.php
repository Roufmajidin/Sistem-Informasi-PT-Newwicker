<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;

class ImportController extends Controller
{
    public function index()
    {
        return view('pages.pfi.index');
    }
    public function preview(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls',
        ]);

        $spreadsheet = IOFactory::load($request->file('file')->getPathname());
        $sheet       = $spreadsheet->getActiveSheet();

        // ===============================
        // AMBIL SEMUA ROW
        // ===============================
        $rows = [];
        foreach ($sheet->toArray(null, true, true, true) as $row) {
            $rows[] = $row;
        }

        // ===============================
        // COMPANY PROFILE (RAPIH, NO ::)
        // ===============================
        $companyProfile = [
            "orderNo"       => $this->clean($rows[4]['C'] ?? null),
            "companyName"   => $this->clean($rows[5]['C'] ?? null),
            "country"       => $this->clean($rows[6]['C'] ?? null),
            "shipmentDate"  => $this->clean($rows[7]['C'] ?? null),
            "packing"       => $this->clean($rows[8]['C'] ?? null),
            "contactPerson" => $this->clean($rows[10]['C'] ?? null),

            "line1"         => "PT. NEWWICKER INDONESIA",
            "line2"         => "Jalan Kisaba Lanang RT/RW 019/002 Bode Lor, Plumbon Cirebon 45155 - Indonesia",
            "line3"         => "office@newwicker.com / info@newwicker.com | www.newwicker.com",
        ];

        // ===============================
        // ITEMS
        // ===============================
        $items    = [];
        $startRow = 13; // baris item pertama (sesuaikan)

        for ($i = $startRow; $i <= count($rows); $i++) {
   $colA = strtolower(trim($rows[$i]['A'] ?? ''));
    $colC = trim($rows[$i]['C'] ?? '');

    // ⛔ STOP TOTAL jika masuk area PAYMENT / BANK
    if (
        str_starts_with($colA, 'payment') ||
        str_starts_with($colA, 'name of bank') ||
        str_starts_with($colA, 'address of bank') ||
        str_starts_with($colA, 'swift code') ||
        str_starts_with($colA, 'account name') ||
        str_starts_with($colA, 'account number')
    ) {
        break;
    }

    // Lewati baris kosong
    if ($colC === '') {
        continue;
    }
            $items[] = [
                "No."                => (string) $rows[$i]['A'],
                "Photo"              => null,
                "Description"        => $rows[$i]['C'],
                "Article Nr."        => $rows[$i]['D'],
                "Sub Category"       => $rows[$i]['E'],
                "Remark"             => $rows[$i]['F'],
                "Cushion"            => $rows[$i]['G'],
                "Glass"              => $rows[$i]['H'],

                "Item"               => [
                    "W" => $this->calc($rows[$i]['I']),
                    "D" => $this->calc($rows[$i]['J']),
                    "H" => $this->calc($rows[$i]['K']),
                ],

                "Packing"            => [
                    "W" => $this->calc($rows[$i]['L']),
                    "D" => $this->calc($rows[$i]['M']),
                    "H" => $this->calc($rows[$i]['N']),
                ],

                "Composition"        => $rows[$i]['O'],
                "Finishing"          => $rows[$i]['P'],
                "QTY"                => $this->calc($rows[$i]['Q']),
                "CBM"                => $this->calc($rows[$i]['R']),
                "FOB JAKARTA IN USD" => $this->number($rows[$i]['S']),
                "Total CBM"          => $this->calc($rows[$i]['T']),
                "Value in USD"       => $this->calc($rows[$i]['U']),

                "col21"              => $this->calc($rows[$i]['V']),
                "col22"              => $this->calc($rows[$i]['W']),
                "col23"              => $this->calc($rows[$i]['X']),
                "col24"              => $rows[$i]['Y'],
                "col25"              => $rows[$i]['Z'],
            ];
        }

        // ===============================
        // RETURN JSON
        // ===============================
        return response()->json([
            "CompanyProfile" => $companyProfile,
            "Items"          => $items,
        ], 200);
    }

    // =====================================================
    // HELPERS
    // =====================================================

    private function clean($value)
    {
        if (! $value) {
            return null;
        }

        return trim(str_replace(':', '', $value));
    }

    private function number($value)
    {
        return is_numeric($value) ? (float) $value : null;
    }

    /**
     * Hitung rumus sederhana:
     * =146/2.54  ✔
     * =10+2      ✔
     * =V19*W19   ❌ (return null)
     */
    private function calc($value)
    {
        if (! $value) {
            return null;
        }

        if (is_numeric($value)) {
            return round((float) $value, 2);
        }

        if (! is_string($value) || ! str_starts_with($value, '=')) {
            return null;
        }

        $expr = substr($value, 1);

        // jika ada huruf → referensi cell → skip
        if (preg_match('/[A-Za-z]/', $expr)) {
            return null;
        }

        // valid operator
        if (! preg_match('/^[0-9\.\+\-\*\/\(\)\s]+$/', $expr)) {
            return null;
        }

        try {
            $result = eval("return {$expr};");
            return is_numeric($result) ? round($result, 2) : null;
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function process()
    {
        $rows = session('excel_preview');

        if (! $rows) {
            return response()->json(['error' => 'Session expired'], 422);
        }

        // ================= FIND HEADER =================
        $headerIndex = null;

        foreach ($rows as $i => $row) {
            $rowText = strtolower(implode(' ', $row));
            if (str_contains($rowText, 'no.') && str_contains($rowText, 'description')) {
                $headerIndex = $i;
                break;
            }
        }

        if ($headerIndex === null) {
            return response()->json(['error' => 'Header tidak ditemukan'], 422);
        }

        $headerMain = $rows[$headerIndex];
        $headerSub  = $rows[$headerIndex + 1] ?? [];

        // ================= NORMALIZE HEADER =================
        $headers = [];

        foreach ($headerMain as $i => $main) {
            $main = strtolower(trim(preg_replace('/\s+/', ' ', str_replace("\n", ' ', $main ?? ''))));
            $sub  = strtolower(trim($headerSub[$i] ?? ''));

            if ($main && $sub) {
                $headers[$i] = $main . ' ' . $sub;
            } elseif ($main) {
                $headers[$i] = $main;
            } else {
                $headers[$i] = null;
            }
        }

        // ================= MAP KOLOM (FIX MERGE) =================
        $map = [];

        foreach ($headers as $i => $h) {
            if (! $h) {
                continue;
            }

            if ($h === 'no.') {
                $map['no'] = $i;
            }

            if ($h === 'description') {
                $map['description'] = $i;
            }

            if (str_contains($h, 'article')) {
                $map['article_nr'] = $i;
            }

            // 🔥 ITEM DIMENSION (MERGED HEADER)
            if (str_contains($h, 'item dimention')) {
                $map['item_w'] = $i;
                $map['item_d'] = $i + 1;
                $map['item_h'] = $i + 2;
            }

            // 🔥 PACKING DIMENSION
            if (str_contains($h, 'packing dimention')) {
                $map['pack_w'] = $i;
                $map['pack_d'] = $i + 1;
                $map['pack_h'] = $i + 2;
            }

            if ($h === 'composition') {
                $map['composition'] = $i;
            }

            if ($h === 'finishing') {
                $map['finishing'] = $i;
            }

            if ($h === 'qty') {
                $map['qty'] = $i;
            }

            if ($h === 'cbm') {
                $map['cbm'] = $i;
            }

            if (str_contains($h, 'fob')) {
                $map['fob'] = $i;
            }

            if (str_contains($h, 'total cbm')) {
                $map['total_cbm'] = $i;
            }

            if (str_contains($h, 'value')) {
                $map['value'] = $i;
            }

        }

        // ================= VALIDATION =================
        foreach (['item_w', 'item_d', 'item_h'] as $k) {
            if (! isset($map[$k])) {
                return response()->json([
                    'error'   => 'Header item dimension tidak lengkap',
                    'missing' => $k,
                    'map'     => $map,
                    'headers' => $headers,
                ], 422);
            }
        }

        // ================= READ DATA =================
        $data = [];

        for ($i = $headerIndex + 2; $i < count($rows); $i++) {
            $row = $rows[$i];

            if (empty($row[$map['no']] ?? null)) {
                continue;
            }

            $data[] = [
                'no'                     => (int) $row[$map['no']],
                'description'            => $row[$map['description']] ?? null,
                'article_nr'             => $row[$map['article_nr']] ?? null,

                'item_dimension_inch'    => [
                    'w' => (float) ($row[$map['item_w']] ?? 0),
                    'd' => (float) ($row[$map['item_d']] ?? 0),
                    'h' => (float) ($row[$map['item_h']] ?? 0),
                ],

                'packing_dimension_inch' => [
                    'w' => (float) ($row[$map['pack_w']] ?? 0),
                    'd' => (float) ($row[$map['pack_d']] ?? 0),
                    'h' => (float) ($row[$map['pack_h']] ?? 0),
                ],
            ];
        }
        dd($data);
        // return response()->json($data, JSON_PRETTY_PRINT);
    }
}
