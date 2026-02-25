<?php

use App\Http\Controllers\Api\EsimController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function() {
    Route::post('doi-sim', [EsimController::class, 'doiSim']);
    Route::post('lay-qr', [EsimController::class, 'layQr']);
    Route::post('lay-tttb-v4', [EsimController::class, 'layTTTBaoV4']);
});
