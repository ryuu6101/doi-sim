<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admins\LoginController;
use App\Http\Controllers\Admins\SectionController;
use App\Http\Controllers\Admins\FunctionController;

Route::get('/', [SectionController::class, 'home'])->name('home.index');

Route::post('/login', [LoginController::class, 'auth'])->name('login.auth');
Route::post('/doi-sim', [FunctionController::class, 'doiSim'])->name('doi-sim.post');