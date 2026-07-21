<?php

use App\Http\Controllers\AbsenController;
use App\Http\Controllers\AgendaController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BankDataController;
use App\Http\Controllers\BomController;
use App\Http\Controllers\BuyerController;
use App\Http\Controllers\CadController;
use App\Http\Controllers\EmployeeLoanController;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\KaryawanController;
use App\Http\Controllers\LabelController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\MarketingController;
use App\Http\Controllers\PameranContrller;
use App\Http\Controllers\PdfController;
use App\Http\Controllers\PengajuanController;
use App\Http\Controllers\PoController;
use App\Http\Controllers\ProduksiController;
use App\Http\Controllers\ProduksiMnController;
use App\Http\Controllers\QcController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SpkController;
use App\Http\Controllers\StockMaterialController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\TokenController;
use App\Http\Controllers\NewPengajuanController;
use Illuminate\Support\Facades\Route;
Route::middleware('auth')->group(function () {

    Route::post('/profile/change-password',[KaryawanController::class, 'changePassword'])->name('profile.change-password');

});
Route::get('/spk/preview/{id}', [SpkController::class, 'preview'])
    ->name('spk.preview');

Route::get('/laporan', [LaporanController::class, 'index'])
    ->name('laporan.index');
Route::get('/laporan/warehouse-history', [LaporanController::class, 'warehouseHistory'])
    ->name('laporan.warehouse-history');
 Route::get('/laporan/detail/{id}',  [LaporanController::class, 'detailBarang']
)->name('laporan.detail');
Route::get(
    '/laporan/detail/{id}/pdf',
    [LaporanController::class,'pdf']
)->name('laporan.detail.pdf');
Route::post('/laporan/update', [LaporanController::class, 'update'])
    ->name('laporan.update');
Route::delete('/laporan/{id}', [LaporanController::class, 'destroy'])
    ->name('laporan.destroy');
// Route::get('/laporan/{stok}/detail', [LaporanController::class, 'detail'])
//     ->name('laporan.detail');
Route::get('/laporan/{id}/detail', [LaporanController::class, 'detail']);
Route::post('/laporan/transaksi/store', [LaporanController::class, 'storeTransaksi'])
    ->name('laporan.transaksi.store');
Route::get('/stok/search', [LaporanController::class, 'searchBarang']);
Route::get('/spk/search-spk', [LaporanController::class,'searchSpk']);
Route::get('/spk/stok/{id}', [LaporanController::class,'detailSpk']);
// ==============================
// 🔐 AUTHENTICATION
// ==============================

Route::post('/karyawan/store', [KaryawanController::class, 'store'])->name('karyawan.store');
Route::post('/karyawan/import', [KaryawanController::class, 'import'])->name('karyawan.import');
Route::post('/karyawan/check-existing-names', [KaryawanController::class, 'checkExistingNames'])->name('karyawan.check_existing_names');
Route::post('/karyawan/bulk-save', [KaryawanController::class, 'bulkSave'])->name('karyawan.bulk_save');
Route::post('/karyawan/update-photo', [KaryawanController::class, 'updatePhoto'])->name('karyawan.updatePhoto');
Route::post('/karyawan/update-inline', [InventoryController::class, 'updateInline'])->name('karyawan.updateInline');
Route::post('/inventory/comment', [InventoryController::class, 'storeComment'])
    ->name('inventory.comment.store');
Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
Route::post('/inventory', [InventoryController::class, 'store'])->name('inventory.store');
Route::post('/inventory-inline-update', [InventoryController::class, 'update'])->name('inventory.inline.update');
Route::post('/inventory/{id}/upload-foto', [InventoryController::class, 'uploadFoto'])->name('inventory.uploadFoto');
Route::get('/inventory/karyawan/search', [InventoryController::class, 'searchKaryawan'])->name('karyawan.search');
Route::post('/storeAbsen', [KaryawanController::class, 'storeAbsen'])->name('absen.storeAbsen');
Route::get('/riwayat-absen', [KaryawanController::class, 'riwayat'])->name('absen.riwayat');

Route::get('/login', [AuthController::class, 'showLoginForm'])
    ->middleware('guest')
    ->name('login');
Route::post('/login', [AuthController::class, 'loginWeb'])->name('login.process');
Route::post('/logout', [AuthController::class, 'logoutWeb'])->name('logout');

Route::get('/', function () {
    return view('pages.dashboard.dashboard');
})->middleware('auth');
Route::get('/qc/mapping', [QcController::class, 'mapping']);
Route::get('/qc/laporan',
    [QcController::class, 'laporan'])
    ->name('qc.laporan');
Route::get('/qc/api/po', [QcController::class, 'getPo'])
    ->middleware('auth');
Route::get('/sqc/monitor/{id}',
    [QcController::class, 'monitorDetail'])
    ->name('qc.monitor.detail');
Route::get(
    '/qc/detail-po/{detailPo}/reports',
    [QcController::class, 'detailPoReports']
);

Route::get('/qc/laporan-qc', [QcController::class, 'laporanQc'])
        ->name('qc.laporans');

Route::get('/inspection/filter', [QcController::class, 'filterInspection'])
    ->name('inspection.filter');
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
// ==============================
// 👥 KARYAWAN
// ==============================
Route::get('/karyawan', [KaryawanController::class, 'index'])->name('karyawan.index');
Route::get('/karyawan-absen', [KaryawanController::class, 'absenkaryawan'])->name('karyawan.absen');
Route::get('/karyawan-lembur', [KaryawanController::class, 'lembur'])->name('karyawan.lembur');
Route::get('/izin-karyawan', [KaryawanController::class, 'izinKaryawan'])->name('karyawan.izin');
Route::get('/karyawan-scan', [KaryawanController::class, 'scan'])->name('karyawan.scan');
Route::post('/lembur/store', [KaryawanController::class, 'storeLembur'])
    ->name('lembur.store');
// absen vvia web

// Route::get('/absen-sekarang', [KaryawanController::class, 'scan'])->name('karyawan.scan');

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

Route::get('/cart-export/{id}', [BuyerController::class, 'cartExport'])->name('cart.exportt');

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
Route::get('/import-excel', [ImportController::class, 'index']);
Route::post('/import-excel', [ImportController::class, 'import']);
Route::get('/qc', [QcController::class, 'index']);
Route::get('/qc/{id}', [QcController::class, 'show'])->name('qc.show');
Route::get('/item/{id}', [QcController::class, 'itemDetail'])->name('qc.item.detail');
Route::get('/qc/cek/{id}', [QcController::class, 'cek']);
Route::get('/qc/getData/{kategoriId}/{detailPo}/{poId}', [QcController::class, 'getData']);
Route::get('/qc/getPoDetail/{kategoriId}/{detailPo}/{poId}', [QcController::class, 'getDataApi']);
Route::get('/insert/{kategoriName}', [QcController::class, 'insertDummy']);
Route::get('/qc/search', [QcController::class, 'ajaxPo'])->name('qc.ajax.poo');

// per kategori (rangka / anyam / dll)
Route::get('/qc/export/{kategori}/{po_id}', [QcController::class, 'exportPdf']);

// 🔥 download semua kategori (ZIP)

Route::get('/marketing-pfi', [PoController::class, 'marketing']);
Route::get('/marketing/ajax/po-list', [QcController::class, 'ajaxPoList'])
    ->name('marketing.ajax.po');
Route::post('/marketing/excel/paste', [PoController::class, 'convert'])->name('marketing.excel.paste');
Route::delete('/marketing/po-delete/{id}', [PoController::class, 'deletePo']);
Route::post('/marketing/excel/upload', [PoController::class, 'uploadExcel'])
    ->name('marketing.excel.upload');
Route::post('/marketing/excel/save', [PoController::class, 'saveExcelData'])
    ->name('marketing.excel.save');
Route::post('/excel/paste', [QcController::class, 'convert'])
    ->name('excel.paste');
Route::get('/marketing-release-order', [QcController::class, 'releaseOrder']);
Route::get('/marketing/po-detail/{id}', [PoController::class, 'getPoDetail']);
Route::post('/marketing/po-item-update-bulk',
    [PoController::class, 'updateItemBulk']);
    // neww yupdate
    Route::post('/marketing/po/update-field', [PoController::class, 'updatePoField'])
    ->name('marketing.po.update.field');
// supplier
Route::middleware(['auth'])->group(function () {

    Route::get('/pengajuan', [PengajuanController::class, 'index']);
    Route::post('/pengajuan/store', [PengajuanController::class, 'store']);
    Route::get('/pengajuan/view-detail/{detailId}', [PengajuanController::class, 'viewDetailImage']);
    Route::post('/pengajuan/upload-detail-image', [PengajuanController::class, 'uploadDetailImage']);
    Route::post('/pengajuan/store-all-divisi', [PengajuanController::class, 'storeAllDivisi']);
Route::post(
    '/pengajuan/add-image/{id}',
    [PengajuanController::class, 'addImage']
)->name('pengajuan.add-image');

    Route::get('/pengajuan/list', [PengajuanController::class, 'list']);
    Route::get('/pengajuan/detail/{id}', [PengajuanController::class, 'detail']);
    Route::get('/pengajuan/messages/{id}', [PengajuanController::class, 'getMessages']);
    Route::post('/pengajuan/send-message', [PengajuanController::class, 'sendMessage']);
    Route::get('/pengajuan/export/{id}', [PengajuanController::class, 'exportExcel']);
    Route::delete('/pengajuan/{id}', [PengajuanController::class, 'destroy']);
    Route::get('/dev/reset-pengajuan', [PengajuanController::class, 'reset']);
    Route::post('/pengajuan/approve/{id}', [PengajuanController::class, 'approveStep']);
    Route::post('/pengajuan/approve-all/{id}', [PengajuanController::class, 'approveAll']);
    Route::get('/dashboard/pending-approval', [PengajuanController::class, 'pendingMyApproval']);
});
Route::get('/supplier', [SupplierController::class, 'index']);
Route::post('/supplier/store', [SupplierController::class, 'storeSupplier']);
Route::post('/supplier/update/{id}', [SupplierController::class, 'updateSupplier']);
Route::post('/jenis/store', [SupplierController::class, 'storeJenis']);
Route::post('/jenis/update/{id}', [SupplierController::class, 'updateJenis']);
Route::get('/supplier/search', [SupplierController::class, 'search']);

Route::get('/qc/export-all/{po_id}', [SpkController::class, 'exportAll']);
// draft
Route::get('/spk/request-r', [SpkController::class, 'draftr'])->name('spk.draft');
Route::post('/payment-request/save-draft-group', [SpkController::class, 'saveDraftGroup'])->name( 'payment-request.save-draft-group');
Route::get('/payment-request-saved/{id}/detail',[SpkController::class, 'detailDraft'])->name( 'payment-request.detail-draft');
//
Route::delete('/karyawan/{id}/delete', [KaryawanController::class, 'destroy'])
    ->name('karyawan.destroy');
// bom
// BOM
Route::get(
    '/bom',
    [BomController::class, 'index']
)->name('bom.index');

Route::post(
    '/bom/store',
    [BomController::class, 'store']
)->name('bom.store');

Route::get('/bom/edit/{id}',[BomController::class, 'edit']
)->name('bom.edit');

Route::post(
    '/bom/update/{id}',
    [BomController::class, 'updateBom']
)->name('bom.update');

Route::delete(
    '/bom/delete/{id}',
    [BomController::class, 'destroyBom']
)->name('bom.destroy');


// MATERIAL PRICE
Route::post(
    '/material-price/store',
    [BomController::class, 'bulkStore']
)->name('material-price.bulk-store');

Route::delete(
    '/material-price/delete/{id}',
    [BomController::class, 'destroy']
)->name('material-price.destroy');

Route::post(
    '/material-price/update/{id}',
    [BomController::class, 'update']
)->name('material-price.update');


// MATERIAL FINISHING
Route::prefix('material-finishing')->group(function () {

    Route::post(
        '/bulk-store',
        [BomController::class, 'bulkStoreFinishing']
    )->name('material-finishing.bulk-store');

    Route::post(
        '/update/{id}',
        [BomController::class, 'updateFinishing']
    )->name('material-finishing.update');

    Route::delete(
        '/delete/{id}',
        [BomController::class, 'destroyFinishing']
    )->name('material-finishing.destroy');

});
// bom CRUD
Route::get('/bom/list', [BomController::class,'list'])
    ->name('bom.list');

Route::get('/bom/show/{id}', [BomController::class,'show'])
    ->name('bom.show');

Route::get('/bom/edit/{id}', [BomController::class,'show'])
    ->name('bom.edit');
Route::post(
    '/bom/update/{id}',
    [BomController::class, 'updateBom']
)->name('bom.update');

Route::post('/bom/store', [BomController::class,'store'])
    ->name('bom.store');

Route::post('/bom/update/{id}', [BomController::class,'updateBom'])
    ->name('bom.update');

Route::get('/produksi/get-data', [SpkController::class, 'getData']);
Route::get('/get-detail-barang', [SpkController::class, 'getDetailBarang']);
Route::post('/produksi/save', [SpkController::class, 'saveData']);
Route::post('/save-process', [SpkController::class, 'saveProcess']);
Route::get('/get-timeline', [SpkController::class, 'getTimeline']);
Route::get('/timeline/data', [PoController::class, 'getTimeline'])->name('timeline.data');
Route::get('/spk/{id}', [SpkController::class, 'index'])->name('spk.index');
Route::post('/spk/create/{po}', [SpkController::class, 'save'])->name('laporan');
Route::post('/spk/update/{spk}', [SpkController::class, 'save'])->name('spk.update');
// Route::post('/spk/create/{po}', [SpkController::class, 'save'])->name('spk.create');
Route::post('/spk/purchase', [SpkController::class, 'purchase'])->name('spk.purchase');

Route::post('/spk/change-status/{spk}', [SpkController::class, 'changeStatus']
)->name('spk.change-status');
Route::get('/spk/timeline/{spk}', [SpkController::class, 'timeline']);
Route::post('/payment-request/store', [SpkController::class, 'paymentstore']);
Route::get('/test-calendar', [SpkController::class, 'calendar'])->name('spk.calendar');
Route::get('/add-calendar', [SpkController::class, 'addCalendar'])->name('spk.addcalendar');

Route::get('/detail-po/search', [SpkController::class, 'search'])
    ->name('detailpo.search');

Route::get('/spkk/timeline', [SpkController::class, 'tima'])->name('spk.time');
Route::get('/all-spk', [SpkController::class, 'allspk'])->name('spk.all');
Route::get('/semua-spk', [SpkController::class, 'spk'])->name('spk.semua');
Route::get('/spk/edit/{id}', [SpkController::class, 'index'])->name('spk.edit');
Route::get('/spk/views/{id}', [SpkController::class, 'index'])->name('spk.view');
Route::post('/spk/update/{spk}', [SpkController::class, 'save'])->name('spk.update');
Route::post('/spk/create/{po}', [SpkController::class, 'save'])->name('spk.create');

Route::post('/spk/simpan-edit/{id}', [SpkController::class, 'saveEdit'])
    ->name('spk.simpan-edit');
// produksi
Route::get('/get-qc', [SpkController::class, 'getQc']);
Route::get('/produksi', [ProduksiController::class, 'index'])->name('produksi.index');
Route::get('/spk/export/{id}', [SpkController::class, 'export'])->name('spk.export');
Route::get('/cad/{id}', [CadController::class, 'index'])->name('cad.index');
Route::post('/cad/upload', [CadController::class, 'upload']);
Route::post('/bom/import', [BomController::class, 'import']);

Route::get('/chatroom/get-room/{id}', [CadController::class, 'getRoom']);
Route::get('/chatroom/messages/{id}', [CadController::class, 'messages']);
Route::post('/chatroom/send', [CadController::class, 'send']);

Route::get('/produksi/mn', [ProduksiMnController::class, 'index'])->name('produksi.mn');
Route::get('/qc-report/{inspectSchedule}', [ProduksiMnController::class, 'qcReport']
)->name('qc.report');
Route::get('/produksi/inventor', [ProduksiMnController::class, 'inventor']);
Route::get(
    '/inventor/spk/{id}',
    [ProduksiMnController::class, 'inventorDetail']
);
Route::post(
    '/inventor/store',
    [ProduksiMnController::class, 'inventorStore']
)->name('inventor.store');
Route::delete('/inventor/delete/{id}', [ProduksiMnController::class, 'delete']);
Route::delete(
    '/spk/delete/{id}',
    [SpkController::class, 'delete']
);
Route::prefix('pfi')->group(function () {

    Route::get('/import', [ImportController::class, 'index'])
        ->name('pfi.import');

    Route::post('/import/preview', [ImportController::class, 'preview'])
        ->name('pfi.import.preview');

    Route::post('/import/process', [ImportController::class, 'process'])
        ->name('pfi.import.process');
});
Route::post('/qc/store', [QcController::class, 'save'])->name('qc.save');
Route::get('/qc/po-list', [QcController::class, 'poList'])->name('qc.po.list');
Route::get('/qc/ajax/po-list', [QcController::class, 'ajaxPoList'])
    ->name('qc.ajax.po');
Route::get('/setting', [SettingController::class, 'index']);
Route::delete('/setting/kategori/{id}', [SettingController::class, 'destroyKategori'])
    ->name('kategori.destroy');

Route::delete('/setting/checkpoint/{id}', [SettingController::class, 'destroyCheckpoint'])
    ->name('checkpoint.destroy');

Route::post('/setting/kategori', [SettingController::class, 'storeKategori']);
Route::post('/setting/checkpoint', [SettingController::class, 'storeCheckpoint']);
Route::post('/setting/checkpoint/mass', [SettingController::class, 'storeCheckpointMass'])
    ->name('checkpoint.store.mass');
// Route::post('/production-timeline/store', [ProduksiController::class, 'store']);
Route::post('/production-timeline/store', [ProduksiController::class, 'store']);
Route::post('/production-timeline/store-batch', [ProduksiController::class, 'storeBatch']);
Route::put('/production-timeline/{id}', [ProduksiController::class, 'update']);
Route::get('/production-timeline/{kategori?}', [ProduksiController::class, 'getByDetail']);
Route::post('/pameran/upload-image', [PameranContrller::class, 'uploadImage'])
    ->name('pameran.uploadImage');
// Route::get('/kategori/{kategori}', [ProduksiController::class, 'getByKategori']);
// Route::get('/kategori/{kategori?}', [ProduksiController::class, 'getByKategori']);
Route::get('/catalogue', [TokenController::class, 'catalogue']);
Route::get('/get-catalogue', [TokenController::class, 'getcatalogue']);

Route::get('/catalogue', [TokenController::class, 'catalogue']);

Route::get('/token', [TokenController::class, 'tokenPage']);
Route::post('/update-token', [TokenController::class, 'updateToken']);
Route::post('/token-check', [TokenController::class, 'checkToken']);
Route::get('/token-list', [TokenController::class, 'list']);
Route::post('/save-visitor', [TokenController::class, 'saveVisitor']);
// BANK DATA
Route::get('/bank-data', [BankDataController::class, 'index'])->name('banl-data.index');
Route::post('/bank-data/upload', [BankDataController::class, 'upload'])->name('bank-data.upload');
Route::post('/generate-token', [TokenController::class, 'generateToken']);
Route::get('/inventory/{id}', [InventoryController::class, 'detail'])
    ->name('inventory.detail');

Route::get('/employee-loan', [EmployeeLoanController::class, 'index'])->name('empl.index');
Route::post('/employee-loans/{id}/approve', [EmployeeLoanController::class, 'approve'])->name('employee-loans.approve');
Route::delete(
    '/employee-loans/{id}',
    [EmployeeLoanController::class, 'destroy']
)->name('employee-loans.destroy');

// inventory bu manti
// Route::get('/stocks', [StockMaterialController::class, 'index']);
// Route::post('/stocks', [StockMaterialController::class, 'store']);
// Route::get('/stocks/{id}', [StockMaterialController::class, 'show']);
// Route::put('/stocks/{id}', [StockMaterialController::class, 'update']);
// Route::delete('/stocks/{id}', [StockMaterialController::class, 'destroy']);
// Route::view('/stocks', 'pages.stock_bahan');

// Route::view(

//     '/stocks',

//     'pages.stock_bahan'

// );
// ==============================
// ⚙️ CEK ENV
// ==============================
Route::get('/pameran/download/{exhibition}/{article}', [PameranContrller::class, 'downloadImage'])->name('pameran.downloadImage');
Route::get('/cek-env', function () {
    return [
        'APP_ENV' => env('APP_ENV'),
        'LAT'     => env('OFFICE_LAT'),
        'LON'     => env('OFFICE_LON'),
        'RADIUS'  => env('OFFICE_RADIUS'),
    ];
});


// ajukan spk sigantrure
Route::get(
    '/spk/{spk}/signature',
    [SpkController::class, 'signature']
)->name('spk.signature');

Route::post(
    '/spk/{id}/submit-signature',
    [SpkController::class, 'submitSignature']
)->name('spk.submit-signature');
Route::post(
    '/spk/signature/{id}',
    [SpkController::class,'signSignature']
)->name('spk.signature.sign');
// approve pengajuan spk payment
Route::post(
    '/payment-request-approval/{id}/approve',
    [SpkController::class, 'approve']
)->name('payment-request.approve');
Route::post(
    '/payment-request/finance-adjustment',
    [SpkController::class,
    'financeAdjustment']
);
Route::post(
    '/inventor/update-harga-vivi',
    [ProduksiMnController::class,'updateHargaVivi']
);

// new pengajuan
Route::get( '/v2/pengajuan',   [NewPengajuanController::class, 'index'])->name('png.index');
Route::get( '/cad',   [CadController::class, 'all'])->name('cad.all');
Route::get('/cad/history/{article}', [CadController::class, 'history'])
    ->name('cad.history');
Route::get('/pfi/notifications', [SpkController::class, 'notifications']);


Route::get('/container-loading', [SpkController::class, 'indexloading'])
    ->name('container.loading.index');

Route::post('/container-loading/generate', [SpkController::class, 'generateLoading'])
    ->name('container.loading.generate');
Route::get('/bom/{id}/export-excel', [BomController::class, 'exportExcel'])->name('bom.export.excel');


Route::get('/mutasi', [ProduksiController::class, 'mutasi'])->name('mutasi.index');
Route::get('/mutasi/{id}', [ProduksiController::class, 'mutasidetail'])
    ->name('mutasi.detail');
Route::get('/mutasi/timeline/detail', [ProduksiController::class, 'mutasiTimelineDetail'])
    ->name('mutasi.mutasiTimelineDetail');
Route::post('/mutasi/timeline/save',
    [ProduksiController::class,'saveTimeline'])
    ->name('mutasi.timeline.save');
Route::get('/phpinfo', function () {
    phpinfo();
});
Route::post('/bom/{bom}/toggle-release', [BomController::class, 'toggleRelease'])
    ->name('bom.toggleRelease');
Route::post(
    '/history/update-po/{id}',
    [LaporanController::class,'updatePo']
);

Route::get('/bom/search', [BomController::class, 'search'])
    ->name('bom.search');

Route::post('/bom/copy', [BomController::class, 'copyBom'])
    ->name('bom.copy');
Route::delete('/bom/{id}', [BomController::class, 'destroyBom'])
    ->name('bom.destroys');

    Route::get('/bom/create-partial', [BomController::class, 'createPartial']);
Route::get('/bom/harga-partial', [BomController::class, 'hargaPartial']);
Route::get('/bom/finishing-partial', [BomController::class, 'finishingPartial']);
Route::get('/bom/released-partial', [BomController::class, 'releasedPartial']);
