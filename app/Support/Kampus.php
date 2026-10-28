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

    /**
     * Domain email (bagian setelah "@"), huruf kecil dan tanpa spasi. Null bila bentuknya bukan
     * "bagian-lokal@domain" dengan tepat satu "@".
     */
    public static function domainDari(string $email): ?string
    {
        $bagian = explode('@', mb_strtolower(trim($email)));

        if (count($bagian) !== 2 || $bagian[0] === '' || $bagian[1] === '') {
            return null;
        }

        return $bagian[1];
    }

    /**
     * Domain email sama persis dengan salah satu domain peran ini. Bukan pencocokan substring:
     * "x@evil-polinema.ac.id", "x@student.polinema.ac.id.evil.com", dan "x@lain.polinema.ac.id" tidak lolos.
     */
    public static function emailSesuaiPeran(string $email, string $peran): bool
    {
        $domain = self::domainDari($email);

        return $domain !== null && in_array($domain, array_map('mb_strtolower', self::domainEmail($peran)), true);
    }

    /** Domain email termasuk salah satu domain kampus (peran apa pun). */
    public static function emailKampus(string $email): bool
    {
        $domain = self::domainDari($email);

        return $domain !== null && in_array($domain, array_map('mb_strtolower', self::semuaDomainEmail()), true);
    }

    /** Contoh: "@student.polinema.ac.id". Bila satu peran punya beberapa domain: "@a atau @b". */
    public static function labelDomain(string $peran): string
    {
        return collect(self::domainEmail($peran))->map(fn ($d) => '@'.$d)->implode(' atau ');
    }

    public static function labelSemuaDomain(): string
    {
        return collect(self::semuaDomainEmail())->map(fn ($d) => '@'.$d)->implode(' atau ');
    }
}
