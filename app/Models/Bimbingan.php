<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;

/** Permintaan bimbingan mahasiswa ke dosen untuk magang atau lomba. */
class Bimbingan extends Model
{
    public const STATUS_MENUNGGU = 'menunggu';
    public const STATUS_DITERIMA = 'diterima';
    public const STATUS_DITOLAK = 'ditolak';

    protected $table = 'bimbingan';

    // dosen_id dan bukti diisi controller setelah validasi; status hanya lewat aksi dosen.
    protected $fillable = ['pesan'];

    protected function casts(): array
    {
        return ['ditinjau_pada' => 'datetime'];
    }

    /** Hapus berkas bukti diterima (kegiatan luar) dari disk privat, bila ada. */
    public function hapusBukti(): void
    {
        if ($this->bukti) {
            Storage::disk('local')->delete($this->bukti);
        }
    }

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mahasiswa_id');
    }

    public function dosen(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dosen_id');
    }

    public function informasi(): MorphTo
    {
        return $this->morphTo();
    }
}
