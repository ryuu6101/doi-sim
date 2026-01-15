<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admins\LoginController;
use App\Http\Controllers\Admins\SectionController;
use App\Http\Controllers\Admins\EsimController;

Route::middleware(['guest'])->group(function () {
    Route::get('/login', [LoginController::class, 'getLogin'])->name('login');
    Route::post('/login', [LoginController::class, 'authenticate'])->name('login.post');
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout.post');

Route::middleware(['auth'])->group(function () {
    Route::get('/', [SectionController::class, 'home'])->name('home.index');
    Route::get('/imei-check', [SectionController::class, 'msinCheck'])->name('msin-check.index');
    Route::get('/mobile-check', [SectionController::class, 'mobileCheck'])->name('mobile-check.index');
    Route::get('/ccos/tra-cuu-mi', [SectionController::class, 'miCheck'])->name('mi-check.index');
    
    Route::post('/ccbs-login', [LoginController::class, 'ccbsLogin'])->name('ccbs-login.post');
    Route::post('/doi-sim', [EsimController::class, 'doiSim'])->name('doi-sim.post');
    Route::post('/lay-ma-sim', [EsimController::class, 'layMaSim'])->name('lay-ma-sim.post');
    Route::post('/tai-anh', [EsimController::class, 'taiAnh'])->name('tai-anh.post');
    Route::post('/check-msin', [EsimController::class, 'checkMSIN'])->name('check-msin.post');
    Route::post('/lay-imei', [EsimController::class, 'layIMEI'])->name('lay-imei.post');
    Route::post('/lay-tttb', [EsimController::class, 'layTTTBao'])->name('lay-tttb.post');
    Route::post('/save-cookie', [LoginController::class, 'saveCookie'])->name('save-cookie.post');
    Route::post('/tra-cuu-mi', [EsimController::class, 'traCuuMI'])->name('tra-cuu-mi.post');
});

Route::get('/test', [EsimController::class, 'pdfTest']);