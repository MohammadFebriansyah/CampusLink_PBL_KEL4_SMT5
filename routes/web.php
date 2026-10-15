<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterLecturerController;
use App\Http\Controllers\Auth\RegisterStudentController;
use App\Http\Controllers\MitraDashboardController;
use App\Http\Controllers\MitraProfilController;
use App\Http\Middleware\EnsureRole;
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

// Mitra: dasbor, profil organisasi, dan kelola informasi milik sendiri.
// EnsureRole memastikan akun aktif dan sudah diverifikasi admin; kepemilikan data diperiksa Policy.
Route::middleware(['auth', EnsureRole::class . ':mitra'])->prefix('mitra')->group(function () {
    Route::get('/', MitraDashboardController::class)->name('mitra.dashboard');
    Route::get('/profil', [MitraProfilController::class, 'show'])->name('mitra.profil.show');
    Route::put('/profil', [MitraProfilController::class, 'update'])->name('mitra.profil.update');

    // Pendaftar informasi milik mitra ini
    Route::get('/pendaftar', fn () => redirect()->route('mitra.dashboard'))->defaults('panel', 'mitra')->name('mitra.pendaftar.index');

    Route::prefix('{kategori}')->where(['kategori' => 'magang|lomba|beasiswa'])->group(function () {
        Route::get('/', fn ($kategori) => redirect()->route('mitra.dashboard'))->name('mitra.informasi.index');
        Route::get('/buat', fn ($kategori) => redirect()->route('mitra.dashboard'))->name('mitra.informasi.create');
    });
});

