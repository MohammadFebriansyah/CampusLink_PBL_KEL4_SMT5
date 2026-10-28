<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Http\Responses\LoginResponse;
use App\Http\Responses\LogoutResponse;
use App\Models\User;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Support\Timebox;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use Laravel\Fortify\Contracts\LogoutResponse as LogoutResponseContract;
use Laravel\Fortify\Fortify;

/**
 * Autentikasi CampusLink lewat Laravel Fortify: login dan logout (route /masuk dan /keluar, lihat config/fortify.php),
 * serta pembuatan akun lewat action CreateNewUser. Registrasi email + OTP dan Google SSO ada di
 * App\Http\Controllers\Auth, dan keduanya membuat akun lewat action yang sama.
 */
class FortifyServiceProvider extends ServiceProvider
{
    /** Waktu minimum proses login (mikrodetik), sama dengan guard bawaan Laravel. */
    private const TIMEBOX_LOGIN = 200_000;

    public function register(): void
    {
        $this->app->singleton(LoginResponseContract::class, LoginResponse::class);
        $this->app->singleton(LogoutResponseContract::class, LogoutResponse::class);
    }

    public function boot(): void
    {
        Fortify::loginView(fn () => view('auth.login'));
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::authenticateUsing(fn (Request $request) => $this->autentikasi($request));

        // Pengiriman kode OTP per IP (registrasi dan kirim ulang). Batas per email ada di RegisterController.
        // Mencegah satu pihak memakai formulir daftar untuk mengirim email ke banyak alamat.
        RateLimiter::for('kirim-kode', function (Request $request) {
            return Limit::perMinutes(10, 6)->by($request->ip())->response(function (Request $request) {
                $pesan = 'Terlalu banyak permintaan kode dari jaringan ini. Coba lagi dalam beberapa menit.';

                return back()->withInput($request->except(['password', 'password_confirmation']))
                    ->withErrors(['email' => $pesan, 'kode' => $pesan]);
            });
        });
    }

    /**
     * Dipanggil pipeline login Fortify. Null berarti gagal: Fortify menghitung percobaan (5 per email + IP
     * per menit) dan menampilkan auth.failed. Akun nonaktif juga null agar keberadaan akun tidak bocor.
     * Bila kata sandi benar tetapi akun belum boleh dipakai (mitra menunggu verifikasi, email belum
     * terverifikasi), alasan spesifik ditampilkan.
     */
    private function autentikasi(Request $request): ?User
    {
        return (new Timebox)->call(function (Timebox $timebox) use ($request) {
            $email = Str::lower(trim((string) $request->input(Fortify::username())));
            $user = User::where('email', $email)->first();

            // Akun khusus Google (tanpa kata sandi) selalu gagal di sini.
            $cocok = $user && Auth::getProvider()->validateCredentials($user, ['password' => (string) $request->input('password')]);

            if (! $cocok || $user->status !== User::STATUS_AKTIF) {
                return null;
            }

            $timebox->returnEarly();

            if ($alasan = $user->alasanTidakBisaMasuk()) {
                throw ValidationException::withMessages([Fortify::username() => $alasan]);
            }

            return $user;
        }, self::TIMEBOX_LOGIN);
    }
}
