<?php

use App\Http\Controllers\AbsenController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\BuyerController;
use App\Http\Controllers\CartController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->post('/absen', [AbsenController::class, 'absen']);
Route::middleware('auth:sanctum')->post('/ajukanizin', [AbsenController::class, 'ajukanIzin']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::get('/absen-me', [AuthController::class, 'absenMe']);
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
    Route::put('{id}', [CartController::class, 'update']);
    Route::delete('{id}', [CartController::class, 'destroy']);
    Route::post('/checkout', [CartController::class, 'checkout']);

});
