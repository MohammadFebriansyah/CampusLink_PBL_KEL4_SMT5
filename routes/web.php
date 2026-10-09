<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterLecturerController;
use App\Http\Controllers\Auth\RegisterStudentController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'index'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);

    Route::get('/register', [RegisterStudentController::class, 'create'])->name('register');
    Route::get('/register/mahasiswa', [RegisterStudentController::class, 'create'])->name('register.mahasiswa');
    Route::post('/register/mahasiswa', [RegisterStudentController::class, 'store'])->name('register.mahasiswa.store');

    Route::get('/register/dosen', [RegisterLecturerController::class, 'create'])->name('register.dosen');
    Route::post('/register/dosen', [RegisterLecturerController::class, 'store'])->name('register.dosen.store');

    Route::get('/register/mitra', function () {
        return view('auth.register-industry');
    })->name('register.industry');
});

Route::post('/logout', [LoginController::class, 'logout'])->name('logout')->middleware('auth');
