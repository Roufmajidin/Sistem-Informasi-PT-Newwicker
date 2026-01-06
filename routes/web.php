<?php

use App\Http\Controllers\AbsenController;
use App\Http\Controllers\AgendaController;
use App\Http\Controllers\BuyerController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\KaryawanController;
use App\Http\Controllers\LabelController;;
use App\Http\Controllers\MarketingController;
use App\Http\Controllers\PameranContrller;
use App\Http\Controllers\PdfController;
use Illuminate\Support\Facades\Route;

// ==============================
// 🔐 AUTHENTICATION
// ==============================
Route::get('/login', [AuthController::class, 'showLoginForm'])
    ->middleware('guest')
    ->name('login');
Route::post('/login', [AuthController::class, 'loginWeb'])->name('login.process');
Route::post('/logout', [AuthController::class, 'logoutWeb'])->name('logout');

Route::get('/', function () {
    return view('pages.dashboard.dashboard');
})->middleware('auth');

// ==============================
// 📈 MARKETING
// ==============================
Route::get('/marketing', [MarketingController::class, 'index'])->name('marketing.index');
Route::get('/marketing/pfi', [MarketingController::class, 'pfi'])->name('marketing.pfi');
Route::get('/marketing/buyers_list', [MarketingController::class, 'buyyerList'])->name('marketing.buyers_list');
Route::get('/marketing/buyers_detail/{id}', [MarketingController::class, 'show'])->name('buyers.show');
Route::get('/marketing/scan/{id}', [MarketingController::class, 'scan'])->name('marketing.items.scan');

Route::post('/marketing/items/update', [MarketingController::class, 'update'])->name('marketing.items.update');
Route::post('/marketing/products/import', [MarketingController::class, 'import'])->name('products.import');
Route::post('/marketing/productss/import', [MarketingController::class, 'importb'])->name('products.importb');
Route::post('/marketing/productss/importImage', [MarketingController::class, 'importImage'])->name('products.importImage');
Route::post('/marketing/export-cart', [MarketingController::class, 'export'])->name('cart.export');
Route::get('/buyers/search', [MarketingController::class, 'search'])->name('buyers.search');
Route::post('/buyers/update', [MarketingController::class, 'updateInline']);

// ==============================
// 📦 INVENTORY
// ==============================
Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
Route::post('/inventory', [InventoryController::class, 'store'])->name('inventory.store');
Route::post('/inventory/inline-update', [InventoryController::class, 'update'])->name('inventory.inline.update');
Route::post('/inventory/{id}/upload-foto', [InventoryController::class, 'uploadFoto'])->name('inventory.uploadFoto');
Route::get('/inventory/karyawan/search', [InventoryController::class, 'searchKaryawan'])->name('karyawan.search');
Route::post('/storeAbsen', [KaryawanController::class, 'storeAbsen'])->name('absen.storeAbsen');
Route::get('/riwayat-absen', [KaryawanController::class, 'riwayat'])->name('absen.riwayat');

// ==============================
// 👥 KARYAWAN
// ==============================
Route::get('/karyawan', [KaryawanController::class, 'index'])->name('karyawan.index');
Route::get('/karyawan-absen', [KaryawanController::class, 'absenkaryawan'])->name('karyawan.absen');
Route::get('/izin-karyawan', [KaryawanController::class, 'izinKaryawan'])->name('karyawan.izin');
Route::get('/karyawan-scan', [KaryawanController::class, 'scan'])->name('karyawan.scan');
// absen vvia web

// Route::get('/absen-sekarang', [KaryawanController::class, 'scan'])->name('karyawan.scan');

Route::post('/karyawan/store', [KaryawanController::class, 'store'])->name('karyawan.store');
Route::post('/karyawan/import', [KaryawanController::class, 'import'])->name('karyawan.import');
Route::post('/karyawan/check-existing-names', [KaryawanController::class, 'checkExistingNames'])->name('karyawan.check_existing_names');
Route::post('/karyawan/bulk-save', [KaryawanController::class, 'bulkSave'])->name('karyawan.bulk_save');
Route::post('/karyawan/update-photo', [KaryawanController::class, 'updatePhoto'])->name('karyawan.updatePhoto');
Route::post('/karyawan/update-inline', [InventoryController::class, 'updateInline'])->name('karyawan.updateInline');

// ==============================
// ⏰ ABSENSI
// ==============================
Route::post('/absen/update', [KaryawanController::class, 'updateAbsen'])->name('absen.update');
Route::get('/absen/filter', [KaryawanController::class, 'filter'])->name('absen.filter');
Route::get('/absen/new', [KaryawanController::class, 'new'])->name('absen.new');
Route::get('/absen/bulanan', [KaryawanController::class, 'bulanan'])->name('absen.bulanan');
Route::get('/absen/export', [AbsenController::class, 'export'])->name('absen.export');
Route::post('/validate-izin/{id}', [AbsenController::class, 'validateIzin'])->name('absen.validate');

// ==============================
// 📄 PDF CONVERT
// ==============================
Route::get('/pdf-to-excel', [PdfController::class, 'showForm'])->name('pdf.form');
Route::post('/pdf-to-excel', [PdfController::class, 'convert'])->name('pdf.convert');

// ==============================
// 🎪 PAMERAN
// ==============================
Route::get('/pameran', [PameranContrller::class, 'index'])->name('pameran.index');
Route::post('/product-pameran/import', [PameranContrller::class, 'import'])->name('product_pameran.import');
Route::get('/pameran/filter', [PameranContrller::class, 'getByExhibition'])->name('pameran.filter');
Route::get('/all-event-config', [PameranContrller::class, 'allEentConfig'])->name('eventconfig');
Route::get('/pameran-api', [PameranContrller::class, 'getPameranData'])->name('getPameranData');
Route::get('/download-api', [PameranContrller::class, 'downloadPameranJson'])->name('downloadPameranJson');
Route::get('/pameran/categories', [PameranContrller::class, 'getCategories'])->name('getCategories');

Route::post('/exhibition/store', [PameranContrller::class, 'storeE'])->name('exhibition.store');
Route::post('/pameran/upload', [PameranContrller::class, 'upload'])->name('pameran.upload');

// new
Route::get('/cart-buyer', [BuyerController::class, 'viewClass'])->name('pameran.cartView');
Route::get('/fetchcart-buyer', [BuyerController::class, 'index'])->name('pameran.cart');



// ==============================
// 🏷️ LABELING
// ==============================
Route::get('/labeling', [LabelController::class, 'index'])->name('labeling.index');
Route::post('/labeling/store', [LabelController::class, 'store'])->name('labeling.store');
Route::delete('/labeling/{id}', [LabelController::class, 'destroy'])->name('labeling.destroy');


Route::get('/agenda', [AgendaController::class, 'index'])->name('agenda.index');
Route::get('/request', [AgendaController::class, 'request_agenda'])->name('agenda.req');
Route::post('/agenda-store', [AgendaController::class, 'store'])->name('agenda.store');
Route::post('/agenda/remark', [AgendaController::class, 'updateRemark'])
    ->name('agenda.remark');
// ==============================
// ⚙️ CEK ENV
// ==============================
Route::get('/cek-env', function () {
    return [
        'APP_ENV' => env('APP_ENV'),
        'LAT'     => env('OFFICE_LAT'),
        'LON'     => env('OFFICE_LON'),
        'RADIUS'  => env('OFFICE_RADIUS'),
    ];
});
