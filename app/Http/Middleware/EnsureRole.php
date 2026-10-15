<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Pemakaian di route: EnsureRole::class . ':admin' atau EnsureRole::class . ':mahasiswa,dosen'
 */
class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$peran): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        // Akun bisa dinonaktifkan atau verifikasi mitra dicabut saat sesi masih aktif.
        if ($alasan = $user->alasanTidakBisaMasuk()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors(['email' => $alasan]);
        }

        abort_unless(in_array($user->role, $peran, true), 403);

        return $next($request);
    }
}
