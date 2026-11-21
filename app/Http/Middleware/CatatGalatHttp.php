<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * Mencatat respons HTTP yang menandakan masalah tetapi tidak dicatat oleh penanganan exception bawaan Laravel
 * (Laravel sengaja tidak melaporkan abort(403), 419, 429, dan sejenisnya):
 *  - 401, 403, 419, 429: peringatan (kegagalan autentikasi, otorisasi, CSRF atau sesi kedaluwarsa, pembatasan laju)
 *  - 404: hanya bila LOG_404=true (bawaan mati karena pemindai bot membuatnya sangat berisik)
 *  - 5xx yang tidak berasal dari exception yang sudah dilaporkan (misalnya abort(503))
 * Exception yang tidak tertangani sudah dicatat oleh handler Laravel dan tidak dicatat dua kali.
 */
class CatatGalatHttp
{
    private const DITOLAK = [401 => 'tidak terautentikasi', 403 => 'akses ditolak', 419 => 'token CSRF atau sesi tidak valid', 429 => 'terlalu banyak permintaan'];

    public function handle(Request $request, Closure $next): Response
    {
        $respons = $next($request);
        $status = $respons->getStatusCode();

        if (isset(self::DITOLAK[$status])) {
            Log::warning('Permintaan ditolak: HTTP '.$status.' ('.self::DITOLAK[$status].')', ['status_code' => $status]);
        } elseif ($status === 404 && config('logging.catat_404')) {
            Log::info('Halaman tidak ditemukan: HTTP 404', ['status_code' => 404]);
        } elseif ($status >= 500 && ! $request->attributes->get('galat_dilaporkan')) {
            Log::error('Respons HTTP '.$status.' dikirim tanpa exception yang dilaporkan', ['status_code' => $status]);
        }

        return $respons;
    }
}
