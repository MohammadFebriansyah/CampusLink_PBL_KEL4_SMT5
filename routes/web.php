<?php

use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\BimbinganController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InformasiController;
use App\Http\Controllers\PendaftaranController;
use App\Http\Controllers\MitraDashboardController;
use App\Http\Controllers\MitraProfilController;
use App\Http\Middleware\EnsureRole;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

// Daftar dan detail informasi. Semua peran yang sudah masuk boleh membaca; tamu diarahkan ke login.
// EnsureRole dipakai (bukan hanya auth) agar akun yang dinonaktifkan saat sesi berjalan langsung ditolak.
Route::middleware(['auth', EnsureRole::class . ':mahasiswa,dosen,mitra,admin'])->group(function () {
    foreach (['magang', 'lomba', 'beasiswa'] as $kategori) {
        Route::get("/{$kategori}", [InformasiController::class, 'index'])
            ->defaults('kategori', $kategori)->name("{$kategori}.index");
        Route::get("/{$kategori}/{id}", [InformasiController::class, 'show'])
            ->defaults('kategori', $kategori)->whereNumber('id')->name("{$kategori}.show");
    }
});

// Direktori hanya untuk mahasiswa dan dosen (UC-06).
Route::middleware(['auth', EnsureRole::class . ':mahasiswa,dosen'])->group(function () {
    Route::get('/direktori', fn() => redirect()->route('home'))->name('direktori.index');
    Route::get('/direktori/{id}', fn() => redirect()->route('home'))->whereNumber('id')->name('direktori.show');
});

// Mahasiswa: mendaftar dengan CV (kegiatan yang didaftar lewat CampusLink) dan mengajukan bimbingan ke dosen.
Route::middleware(['auth', EnsureRole::class . ':mahasiswa'])->group(function () {
    Route::get('/kegiatan-saya', [PendaftaranController::class, 'index'])->name('kegiatan.index');
    Route::post('/{kategori}/{id}/daftar', [PendaftaranController::class, 'store'])
        ->where('kategori', 'magang|lomba|beasiswa')->whereNumber('id')->name('pendaftaran.store');
    Route::delete('/pendaftaran/{id}', [PendaftaranController::class, 'destroy'])->whereNumber('id')->name('pendaftaran.destroy');

    Route::post('/{kategori}/{id}/bimbingan', [BimbinganController::class, 'store'])
        ->where('kategori', 'magang|lomba')->whereNumber('id')->name('bimbingan.store');
    Route::delete('/bimbingan-saya/{id}', [BimbinganController::class, 'destroy'])->whereNumber('id')->name('bimbingan.destroy');
});

// Bukti diterima (kegiatan luar) untuk bimbingan: mahasiswa pengaju atau dosen yang dituju.
Route::get('/bimbingan/{id}/bukti', [BimbinganController::class, 'bukti'])
    ->middleware(['auth', EnsureRole::class . ':mahasiswa,dosen'])->whereNumber('id')->name('bimbingan.bukti');

// CV pendaftar: pemilik, mitra pembuat informasi, atau admin (kegiatan kampus).
Route::get('/pendaftaran/{id}/cv', [PendaftaranController::class, 'cv'])
    ->middleware(['auth', EnsureRole::class . ':mahasiswa,mitra,admin'])->whereNumber('id')->name('pendaftaran.cv');

// Dosen: permintaan bimbingan dari mahasiswa.
Route::middleware(['auth', EnsureRole::class . ':dosen'])->group(function () {
    Route::get('/bimbingan', [BimbinganController::class, 'index'])->name('bimbingan.index');
    Route::patch('/bimbingan/{id}/terima', [BimbinganController::class, 'terima'])->whereNumber('id')->name('bimbingan.terima');
    Route::patch('/bimbingan/{id}/tolak', [BimbinganController::class, 'tolak'])->whereNumber('id')->name('bimbingan.tolak');
});

// Profil dan Dasbor
Route::middleware('auth')->group(function () {
    Route::get('/profil', fn() => redirect()->route('home'))->name('profil.show');
    Route::get('/mitra', fn() => redirect()->route('home'))->name('mitra.dashboard');
    Route::get('/mitra/profil', fn() => redirect()->route('home'))->name('mitra.profil.show');
    Route::get('/admin', fn() => redirect()->route('home'))->name('admin.dashboard');
});

// Tanpa middleware guest: pengguna yang sudah masuk juga memakainya untuk menghubungkan akun Google.
Route::get('/auth/google', [GoogleController::class, 'redirect'])->name('google.redirect');
Route::get('/auth/google/callback', [GoogleController::class, 'callback'])->name('google.callback');

Route::middleware('guest')->group(function () {
    // Canonical routes sesuai referensi
    Route::get('/masuk', [LoginController::class, 'create'])->name('login');
    Route::post('/masuk', [LoginController::class, 'store'])->name('login.store');

    // Kompatibilitas URL lama tanpa duplikasi nama route
    Route::get('/login', [LoginController::class, 'create']);
    Route::post('/login', [LoginController::class, 'store']);

    // Registrasi terpadu
    Route::get('/daftar', [RegisterController::class, 'create'])->name('register');
    Route::post('/daftar', [RegisterController::class, 'store'])->name('register.store');
    Route::get('/daftar/verifikasi', [RegisterController::class, 'verifikasi'])->name('register.verifikasi');
    Route::post('/daftar/verifikasi', [RegisterController::class, 'konfirmasi'])
        ->middleware('throttle:10,1')->name('register.konfirmasi');
    Route::post('/daftar/verifikasi/kirim-ulang', [RegisterController::class, 'kirimUlang'])->name('register.kirim-ulang');

    // Kompatibilitas URL /register lama
    Route::get('/register', fn () => redirect()->route('register'));
    Route::get('/register/mahasiswa', fn () => redirect()->route('register', ['peran' => 'mahasiswa']));
    Route::get('/register/dosen', fn () => redirect()->route('register', ['peran' => 'dosen']));
    Route::get('/register/mitra', fn () => redirect()->route('register', ['peran' => 'mitra']));

    // Melengkapi pendaftaran setelah verifikasi Google (NIM/NIDN, program studi).
    Route::get('/daftar/google', [GoogleController::class, 'lengkapi'])->name('google.lengkapi');
    Route::post('/daftar/google', [GoogleController::class, 'simpan'])->name('google.simpan');
});

Route::post('/keluar', [LoginController::class, 'destroy'])->name('logout')->middleware('auth');
Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth');

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

