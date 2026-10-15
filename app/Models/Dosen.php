<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Dosen extends Model
{
    protected $table = 'dosen';

    protected $fillable = [
        'nidn',
        'jurusan',
        'program_studi',
        'foto',
        'kontak',
        'tentang',
        'bersedia_membimbing_magang',
        'bersedia_membimbing_lomba',
        'tampil_di_direktori',
    ];

    protected function casts(): array
    {
        return [
            'bersedia_membimbing_magang' => 'boolean',
            'bersedia_membimbing_lomba' => 'boolean',
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
