<?php

use App\Http\Controllers\AbsenController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\BuyerController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\QcController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PengajuanController;
use App\Http\Controllers\EmployeeLoanController;
Route::get('/ping', function () {
    return response()->json([
        'ok' => true
    ]);
});
Route::middleware('auth:sanctum')->get(
    '/qc/po',
    [QcController::class, 'getPoDashboard']
);
Route::middleware('auth:sanctum')->get('/cek-auth', function () {
    return response()->json([
        'id' => auth()->id(),
    ]);
});
// qc
Route::prefix('qc')->middleware('auth:sanctum')->group(function () {

    Route::get('/batches/{jenis}/{detailPoId}/{poId}', [QcController::class, 'getData']);
    Route::get('/po', [QcController::class, 'getPo']);
    Route::get('/checkpoint/{kategoriName}', [QcController::class, 'getCheckpointData']);
    Route::get('/detailpo/{divisi}/{poId}', [
            QcController::class,
            'detailPo'
        ]);
    Route::post('/store/{kategoriName}', [QcController::class, 'insertInspection']);

    Route::get('/detail/{id}', [QcController::class, 'show']);
    Route::get('/timeline', [QcController::class, 'timeline']);
        Route::get('/date', [QcController::class, 'getDate']);

});

Route::middleware('auth:sanctum')->post('/absen', [AbsenController::class, 'absen']);
Route::middleware('auth:sanctum')->post('/absen-lembur', [AbsenController::class, 'absenLembur']);
Route::middleware('auth:sanctum')->get('/izin-saya', [AbsenController::class, 'izinSaya']);
Route::post('/users/update-mass', [PengajuanController::class, 'updateMassCustom']);

Route::middleware('auth:sanctum')->post('/ajukanizin', [AbsenController::class, 'ajukanIzin']);
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:sanctum')->get('/izin-types', [AbsenController::class, 'getTypes']);


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::get('/absen-me', [AuthController::class, 'absenMe']);
    Route::get('/absen-lembur', [AuthController::class, 'getLemburSaya']);
    Route::get('/locationKantor', [AuthController::class, 'locationCantor']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

// new route
Route::prefix('buyers')->group(function () {
    Route::get('/', [BuyerController::class, 'index']);
    Route::post('/', [BuyerController::class, 'store']);
    Route::get('{id}', [BuyerController::class, 'show']);
    Route::put('{id}', [BuyerController::class, 'update']);
    Route::delete('{id}', [BuyerController::class, 'destroy']);
});


Route::prefix('cart')->group(function () {
    Route::get('/', [CartController::class, 'index']); // ?buyer_id=12
    Route::post('/', [CartController::class, 'store']);
    Route::get('{id}', [CartController::class, 'show']);
    // Route::put('{id}', [CartController::class, 'update']);
        Route::put('{id}/{article_code}', [CartController::class, 'update']);

    Route::delete('{id}', [CartController::class, 'destroy']);
    Route::post('/checkout', [CartController::class, 'checkout']);



});
Route::prefix('products')->group(function () {

    Route::get('{id}', [BuyerController::class, 'product']);
});


Route::middleware('auth:sanctum')->group(function () {

    Route::post(
        '/employee-loans/store',
        [EmployeeLoanController::class, 'store']
    );
    Route::get(
        '/my-loans',
        [EmployeeLoanController::class, 'myLoans']
    );
});
