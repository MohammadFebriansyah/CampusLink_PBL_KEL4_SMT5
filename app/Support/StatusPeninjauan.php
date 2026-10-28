<?php

namespace App\Support;

/**
 * Label dan varian warna status yang ditinjau (pendaftaran dan bimbingan: menunggu, diterima, ditolak).
 * Varian dipakai tampilan untuk memilih warna lewat atribut data-varian (warna selalu disertai label).
 */
class StatusPeninjauan
{
    public const LABEL = ['menunggu' => 'Menunggu', 'diterima' => 'Diterima', 'ditolak' => 'Ditolak'];

    public const VARIAN = ['menunggu' => 'warning', 'diterima' => 'success', 'ditolak' => 'danger'];

    /**
     * @return array{kunci: string, label: string, varian: string}
     */
    public static function data(string $status, ?string $label = null): array
    {
        return [
            'kunci' => $status,
            'label' => $label ?? (self::LABEL[$status] ?? $status),
            'varian' => self::VARIAN[$status] ?? 'neutral',
        ];
    }
}
