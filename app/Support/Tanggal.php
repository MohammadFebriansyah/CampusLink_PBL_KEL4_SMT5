<?php

namespace App\Support;

use Carbon\Carbon;
use Carbon\CarbonInterface;

/** Format tanggal Indonesia dan hitungan sisa hari untuk batas pendaftaran. */
class Tanggal
{
    /** Contoh: 12 Oktober 2026. Bahasa dipaksa "id" agar tidak bergantung pada pengaturan locale. */
    public static function panjang(?CarbonInterface $tanggal): string
    {
        return $tanggal ? $tanggal->copy()->locale('id')->translatedFormat('j F Y') : '-';
    }

    /** Selisih hari dari hari ini ke batas. 0 = hari ini, negatif = sudah lewat. */
    public static function sisaHari(CarbonInterface $batas): int
    {
        return (int) Carbon::today()->diffInDays($batas->copy()->startOfDay(), false);
    }

    public static function labelSisa(CarbonInterface $batas): string
    {
        $sisa = self::sisaHari($batas);

        return match (true) {
            $sisa < 0 => 'Ditutup',
            $sisa === 0 => 'Terakhir hari ini',
            default => $sisa . ' hari lagi',
        };
    }

    /** Satu tanggal bila mulai = selesai, selain itu "mulai sampai selesai". */
    public static function rentang(CarbonInterface $mulai, CarbonInterface $selesai): string
    {
        if ($mulai->isSameDay($selesai)) {
            return self::panjang($mulai);
        }

        return self::panjang($mulai) . ' sampai ' . self::panjang($selesai);
    }
}
