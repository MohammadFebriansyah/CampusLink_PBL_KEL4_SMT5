<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    private const BATAS_PERCOBAAN = 5;

    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        // Kunci per email + IP, agar satu orang tidak mengunci akun orang lain dari IP berbeda.
        $kunci = Str::lower($request->input('email')) . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($kunci, self::BATAS_PERCOBAAN)) {
            $detik = RateLimiter::availableIn($kunci);

            throw ValidationException::withMessages([
                'email' => "Terlalu banyak percobaan masuk. Coba lagi dalam {$detik} detik.",
            ]);
        }

        // Akun nonaktif ikut gagal di sini dengan pesan yang sama (tidak membocorkan keberadaan akun).
        $berhasil = Auth::attempt([
            'email' => $request->input('email'),
            'password' => $request->input('password'),
            'status' => User::STATUS_AKTIF,
        ]);

        if (! $berhasil) {
            RateLimiter::hit($kunci, 60);

            throw ValidationException::withMessages([
                'email' => 'Email atau kata sandi salah.',
            ]);
        }

        $user = Auth::user();

        // Kredensial benar, tapi mitra belum/tidak disetujui. Pesan spesifik aman karena password sudah benar.
        if ($alasan = $user->alasanTidakBisaMasuk()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            throw ValidationException::withMessages(['email' => $alasan]);
        }

        RateLimiter::clear($kunci);
        $request->session()->regenerate(); // cegah session fixation

        $panelRoute = $user->panel()['rute'];
        $target = Route::has($panelRoute) ? route($panelRoute) : route('home');

        return redirect()->intended($target);
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
