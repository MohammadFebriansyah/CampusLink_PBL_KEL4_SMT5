<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;

/** Status yang dilihat mitra: dibuka, ditutup (lewat batas), atau disembunyikan admin. */
class StatusInformasi
{
    public const DIBUKA = 'dibuka';

    public const DITUTUP = 'ditutup';

    public const DISEMBUNYIKAN = 'disembunyikan';

    public static function dari(Model $informasi): string
    {
        if ($informasi->status === 'disembunyikan') {
            return self::DISEMBUNYIKAN;
        }

        return $informasi->batas_pendaftaran->copy()->endOfDay()->isFuture()
            ? self::DIBUKA
            : self::DITUTUP;
    }

    /**
     * Status siap tampil untuk daftar yang dirender Alpine.
     *
     * @return array{kunci: string, label: string, varian: string}
     */
    public static function data(Model $informasi): array
    {
        $status = self::dari($informasi);

        return [
            'kunci' => $status,
            'label' => self::label($status),
            'varian' => match ($status) {
                self::DIBUKA => 'success',
                self::DISEMBUNYIKAN => 'warning',
                default => 'neutral',
            },
        ];
    }

    public static function label(string $status): string
    {
        return match ($status) {
            self::DIBUKA => 'Dibuka',
            self::DITUTUP => 'Ditutup',
            self::DISEMBUNYIKAN => 'Disembunyikan admin',
        };
    }
}
