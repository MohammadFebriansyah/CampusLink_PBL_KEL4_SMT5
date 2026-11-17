<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AdminInformasiController;
use App\Http\Controllers\AdminKeahlianController;
use App\Http\Controllers\AdminMitraController;
use App\Http\Controllers\AdminPenggunaController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\BimbinganController;
use App\Http\Controllers\DasborController;
use App\Http\Controllers\DirektoriController;
use App\Http\Controllers\FotoProfilController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InformasiController;
use App\Http\Controllers\MitraDashboardController;
use App\Http\Controllers\MitraInformasiController;
use App\Http\Controllers\MitraProfilController;
use App\Http\Controllers\PendaftaranController;
use App\Http\Controllers\PeninjauanController;
use App\Http\Controllers\ProfilController;
use App\Http\Controllers\RiwayatProfilController;
use App\Http\Middleware\EnsureRole;
use Illuminate\Support\Facades\Route;

// BUG_CL_0009 – Batasi panjang {id} agar ID yang melebihi bigint PostgreSQL (> 18 digit)
// tidak diteruskan ke query dan tidak memicu SQLSTATE[22003].
// Pola [0-9]{1,18} cukup mencakup seluruh rentang bigint positif (0–9223372036854775807)
// tanpa mengandalkan validasi numerik level database.
Route::pattern('id', '[0-9]{1,18}');

Route::get('/', HomeController::class)->name('home');

// Daftar dan detail informasi. Semua peran yang sudah masuk boleh membaca; tamu diarahkan ke login.
// EnsureRole dipakai (bukan hanya auth) agar akun yang dinonaktifkan saat sesi berjalan langsung ditolak.
Route::middleware(['auth', EnsureRole::class.':mahasiswa,dosen,mitra,admin'])->group(function () {
    foreach (['magang', 'lomba', 'beasiswa'] as $kategori) {
        Route::get("/{$kategori}", [InformasiController::class, 'index'])
            ->defaults('kategori', $kategori)->name("{$kategori}.index");
        Route::get("/{$kategori}/{id}", [InformasiController::class, 'show'])
            ->defaults('kategori', $kategori)->name("{$kategori}.show");
    }
});

// Direktori hanya untuk mahasiswa dan dosen (UC-06). Mitra dan admin tidak diberi akses ke data mahasiswa.
Route::middleware(['auth', EnsureRole::class.':mahasiswa,dosen'])->group(function () {
    Route::get('/direktori', [DirektoriController::class, 'index'])->name('direktori.index');
    Route::get('/direktori/{id}', [DirektoriController::class, 'show'])->name('direktori.show');
});

// Login (GET/POST /masuk) dan logout (POST /keluar) didaftarkan Laravel Fortify: route login, login.store,
// logout. Lihat config/fortify.php dan App\Providers\FortifyServiceProvider.

// Tamu saja: pengguna yang sudah masuk dialihkan keluar dari halaman ini.
// Registrasi email: data ditahan di sesi, akun baru dibuat (CreateNewUser) setelah kode OTP benar.
Route::middleware('guest')->group(function () {
    Route::get('/daftar', [RegisterController::class, 'create'])->name('register');
    Route::post('/daftar', [RegisterController::class, 'store'])->name('register.store');
    Route::get('/daftar/verifikasi', [RegisterController::class, 'verifikasi'])->name('register.verifikasi');
    Route::post('/daftar/verifikasi', [RegisterController::class, 'konfirmasi'])
        ->middleware('throttle:10,1')->name('register.konfirmasi');
    Route::post('/daftar/verifikasi/kirim-ulang', [RegisterController::class, 'kirimUlang'])->name('register.kirim-ulang');

    // Melengkapi pendaftaran setelah verifikasi Google (NIM/NIDN, program studi).
    Route::get('/daftar/google', [GoogleController::class, 'lengkapi'])->name('google.lengkapi');
    Route::post('/daftar/google', [GoogleController::class, 'simpan'])->name('google.simpan');
});

// Tanpa middleware guest: pengguna yang sudah masuk juga memakainya untuk menghubungkan akun Google.
Route::get('/auth/google', [GoogleController::class, 'redirect'])->name('google.redirect');
Route::get('/auth/google/callback', [GoogleController::class, 'callback'])->name('google.callback');

// Foto profil dikirim lewat aplikasi agar tidak bergantung pada `php artisan storage:link`.
Route::get('/foto-profil/{berkas}', FotoProfilController::class)
    ->middleware('auth')
    ->where('berkas', '[A-Za-z0-9]{1,100}\.(jpg|jpeg|png|webp)')
    ->name('foto.profil');

// Mahasiswa: mendaftar dengan CV (kegiatan yang didaftar lewat CampusLink) dan mengajukan bimbingan ke dosen.
Route::middleware(['auth', EnsureRole::class.':mahasiswa'])->group(function () {
    Route::get('/kegiatan-saya', [PendaftaranController::class, 'index'])->name('kegiatan.index');
    Route::post('/{kategori}/{id}/daftar', [PendaftaranController::class, 'store'])
        ->where('kategori', 'magang|lomba|beasiswa')->name('pendaftaran.store');
    Route::delete('/pendaftaran/{id}', [PendaftaranController::class, 'destroy'])->name('pendaftaran.destroy');

    Route::post('/{kategori}/{id}/bimbingan', [BimbinganController::class, 'store'])
        ->where('kategori', 'magang|lomba')->name('bimbingan.store');
    Route::delete('/bimbingan-saya/{id}', [BimbinganController::class, 'destroy'])->name('bimbingan.destroy');

    // Sertifikasi, pelatihan, proyek, pencapaian di profil. Kepemilikan diperiksa di controller (404 untuk milik orang lain).
    Route::post('/profil/riwayat', [RiwayatProfilController::class, 'store'])->name('profil.riwayat.store');
    Route::put('/profil/riwayat/{id}', [RiwayatProfilController::class, 'update'])->name('profil.riwayat.update');
    Route::delete('/profil/riwayat/{id}', [RiwayatProfilController::class, 'destroy'])->name('profil.riwayat.destroy');
});

// Bukti diterima (kegiatan luar) untuk bimbingan: mahasiswa pengaju atau dosen yang dituju. Diperiksa di controller.
Route::get('/bimbingan/{id}/bukti', [BimbinganController::class, 'bukti'])
    ->middleware(['auth', EnsureRole::class.':mahasiswa,dosen'])->name('bimbingan.bukti');

// CV pendaftar: pemilik, mitra pembuat informasi, atau admin (kegiatan kampus). Diperiksa di controller.
Route::get('/pendaftaran/{id}/cv', [PendaftaranController::class, 'cv'])
    ->middleware(['auth', EnsureRole::class.':mahasiswa,mitra,admin'])->name('pendaftaran.cv');

// Dosen: permintaan bimbingan dari mahasiswa.
Route::middleware(['auth', EnsureRole::class.':dosen'])->group(function () {
    Route::get('/bimbingan', [BimbinganController::class, 'index'])->name('bimbingan.index');
    Route::patch('/bimbingan/{id}/terima', [BimbinganController::class, 'terima'])->name('bimbingan.terima');
    Route::patch('/bimbingan/{id}/tolak', [BimbinganController::class, 'tolak'])->name('bimbingan.tolak');
});

// Mahasiswa dan dosen: dasbor panel (ringkasan, hanya membaca) dan profil. Halaman /profil langsung berisi formulir ubah profil.
Route::middleware(['auth', EnsureRole::class.':mahasiswa,dosen'])->group(function () {
    Route::get('/dasbor', DasborController::class)->name('dasbor');
    Route::get('/profil', [ProfilController::class, 'show'])->name('profil.show');
    Route::put('/profil', [ProfilController::class, 'update'])->name('profil.update');
});

// Mitra: dasbor, profil organisasi, dan kelola informasi milik sendiri.
// EnsureRole memastikan akun aktif dan sudah diverifikasi admin; kepemilikan data diperiksa Policy.
Route::middleware(['auth', EnsureRole::class.':mitra'])->prefix('mitra')->group(function () {
    Route::get('/', MitraDashboardController::class)->name('mitra.dashboard');
    Route::get('/profil', [MitraProfilController::class, 'show'])->name('mitra.profil.show');
    Route::put('/profil', [MitraProfilController::class, 'update'])->name('mitra.profil.update');

    // Pendaftar informasi milik mitra ini
    Route::get('/pendaftar', [PeninjauanController::class, 'index'])->defaults('panel', 'mitra')->name('mitra.pendaftar.index');
    Route::get('/pendaftar/{id}', [PeninjauanController::class, 'show'])->defaults('panel', 'mitra')->name('mitra.pendaftar.show');
    Route::patch('/pendaftar/{id}/terima', [PeninjauanController::class, 'terima'])->name('mitra.pendaftar.terima');
    Route::patch('/pendaftar/{id}/tolak', [PeninjauanController::class, 'tolak'])->name('mitra.pendaftar.tolak');

    Route::prefix('{kategori}')->where(['kategori' => 'magang|lomba|beasiswa'])->group(function () {
        Route::get('/', [MitraInformasiController::class, 'index'])->name('mitra.informasi.index');
        Route::get('/buat', [MitraInformasiController::class, 'create'])->name('mitra.informasi.create');
        Route::post('/', [MitraInformasiController::class, 'store'])->name('mitra.informasi.store');
        Route::get('/{id}/ubah', [MitraInformasiController::class, 'edit'])->name('mitra.informasi.edit');
        Route::put('/{id}', [MitraInformasiController::class, 'update'])->name('mitra.informasi.update');
        Route::delete('/{id}', [MitraInformasiController::class, 'destroy'])->name('mitra.informasi.destroy');
    });
});

// Panel admin (FA-01 sampai FA-09). Akun admin dibuat pengelola sistem (seeder), bukan lewat registrasi.
Route::middleware(['auth', EnsureRole::class.':admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', AdminDashboardController::class)->name('dashboard');

    // Verifikasi mitra
    Route::get('/mitra', [AdminMitraController::class, 'index'])->name('mitra.index');
    Route::patch('/mitra/{id}/setujui', [AdminMitraController::class, 'setujui'])->name('mitra.setujui');
    Route::patch('/mitra/{id}/tolak', [AdminMitraController::class, 'tolak'])->name('mitra.tolak');

    // Pengguna (aksi dibuat sebagai route terpisah agar jelas dan tanpa masukan yang bisa dimanipulasi)
    Route::get('/pengguna', [AdminPenggunaController::class, 'index'])->name('pengguna.index');
    Route::get('/pengguna/{id}', [AdminPenggunaController::class, 'show'])->name('pengguna.show');
    Route::get('/pengguna/{id}/ubah', [AdminPenggunaController::class, 'edit'])->name('pengguna.edit');
    Route::put('/pengguna/{id}', [AdminPenggunaController::class, 'update'])->name('pengguna.update');
    Route::patch('/pengguna/{id}/aktifkan', [AdminPenggunaController::class, 'aktifkan'])->name('pengguna.aktifkan');
    Route::patch('/pengguna/{id}/nonaktifkan', [AdminPenggunaController::class, 'nonaktifkan'])->name('pengguna.nonaktifkan');
    Route::patch('/pengguna/{id}/sembunyikan-profil', [AdminPenggunaController::class, 'sembunyikanProfil'])->name('pengguna.sembunyikan');
    Route::patch('/pengguna/{id}/tampilkan-profil', [AdminPenggunaController::class, 'tampilkanProfil'])->name('pengguna.tampilkan');

    // Pendaftar kegiatan kampus (informasi buatan admin yang didaftar lewat CampusLink)
    Route::get('/pendaftar', [PeninjauanController::class, 'index'])->defaults('panel', 'admin')->name('pendaftar.index');
    Route::get('/pendaftar/{id}', [PeninjauanController::class, 'show'])->defaults('panel', 'admin')->name('pendaftar.show');
    Route::patch('/pendaftar/{id}/terima', [PeninjauanController::class, 'terima'])->name('pendaftar.terima');
    Route::patch('/pendaftar/{id}/tolak', [PeninjauanController::class, 'tolak'])->name('pendaftar.tolak');

    // Data master keahlian
    Route::get('/keahlian', [AdminKeahlianController::class, 'index'])->name('keahlian.index');
    Route::post('/keahlian', [AdminKeahlianController::class, 'store'])->name('keahlian.store');
    Route::get('/keahlian/{id}/ubah', [AdminKeahlianController::class, 'edit'])->name('keahlian.edit');
    Route::put('/keahlian/{id}', [AdminKeahlianController::class, 'update'])->name('keahlian.update');
    Route::delete('/keahlian/{id}', [AdminKeahlianController::class, 'destroy'])->name('keahlian.destroy');

    // Informasi magang, lomba, beasiswa: kelola dan moderasi
    Route::prefix('{kategori}')->where(['kategori' => 'magang|lomba|beasiswa'])->group(function () {
        Route::get('/', [AdminInformasiController::class, 'index'])->name('informasi.index');
        Route::get('/buat', [AdminInformasiController::class, 'create'])->name('informasi.create');
        Route::post('/', [AdminInformasiController::class, 'store'])->name('informasi.store');
        Route::get('/{id}/ubah', [AdminInformasiController::class, 'edit'])->name('informasi.edit');
        Route::put('/{id}', [AdminInformasiController::class, 'update'])->name('informasi.update');
        Route::delete('/{id}', [AdminInformasiController::class, 'destroy'])->name('informasi.destroy');
        Route::patch('/{id}/sembunyikan', [AdminInformasiController::class, 'sembunyikan'])->name('informasi.sembunyikan');
        Route::patch('/{id}/tampilkan', [AdminInformasiController::class, 'tampilkan'])->name('informasi.tampilkan');
    });
});
