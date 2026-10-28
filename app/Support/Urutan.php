<?php

namespace App\Support;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * Pengurutan daftar dari query string (?urut=...&arah=asc|desc).
 * Hanya kunci yang terdaftar di $pilihan yang dipakai; kunci lain jatuh ke urutan bawaan,
 * jadi nama kolom tidak pernah datang langsung dari masukan pengguna.
 */
class Urutan
{
    /**
     * @param  array<string, string|Closure(Builder, string): mixed>  $pilihan  kunci => kolom, atau closure(query, arah)
     * @param  array{0: string, 1: string}  $bawaan  [kunci, arah] bila masukan kosong atau tidak dikenal
     * @return array{urut: string, arah: string} urutan yang benar-benar dipakai (dikirim balik ke tampilan)
     */
    public static function terapkan(Builder|Relation $query, ?string $urut, ?string $arah, array $pilihan, array $bawaan): array
    {
        if (! array_key_exists((string) $urut, $pilihan)) {
            [$urut, $arah] = $bawaan;
        }
        $arah = $arah === 'desc' ? 'desc' : 'asc';

        $kolom = $pilihan[$urut];
        $kolom instanceof Closure ? $kolom($query, $arah) : $query->orderBy($kolom, $arah);

        // Pemutus seri agar urutan antarhalaman stabil (baris dengan nilai sama tidak berpindah halaman).
        $query->orderBy($query->getModel()->getQualifiedKeyName(), $arah);

        return ['urut' => $urut, 'arah' => $arah];
    }
}
