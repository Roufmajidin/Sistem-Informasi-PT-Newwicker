<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\KaryawanController;
use App\Http\Controllers\MarketingController;
use App\Http\Controllers\pdfController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('master.master');
});
Route::get('/marketing', [MarketingController::class, 'index'])->name('marketing.index');
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
Route::post('/buyers/update', [MarketingController::class, 'updateInline']);
// karyawan routing
Route::get('/karyawan', [KaryawanController::class, 'index'])->name('karyawan.index');
Route::get('/karyawan-absen', [KaryawanController::class, 'absenkaryawan'])->name('karyawan.absen');
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
