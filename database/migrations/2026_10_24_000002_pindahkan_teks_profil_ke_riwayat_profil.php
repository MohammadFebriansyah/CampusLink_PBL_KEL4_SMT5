<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Memindahkan isi kolom teks lama mahasiswa (sertifikasi, pelatihan, proyek, pencapaian) menjadi
 * satu entri riwayat_profil per jenis. Baris pertama menjadi judul, sisanya deskripsi.
 *
 * Aman dijalankan ulang: jenis yang sudah punya entri untuk pengguna itu dilewati.
 * Kolom lama TIDAK diubah atau dihapus, jadi teks aslinya tetap ada sebagai cadangan.
 */
return new class extends Migration
{
    private const JENIS = ['sertifikasi', 'pelatihan', 'proyek', 'pencapaian'];

    public function up(): void
    {
        DB::table('mahasiswa')
            ->select(['id', 'user_id', 'updated_at', ...self::JENIS])
            ->orderBy('id')
            ->chunk(200, function ($daftar) {
                foreach ($daftar as $mhs) {
                    foreach (self::JENIS as $jenis) {
                        $this->pindahkan($mhs, $jenis);
                    }
                }
            });
    }

    public function down(): void
    {
        // Tidak menghapus apa pun: entri hasil pindahan tidak bisa dibedakan dari entri yang dibuat pengguna.
        // Tabelnya sendiri dihapus oleh migration create_riwayat_profil_table.
    }

    private function pindahkan(object $mhs, string $jenis): void
    {
        $teks = trim((string) $mhs->{$jenis});
        if ($teks === '') {
            return;
        }

        $sudahAda = DB::table('riwayat_profil')->where('user_id', $mhs->user_id)->where('jenis', $jenis)->exists();
        if ($sudahAda) {
            return;
        }

        $baris = array_values(array_filter(array_map('trim', preg_split('/\R/', $teks)), fn ($b) => $b !== ''));
        $judul = Str::limit($baris[0], 147);           // 147 + "..." = 150 karakter
        $sisa = implode("\n", array_slice($baris, 1));
        if ($judul !== $baris[0]) {
            $sisa = trim($baris[0] . "\n" . $sisa);   // judul terpotong: teks lengkapnya masuk deskripsi
        }

        DB::table('riwayat_profil')->insert([
            'user_id' => $mhs->user_id,
            'jenis' => $jenis,
            'judul' => $judul,
            'deskripsi' => $sisa !== '' ? Str::limit($sisa, 997) : null,
            'created_at' => $mhs->updated_at ?? now(),
            'updated_at' => $mhs->updated_at ?? now(),
        ]);
    }
};
