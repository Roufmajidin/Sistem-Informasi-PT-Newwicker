# NewWicker QC Excel Add-in

Office Add-in untuk Microsoft Excel yang mengambil data QC dari Laravel NewWicker dan menuliskannya ke worksheet `QC Report`.

## Struktur

```text
newwicker-qc-excel/
├── manifest.xml
├── package.json
├── webpack.config.js
├── src/
│   ├── taskpane.html
│   ├── taskpane.js
│   └── taskpane.css
└── assets/
    ├── icon-16.png
    ├── icon-32.png
    └── icon-80.png
```

## 1. Lokasi yang disarankan

Jika Laravel kamu berada di:

```text
C:\Users\roufm\Documents\my docs\bismillah - 2025\PT Newwicker Sistem\sistem_informasi_newwicker
```

letakkan folder ini menjadi:

```text
C:\Users\roufm\Documents\my docs\bismillah - 2025\PT Newwicker Sistem\sistem_informasi_newwicker\excel-addin
```

## 2. Install dependency

Buka CMD/PowerShell:

```bat
cd "C:\Users\roufm\Documents\my docs\bismillah - 2025\PT Newwicker Sistem\sistem_informasi_newwicker\excel-addin"
npm install
```

## 3. Jalankan Add-in

```bat
npm start
```

Development server akan berjalan di:

```text
https://localhost:3000
```

## 4. Laravel API

Add-in mengharapkan endpoint:

```text
GET http://127.0.0.1:8000/api/qc/laporan/add-in
```

Dengan filter opsional:

```text
?inspector=123&from=2026-10-01&to=2026-10-07
```

Response yang paling mudah:

```json
{
  "success": true,
  "data": [
    {
      "tanggal": "07/10/2026",
      "spk": "SPK-609",
      "po": "PO-123",
      "buyer": "Loberon",
      "description": "Example",
      "kategori": "Anyam",
      "batch": 2,
      "inspect": 64,
      "passed": 64,
      "rejected": 0,
      "qc": "Rouf"
    }
  ]
}
```

Add-in juga cukup toleran terhadap response lama yang menggunakan:

```json
{
  "success": true,
  "data": {
    "inspection": [],
    "qcs": []
  }
}
```

## 5. CORS Laravel

Karena Add-in berjalan dari `https://localhost:3000` dan Laravel dari `http://127.0.0.1:8000`, browser dapat menerapkan CORS.

Pastikan Laravel mengizinkan origin development Add-in.

Contoh konfigurasi CORS:

```php
'paths' => ['api/*', 'sanctum/csrf-cookie'],
'allowed_methods' => ['*'],
'allowed_origins' => [
    'https://localhost:3000',
],
'allowed_origins_patterns' => [],
'allowed_headers' => ['*'],
'exposed_headers' => [],
'max_age' => 0,
'supports_credentials' => false,
```

Sesuaikan dengan konfigurasi Laravel kamu.

## 6. Sideload ke Excel Desktop

Setelah:

```bat
npm start
```

berjalan, gunakan Excel desktop:

1. Buka Excel.
2. Buka workbook kosong.
3. Masuk ke **Home > Add-ins**.
4. Pilih **More Add-ins** / **My Add-ins**.
5. Pilih opsi untuk upload / manage custom add-in sesuai versi Office.
6. Pilih file `manifest.xml`.
7. Add-in `NewWicker QC Monitor` akan muncul.
8. Buka task pane dan klik **Load QC Data**.

Jika menu sideload tidak tersedia pada Office kamu, gunakan mekanisme sideload yang tersedia pada build Microsoft 365/Office yang digunakan.

## 7. Alur

```text
Excel
  ↓
NewWicker QC Monitor Add-in
  ↓
GET /api/qc/laporan/add-in
  ↓
Laravel
  ↓
Database
  ↓
JSON
  ↓
Excel worksheet "QC Report"
```

## 8. Catatan penting

- `ms-excel:` tidak digunakan oleh Add-in ini.
- `manifest.xml` menunjuk ke `https://localhost:3000/taskpane.html`.
- URL API Laravel berada di `API_BASE_URL` pada `src/taskpane.js`.
- Saat hosting, ganti URL Add-in di `manifest.xml` dari `https://localhost:3000` menjadi domain HTTPS production kamu.
- Ganti `API_BASE_URL` dari `http://127.0.0.1:8000` menjadi URL Laravel production.
- Untuk production, gunakan HTTPS.
