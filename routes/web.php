<?php
use App\Http\Controllers\PengajuanController;

use App\Http\Controllers\AbsenController;
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
use App\Http\Controllers\AgendaController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\PoController;
use App\Http\Controllers\QcController;
use App\Http\Controllers\CadController;
use App\Http\Controllers\BomController;
use App\Http\Controllers\EmployeeLoanController;
use App\Http\Controllers\SpkController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\ProduksiMnController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\NewPengajuanController;
use App\Http\Controllers\ProduksiController;
use App\Http\Controllers\CogController;
use App\Http\Controllers\ITController;
use App\Http\Controllers\MonitoringInvoiceController;
use App\Http\Controllers\UpahController;
use App\Http\Controllers\SubkonController;
use Illuminate\Http\Request;
use Pusher\Pusher;
use App\Http\Controllers\SofianController;
use App\Http\Controllers\EdController;

// ==========================================================
// MAGIC APPROVAL LINK - PUBLIC ENTRY POINT
// ==========================================================
Route::get('/approval/bypass_config/{token}', [
    SpkController::class,
    'magicApproval'
])->name('approval.magic');

// ==========================================================
// AUTH PROTECTION - ALL INTERNAL ROUTES
// ==========================================================
Route::middleware('auth')->group(function () {
// sofian

// exports
Route::get('/export/{id}/IPLEX', [SofianController::class, 'downloadPackingList'])->name('export.packing-list');
Route::get('/export/{id}/INVEX', [SofianController::class, 'downloadInvoiceList'])->name('export.inv-list');
Route::get('/export/stock', [EdController::class, 'stock'])->name('export.stock');
Route::get('/export/doc_exports', [EdController::class, 'docExports'])->name('export.docExports');

Route::get('/export/index', [EdController::class, 'index'])->name('export.index');
Route::get('/export/search-po', [EdController::class, 'searchPo'])
    ->name('export.search-po');
Route::put('/export/{id}', [EdController::class, 'updateIpl'])
    ->name('export.updateIpl');
Route::get('/export/{id}/edit', [EdController::class, 'edit'])
    ->name('export.edit');
Route::get('/export/po-items/{id}', [EdController::class, 'poItems']);
Route::post('/export/save-ipl', [EdController::class, 'saveIpl'])->name('export.saveIpl');
Route::get('/export/ipl', [EdController::class, 'ipl'])->name('export.ipl');
Route::get('/export/history', [EdController::class, 'history'])->name('export.document.index');
Route::get('/export/document/{id}/edit', [EdController::class, 'editDoc'])
    ->name('export.document.edit');
Route::put('/export/document/{id}', [EdController::class, 'update'])
    ->name('export.document.update');
Route::get(
    '/export/check-detail/{detail_po_id}',  
    [EdController::class, 'check']
)->name('export.check');
Route::get('/export/document/list', [EdController::class, 'documentList'])
    ->name('export.document.list');

Route::get('/export/document/{id}', [EdController::class, 'documentDetail'])
    ->name('export.document.detail');
// save doc
Route::post('/export/document', [EdController::class, 'storeDocument'])->name('export.document.store');
Route::delete('/export/item/{id}', [EdController::class, 'deleteItem'])
    ->name('export.item.delete');
Route::post('/export/{id}/release', [EdController::class, 'releaseIpl'])
    ->name('export.release');
    
// list po 
Route::get('/export/po-list', [EdController::class, 'poList'])
    ->name('export.po.list');

Route::get('/export/po-detail/{id}', [EdController::class, 'poDetail'])
    ->name('export.po.detail');

// pusher
Route::post('/pusher/auth', function (Request $request) {

    $user = auth()->user();

    abort_unless($user, 401);

    $pusher = new Pusher(
        config('broadcasting.connections.pusher.key'),
        config('broadcasting.connections.pusher.secret'),
        config('broadcasting.connections.pusher.app_id'),
        [
            'cluster' => config(
                'broadcasting.connections.pusher.options.cluster'
            ),
            'useTLS' => true,
        ]
    );

    return response(
        $pusher->presence_auth(
            $request->channel_name,
            $request->socket_id,
            (string) $user->id,
            [
                'name' =>
                    $user->name ??
                    'User',
            ]
        )
    );

})->middleware('auth')
  ->name('pusher.auth');

  Route::post('/profile/change-password',[KaryawanController::class, 'changePassword'])->name('profile.change-password');

Route::get( '/cad',   [CadController::class, 'all'])->name('cad.all');
Route::get('/laporan', [LaporanController::class, 'index'])
    ->name('laporan.index');
Route::get('/laporan/warehouse-history', [LaporanController::class, 'warehouseHistory'])
    ->name('laporan.warehouse-history');

// export warehouse history
Route::get(
    '/laporan/warehouse-history/export',
    [LaporanController::class, 'exportWarehouseHistory']
)->name('warehouse.history.export');


Route::get('/warehouse/overview', [LaporanController::class, 'overview'])
    ->name('warehouse.overview');
 Route::get('/laporan/detail/{id}',  [LaporanController::class, 'detailBarang']
)->name('laporan.detail');
// editble
Route::post(
    '/history/update-spk/{id}',
    [LaporanController::class, 'updateHistorySpk']
)->name('history.updateSpk');
Route::post(
    '/history/update-field/{id}',
    [LaporanController::class,'updateHistoryField']
)->name('history.updateField');
Route::get(
    '/laporan/detail/{id}/pdf',
    [LaporanController::class,'pdf']
)->name('laporan.detail.pdf');
Route::post('/laporan/update', [LaporanController::class, 'update'])
    ->name('laporan.update');
// Route::get('/laporan/{stok}/detail', [LaporanController::class, 'detail'])
//     ->name('laporan.detail');
Route::get('/laporan/{id}/detail', [LaporanController::class, 'detail']);
Route::post('/laporan/transaksi/store', [LaporanController::class, 'storeTransaksi'])
    ->name('laporan.transaksi.store');
Route::get('/stok/search', [LaporanController::class, 'searchBarang']);
Route::get('/spk/search-spk', [LaporanController::class,'searchSpk']);
Route::get('/spk/stok/{id}', [LaporanController::class,'detailSpk']);
// timeline
Route::get('/timeline/data', [PoController::class, 'getTimeline'])->name('timeline.data');
Route::post('/inventory/comment', [InventoryController::class, 'storeComment'])
    ->name('inventory.comment.store');
    Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
Route::post('/inventory', [InventoryController::class, 'store'])->name('inventory.store');
Route::post('/inventory-inline-update', [InventoryController::class, 'update'])->name('inventory.inline.update');
Route::post('/inventory/{id}/upload-foto', [InventoryController::class, 'uploadFoto'])->name('inventory.uploadFoto');
Route::get('/inventory/karyawan/search', [InventoryController::class, 'searchKaryawan'])->name('karyawan.search');
Route::post('/storeAbsen', [KaryawanController::class, 'storeAbsen'])->name('absen.storeAbsen');
Route::get('/riwayat-absen', [KaryawanController::class, 'riwayat'])->name('absen.riwayat');
// bom
// BOM
Route::get(
    '/bom',
    [BomController::class, 'index']
)->name('bom.index');

Route::get('/ajaxBom', [BomController::class,'ajaxMaterialPrice'])->name('ajax');
Route::post('/bom-material-price/store', [BomController::class, 'bulkStore']);


Route::get(
    '/bom/edit/{id}',
    [BomController::class, 'edit']
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





Route::get('/qc/mapping', [QcController::class, 'mapping']);
Route::get('/qc/laporan',
    [QcController::class, 'laporan'])
    ->name('qc.laporan');
Route::get('/qc/api/po', [QcController::class, 'getPo'])
    ->middleware('auth');

Route::get('/sqc/monitor/{id}',
    [QcController::class, 'monitorDetail'])
    ->name('qc.monitor.detail');

// qc

Route::get('/qc/laporan-qc', [QcController::class, 'laporanQc'])
        ->name('qc.laporans');

Route::get('/inspection/filter', [QcController::class, 'filterInspection'])
    ->name('inspection.filter');
    
Route::get('/qc', [QcController::class, 'index']);
Route::get('/qc/{id}', [QcController::class, 'show'])->name('qc.show');
Route::get('/item/{id}', [QcController::class, 'itemDetail'])->name('qc.item.detail');
Route::get('/qc/cek/{id}', [QcController::class, 'cek']);
Route::get('/qc/getData/{kategoriId}/{detailPo}/{poId}', [QcController::class, 'getData']);
Route::get('/qc/getPoDetail/{kategoriId}/{detailPo}/{poId}', [QcController::class, 'getDataApi']);
Route::get('/insert/{kategoriName}', [QcController::class, 'insertDummy']);
Route::get('/qc/search', [QcController::class, 'ajaxPo'])->name('qc.ajax.poo');
Route::post('/qc/store', [QcController::class, 'save'])->name('qc.save');
Route::get('/qc/po-list', [QcController::class, 'poList'])->name('qc.po.list');
Route::get('/qc/ajax/po-list', [QcController::class, 'ajaxPoList'])
    ->name('qc.ajax.po');

// marketing neww

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

// add

Route::post('/marketing/po-detail/add-item',
    [PoController::class, 'addDetailItem']);

Route::post('/marketing/po-detail/upload-image',
    [PoController::class, 'uploadDetailImage']);
// setting
Route::get('/setting', [SettingController::class, 'index']);
Route::delete('/setting/kategori/{id}', [SettingController::class, 'destroyKategori'])
    ->name('kategori.destroy');

Route::delete('/setting/checkpoint/{id}', [SettingController::class, 'destroyCheckpoint'])
    ->name('checkpoint.destroy');

Route::post('/setting/kategori', [SettingController::class, 'storeKategori']);
Route::post('/setting/checkpoint', [SettingController::class, 'storeCheckpoint']);
Route::post('/setting/checkpoint/mass', [SettingController::class, 'storeCheckpointMass'])
    ->name('checkpoint.store.mass');
// Magic Approval Link generator (must be logged in)
Route::post('/spk/request-r/magic-link', [
    SpkController::class,
    'generateMagicApprovalLink'
])->name('approval.magic.generate');


});

// ==============================
// 🔐 AUTHENTICATION
// ==============================
Route::get('/login', [AuthController::class, 'showLoginForm'])
    ->middleware('guest')
    ->name('login');
Route::post('/login', [AuthController::class, 'loginWeb'])->name('login.process');
Route::post('/logout', [AuthController::class, 'logoutWeb'])->name('logout')->middleware('auth');

// ==========================================================
// RESUME AUTH PROTECTION FOR INTERNAL ROUTES
// ==========================================================
Route::middleware('auth')->group(function () {



Route::get('/', function () {
    return view('pages.dashboard.dashboard');
})->middleware('auth');

// ==============================
// 📈 MARKETING
// ==============================


// ==============================
// 👥 KARYAWAN
// ==============================
Route::get('/karyawan', [KaryawanController::class, 'index'])->name('karyawan.index');
Route::get('/karyawan-absen', [KaryawanController::class, 'absenkaryawan'])->name('karyawan.absen');
Route::get('/izin-karyawan', [KaryawanController::class, 'izinKaryawan'])->name('karyawan.izin');
Route::get('/karyawan-scan', [KaryawanController::class, 'scan'])->name('karyawan.scan');
Route::get('/karyawan-lembur', [KaryawanController::class, 'lembur'])->name('karyawan.lembur');
Route::post('/lembur/store', [KaryawanController::class, 'storeLembur'])
    ->name('lembur.store');
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

Route::get('/cart-export/{id}', [BuyerController::class, 'cartExport'])->name('cart.exportt');

Route::post('/pameran/upload-image', [PameranContrller::class,'uploadImage'])
->name('pameran.uploadImage');
Route::get('/pameran/download/{exhibition}/{article}', [PameranContrller::class, 'downloadImage'])->name('pameran.downloadImage');

// pengajuan
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

// cad

Route::get('/cad/{id}', [CadController::class, 'index'])->name('cad.index');
Route::post('/cad/upload', [CadController::class, 'upload']);
Route::post('/bom/import', [BomController::class, 'import']);

Route::get('/chatroom/get-room/{id}', [CadController::class, 'getRoom']);
Route::get('/chatroom/messages/{id}', [CadController::class, 'messages']);
Route::post('/chatroom/send', [CadController::class, 'send']);

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
Route::get('/inventory/{id}', [InventoryController::class, 'detail'])
    ->name('inventory.detail');
    

Route::get('/employee-loan', [EmployeeLoanController::class, 'index'])->name('empl.index');
Route::post('/employee-loans/{id}/approve', [EmployeeLoanController::class, 'approve'])->name('employee-loans.approve');
Route::delete(
    '/employee-loans/{id}',
    [EmployeeLoanController::class, 'destroy']
)->name('employee-loans.destroy');

Route::get('/qc/export/{kategori}/{po_id}', [QcController::class, 'exportPdf']);

Route::get('/supplier', [SupplierController::class, 'index']);
Route::post('/supplier/store', [SupplierController::class, 'storeSupplier']);
Route::post('/supplier/update/{id}', [SupplierController::class, 'updateSupplier']);
Route::post('/jenis/store', [SupplierController::class, 'storeJenis']);
Route::post('/jenis/update/{id}', [SupplierController::class, 'updateJenis']);
Route::get('/supplier/search', [SupplierController::class, 'search']);

Route::get('/qc/export-all/{po_id}', [SpkController::class, 'exportAll']);

// SPK

Route::get('/qc/export-all/{po_id}', [SpkController::class, 'exportAll']);

Route::get('/spk/request-r', [SpkController::class, 'draftr'])->name('spk.draft');
Route::post('/payment-request/save-draft-group', [SpkController::class, 'saveDraftGroup'])->name( 'payment-request.save-draft-group');
Route::get('/payment-request-saved/{id}/detail',[SpkController::class, 'detailDraft'])->name( 'payment-request.detail-draft');
Route::post(
    '/payment-request/save-draft',
    [SpkController::class, 'saveDraftRequest']
)->name('payment-request.save-draft');
Route::get('/produksi/get-data', [SpkController::class, 'getData']);
Route::get('/get-detail-barang', [SpkController::class, 'getDetailBarang']);
Route::post('/produksi/save', [SpkController::class, 'saveData']);
Route::post('/save-process', [SpkController::class, 'saveProcess']);
Route::get('/get-timeline', [SpkController::class, 'getTimeline']);
Route::get('/timeline/data', [PoController::class, 'getTimeline'])->name('timeline.data');
Route::get('/spk/{id}', [SpkController::class, 'index'])->name('spk.index');
Route::post('/spk/create/{po}', [SpkController::class, 'save'])->name('spk.create');
Route::post('/spk/update/{spk}', [SpkController::class, 'save'])->name('spk.update');
Route::post('/spk/create/{po}', [SpkController::class, 'save'])->name('spk.create');
Route::post('/spk/purchase', [SpkController::class, 'purchase'])->name('spk.purchase');

Route::post('/spk/change-status/{spk}', [SpkController::class, 'changeStatus']
)->name('spk.change-status');
Route::get('/spk/timeline/{spk}', [SpkController::class, 'timeline']);
Route::post('/payment-request/store',[SpkController::class,'paymentstore']);
Route::get('/test-calendar', [SpkController::class, 'calendar'])->name('spk.calendar');
Route::get('/add-calendar', [SpkController::class, 'addCalendar'])->name('spk.addcalendar');

Route::get('/detail-po/search', [SpkController::class, 'search'])
    ->name('detailpo.search');
Route::get('/spk/views/{id}', [SpkController::class, 'index'])->name('spk.view');
Route::get('/spkk/timeline', [SpkController::class, 'tima'])->name('spk.time');
Route::get('/all-spk', [SpkController::class, 'allspk'])->name('spk.all');
Route::get('/semua-spk', [SpkController::class, 'spk'])->name('spk.semua');

Route::get('/spk/edit/{id}', [SpkController::class, 'index'])->name('spk.edit');

Route::post('/spk/update/{spk}', [SpkController::class, 'save'])->name('spk.update');
Route::post('/spk/create/{po}', [SpkController::class, 'save'])->name('spk.create');

Route::post('/spk/simpan-edit/{id}', [SpkController::class, 'saveEdit'])
    ->name('spk.simpan-edit');
// produksi
Route::get('/get-qc', [SpkController::class, 'getQc']);
Route::get('/produksi', [ProduksiController::class, 'index'])->name('produksi.index');
Route::get('/spk/export/{id}', [SpkController::class, 'export'])->name('spk.export');

// produksi
Route::delete('/karyawan/{id}/delete', [KaryawanController::class, 'destroy'])
    ->name('karyawan.destroy');

Route::get('/produksi/mn', [ProduksiMnController::class, 'index'])->name('produksi.mn');
Route::get('/produksi/monitoring-finishing', [ProduksiMnController::class, 'monitoringFinishing']);

Route::get('/qc-report/{inspectSchedule}',[ProduksiMnController::class, 'qcReport']
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

Route::get('/cad/history/{article}', [CadController::class, 'history'])
    ->name('cad.history');


Route::get('/pfi/notifications', [SpkController::class, 'notifications']);
Route::get('/bom/{id}/export-excel', [BomController::class, 'exportExcel'])->name('bom.export.excel');

// siti
Route::get('/mutasi', [ProduksiController::class, 'mutasi'])->name('mutasi.index');
Route::get('/mutasi/{id}', [ProduksiController::class, 'mutasidetail'])
    ->name('mutasi.detail');
Route::get('/mutasi/timeline/detail', [ProduksiController::class, 'mutasiTimelineDetail'])
    ->name('mutasi.mutasiTimelineDetail');
Route::post('/mutasi/timeline/save',
    [ProduksiController::class,'saveTimeline'])
    ->name('mutasi.timeline.save');
Route::post('/bom/{bom}/toggle-release', [BomController::class, 'toggleRelease'])
    ->name('bom.toggleRelease');

Route::post(
    '/history/update-po/{id}',
    [LaporanController::class,'updatePo']
);

Route::post('/bom/copy', [BomController::class, 'copyBom'])
    ->name('bom.copy');
Route::delete('/bom/{id}', [BomController::class, 'destroyBom'])
    ->name('bom.destroyed');
    

    Route::get('/bom/create-partial', [BomController::class, 'createPartial']);
Route::get('/bom/harga-partial', [BomController::class, 'hargaPartial']);
Route::get('/bom/finishing-partial', [BomController::class, 'finishingPartial']);
Route::get('/bom/released-partial', [BomController::class, 'releasedPartial']);
    Route::post('/marketing/po/update-field', [PoController::class, 'updatePoField'])
    ->name('marketing.po.update.field');


// siti
Route::get('/produksi/in_out_barang_jadi', [ProduksiMnController::class, 'barangJadi'])->name('barang.jadi');
Route::get('/laporan/barang-jadi/export', [ProduksiMnController::class, 'exportBarangJadi'])
    ->name('barang.jadi.export');

Route::get(
    '/produksi/in_out_barang_jadi/rekap',
    [ProduksiMnController::class, 'barangJadiRekap']
)->name('barang.jadi.rekap');
// new routing
Route::get('/produksi/inventor/arsip', [ProduksiMnController::class, 'inventorArsip'])
    ->name('inventor.arsip');

// cog 

    /*
    |--------------------------------------------------------------------------
    | BOM PRODUKSI
    |--------------------------------------------------------------------------
    */
    Route::get('/bom-produksi/edit/{id}', [CogController::class, 'show'])->name('bom_p.c_edit');

    Route::prefix('bom-produksi')->name('cog.')->group(function () {
    Route::get('/bom/{id}/export-excel', [CogController::class, 'exportExcel'])->name('bom-prod.export.excel');

        Route::get('/', [CogController::class, 'index'])->name('index');

        Route::post('/store', [CogController::class, 'store'])->name('store');

        Route::get('/list', [CogController::class, 'list'])->name('list');

        Route::get('/show/{id}', [CogController::class, 'show'])->name('show');


        Route::post('/update/{id}', [CogController::class, 'updateBom'])->name('update');

        Route::delete('/delete/{id}', [CogController::class, 'destroyBom'])->name('destroy');

        //
        Route::post('/bom/{bom}/toggle-release', [CogController::class, 'toggleRelease'])
            ->name('bom.toggleRelease');
        Route::post(
            '/history/update-po/{id}',
            [LaporanController::class,'updatePo']
        );

        Route::get('/bom/search', [CogController::class, 'search'])
            ->name('bom.search');

        Route::post('/bom/copy', [CogController::class, 'copyBom'])
            ->name('bom.copy');
        Route::delete('/bom/{id}', [CogController::class, 'destroyBom'])
            ->name('bom.destroys');

            Route::get('/bom/create-partial', [CogController::class, 'createPartial']);
        Route::get('/bom/harga-partial', [CogController::class, 'hargaPartial']);
        Route::get('/bom/finishing-partial', [CogController::class, 'finishingPartial']);
        Route::get('/bom/released-partial', [CogController::class, 'releasedPartial']);


        
    });


    /*
    |--------------------------------------------------------------------------
    | MATERIAL PRICE
    |--------------------------------------------------------------------------
    */
    Route::prefix('cog-material-price')->name('cog.material-price.')->group(function () {
        Route::get('/ajax', [CogController::class,'ajaxMaterialPrice'])->name('ajax');

        Route::post('/store', [CogController::class, 'bulkStore'])
            ->name('bulk-store');

        Route::post('/update/{id}', [CogController::class, 'update'])
            ->name('update');

        Route::delete('/delete/{id}', [CogController::class, 'destroy'])
            ->name('destroy');
    });


    /*
    |--------------------------------------------------------------------------
    | MATERIAL FINISHING
    |--------------------------------------------------------------------------
    */
    Route::prefix('cog-material-finishing')->name('cog.material-finishing.')->group(function () {

        Route::post('/bulk-store', [CogController::class, 'bulkStoreFinishing'])
            ->name('bulk-store');

        Route::post('/update/{id}', [CogController::class, 'updateFinishing'])
            ->name('update');

        Route::delete('/delete/{id}', [CogController::class, 'destroyFinishing'])
            ->name('destroy');
    });

 Route::get('/it-dashboard', [ITController::class, 'index'])
    ->name('it.index');
Route::get('/it-dashboard/data', [ITController::class, 'data'])
    ->name('it.data');



    Route::middleware(['auth'])->group(function () { 
        Route::get( '/monitoring-invoice', [MonitoringInvoiceController::class, 'index'] )->name('monitoring-invoice.index'); 
        Route::post( '/monitoring-invoice', [MonitoringInvoiceController::class, 'store'] )->name('monitoring-invoice.store'); 
        Route::put( '/monitoring-invoice/{id}', [MonitoringInvoiceController::class, 'update'] )->name('monitoring-invoice.update'); 
        Route::delete( '/monitoring-invoice/{id}', [MonitoringInvoiceController::class, 'destroy'] )->name('monitoring-invoice.destroy'); });
                Route::post(
    '/monitoring-finishing/invoice-lama/store',
    [MonitoringInvoiceController::class, 'storeInvoiceLama']
)->name('monitoring-finishing.invoice-lama.store');





  // UPAH
  // UPAH
Route::prefix('upah')
    ->name('upah.')
    ->group(function () {

        /*
        |--------------------------------------------------------------------------
        | MASTER UPAH BORONGAN
        |--------------------------------------------------------------------------
        */

        Route::get('/', [
            UpahController::class,
            'index'
        ])->name('index');

        Route::delete('/transaksi/{id}', [
            UpahController::class,
            'destroyT'
        ])->name('upah.transaksi.destroyss');

        Route::post('/', [
            UpahController::class,
            'store'
        ])->name('store');

        Route::get('/ajax/articles', [
            UpahController::class,
            'searchArticle'
        ])->name('ajax.articles');

        Route::get('/transaksi/search-po', [
            UpahController::class,
            'searchPoByArticle'
        ])->name('transaksi.search.po');

        Route::get('/{upah}/data', [
            UpahController::class,
            'editData'
        ])->name('data');

        Route::put('/{upah}', [
            UpahController::class,
            'update'
        ])->name('update');

        Route::delete('/{upah}', [
            UpahController::class,
            'destroy'
        ])->name('destroy');


        /*
        |--------------------------------------------------------------------------
        | MASTER UPAH BORONGAN - MASS
        |--------------------------------------------------------------------------
        */

        Route::post('/mass', [
            UpahController::class,
            'storeMass'
        ])->name('mass.store');


        /*
        |--------------------------------------------------------------------------
        | TRANSAKSI UPAH
        |--------------------------------------------------------------------------
        */

        Route::get('/transaksi', [
            UpahController::class,
            'upah'
        ])->name('transaksi');


        /*
        |--------------------------------------------------------------------------
        | SEARCH ARTICLE TRANSAKSI
        |--------------------------------------------------------------------------
        */

        Route::get('/transaksi/search-article', [
            UpahController::class,
            'searchUpahArticle'
        ])->name('transaksi.search.article');


        /*
        |--------------------------------------------------------------------------
        | SEARCH PEKERJAAN
        |--------------------------------------------------------------------------
        */

        Route::get('/transaksi/search-pekerjaan', [
            UpahController::class,
            'searchPekerjaan'
        ])->name('transaksi.search.pekerjaan');


        /*
        |--------------------------------------------------------------------------
        | STORE TRANSAKSI NORMAL
        |--------------------------------------------------------------------------
        */

        Route::post('/transaksi', [
            UpahController::class,
            'storeUpah'
        ])->name('transaksi.store');

Route::get('/transaksi/check-qty', [
    UpahController::class,
    'checkQtyUpah'
])->name('transaksi.check.qty');
        /*
        |--------------------------------------------------------------------------
        | UPDATE TRANSAKSI NORMAL
        |--------------------------------------------------------------------------
        */

        Route::put('/transaksi/{id}', [
            UpahController::class,
            'updateUpah'
        ])->name('transaksi.update');


        /*
        |--------------------------------------------------------------------------
        | STORE TRANSAKSI MASS
        |--------------------------------------------------------------------------
        */

        Route::post('/transaksi/mass', [
            UpahController::class,
            'storeMassUpah'
        ])->name('transaksi.mass.store');


        Route::get('/export', [
            UpahController::class,
            'export'
        ])->name('upah.transaksi.export');
    });
    




// subkon 
Route::prefix('subkon')
    ->name('subkon.')
    ->group(function () {

        Route::get('/', [
            SubkonController::class,
            'index'
        ])->name('index');

        // AJAX
        Route::get('/ajax/articles', [
            SubkonController::class,
            'searchArticle'
        ])->name('ajax.articles');

        Route::get('/ajax/suppliers', [
            SubkonController::class,
            'searchSupplier'
        ])->name('ajax.suppliers');

        Route::get('/ajax/kategori', [
            SubkonController::class,
            'searchKategori'
        ])->name('ajax.kategori');

        // Store dari modal
        Route::post('/', [
            SubkonController::class,
            'store'
        ])->name('store');

        Route::get('/{subkon}/data', [
            SubkonController::class,
            'editData'
        ])->name('edit');

        Route::put('/{subkon}', [
            SubkonController::class,
            'update'
        ])->name('update');

        Route::delete('/{subkon}', [
            SubkonController::class,
            'destroy'
        ])->name('destroy');

        Route::get('/{subkon}/timeline', [
            SubkonController::class,
            'timeline'
        ])->name('timeline');
    });
    
 // finance export 
    Route::get(
    '/payment-request-saved/{id}/export',
    [SpkController::class, 'exportPengajuanSpk']
)->name('payment-request-saved.export');
Route::get(
    '/payment-request/export-all',
    [SpkController::class, 'exportAllPaymentRequest']
)->name('payment-request.export-all');

Route::get('monitoring-barang-masuk', [ProduksiMnController::class, 'test'])->name('produksi.test');
Route::get(
    '/produksi/mn/data',
    [ProduksiMnController::class, 'data']
)->name('produksi.mn.data');




Route::delete(
    '/laporan/transaksi/{id}',
    [LaporanController::class, 'deleteTransaksi']
)->name('laporan.transaksi.delete');


Route::post(
    '/spk/bahan-baku/keterangan',
    [SpkController::class, 'updateBahanBakuKeterangan']
)->name('spk.bahan-baku.keterangan');

});
