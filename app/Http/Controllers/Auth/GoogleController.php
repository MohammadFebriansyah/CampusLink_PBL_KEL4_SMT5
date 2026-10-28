<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\GoogleRegisterRequest;
use App\Models\User;
use App\Support\Kampus;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Socialite\Contracts\User as GoogleUser;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\AbstractProvider;

/**
 * Masuk dan daftar dengan akun Google.
 * Mahasiswa dan dosen wajib memakai akun Google kampus. Domain yang boleh per peran
 * diatur di config/kampus.php ("domain_email_per_peran").
 * Mitra boleh memakai akun Google apa saja, tetapi menunggu verifikasi admin.
 * Admin tidak bisa masuk lewat Google.
 */
class GoogleController extends Controller
{
    private const MASA_LENGKAPI_MENIT = 15;

    public function redirect(Request $request): RedirectResponse
    {
        $peran = (string) $request->query('peran');

        if (in_array($peran, GoogleRegisterRequest::PERAN, true)) {
            session(['google_peran' => $peran]);
        }

        $param = ['prompt' => 'select_account'];

        // "hd" hanya petunjuk tampilan pemilih akun dan hanya bisa berisi satu domain.
        // Dipakai bila peran ini punya tepat satu domain (dosen). Mahasiswa punya dua, mitra bebas.
        $domain = Kampus::domainEmail($peran);

        if (count($domain) === 1) {
            $param['hd'] = $domain[0];
        }

        /** @var AbstractProvider $driver */
        $driver = Socialite::driver('google');

        return $driver->with($param)->redirect();
    }

    public function callback(Request $request): RedirectResponse
    {
        try {
            $google = Socialite::driver('google')->user();
        } catch (\Throwable $e) {
            report($e);

            return $this->gagal('Masuk dengan Google gagal. Silakan coba lagi.');
        }

        $raw = $google->user ?? [];

        // Berlaku untuk semua peran, termasuk mitra.
        if (! filter_var($raw['email_verified'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            return $this->gagal('Email Google Anda belum terverifikasi oleh Google.');
        }

        $kampus = $this->akunKampusValid($google);

        return Auth::check()
            ? $this->tautkan($google, $kampus)
            : $this->masuk($request, $google, $kampus);
    }

    /** Formulir data tambahan untuk akun Google yang belum terdaftar. */
    public function lengkapi(Request $request): View|RedirectResponse
    {
        $pending = $this->pending($request);

        if (! $pending) {
            return $this->gagal('Sesi pendaftaran Google berakhir. Silakan mulai lagi.');
        }

        $peran = $request->old('peran', $request->query('peran', session('google_peran')));

        if (! in_array($peran, GoogleRegisterRequest::PERAN, true)) {
            $peran = User::ROLE_MAHASISWA;
        }

        // Akun Google non-kampus (misalnya Gmail) hanya boleh mendaftar sebagai mitra.
        if (! ($pending['kampus'] ?? false)) {
            $peran = User::ROLE_MITRA;
        }

        return view('auth.google-lengkapi', compact('peran', 'pending'));
    }

    public function simpan(GoogleRegisterRequest $request, CreatesNewUsers $pembuatAkun): RedirectResponse
    {
        $pending = $this->pending($request);

        if (! $pending) {
            return $this->gagal('Sesi pendaftaran Google berakhir. Silakan mulai lagi.');
        }

        // Akun bisa saja sudah dibuat di tab lain sejak callback.
        if (User::where('email', $pending['email'])->orWhere('google_id', $pending['google_id'])->exists()) {
            $request->session()->forget('google_pending');

            return $this->gagal('Akun ini sudah terdaftar. Silakan masuk dengan Google.');
        }

        $data = $request->validated();
        $peran = $data['peran'];

        if ($peran !== User::ROLE_MITRA) {
            // Tanpa ini, pemilik Gmail bisa memilih tab Mahasiswa lalu mendaftar.
            if (! ($pending['kampus'] ?? false)) {
                return redirect()->route('google.lengkapi', ['peran' => User::ROLE_MITRA])
                    ->with('galat', $this->pesanDomain($peran));
            }

            // Domain harus cocok dengan peran yang dipilih (akun @student tidak boleh jadi dosen).
            if (! Kampus::emailSesuaiPeran($pending['email'], $peran)) {
                return redirect()->route('google.lengkapi', ['peran' => $peran])
                    ->with('galat', $this->pesanDomain($peran));
            }
        }

        if (isset($data['program_studi'])) {
            $data['jurusan'] = Kampus::jurusanDariProdi($data['program_studi']);
        }

        // Email dan google_id dari sesi (hasil callback Google), tidak pernah dari formulir.
        // Tanpa password: akun ini hanya bisa masuk lewat Google.
        $user = $pembuatAkun->create(['email' => $pending['email'], 'google_id' => $pending['google_id'], 'password' => null] + $data);
        event(new Registered($user));

        $request->session()->forget(['google_pending', 'google_peran']);

        // Mitra harus menunggu admin, jadi tidak langsung masuk.
        if ($user->role === User::ROLE_MITRA) {
            return redirect()->route('login')->with(
                'status',
                'Pendaftaran berhasil. Akun mitra bisa dipakai setelah diverifikasi admin.'
            );
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('profil.show')->with(
            'status',
            'Akun berhasil dibuat. Lengkapi profil Anda agar muncul di direktori.'
        );
    }

    // ------------------------------------------------------------------

    /**
     * Akun dianggap "kampus" bila ketiganya terpenuhi:
     * 1. Google menyatakan email terverifikasi.
     * 2. Email berakhiran salah satu domain kampus (config/kampus.php).
     * 3. Klaim "hd" (Google Workspace) adalah salah satu domain kampus. Domain di email saja tidak
     *    cukup: akun Google pribadi bisa memakai alamat email apa pun.
     *
     * Kecocokan domain dengan peran (mahasiswa vs dosen) diperiksa terpisah di simpan().
     */
    private function akunKampusValid(GoogleUser $google): bool
    {
        $domains = array_map(fn ($d) => Str::lower($d), Kampus::semuaDomainEmail());
        $raw = $google->user ?? [];

        $emailTerverifikasi = filter_var($raw['email_verified'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $emailKampus = Kampus::emailKampus((string) $google->getEmail());
        $hdKampus = in_array(Str::lower((string) ($raw['hd'] ?? '')), $domains, true);

        if (config('kampus.google_wajib_hd') === false) {
            $hdKampus = true; // mode lebih lemah, lihat config/kampus.php
        }

        return $emailTerverifikasi && $emailKampus && $hdKampus;
    }

    private function masuk(Request $request, GoogleUser $google, bool $kampus): RedirectResponse
    {
        $email = Str::lower((string) $google->getEmail());
        $user = User::where('google_id', $google->getId())->first();

        if (! $user) {
            $user = User::where('email', $email)->first();

            if ($user && ! in_array($user->role, GoogleRegisterRequest::PERAN, true)) {
                return $this->gagal('Email ini terdaftar sebagai akun lain. Masuk dengan email dan kata sandi.');
            }

            // Harus SEBELUM menautkan: penautan menghapus kata sandi lama.
            if ($user && ! $this->domainBolehUntuk($user, $email, $kampus)) {
                return $this->gagal($this->pesanDomain($user->role));
            }

            if ($user) {
                // Google membuktikan pemilik email. Kata sandi lama dihapus karena bisa saja dibuat
                // orang lain yang lebih dulu mendaftar dengan email ini. Update langsung ke tabel
                // agar tidak melewati cast "hashed".
                User::whereKey($user->id)->update([
                    'google_id' => $google->getId(),
                    'password' => null,
                    'remember_token' => null,
                    'email_verified_at' => $user->email_verified_at ?? now(),
                ]);
                $user->refresh();
            }
        }

        if (! $user) {
            session(['google_pending' => [
                'google_id' => $google->getId(),
                'email' => $email,
                'name' => $google->getName(),
                'kampus' => $kampus,
                'sampai' => now()->addMinutes(self::MASA_LENGKAPI_MENIT)->timestamp,
            ]]);

            return redirect()->route('google.lengkapi');
        }

        if ($alasan = $user->alasanTidakBisaMasuk()) {
            return $this->gagal($alasan);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route($user->panel()['rute']));
    }

    /** Pengguna yang sudah masuk menghubungkan akun Google miliknya sendiri. */
    private function tautkan(GoogleUser $google, bool $kampus): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $samaEmail = Str::lower((string) $google->getEmail()) === Str::lower($user->email);
        $peranBoleh = in_array($user->role, GoogleRegisterRequest::PERAN, true);

        if (! $samaEmail || ! $peranBoleh) {
            return $this->gagal('Akun Google harus memakai email yang sama dengan akun CampusLink Anda.');
        }

        if (! $this->domainBolehUntuk($user, (string) $google->getEmail(), $kampus)) {
            return $this->gagal($this->pesanDomain($user->role));
        }

        $dipakaiLain = User::where('google_id', $google->getId())->where('id', '!=', $user->id)->exists();

        if ($dipakaiLain) {
            return $this->gagal('Akun Google ini sudah terhubung ke akun CampusLink lain.');
        }

        User::whereKey($user->id)->update([
            'google_id' => $google->getId(),
            'email_verified_at' => $user->email_verified_at ?? now(),
        ]);

        return redirect()->route('profil.show')->with('status', 'Akun Anda kini terverifikasi dengan Google.');
    }

    /**
     * Mahasiswa dan dosen hanya boleh menautkan akun Google kampus (lihat akunKampusValid) yang domain emailnya
     * persis milik perannya: @student.polinema.ac.id untuk mahasiswa, @polinema.ac.id untuk dosen.
     * Mitra boleh akun Google apa saja.
     */
    private function domainBolehUntuk(User $user, string $email, bool $kampus): bool
    {
        if (! in_array($user->role, [User::ROLE_MAHASISWA, User::ROLE_DOSEN], true)) {
            return true;
        }

        return $kampus && Kampus::emailSesuaiPeran($email, $user->role);
    }

    private function pending(Request $request): ?array
    {
        $pending = $request->session()->get('google_pending');

        if (! $pending || ($pending['sampai'] ?? 0) < now()->timestamp) {
            $request->session()->forget('google_pending');

            return null;
        }

        return $pending;
    }

    private function pesanDomain(string $peran): string
    {
        return ucfirst($peran).' wajib memakai akun Google '.Kampus::labelDomain($peran).'.';
    }

    private function gagal(string $pesan): RedirectResponse
    {
        /** @var User|null $user */
        $user = Auth::user();

        $tujuan = $user ? $user->panel()['rute'] : 'login';

        return redirect()->route($tujuan)->with('galat', $pesan);
    }
}
