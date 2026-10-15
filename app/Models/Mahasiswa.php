<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Mahasiswa extends Model
{
    protected $table = 'mahasiswa';

    protected $fillable = [
        'nim',
        'kelas',
        'jurusan',
        'program_studi',
        'angkatan',
        'foto',
        'kontak',
        'tentang',
        'sertifikasi',
        'pelatihan',
        'proyek',
        'pencapaian',
        'tampil_di_direktori',
    ];

    protected function casts(): array
    {
        return [
            'angkatan' => 'integer',
            'tampil_di_direktori' => 'boolean',
            'disembunyikan_admin' => 'boolean',
        ];
    }

    /**
     * URL foto profil, atau null bila belum ada. Foto dikirim lewat route foto.profil
     * (FotoProfilController), jadi tidak perlu `php artisan storage:link` dan tidak
     * bergantung pada APP_URL (127.0.0.1 vs localhost).
     */
    public function fotoUrl(): ?string
    {
        return $this->foto ? route('foto.profil', basename($this->foto)) : null;
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
