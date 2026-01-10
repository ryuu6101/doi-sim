<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admins\LoginController;
use App\Http\Controllers\Admins\SectionController;
use App\Http\Controllers\Admins\EsimController;

Route::get('/', [SectionController::class, 'home'])->name('home.index');
Route::get('/imei-check', [SectionController::class, 'msinCheck'])->name('msin-check.index');

Route::post('/login', [LoginController::class, 'auth'])->name('login.auth');
Route::post('/doi-sim', [EsimController::class, 'doiSim'])->name('doi-sim.post');
Route::post('/lay-ma-sim', [EsimController::class, 'layMaSim'])->name('lay-ma-sim.post');
Route::post('/tai-anh', [EsimController::class, 'taiAnh'])->name('tai-anh.post');
Route::post('/check-msin', [EsimController::class, 'checkMSIN'])->name('check-msin.post');
Route::get('/test', [EsimController::class, 'pdfTest']);