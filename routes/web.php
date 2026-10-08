<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterLecturerController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'index'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);

    Route::get('/register/dosen', [RegisterLecturerController::class, 'create'])->name('register.dosen');
    Route::post('/register/dosen', [RegisterLecturerController::class, 'store'])->name('register.dosen.store');
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');
