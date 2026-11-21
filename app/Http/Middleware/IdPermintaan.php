<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Context;
use Symfony\Component\HttpFoundation\Response;

/**
 * Memberi setiap permintaan HTTP satu Request ID (Correlation ID). ID ini ikut di setiap baris log,
 * di header respons X-Request-ID, dan diteruskan otomatis ke job antrean lewat Context.
 *
 * ID dari klien (misalnya dari Nginx) hanya dipakai bila bentuknya aman (8 sampai 64 karakter
 * huruf, angka, titik, garis bawah, atau strip); selain itu dibuat ID baru. Dengan begitu klien tidak
 * bisa menyuntikkan baris baru atau teks panjang ke dalam log.
 */
class IdPermintaan
{
    public const HEADER = 'X-Request-ID';

    public function handle(Request $request, Closure $next): Response
    {
        $kiriman = $request->headers->get(self::HEADER);
        $id = is_string($kiriman) && preg_match('/^[A-Za-z0-9._-]{8,64}$/', $kiriman)
            ? $kiriman
            : 'req-'.bin2hex(random_bytes(8));

        $request->attributes->set('request_id', $id);
        Context::add('request_id', $id);

        $respons = $next($request);
        $respons->headers->set(self::HEADER, $id);

        return $respons;
    }
}
