<?php

namespace App\Support;

use Illuminate\Database\Query\Builder;

class Cari
{
    public static function mengandung(Builder $query, string $kolom, string $kata, string $boolean = 'and'): Builder
    {
        $pola = '%'.strtr($kata, ['\\' => '\\\\', '%' => '\\%', '_' => '\\_']).'%';
        $operator = $query->getConnection()->getDriverName() === 'pgsql' ? 'ILIKE' : 'LIKE';
        $kolom = $query->getGrammar()->wrap($kolom);

        // SQLite tidak punya escape LIKE bawaan; tentukan eksplisit untuk kedua driver.
        return $query->whereRaw("{$kolom} {$operator} ? ESCAPE ?", [$pola, '\\'], $boolean);
    }
}
