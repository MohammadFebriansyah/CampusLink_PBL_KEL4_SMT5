<?php

use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterLecturerController;
use App\Http\Controllers\Auth\RegisterStudentController;
use App\Http\Controllers\FotoProfilController;
use App\Http\Middleware\EnsureRole;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::middleware('guest')->group(function () {
    // Canonical routes sesuai referensi
    Route::get('/masuk', [LoginController::class, 'create'])->name('login');
    Route::post('/masuk', [LoginController::class, 'store'])->name('login.store');

    // Kompatibilitas URL lama tanpa duplikasi nama route
    Route::get('/login', [LoginController::class, 'create']);
    Route::post('/login', [LoginController::class, 'store']);

    // Melengkapi pendaftaran setelah verifikasi Google (NIM/NIDN, program studi).
    Route::get('/daftar/google', [GoogleController::class, 'lengkapi'])->name('google.lengkapi');
    Route::post('/daftar/google', [GoogleController::class, 'simpan'])->name('google.simpan');

    // Registrasi milik anggota tim lain (dipertahankan utuh)
    Route::get('/register', [RegisterStudentController::class, 'create'])->name('register');
    Route::get('/register/mahasiswa', [RegisterStudentController::class, 'create'])->name('register.mahasiswa');
    Route::post('/register/mahasiswa', [RegisterStudentController::class, 'store'])->name('register.mahasiswa.store');

    Route::get('/register/dosen', [RegisterLecturerController::class, 'create'])->name('register.dosen');
    Route::post('/register/dosen', [RegisterLecturerController::class, 'store'])->name('register.dosen.store');

    Route::get('/register/mitra', function () {
        return view('auth.register-industry');
    })->name('register.industry');
});

// Tanpa middleware guest: pengguna yang sudah masuk juga memakainya untuk menghubungkan akun Google.
Route::get('/auth/google', [GoogleController::class, 'redirect'])->name('google.redirect');
Route::get('/auth/google/callback', [GoogleController::class, 'callback'])->name('google.callback');

// Foto profil dikirim lewat aplikasi agar tidak bergantung pada `php artisan storage:link`.
Route::get('/foto-profil/{berkas}', FotoProfilController::class)
    ->middleware('auth')
    ->where('berkas', '[A-Za-z0-9]{1,100}\.(jpg|jpeg|png|webp)')
    ->name('foto.profil');

// Logout canonical dan kompatibilitas URL lama
Route::post('/keluar', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');
Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth');
