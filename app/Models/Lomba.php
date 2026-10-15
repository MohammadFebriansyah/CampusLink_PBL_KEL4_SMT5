<?php

namespace App\Models;

use App\Models\Concerns\PunyaPendaftaran;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lomba extends Model
{
    use PunyaPendaftaran;

    public const STATUS_TAYANG = 'tayang';
    public const STATUS_DISEMBUNYIKAN = 'disembunyikan';

    protected $table = 'lomba';

    // status (moderasi) tidak ada di sini: hanya admin yang boleh mengubahnya.
    protected $fillable = [
        'judul',
        'penyelenggara',
        'deskripsi',
        'persyaratan',
        'tingkat',
        'tanggal_mulai',
        'tanggal_selesai',
        'batas_pendaftaran',
        'kontak',
        'tautan_pendaftaran',
        'cara_daftar',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
            'batas_pendaftaran' => 'date',
        ];
    }

    /** Pembuat informasi: akun mitra atau admin. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
