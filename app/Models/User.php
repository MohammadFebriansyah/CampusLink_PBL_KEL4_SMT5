<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const ROLE_ADMIN = 'admin';
    public const ROLE_MAHASISWA = 'mahasiswa';
    public const ROLE_DOSEN = 'dosen';
    public const ROLE_MITRA = 'mitra';

    public const STATUS_AKTIF = 'aktif';
    public const STATUS_NONAKTIF = 'nonaktif';

    // Jangan pernah mengisi dari $request->all(). Pakai hasil validasi FormRequest.
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    /**
     * Alasan akun tidak boleh dipakai saat ini, atau null bila boleh.
     * Dipakai saat login DAN di setiap request terlindungi, karena akun bisa
     * dinonaktifkan atau verifikasi mitra dicabut ketika sesi masih berjalan.
     */
    public function alasanTidakBisaMasuk(): ?string
    {
        if ($this->status !== self::STATUS_AKTIF) {
            return 'Akun ini dinonaktifkan. Hubungi admin CampusLink.';
        }

        if ($this->role === self::ROLE_MITRA) {
            $status = $this->mitra?->status_verifikasi;

            if ($status === Mitra::STATUS_PENDING) {
                return 'Akun mitra Anda masih menunggu verifikasi admin.';
            }

            if ($status !== Mitra::STATUS_DISETUJUI) {
                return 'Pendaftaran mitra Anda tidak disetujui. Hubungi admin CampusLink.';
            }
        }

        return null;
    }

    /** Terisi bila pengguna pernah masuk dengan Google, artinya kepemilikan email sudah dibuktikan. */
    public function terverifikasiGoogle(): bool
    {
        return $this->google_id !== null;
    }

    /** Halaman utama milik peran ini: nama route dan label tautannya. */
    public function panel(): array
    {
        return match ($this->role) {
            self::ROLE_ADMIN => ['rute' => 'admin.dashboard', 'label' => 'Dasbor'],
            self::ROLE_MITRA => ['rute' => 'mitra.dashboard', 'label' => 'Dasbor'],
            default => ['rute' => 'profil.show', 'label' => 'Profil'],
        };
    }

    public function mahasiswa(): HasOne
    {
        return $this->hasOne(Mahasiswa::class);
    }

    public function dosen(): HasOne
    {
        return $this->hasOne(Dosen::class);
    }

    public function mitra(): HasOne
    {
        return $this->hasOne(Mitra::class);
    }

    public function keahlian(): BelongsToMany
    {
        return $this->belongsToMany(Keahlian::class, 'pengguna_keahlian', 'user_id', 'keahlian_id');
    }

    public function magang(): HasMany
    {
        return $this->hasMany(Magang::class);
    }

    public function lomba(): HasMany
    {
        return $this->hasMany(Lomba::class);
    }

    public function beasiswa(): HasMany
    {
        return $this->hasMany(Beasiswa::class);
    }

    /** Pendaftaran milik mahasiswa ini. */
    public function pendaftaran(): HasMany
    {
        return $this->hasMany(Pendaftaran::class);
    }

    /** Permintaan bimbingan yang diajukan mahasiswa ini. */
    public function bimbinganDiajukan(): HasMany
    {
        return $this->hasMany(Bimbingan::class, 'mahasiswa_id');
    }

    /** Permintaan bimbingan yang diterima dosen ini. */
    public function bimbinganMasuk(): HasMany
    {
        return $this->hasMany(Bimbingan::class, 'dosen_id');
    }
}
