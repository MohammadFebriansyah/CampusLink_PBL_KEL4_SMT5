<?php

namespace App\Logging;

use Illuminate\Database\QueryException;

/**
 * Pembersih data sebelum masuk ke log: kunci sensitif disamarkan, dan teks pesan dibersihkan dari
 * token atau kata sandi yang terlanjur menempel. Nilai asli tidak pernah ditulis ke berkas log.
 */
final class Sanitasi
{
    public const SAMAR = '[DISEMBUNYIKAN]';

    /** Nama kunci (tanpa membedakan huruf besar kecil) yang nilainya selalu disamarkan. */
    private const KUNCI_SENSITIF = '/pass|secret|token|authorization|cookie|session|api[_-]?key|csrf|xsrf|otp|kode|credential|bearer|signature/i';

    /** @param  array<mixed>  $data */
    public static function bersihkan(array $data, int $kedalaman = 0): array
    {
        $hasil = [];

        foreach ($data as $kunci => $nilai) {
            if (is_string($kunci) && preg_match(self::KUNCI_SENSITIF, $kunci)) {
                $hasil[$kunci] = self::SAMAR;
            } elseif (is_array($nilai)) {
                $hasil[$kunci] = $kedalaman >= 6 ? '[terpotong]' : self::bersihkan($nilai, $kedalaman + 1);
            } elseif (is_string($nilai)) {
                $hasil[$kunci] = self::teks($nilai);
            } else {
                $hasil[$kunci] = $nilai;
            }
        }

        return $hasil;
    }

    /** Menyamarkan token Bearer dan pasangan kunci=nilai yang sensitif di dalam teks bebas. */
    public static function teks(string $teks): string
    {
        $teks = preg_replace('/\bBearer\s+[A-Za-z0-9\-._~+\/]+=*/i', 'Bearer '.self::SAMAR, $teks) ?? $teks;

        return preg_replace('/\b(password|passwd|pwd|token|secret|api[_-]?key|authorization)(["\']?\s*[=:]\s*["\']?)[^\s"\'&,;)]+/i', '$1$2'.self::SAMAR, $teks) ?? $teks;
    }

    /**
     * Pesan exception yang aman. Pesan QueryException memuat seluruh SQL beserta nilai yang diikat
     * (bisa berisi email atau hash kata sandi), jadi bagian "SQL: ..." dibuang. SQL tanpa nilai
     * (tanda tanya sebagai pengganti) dicatat terpisah lewat {@see self::sqlAman()}.
     */
    public static function pesanException(\Throwable $e): string
    {
        $pesan = $e->getMessage();

        if ($e instanceof QueryException) {
            $pesan = preg_replace('/\s*\(Connection:\s*([^,)]*)[^)]*SQL:.*\)\s*$/s', ' (Connection: $1)', $pesan) ?? $pesan;
            $pesan = preg_replace('/\s*\(SQL:.*\)\s*$/s', '', $pesan) ?? $pesan;
        }

        return self::teks($pesan);
    }

    /** SQL dengan tanda tanya pada posisi nilai (tanpa nilai yang diikat), hanya untuk QueryException. */
    public static function sqlAman(\Throwable $e): ?string
    {
        return $e instanceof QueryException ? $e->getSql() : null;
    }
}
