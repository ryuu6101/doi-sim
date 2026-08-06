<?php

use App\Http\Controllers\Admins\EsimController;
use App\Http\Controllers\Admins\GoogleController;
use App\Http\Controllers\Admins\GoogleSheetController;
use App\Http\Controllers\Admins\LoginController;
use App\Http\Controllers\Admins\SectionController;
use Illuminate\Support\Facades\Route;

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
    Route::get('/swap-imei', [SectionController::class, 'swapIMEI'])->name('swap-imei.index');
    Route::get('/toggle-gprs', [SectionController::class, 'toggleGPRS'])->name('toggle-gprs.index');
    Route::get('/toggle-ioc', [SectionController::class, 'toggleIOC'])->name('toggle-ioc.index');
    Route::get('/toggle-smt-smo', [SectionController::class, 'toggleSmtSmo'])->name('toggle-smt-smo.index');
    Route::get('/esim-report/import', [SectionController::class, 'importEsimReport'])->name('esim-report.import.index');
    Route::get('/esim-report/statistical', [SectionController::class, 'listEsimReport'])->name('esim-report.statistical.index');
    Route::get('/toggle-services', [SectionController::class, 'toggleServices'])->name('toggle-services.index');
    Route::get('/subscriber-check', [SectionController::class, 'subscriberCheck'])->name('subscriber-check.index');
    Route::get('/ccos/subscriber-check', [SectionController::class, 'subscriberCheckCcos'])->name('subscriber-check-ccos.index');
    
    Route::post('/ccbs-login', [EsimController::class, 'ccbsLogin'])->name('ccbs-login.post');
    Route::post('/doi-sim', [EsimController::class, 'doiSim'])->name('doi-sim.post');
    Route::post('/lay-ma-sim', [EsimController::class, 'layMaSim'])->name('lay-ma-sim.post');
    Route::post('/tai-anh', [EsimController::class, 'taiAnh'])->name('tai-anh.post');
    Route::post('/check-msin', [EsimController::class, 'checkMSIN'])->name('check-msin.post');
    Route::post('/lay-tttb-v4', [EsimController::class, 'layTTTBaoV4'])->name('lay-tttb-v4.post');
    Route::post('/lay-tttb', [EsimController::class, 'layTTTBao'])->name('lay-tttb.post');
    Route::post('/save-cookie', [EsimController::class, 'saveCookie'])->name('save-cookie.post');
    Route::post('/tra-cuu-mi', [EsimController::class, 'traCuuMI'])->name('tra-cuu-mi.post');
    Route::post('/dao-sim', [EsimController::class, 'daoSim'])->name('dao-sim.post');
    Route::post('/lay-dvu', [EsimController::class, 'layDVu'])->name('lay-dvu.post');
    Route::post('/dm-dvu', [EsimController::class, 'dmDVu'])->name('dm-dvu.post');
    Route::post('/lay-ioc', [EsimController::class, 'layIOC'])->name('lay-ioc.post');
    Route::post('/catmo-ioc', [EsimController::class, 'catmoIOC'])->name('catmo-ioc.post');
    Route::post('/lay-bc-esim', [EsimController::class, 'layBcEsim'])->name('lay-bc-esim.post');
    Route::post('/send-welcome-sms', [EsimController::class, 'sendWelcomeMessage'])->name('send-welcome-sms.post');
    Route::post('/kich-hoat-gprs', [EsimController::class, 'kichHoatGPRS'])->name('kich-hoat-gprs.post');
    Route::post('/ktra-trung-tb', [EsimController::class, 'kiemTraTrungTB'])->name('ktra-trung-tb.post');
    Route::post('/kiem-tra-tttb', [EsimController::class, 'kiemTraTTTBao'])->name('kiem-tra-tttb.post');
    Route::post('/lay-lsu-tbao', [EsimController::class, 'layLSuTBao'])->name('lay-lsu-tbao.post');
    Route::post('/lay-lsu-3g', [EsimController::class, 'layLSu3g'])->name('lay-lsu-3g.post');
    Route::post('/lay-tt-khtb', [EsimController::class, 'layTTKhTb'])->name('lay-tt-khtb.post');
    Route::post('/tra-cuu-tttb-ccos', [EsimController::class, 'traCuuTTTBaoCCOS'])->name('tra-cuu-tttb-ccos.post');

    Route::get('/auth/google/redirect', [GoogleController::class, 'redirect'])->name('google.redirect');
    Route::get('/auth/google/callback', [GoogleController::class, 'callback'])->name('google.callback');
    Route::post('/google-sheet/read', [GoogleSheetController::class, 'read'])->name('google-sheet.read.post');
});