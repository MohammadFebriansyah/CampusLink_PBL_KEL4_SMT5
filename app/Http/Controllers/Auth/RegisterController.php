<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Mail\KodeVerifikasiMail;
use App\Models\User;
use App\Support\Kampus;
use Illuminate\Auth\Events\Registered;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;

/**
 * Registrasi dengan email dan kata sandi, diverifikasi kode OTP 6 angka yang dikirim ke email.
 *
 * Akun BELUM dibuat saat formulir dikirim: data (kata sandi sudah di-hash) dan hash kode ditahan di sesi.
 * Akun baru dibuat lewat action Fortify CreateNewUser setelah kode benar, jadi email/NIM orang lain tidak
 * bisa "diserobot" oleh pendaftaran yang tidak pernah diverifikasi. Login dan logout ditangani Fortify.
 */
class RegisterController extends Controller
{
    private const MASA_KODE_MENIT = 10;

    private const BATAS_SALAH = 5;

    private const BATAS_KIRIM = 3; // per email, per masa kode (batas per IP: limiter "kirim-kode")

    private const TERKIRIM = 'terkirim';

    private const DIBATASI = 'dibatasi';

    private const GAGAL = 'gagal';

    public function create(Request $request): View
    {
        // old() menjaga tab tetap sama setelah validasi gagal; query ?peran dari tautan landing page.
        $peran = $request->old('peran', $request->query('peran'));

        if (! in_array($peran, RegisterRequest::PERAN, true)) {
            $peran = User::ROLE_MAHASISWA;
        }

        return view('auth.register', compact('peran'));
    }

    public function store(RegisterRequest $request): RedirectResponse
    {
        $data = $request->validated();

        if (isset($data['program_studi'])) {
            $data['jurusan'] = Kampus::jurusanDariProdi($data['program_studi']);
        }

        // Kata sandi langsung di-hash: teksnya tidak pernah disimpan di sesi.
        $data['password'] = Hash::make($data['password']);
        $data = Arr::except($data, ['password_confirmation']);

        $hasil = $this->kirimKode($request, $data);

        if ($hasil !== self::TERKIRIM) {
            return back()->withInput($request->except(['password', 'password_confirmation']))
                ->withErrors(['email' => $this->pesanKirim($hasil)]);
        }

        return redirect()->route('register.verifikasi');
    }

    public function verifikasi(Request $request): View|RedirectResponse
    {
        $pending = $this->pending($request);

        if (! $pending) {
            return $this->sesiBerakhir();
        }

        return view('auth.verifikasi-email', [
            'email' => $pending['data']['email'],
            'menit' => self::MASA_KODE_MENIT,
        ]);
    }

    public function konfirmasi(Request $request, CreatesNewUsers $pembuatAkun): RedirectResponse
    {
        $request->validate(
            ['kode' => ['required', 'digits:6']],
            ['kode.required' => 'Masukkan kode dari email.', 'kode.digits' => 'Kode terdiri dari 6 angka.']
        );

        $pending = $this->pending($request);

        if (! $pending) {
            return $this->sesiBerakhir();
        }

        if (! Hash::check($request->input('kode'), $pending['kode'])) {
            $pending['salah']++;

            if ($pending['salah'] >= self::BATAS_SALAH) {
                $request->session()->forget('registrasi_pending');

                return redirect()->route('register')
                    ->with('galat', 'Terlalu banyak kode salah. Silakan daftar lagi.');
            }

            $request->session()->put('registrasi_pending', $pending);

            throw ValidationException::withMessages([
                'kode' => 'Kode salah. Sisa percobaan: '.(self::BATAS_SALAH - $pending['salah']).'.',
            ]);
        }

        // Kode hanya berlaku sekali: dihapus dari sesi sebelum akun dibuat.
        $request->session()->forget('registrasi_pending');

        try {
            $user = $pembuatAkun->create($pending['data']);
        } catch (UniqueConstraintViolationException) {
            // Email atau NIM/NIDN keburu dipakai akun lain selama menunggu kode.
            return redirect()->route('register')
                ->with('galat', 'Email atau NIM/NIDN sudah terdaftar. Silakan masuk atau daftar ulang.');
        }

        event(new Registered($user));

        // Mitra harus menunggu admin, jadi tidak langsung masuk.
        if ($user->role === User::ROLE_MITRA) {
            return redirect()->route('login')->with(
                'status',
                'Email terverifikasi. Akun mitra bisa dipakai setelah diverifikasi admin.'
            );
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('profil.show')->with(
            'status',
            'Akun berhasil dibuat. Lengkapi profil Anda agar muncul di direktori.'
        );
    }

    public function kirimUlang(Request $request): RedirectResponse
    {
        if (! $this->pending($request)) {
            return $this->sesiBerakhir();
        }

        $hasil = $this->kirimKode($request);

        if ($hasil !== self::TERKIRIM) {
            return back()->withErrors(['kode' => $this->pesanKirim($hasil)]);
        }

        return back()->with('status', 'Kode baru sudah dikirim ke email Anda. Kode sebelumnya tidak berlaku lagi.');
    }

    // ------------------------------------------------------------------

    /**
     * Buat kode baru, kirim ke email, lalu simpan hash-nya di sesi (kode lama otomatis tidak berlaku).
     * Kode baru hanya menggantikan yang lama bila email benar-benar terkirim, dan kegagalan server email
     * tidak mengurangi jatah kirim.
     *
     * @param  array<string, mixed>|null  $data  data pendaftaran; null = pakai data pendaftaran di sesi
     * @return self::TERKIRIM|self::DIBATASI|self::GAGAL
     */
    private function kirimKode(Request $request, ?array $data = null): string
    {
        $data ??= $request->session()->get('registrasi_pending')['data'];
        $kunci = 'kode-email|'.$data['email'];

        if (RateLimiter::tooManyAttempts($kunci, self::BATAS_KIRIM)) {
            return self::DIBATASI;
        }

        $kode = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        try {
            Mail::to($data['email'])->send(new KodeVerifikasiMail($kode, $data['name'], self::MASA_KODE_MENIT));
        } catch (TransportExceptionInterface $e) {
            report($e);

            return self::GAGAL;
        }

        RateLimiter::hit($kunci, self::MASA_KODE_MENIT * 60);

        $request->session()->put('registrasi_pending', [
            'data' => $data,
            'kode' => Hash::make($kode),
            'salah' => 0,
            'sampai' => now()->addMinutes(self::MASA_KODE_MENIT)->timestamp,
        ]);

        return self::TERKIRIM;
    }

    private function pesanKirim(string $hasil): string
    {
        return $hasil === self::DIBATASI
            ? 'Terlalu banyak permintaan kode untuk email ini. Coba lagi beberapa menit lagi.'
            : 'Email berisi kode verifikasi gagal dikirim. Periksa alamat email Anda, lalu coba lagi beberapa saat lagi.';
    }

    /** @return array{data: array<string, mixed>, kode: string, salah: int, sampai: int}|null */
    private function pending(Request $request): ?array
    {
        $pending = $request->session()->get('registrasi_pending');

        if (! $pending || ($pending['sampai'] ?? 0) < now()->timestamp) {
            $request->session()->forget('registrasi_pending');

            return null;
        }

        return $pending;
    }

    private function sesiBerakhir(): RedirectResponse
    {
        return redirect()->route('register')
            ->with('galat', 'Kode kedaluwarsa atau sesi berakhir. Silakan daftar lagi.');
    }
}
