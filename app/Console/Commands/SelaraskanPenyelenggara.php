<?php

namespace App\Console\Commands;

use App\Models\Mitra;
use App\Models\User;
use Illuminate\Console\Command;

/**
 * Menyelaraskan kolom penyelenggara pada informasi (magang/lomba/beasiswa) buatan
 * mitra dengan nama_organisasi mitra saat ini.
 *
 * Idempoten: menjalankan dua kali berturut-turut tidak mengubah apa pun pada
 * eksekusi kedua. Hanya memperbarui baris yang nilainya memang berbeda.
 *
 * Kompatibel PostgreSQL dan SQLite (Eloquent query builder, tanpa SQL mentah).
 */
class SelaraskanPenyelenggara extends Command
{
    protected $signature = 'informasi:selaraskan-penyelenggara';

    protected $description = 'Menyelaraskan penyelenggara informasi buatan mitra dengan nama_organisasi mitra saat ini.';

    public function handle(): int
    {
        $totalDiperbarui = 0;

        // Ambil semua mitra yang sudah punya profil.
        Mitra::with('user')->chunk(100, function ($mitraCollection) use (&$totalDiperbarui) {
            foreach ($mitraCollection as $mitra) {
                $user = $mitra->user;
                if (! $user || $user->role !== User::ROLE_MITRA) {
                    continue;
                }

                $namaBaru = $mitra->nama_organisasi;

                foreach (['magang', 'lomba', 'beasiswa'] as $relasi) {
                    // Hanya perbarui baris yang penyelenggara-nya memang berbeda.
                    $diperbarui = $user->{$relasi}()
                        ->where('penyelenggara', '!=', $namaBaru)
                        ->update(['penyelenggara' => $namaBaru]);

                    $totalDiperbarui += $diperbarui;
                }
            }
        });

        $this->info("Selesai. {$totalDiperbarui} baris informasi diperbarui.");

        return self::SUCCESS;
    }
}
