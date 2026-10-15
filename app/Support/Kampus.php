<?php

namespace App\Support;

/**
 * Akses ke daftar jurusan dan program studi di config/kampus.php.
 * Pengguna hanya memilih program studi, jurusan diturunkan darinya, jadi keduanya
 * tidak mungkin tidak sinkron (penting untuk filter direktori).
 */
class Kampus
{
    /** @return array<string, array<int, string>> jurusan => daftar program studi */
    public static function jurusan(): array
    {
        return config('kampus.jurusan');
    }

    /** @return array<int, string> semua program studi dalam satu daftar */
    public static function semuaProdi(): array
    {
        return array_merge(...array_values(self::jurusan()));
    }

    public static function jurusanDariProdi(string $prodi): ?string
    {
        foreach (self::jurusan() as $jurusan => $daftar) {
            if (in_array($prodi, $daftar, true)) {
                return $jurusan;
            }
        }

        return null;
    }

    /** Domain email yang diizinkan untuk peran ini (tanpa @). Mitra dan admin: kosong. */
    public static function domainEmail(string $peran): array
    {
        return config("kampus.domain_email_per_peran.$peran", []);
    }

    /** Gabungan semua domain kampus, tanpa duplikat. */
    public static function semuaDomainEmail(): array
    {
        return array_values(array_unique(array_merge(...array_values(config('kampus.domain_email_per_peran')))));
    }

    /** Cocok persis dengan "@domain". Subdomain lain dan domain palsu tidak lolos. */
    public static function emailSesuaiPeran(string $email, string $peran): bool
    {
        $email = mb_strtolower($email);

        foreach (self::domainEmail($peran) as $domain) {
            if (str_ends_with($email, '@' . mb_strtolower($domain))) {
                return true;
            }
        }

        return false;
    }

    /** Contoh: "@polinema.ac.id atau @student.polinema.ac.id" */
    public static function labelDomain(string $peran): string
    {
        return collect(self::domainEmail($peran))->map(fn($d) => '@' . $d)->implode(' atau ');
    }

    public static function labelSemuaDomain(): string
    {
        return collect(self::semuaDomainEmail())->map(fn($d) => '@' . $d)->implode(' atau ');
    }
}
