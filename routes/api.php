<?php

use App\Http\Controllers\Api\EsimController;
use App\Http\Controllers\Api\LoginController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest:santum')->group(function() {
    Route::post('login', [LoginController::class, 'login']);
});

Route::middleware('auth:sanctum')->group(function() {
    Route::post('doi-sim', [EsimController::class, 'doiSim']);
    Route::post('lay-qr', [EsimController::class, 'layQr']);
    Route::post('lay-tttb-v4', [EsimController::class, 'layTTTBaoV4']);

    Route::get('logout', [LoginController::class, 'logout']);
});
