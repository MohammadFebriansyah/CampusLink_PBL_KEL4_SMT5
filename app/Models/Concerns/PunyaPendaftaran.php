<?php

namespace App\Models\Concerns;

use App\Models\Bimbingan;
use App\Models\Pendaftaran;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Storage;

/**
 * Perilaku bersama Magang, Lomba, Beasiswa terkait pendaftaran dan bimbingan.
 * Nama kategori (magang/lomba/beasiswa) sama dengan alias morph map di AppServiceProvider.
 *
 * Anotasi di bawah hanya untuk editor (Intelephense/PhpStorm): trait ini selalu dipakai di Model
 * yang punya kolom-kolom berikut.
 *
 * @mixin \Illuminate\Database\Eloquent\Model
 *
 * @property int $user_id
 * @property string $cara_daftar
 * @property string $status
 * @property \Illuminate\Support\Carbon $batas_pendaftaran
 * @property-read \App\Models\User|null $user
 */
trait PunyaPendaftaran
{
    protected static function bootPunyaPendaftaran(): void
    {
        static::deleting(function ($model) {
            $model->hapusPendaftaranDanBimbingan();
        });
    }
    public const CARA_INTERNAL = 'internal';
    public const CARA_EKSTERNAL = 'eksternal';

    /**
     * Hapus pendaftaran (beserta berkas CV) dan permintaan bimbingan (beserta berkas bukti) milik
     * informasi ini. Relasi morph tidak punya foreign key, jadi dibersihkan sendiri.
     * Dipanggil dari event "deleting" di booted() masing-masing model (Magang, Lomba, Beasiswa).
     */
    public function hapusPendaftaranDanBimbingan(): void
    {
        $this->pendaftaran()->get()->each(function (Pendaftaran $p) {
            Storage::disk('local')->delete($p->cv);
            $p->delete();
        });

        $this->bimbingan()->get()->each(function (Bimbingan $b) {
            $b->hapusBukti();
            $b->delete();
        });
    }

    public function pendaftaran(): MorphMany
    {
        return $this->morphMany(Pendaftaran::class, 'informasi');
    }

    public function bimbingan(): MorphMany
    {
        return $this->morphMany(Bimbingan::class, 'informasi');
    }

    /** magang | lomba | beasiswa */
    public function kategori(): string
    {
        return $this->getMorphClass();
    }

    /** Mahasiswa mendaftar di CampusLink (bukan di situs penyelenggara). */
    public function lewatCampuslink(): bool
    {
        return $this->cara_daftar === self::CARA_INTERNAL;
    }

    /** Masih bisa didaftar: tayang dan batas pendaftaran belum lewat. */
    public function terbuka(): bool
    {
        return $this->status === 'tayang' // = STATUS_TAYANG di Magang/Lomba/Beasiswa
            && $this->batas_pendaftaran->copy()->endOfDay()->isFuture();
    }

    /** Bimbingan dosen hanya untuk magang dan lomba. */
    public function bisaBimbingan(): bool
    {
        return in_array($this->kategori(), ['magang', 'lomba'], true);
    }

    /**
     * Apakah $user berhak meninjau (menerima/menolak) pendaftar informasi ini.
     * Milik mitra -> hanya mitra pembuatnya. Milik admin (kegiatan kampus) -> semua admin.
     */
    public function bisaDitinjauOleh(User $user): bool
    {
        if (! $this->lewatCampuslink()) {
            return false;
        }

        $pembuat = $this->user;

        if ($pembuat?->role === User::ROLE_ADMIN) {
            return $user->role === User::ROLE_ADMIN;
        }

        return $user->role === User::ROLE_MITRA && (int) $this->user_id === (int) $user->id;
    }
}
