<?php

namespace App\Models;

use App\Models\Concerns\PunyaPendaftaran;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Beasiswa extends Model
{
    use PunyaPendaftaran;

    public const STATUS_TAYANG = 'tayang';
    public const STATUS_DISEMBUNYIKAN = 'disembunyikan';

    protected $table = 'beasiswa';

    // status (moderasi) tidak ada di sini: hanya admin yang boleh mengubahnya.
    protected $fillable = [
        'judul',
        'penyelenggara',
        'deskripsi',
        'persyaratan',
        'batas_pendaftaran',
        'kontak',
        'tautan_pendaftaran',
        'cara_daftar',
    ];

    protected function casts(): array
    {
        return [
            'batas_pendaftaran' => 'date',
        ];
    }

    /** Pembuat informasi: akun mitra atau admin. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
