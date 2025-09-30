<?php

use App\Http\Controllers\AbsenController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\KaryawanController;
use App\Http\Controllers\MarketingController;
use App\Http\Controllers\PameranContrller;
use App\Http\Controllers\pdfController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('master.master');
});
Route::get('/marketing', [MarketingController::class, 'index'])->name('marketing.index');
Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
Route::post('/inventory-inline-update', [InventoryController::class, 'update']);
Route::post('/inventory/{id}/upload-foto', [InventoryController::class, 'uploadFoto'])->name('inventory.uploadFoto');

Route::get('/marketing/pfi', [MarketingController::class, 'pfi'])->name('marketing.pfi');
Route::get('/marketing/buyers_list', [MarketingController::class, 'buyyerList']);
Route::get('/buyers/search', [MarketingController::class, 'search'])->name('buyers.search');
Route::get('/buyers_detail/{id}', [MarketingController::class, 'show'])->name('buyers.show');
Route::post('/products/import', [MarketingController::class, 'import'])->name('products.import');
Route::post('/convert-pdf-to-excel', [pdfController::class, 'pdfToExcel'])->name('pdf.to.excel');
Route::get('/pdf-to-excel', [PdfController::class, 'showForm']);
Route::post('/pdf-to-excel', [PdfController::class, 'convert'])->name('pdf.convert');
Route::post('/productss/import', [MarketingController::class, 'importb'])->name('products.importb');
Route::post('/productss/importImage', [MarketingController::class, 'importImage'])->name('products.importImage');
Route::post('/marketing/items/update', [MarketingController::class, 'update'])->name('marketing.items.update');
Route::get('/marketing/scan/{id}', [MarketingController::class, 'scan'])->name('marketing.items.scan');
Route::post('/export-cart', [MarketingController::class, 'export'])->name('cart.export');

Route::post('/buyers/update', [MarketingController::class, 'updateInline']);
// karyawan routing
Route::get('/karyawan', [KaryawanController::class, 'index'])->name('karyawan.index');
Route::get('/karyawan-absen', [KaryawanController::class, 'absenkaryawan'])->name('karyawan.absen');
Route::get('/izin-karyawan', [KaryawanController::class, 'izinKaryawan'])->name('karyawan.izin');
Route::post('/karyawan/import', [KaryawanController::class, 'import'])->name('karyawan.import');
Route::post('/karyawan/check-existing-names', [KaryawanController::class, 'checkExistingNames'])->name('karyawan.check_existing_names');
Route::post('/karyawan/bulk-save', [KaryawanController::class, 'bulkSave'])->name('karyawan.bulk_save');
Route::post('/absen/update', [KaryawanController::class, 'updateAbsen'])->name('absen.update');
Route::get('/absen/filter', [KaryawanController::class, 'filter'])->name('absen.filter');
Route::get('/absen/new', [KaryawanController::class, 'new'])->name('absen.new');

Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout']);
Route::get('/me', [AuthController::class, 'me'])->middleware('auth');
Route::get('/scan', [KaryawanController::class, 'scan'])->name('karyawan.scan');
Route::get('/login', [KaryawanController::class, 'login'])->name('karyawan.login');

Route::post('/inventory', [InventoryController::class, 'store'])->name('inventory.store');
Route::post('/inventory/{id}/upload-foto', [InventoryController::class, 'uploadFoto'])->name('inventory.uploadFoto');
Route::get('/karyawan/search', [InventoryController::class, 'searchKaryawan'])->name('karyawan.search');
Route::post('/inventory/upload-foto', [InventoryController::class, 'uploadFoto']);
Route::get('/absen/bulanan', [KaryawanController::class, 'bulanan'])->name('absen.bulanan');
Route::get('/absen/export', [AbsenController::class, 'export'])->name('absen.export');
Route::post('/validate-izin/{id}', [App\Http\Controllers\AbsenController::class, 'validateIzin']);
Route::get('/pameran', [PameranContrller::class, 'index'])->name('pameran.index');
Route::post('/product-pameran/import', [PameranContrller::class, 'import'])->name('product_pameran.import');

Route::get('/pameran/filter', [PameranContrller::class, 'getByExhibition'])->name('pameran.filter');
Route::get('/all-event-config', [PameranContrller::class, 'allEentConfig'])->name('eventconfig');

Route::get('/cek-env', function () {
    return [
        'APP_ENV' => env('APP_ENV'),
        'LAT' => env('OFFICE_LAT'),
        'LON' => env('OFFICE_LON'),
        'RADIUS' => env('OFFICE_RADIUS'),
    ];
});
