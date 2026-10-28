<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;
use Illuminate\Http\Response;

/**
 * Respons untuk halaman daftar yang dirender Alpine (resources/js/daftar.js).
 * URL yang sama melayani dua bentuk:
 *  - fetch dengan "Accept: application/json": JSON berisi data, meta paginasi, filter, dan data tambahan;
 *  - kunjungan biasa: halaman Blade dengan JSON yang sama sebagai data awal (tanpa request tambahan).
 */
class Daftar
{
    /** Jumlah baris per halaman yang boleh dipilih pengguna. */
    public const PER_HALAMAN = [10, 20, 50];

    /**
     * Jumlah per halaman yang dipakai: pilihan pengguna bila termasuk PER_HALAMAN, selain itu bawaan daftar.
     * Nilai di luar daftar tidak ditolak, hanya diabaikan, jadi bawaan daftar (mis. 12 atau 15) tetap bisa dipilih kembali.
     */
    public static function perHalaman(int|string|null $minta, int $bawaan): int
    {
        return in_array((int) $minta, self::PER_HALAMAN, true) ? (int) $minta : $bawaan;
    }

    /**
     * @param  array<string, mixed>  $filter  filter yang dipakai, termasuk urut dan arah
     * @param  array<string, mixed>  $tambahan  data lain yang ikut berubah saat filter berubah (mis. jumlah per status)
     * @param  array<string, mixed>  $dataView  variabel lain untuk view Blade
     */
    public static function tanggapi(
        Request $request,
        ResourceCollection $koleksi,
        string $view,
        array $filter,
        array $tambahan = [],
        array $dataView = [],
    ): JsonResponse|Response {
        $koleksi->additional(['filter' => (object) $filter, 'tambahan' => (object) $tambahan]);

        if ($request->wantsJson()) {
            // Vary: halaman HTML dan JSON memakai URL yang sama; cache browser tidak boleh menukarnya.
            return $koleksi->response($request)->header('Vary', 'Accept');
        }

        return response()
            ->view($view, $dataView + ['awal' => $koleksi->response($request)->getData(true)])
            ->header('Vary', 'Accept');
    }
}
