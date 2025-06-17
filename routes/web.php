<?php

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
