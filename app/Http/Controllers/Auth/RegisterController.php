<?php

namespace App\Http\Controllers\Auth;

use App\Mail\KodeVerifikasiMail;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Support\Kampus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class RegisterController extends Controller
{
    private const MASA_KODE_MENIT = 10;
    private const BATAS_SALAH = 5;
    private const BATAS_KIRIM = 3; // per email, per masa kode

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

        // Akun BELUM dibuat. Data ditahan di sesi sampai kode dari email benar.
        $data['password'] = Hash::make($data['password']);
        $data = Arr::except($data, ['password_confirmation']);

        if (! $this->kirimKode($request, $data)) {
            return back()->withInput()->withErrors([
                'email' => 'Terlalu banyak permintaan kode untuk email ini. Coba lagi beberapa menit lagi.',
            ]);
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

    public function konfirmasi(Request $request): RedirectResponse
    {
        $request->validate(
            ['kode' => ['required', 'digits:6']],
            ['kode.digits' => 'Kode terdiri dari 6 angka.']
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
                'kode' => 'Kode salah. Sisa percobaan: ' . (self::BATAS_SALAH - $pending['salah']) . '.',
            ]);
        }

        try {
            $user = $this->buatAkun($pending['data']);
        } catch (UniqueConstraintViolationException) {
            // Email atau NIM/NIDN keburu dipakai akun lain selama menunggu kode.
            $request->session()->forget('registrasi_pending');

            return redirect()->route('register')
                ->with('galat', 'Email atau NIM/NIDN sudah terdaftar. Silakan masuk atau daftar ulang.');
        }

        $request->session()->forget('registrasi_pending');

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

        if (! $this->kirimKode($request)) {
            return back()->withErrors(['kode' => 'Batas kirim ulang tercapai. Coba lagi beberapa menit lagi.']);
        }

        return back()->with('status', 'Kode baru sudah dikirim ke email Anda.');
    }

// ------------------------------------------------------------------

    /** Buat kode baru, simpan hash-nya di sesi, lalu kirim ke email. False bila kena batas kirim. */
    private function kirimKode(Request $request, ?array $data = null): bool
    {
        $data ??= $request->session()->get('registrasi_pending')['data'];
        $kunci = 'kode-email|' . $data['email'];

        if (RateLimiter::tooManyAttempts($kunci, self::BATAS_KIRIM)) {
            return false;
        }

        RateLimiter::hit($kunci, self::MASA_KODE_MENIT * 60);

        $kode = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $request->session()->put('registrasi_pending', [
            'data' => $data,
            'kode' => Hash::make($kode),
            'salah' => 0,
            'sampai' => now()->addMinutes(self::MASA_KODE_MENIT)->timestamp,
        ]);

        Mail::to($data['email'])->send(new KodeVerifikasiMail($kode, $data['name'], self::MASA_KODE_MENIT));

        return true;
    }

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

    /** Akun dan profil dibuat bersama: gagal salah satu, batal semua. Password sudah berupa hash. */
    private function buatAkun(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'role' => $data['peran'],
            ]);

            match ($data['peran']) {
                User::ROLE_MAHASISWA => $user->mahasiswa()->create(
                    Arr::only($data, ['nim', 'kelas', 'jurusan', 'program_studi', 'angkatan'])
                ),
                User::ROLE_DOSEN => $user->dosen()->create(
                    Arr::only($data, ['nidn', 'jurusan', 'program_studi'])
                ),
                User::ROLE_MITRA => $user->mitra()->create(
                    Arr::only($data, ['nama_organisasi', 'kontak'])
                ),
            };

            return $user;
        });
    }
}
