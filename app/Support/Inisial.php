<?php

namespace App\Support;

/** Inisial (maksimal dua huruf) untuk avatar tanpa foto. */
class Inisial
{
    /** Nama orang. Gelar di depan (Dr, Prof, Ir) diabaikan. */
    public static function orang(string $nama): string
    {
        return self::ambil(preg_replace('/^(Dr|Prof|Ir)\.?\s+/i', '', $nama));
    }

    /** Nama organisasi. Bentuk badan usaha di depan (PT, CV, UD) diabaikan. */
    public static function organisasi(string $nama): string
    {
        return self::ambil(preg_replace('/^(PT|CV|UD)\.?\s+/i', '', $nama));
    }

    private static function ambil(string $nama): string
    {
        $inisial = collect(preg_split('/\s+/', trim($nama)))
            ->filter()->take(2)->map(fn (string $kata) => mb_substr($kata, 0, 1))->join('');

        return mb_strtoupper($inisial) ?: '?';
    }
}
