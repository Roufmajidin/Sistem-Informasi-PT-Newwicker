<?php
use App\Http\Controllers\{
    AuthController,
    MarketingController,
    InventoryController,
    KaryawanController,
    PdfController,
    AbsenController,
    PameranContrller,
    LabelController
};
use Illuminate\Support\Facades\Route;

// ==============================
// 🔐 AUTHENTICATION
// ==============================
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'loginWeb'])->name('login.process');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::get('/', function () {
    return view('master.master');
})->middleware('auth');

//  MARKETING
Route::prefix('marketing')->group(function () {
    Route::get('/', [MarketingController::class, 'index'])->name('marketing.index');
    Route::get('/pfi', [MarketingController::class, 'pfi'])->name('marketing.pfi');
    Route::get('/buyers_list', [MarketingController::class, 'buyyerList'])->name('marketing.buyers_list');
    Route::get('/buyers_detail/{id}', [MarketingController::class, 'show'])->name('buyers.show');
    Route::get('/scan/{id}', [MarketingController::class, 'scan'])->name('marketing.items.scan');

    Route::post('/items/update', [MarketingController::class, 'update'])->name('marketing.items.update');
    Route::post('/products/import', [MarketingController::class, 'import'])->name('products.import');
    Route::post('/productss/import', [MarketingController::class, 'importb'])->name('products.importb');
    Route::post('/productss/importImage', [MarketingController::class, 'importImage'])->name('products.importImage');
    Route::post('/export-cart', [MarketingController::class, 'export'])->name('cart.export');
    Route::get('/buyers/search', [MarketingController::class, 'search'])->name('buyers.search');
    Route::post('/buyers/update', [MarketingController::class, 'updateInline']);
});

//  INVENTORY
Route::prefix('inventory')->group(function () {
    Route::get('/', [InventoryController::class, 'index'])->name('inventory.index');
    Route::post('/', [InventoryController::class, 'store'])->name('inventory.store');
    Route::post('/inline-update', [InventoryController::class, 'update'])->name('inventory.inline.update');
    Route::post('/{id}/upload-foto', [InventoryController::class, 'uploadFoto'])->name('inventory.uploadFoto');
    Route::get('/karyawan/search', [InventoryController::class, 'searchKaryawan'])->name('karyawan.search');
});

//  KARYAWAN
Route::prefix('karyawan')->group(function () {
    Route::get('/', [KaryawanController::class, 'index'])->name('karyawan.index');
    Route::get('/absen', [KaryawanController::class, 'absenkaryawan'])->name('karyawan.absen');
    Route::get('/izin', [KaryawanController::class, 'izinKaryawan'])->name('karyawan.izin');
    Route::get('/scan', [KaryawanController::class, 'scan'])->name('karyawan.scan');

    Route::post('/store', [KaryawanController::class, 'store'])->name('karyawan.store');
    Route::post('/import', [KaryawanController::class, 'import'])->name('karyawan.import');
    Route::post('/check-existing-names', [KaryawanController::class, 'checkExistingNames'])->name('karyawan.check_existing_names');
    Route::post('/bulk-save', [KaryawanController::class, 'bulkSave'])->name('karyawan.bulk_save');
    Route::post('/update-photo', [KaryawanController::class, 'updatePhoto'])->name('karyawan.updatePhoto');
    Route::post('/update-inline', [InventoryController::class, 'updateInline'])->name('karyawan.updateInline');
});

// ABSENSI
Route::prefix('absen')->group(function () {
    Route::post('/update', [KaryawanController::class, 'updateAbsen'])->name('absen.update');
    Route::get('/filter', [KaryawanController::class, 'filter'])->name('absen.filter');
    Route::get('/new', [KaryawanController::class, 'new'])->name('absen.new');
    Route::get('/bulanan', [KaryawanController::class, 'bulanan'])->name('absen.bulanan');
    Route::get('/export', [AbsenController::class, 'export'])->name('absen.export');
    Route::post('/validate-izin/{id}', [AbsenController::class, 'validateIzin'])->name('absen.validate');
});

// PDF CONVERT
Route::get('/pdf-to-excel', [PdfController::class, 'showForm'])->name('pdf.form');
Route::post('/pdf-to-excel', [PdfController::class, 'convert'])->name('pdf.convert');

// PAMERAN
Route::prefix('pameran')->group(function () {
    Route::get('/', [PameranContrller::class, 'index'])->name('pameran.index');
    Route::post('/product/import', [PameranContrller::class, 'import'])->name('product_pameran.import');
    Route::get('/filter', [PameranContrller::class, 'getByExhibition'])->name('pameran.filter');
});
Route::get('/all-event-config', [PameranContrller::class, 'allEentConfig'])->name('eventconfig');
Route::get('/pameran-api', [PameranContrller::class, 'getPameranData'])->name('getPameranData');
Route::post('/exhibition/store', [PameranContrller::class, 'storeE'])->name('exhibition.store');

// LABELING
Route::prefix('labeling')->group(function () {
    Route::get('/', [LabelController::class, 'index'])->name('labeling.index');
    Route::post('/store', [LabelController::class, 'store'])->name('labeling.store');
    Route::delete('/{id}', [LabelController::class, 'destroy'])->name('labeling.destroy');
});

// CEK ENV
Route::get('/cek-env', function () {
    return [
        'APP_ENV' => env('APP_ENV'),
        'LAT'     => env('OFFICE_LAT'),
        'LON'     => env('OFFICE_LON'),
        'RADIUS'  => env('OFFICE_RADIUS'),
    ];
});

