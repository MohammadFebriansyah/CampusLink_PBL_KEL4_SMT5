<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Mengirim foto profil dari storage/app/public/foto-profil lewat aplikasi.
 * Dengan begini foto tetap tampil walau `php artisan storage:link` belum dijalankan
 * atau gagal (sering terjadi di Windows). Hanya untuk pengguna yang sudah masuk,
 * sama seperti direktori dan halaman profil yang menampilkan foto.
 */
class FotoProfilController extends Controller
{
    public function __invoke(string $berkas): BinaryFileResponse
    {
        // Nama berkas sudah dibatasi pola route (huruf/angka + ekstensi gambar), jadi tidak bisa keluar folder.
        $path = 'foto-profil/' . $berkas;
        $disk = Storage::disk('public');

        abort_unless($disk->exists($path), 404);

        return response()->file($disk->path($path), [
            // Nama berkas acak dan berganti setiap unggah, jadi aman disimpan lama di cache browser.
            'Cache-Control' => 'private, max-age=604800',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
