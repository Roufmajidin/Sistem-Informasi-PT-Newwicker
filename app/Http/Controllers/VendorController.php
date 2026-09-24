<?php

namespace App\Http\Controllers;

use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class VendorController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | INDEX
    |--------------------------------------------------------------------------
    */

    public function index()
    {
        /*
         * ID ASC
         *
         * Vendor lama di atas
         * Vendor baru di bawah
         */
        $vendors = Vendor::orderBy('id', 'asc')
            ->paginate(20);

        return view('pages.vendor.index', compact('vendors'));
    }


    /*
    |--------------------------------------------------------------------------
    | STORE SINGLE
    |--------------------------------------------------------------------------
    */

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_vendor'     => 'required|string|max:255',
            'alamat'          => 'nullable|string|max:255',
            'nomor_rekening'  => 'nullable|string|max:100',
            'nama_rekening'   => 'nullable|string|max:255',
            'bank'            => 'nullable|string|max:100',
            'npwp'             => 'nullable|string|max:100',
            'vendor_type'     => 'nullable|string|max:100',
            'vendor_type2'    => 'nullable|string|max:100',
        ]);


        DB::beginTransaction();

        try {

            /*
             * Generate uniq otomatis
             */
            $uniq = $this->generateVendorUniq(
                $validated['nama_vendor'],
                $validated['nomor_rekening'] ?? '',
                $validated['bank'] ?? ''
            );


            $vendor = Vendor::create([
                'nama_vendor'    => $validated['nama_vendor'],
                'uniq'           => $uniq,
                'alamat'         => $validated['alamat'] ?? null,
                'nomor_rekening' => $validated['nomor_rekening'] ?? null,
                'nama_rekening'  => $validated['nama_rekening'] ?? null,
                'bank'           => $validated['bank'] ?? null,
                'npwp'           => $validated['npwp'] ?? null,
                'vendor_type'    => $validated['vendor_type'] ?? null,
                'vendor_type2'   => $validated['vendor_type2'] ?? null,
            ]);


            DB::commit();


            return response()->json([
                'success' => true,
                'message' => 'Vendor berhasil ditambahkan.',
                'data'    => $vendor,
            ]);

        } catch (\Throwable $e) {

            DB::rollBack();

            Log::error('Vendor store error', [
                'message' => $e->getMessage(),
                'line'    => $e->getLine(),
                'file'    => $e->getFile(),
            ]);


            return response()->json([
                'success' => false,
                'message' => 'Vendor gagal ditambahkan.',
            ], 500);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE
    |--------------------------------------------------------------------------
    */

    public function update(Request $request, $id)
    {
        $vendor = Vendor::findOrFail($id);


        $validated = $request->validate([
            'nama_vendor'     => 'required|string|max:255',
            'alamat'          => 'nullable|string|max:255',
            'nomor_rekening'  => 'nullable|string|max:100',
            'nama_rekening'   => 'nullable|string|max:255',
            'bank'            => 'nullable|string|max:100',
            'npwp'             => 'nullable|string|max:100',
            'vendor_type'     => 'nullable|string|max:100',
            'vendor_type2'    => 'nullable|string|max:100',
        ]);


        DB::beginTransaction();

        try {

            /*
             * Generate ulang uniq.
             *
             * Vendor yang sedang diedit dikecualikan
             * dari pengecekan duplicate.
             */
            $uniq = $this->generateVendorUniq(
                $validated['nama_vendor'],
                $validated['nomor_rekening'] ?? '',
                $validated['bank'] ?? '',
                $vendor->id
            );


            $vendor->update([
                'nama_vendor'    => $validated['nama_vendor'],
                'uniq'           => $uniq,
                'alamat'         => $validated['alamat'] ?? null,
                'nomor_rekening' => $validated['nomor_rekening'] ?? null,
                'nama_rekening'  => $validated['nama_rekening'] ?? null,
                'bank'           => $validated['bank'] ?? null,
                'npwp'           => $validated['npwp'] ?? null,
                'vendor_type'    => $validated['vendor_type'] ?? null,
                'vendor_type2'   => $validated['vendor_type2'] ?? null,
            ]);


            DB::commit();


            return response()->json([
                'success' => true,
                'message' => 'Vendor berhasil diperbarui.',
                'data'    => $vendor->fresh(),
            ]);

        } catch (\Throwable $e) {

            DB::rollBack();

            Log::error('Vendor update error', [
                'vendor_id' => $id,
                'message'   => $e->getMessage(),
                'line'      => $e->getLine(),
                'file'      => $e->getFile(),
            ]);


            return response()->json([
                'success' => false,
                'message' => 'Vendor gagal diperbarui.',
            ], 500);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | DELETE
    |--------------------------------------------------------------------------
    */

    public function destroy($id)
    {
        try {

            $vendor = Vendor::findOrFail($id);

            $vendor->delete();


            return response()->json([
                'success' => true,
                'message' => 'Vendor berhasil dihapus.',
            ]);

        } catch (\Throwable $e) {

            Log::error('Vendor delete error', [
                'vendor_id' => $id,
                'message'   => $e->getMessage(),
            ]);


            return response()->json([
                'success' => false,
                'message' => 'Vendor gagal dihapus.',
            ], 500);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | MASS STORE
    |--------------------------------------------------------------------------
    */

    public function massStore(Request $request)
    {
        $request->validate([
            'data' => 'required|string',
        ]);


        $raw = $request->input('data');


        /*
         * Parse semua baris
         */
        $rows = $this->parseMassVendorData($raw);


        if (empty($rows)) {

            return response()->json([
                'success' => false,
                'message' => 'Tidak ada data vendor yang valid.',
            ], 422);
        }


        DB::beginTransaction();

        try {

            /*
             * Ambil semua uniq yang sudah digunakan.
             *
             * Ini supaya mass upload tidak menghasilkan
             * duplicate uniq.
             */
            $usedUniq = Vendor::whereNotNull('uniq')
                ->pluck('uniq')
                ->map(function ($value) {
                    return strtoupper(trim($value));
                })
                ->flip()
                ->toArray();


            $inserted = 0;


            foreach ($rows as $row) {

                $namaVendor = $row[0] ?? '';

                /*
                 * Nama vendor wajib ada
                 */
                if (trim($namaVendor) === '') {
                    continue;
                }


                $nomorRekening = $row[2] ?? '';

                $bank = $row[4] ?? '';


                /*
                 * Generate uniq
                 */
                $uniq = $this->generateVendorUniq(
                    $namaVendor,
                    $nomorRekening,
                    $bank,
                    null,
                    $usedUniq
                );


                /*
                 * Tandai langsung supaya row berikutnya
                 * tidak mendapatkan uniq yang sama.
                 */
                $usedUniq[strtoupper($uniq)] = true;


                Vendor::create([
                    'nama_vendor'    => $namaVendor,
                    'uniq'           => $uniq,
                    'alamat'         => $row[1] ?? null,
                    'nomor_rekening' => $nomorRekening ?: null,
                    'nama_rekening'  => $row[3] ?? null,
                    'bank'           => $bank ?: null,
                    'npwp'           => $row[5] ?? null,
                    'vendor_type'    => $row[6] ?? null,
                    'vendor_type2'   => $row[7] ?? null,
                ]);


                $inserted++;
            }


            DB::commit();


            return response()->json([
                'success' => true,
                'message' => $inserted . ' vendor berhasil diimport.',
                'inserted' => $inserted,
            ]);

        } catch (\Throwable $e) {

            DB::rollBack();


            Log::error('Vendor mass store error', [
                'message' => $e->getMessage(),
                'line'    => $e->getLine(),
                'file'    => $e->getFile(),
            ]);


            return response()->json([
                'success' => false,
                'message' => 'Mass import gagal: ' . $e->getMessage(),
            ], 500);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | GENERATE VENDOR UNIQ
    |--------------------------------------------------------------------------
    |
    | Contoh:
    |
    | HENDRIK JEPARA
    | 589301004032532
    | BRI
    |
    | HJ-589-BRI
    |
    |--------------------------------------------------------------------------
    */

    private function generateVendorUniq(
        $namaVendor,
        $nomorRekening,
        $bank,
        $excludeId = null,
        &$usedUniq = null
    ) {

        /*
         * ================================
         * 1. Ambil inisial nama
         * ================================
         */

        $namaVendor = trim(
            preg_replace('/\s+/', ' ', $namaVendor)
        );


        $words = preg_split(
            '/\s+/',
            strtoupper($namaVendor)
        );


        $initials = '';


        foreach ($words as $word) {

            /*
             * Bersihkan karakter non huruf/angka
             */
            $word = preg_replace(
                '/[^A-Z0-9]/',
                '',
                $word
            );


            if ($word === '') {
                continue;
            }


            $initials .= substr($word, 0, 1);
        }


        /*
         * Kalau tidak ada nama
         */
        if ($initials === '') {
            $initials = 'V';
        }


        /*
         * ================================
         * 2. Ambil 3 digit rekening pertama
         * ================================
         */

        $rekening = preg_replace(
            '/[^0-9]/',
            '',
            (string) $nomorRekening
        );


        if ($rekening !== '') {

            $rekening3 = substr(
                str_pad($rekening, 3, '0'),
                0,
                3
            );

        } else {

            /*
             * Kalau rekening kosong
             */
            $rekening3 = '000';

        }


        /*
         * ================================
         * 3. Bank
         * ================================
         */

        $bank = strtoupper(
            trim((string) $bank)
        );


        $bank = preg_replace(
            '/[^A-Z0-9]/',
            '',
            $bank
        );


        if ($bank === '') {
            $bank = 'BANK';
        }


        /*
         * ================================
         * 4. Base uniq
         * ================================
         */

        $baseUniq =
            $initials .
            '-' .
            $rekening3 .
            '-' .
            $bank;


        $uniq = $baseUniq;


        /*
         * ================================
         * 5. Cek duplicate
         * ================================
         */

        $counter = 1;


        while (
            $this->vendorUniqExists(
                $uniq,
                $excludeId,
                $usedUniq
            )
        ) {

            $counter++;

            $uniq =
                $baseUniq .
                '-' .
                $counter;
        }


        return $uniq;
    }


    /*
    |--------------------------------------------------------------------------
    | CHECK UNIQUE
    |--------------------------------------------------------------------------
    */

    private function vendorUniqExists(
        $uniq,
        $excludeId = null,
        $usedUniq = null
    ) {

        /*
         * Untuk mass import:
         * cek array memory terlebih dahulu.
         */
        if (
            is_array($usedUniq) &&
            isset($usedUniq[strtoupper($uniq)])
        ) {

            return true;
        }


        /*
         * Cek database
         */
        $query = Vendor::where(
            'uniq',
            $uniq
        );


        /*
         * Saat edit, jangan anggap record sendiri
         * sebagai duplicate.
         */
        if ($excludeId !== null) {

            $query->where(
                'id',
                '!=',
                $excludeId
            );
        }


        return $query->exists();
    }


    /*
    |--------------------------------------------------------------------------
    | PARSE MASS DATA
    |--------------------------------------------------------------------------
    */

    private function parseMassVendorData($raw)
    {
        $raw = str_replace(
            ["\r\n", "\r"],
            "\n",
            $raw
        );


        $lines = explode(
            "\n",
            $raw
        );


        $result = [];


        foreach ($lines as $line) {

            $line = trim($line);


            if ($line === '') {
                continue;
            }


            /*
             * ==========================================
             * Excel / Google Sheets
             * ==========================================
             */

            if (str_contains($line, "\t")) {

                $columns = explode(
                    "\t",
                    $line
                );

            } else {

                /*
                 * ==========================================
                 * Format pipe
                 * ==========================================
                 */

                $line = trim($line);


                if (
                    str_starts_with($line, '|') &&
                    str_ends_with($line, '|')
                ) {

                    $line = substr(
                        $line,
                        1,
                        -1
                    );
                }


                $columns = explode(
                    '|',
                    $line
                );
            }


            /*
             * Trim semua column
             */
            $columns = array_map(
                function ($value) {

                    $value = trim($value);


                    if ($value === '-') {
                        return '';
                    }


                    return $value;
                },
                $columns
            );


            /*
             * Skip markdown separator:
             *
             * | -------- | ------ |
             */
            if (
                !empty($columns) &&
                collect($columns)->every(function ($value) {

                    return $value === '' ||
                        preg_match(
                            '/^-+$/',
                            $value
                        );

                })
            ) {

                continue;
            }


            /*
             * Skip header
             */
            $first = strtolower(
                preg_replace(
                    '/[\s_]+/',
                    '',
                    $columns[0] ?? ''
                )
            );


            if (
                in_array(
                    $first,
                    [
                        'namavendor',
                        'nama',
                        'vendor'
                    ]
                )
            ) {

                continue;
            }


            /*
             * Pastikan minimal 8 kolom
             */
            while (count($columns) < 8) {

                $columns[] = '';
            }


            /*
             * Kalau lebih dari 8 kolom,
             * gabungkan ke kolom terakhir.
             */
            if (count($columns) > 8) {

                $columns = [
                    $columns[0],
                    $columns[1],
                    $columns[2],
                    $columns[3],
                    $columns[4],
                    $columns[5],
                    $columns[6],
                    implode(
                        ' | ',
                        array_slice($columns, 7)
                    ),
                ];
            }


            /*
             * Nama vendor wajib ada
             */
            if (
                trim($columns[0]) === ''
            ) {

                continue;
            }


            $result[] = $columns;
        }


        return $result;
    }
}