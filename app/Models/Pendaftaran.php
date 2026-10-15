<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/** Pendaftaran mahasiswa ke magang/lomba/beasiswa yang cara daftarnya internal. */
class Pendaftaran extends Model
{
    public const STATUS_MENUNGGU = 'menunggu';
    public const STATUS_DITERIMA = 'diterima';
    public const STATUS_DITOLAK = 'ditolak';

    public const LABEL_STATUS = ['menunggu' => 'Menunggu', 'diterima' => 'Diterima', 'ditolak' => 'Ditolak'];

    protected $table = 'pendaftaran';

    // status, catatan, dan peninjau hanya diubah lewat aksi terima/tolak, bukan dari input bebas.
    protected $fillable = ['cv'];

    protected function casts(): array
    {
        return ['ditinjau_pada' => 'datetime'];
    }

    /** Mahasiswa pendaftar. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function informasi(): MorphTo
    {
        return $this->morphTo();
    }

    public function peninjau(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ditinjau_oleh');
    }
}
