<?php

namespace App\Models;

use App\Support\Tanggal;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Satu entri sertifikasi, pelatihan, proyek, atau pencapaian di profil mahasiswa. */
class RiwayatProfil extends Model
{
    public const BATAS_PER_JENIS = 10;

    /**
     * TAMBAHAN: label per jenis. Arti kolom penerbit dan tanggal berbeda per jenis, jadi labelnya juga.
     * mulai/selesai null = kolom itu tidak ditampilkan di form untuk jenis tersebut.
     */
    public const JENIS = [
        'sertifikasi' => [
            'nama' => 'Sertifikasi', 'kecil' => 'sertifikasi', 'judul' => 'Nama sertifikasi', 'penerbit' => 'Penerbit',
            'mulai' => 'Tanggal terbit', 'selesai' => 'Berlaku sampai', 'tautan' => 'Tautan kredensial',
        ],
        'pelatihan' => [
            'nama' => 'Pelatihan', 'kecil' => 'pelatihan', 'judul' => 'Nama pelatihan', 'penerbit' => 'Penyelenggara',
            'mulai' => 'Mulai', 'selesai' => 'Selesai', 'tautan' => 'Tautan sertifikat',
        ],
        'proyek' => [
            'nama' => 'Proyek', 'kecil' => 'proyek', 'judul' => 'Nama proyek', 'penerbit' => 'Institusi atau organisasi',
            'mulai' => 'Mulai', 'selesai' => 'Selesai', 'tautan' => 'Tautan proyek',
        ],
        'pencapaian' => [
            'nama' => 'Pencapaian', 'kecil' => 'pencapaian', 'judul' => 'Nama pencapaian', 'penerbit' => 'Pemberi penghargaan',
            'mulai' => 'Tanggal', 'selesai' => null, 'tautan' => 'Tautan bukti',
        ],
    ];

    protected $table = 'riwayat_profil';

    // user_id dan jenis sengaja tidak fillable: diisi controller, bukan dari masukan pengguna.
    protected $fillable = [
        'judul',
        'penerbit',
        'tanggal_mulai',
        'tanggal_selesai',
        'deskripsi',
        'tautan',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_mulai' => 'date',
            'tanggal_selesai' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Kelompokkan per jenis (urutan tetap sesuai JENIS), tiap kelompok terbaru dulu:
     * tanggal selesai (atau mulai bila tidak ada), lalu entri yang lebih baru dibuat.
     * Diurutkan di PHP agar sama di SQLite dan PostgreSQL (letak NULL saat ORDER BY berbeda).
     *
     * @return array<string, Collection<int, self>>
     */
    public static function kelompokkan(Collection $riwayat): array
    {
        $hasil = [];
        foreach (array_keys(self::JENIS) as $jenis) {
            $hasil[$jenis] = $riwayat->where('jenis', $jenis)
                ->sortByDesc(fn (self $r) => sprintf('%s-%010d', ($r->tanggal_selesai ?? $r->tanggal_mulai)?->format('Y-m-d') ?? '0000-00-00', $r->id))
                ->values();
        }

        return $hasil;
    }

    /** Contoh: "Maret 2024 sampai Juni 2024", "Terbit Maret 2024", atau "" bila tanpa tanggal. */
    public function rentangTanggal(): string
    {
        $mulai = $this->tanggal_mulai ? Tanggal::bulanTahun($this->tanggal_mulai) : null;
        $selesai = $this->tanggal_selesai ? Tanggal::bulanTahun($this->tanggal_selesai) : null;

        if ($this->jenis === 'sertifikasi') {
            return collect([$mulai ? 'Terbit ' . $mulai : null, $selesai ? 'berlaku sampai ' . $selesai : null])
                ->filter()->implode(', ');
        }

        return match (true) {
            $mulai && $selesai && $mulai !== $selesai => $mulai . ' sampai ' . $selesai,
            (bool) $mulai => $mulai,
            (bool) $selesai => $selesai,
            default => '',
        };
    }
}
