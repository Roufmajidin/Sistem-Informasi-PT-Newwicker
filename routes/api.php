<?php

use App\Http\Controllers\AbsenController;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->post('/absen', [AbsenController::class, 'absen']);
Route::middleware('auth:sanctum')->post('/ajukanizin', [AbsenController::class, 'ajukanIzin']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
});
