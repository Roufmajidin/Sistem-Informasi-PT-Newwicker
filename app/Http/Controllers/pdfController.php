<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Smalot\PdfParser\Parser;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ArrayExport;
use Illuminate\Support\Facades\Http;

class pdfController extends Controller
{
    /**
     * Display a listing of the resource.
     */
     public function showForm()
    {
        return view('pdf.form');
    }


       public function convert(Request $request)
    {
        $request->validate([
            'pdf' => 'required|mimes:pdf|max:20480', // max 20MB
        ]);

        $file = $request->file('pdf');
        $filename = $file->getClientOriginalName();

        $apiKey = env('PDFCO_API_KEY'); // simpan di .env

        // 1. Get presigned URL
        $presignRes = Http::withHeaders([
            'x-api-key' => $apiKey
        ])->get("https://api.pdf.co/v1/file/upload/get-presigned-url", [
            'name' => $filename,
            'contenttype' => 'application/octet-stream'
        ]);

        if ($presignRes->failed()) {
            return response()->json([
                'error' => true,
                'message' => 'Gagal request ke PDF.co (presign)',
                'details' => $presignRes->body()
            ], $presignRes->status());
        }

        $presignData = $presignRes->json();
        $uploadUrl = $presignData['presignedUrl'] ?? null;
        $uploadedFileUrl = $presignData['url'] ?? null;

        if (!$uploadUrl || !$uploadedFileUrl) {
            return response()->json([
                'error' => true,
                'message' => 'Presigned URL tidak ditemukan.',
                'data' => $presignData
            ]);
        }

        // 2. Upload file ke presigned URL
        $uploadRes = Http::withBody(file_get_contents($file->getPathname()), 'application/octet-stream')
            ->put($uploadUrl);

        if ($uploadRes->failed()) {
            return response()->json([
                'error' => true,
                'message' => 'Gagal upload file ke PDF.co',
                'details' => $uploadRes->body()
            ], $uploadRes->status());
        }

        // 3. Konversi file PDF ke Excel (XLSX)
        $conversionPayload = [
            'url' => $uploadedFileUrl,
            'pages' => '0-', // semua halaman
            'async' => false,
            'name' => 'converted.xlsx'
        ];

        $convertRes = Http::withHeaders([
            'x-api-key' => $apiKey,
            'Content-Type' => 'application/json'
        ])->post('https://api.pdf.co/v1/pdf/convert/to/xlsx', $conversionPayload);

        if ($convertRes->failed()) {
            return response()->json([
                'error' => true,
                'message' => 'Gagal konversi PDF ke Excel',
                'details' => $convertRes->body()
            ], $convertRes->status());
        }

        $resultData = $convertRes->json();

        // Tangani karakter UTF-8 yang rusak
        $safeJson = json_encode($this->utf8ize($resultData), JSON_INVALID_UTF8_SUBSTITUTE);

        return response($safeJson, 200)->header('Content-Type', 'application/json');
    }

    private function utf8ize($mixed)
    {
        if (is_array($mixed)) {
            foreach ($mixed as $key => $value) {
                $mixed[$key] = $this->utf8ize($value);
            }
        } elseif (is_string($mixed)) {
            return mb_convert_encoding($mixed, 'UTF-8', 'UTF-8');
        }
        return $mixed;
    }

      public function pdfToExcel(Request $request)
    {
        $request->validate([
            'pdf' => 'required|mimes:pdf',
        ]);

        $file = $request->file('pdf');

        $parser = new Parser();
        $pdf    = $parser->parseFile($file->getPathname());
        $text   = $pdf->getText();

        // Pisahkan teks berdasarkan baris
        $lines = explode("\n", $text);

        // Ubah ke array untuk Excel
        $data = [];
       foreach ($lines as $line) {
    $line = trim($line);
    if ($line === '') continue;

    $columns = preg_split('/\s{2,}/', $line);
    $data[] = $columns;
}

        // Ekspor ke Excel
        return Excel::download(new ArrayExport($data), 'converted.xlsx');
    }
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
