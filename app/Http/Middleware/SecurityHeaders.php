<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * TAMBAHAN di luar materi (Bagian 9): header keamanan dasar untuk semua respons web.
 * Content-Security-Policy sengaja tidak dipasang: Alpine.js versi standar memakai
 * eval, sehingga CSP yang ketat memerlukan build khusus. Lihat SERAH-TERIMA.md.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        // HSTS hanya bermakna (dan hanya dikirim) bila permintaan datang lewat HTTPS.
        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000');
        }

        return $response;
    }
}
