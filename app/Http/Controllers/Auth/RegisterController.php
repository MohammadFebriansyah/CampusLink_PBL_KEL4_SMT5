<?php

namespace App\Http\Controllers\Auth;

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

        // Pengguna hanya memilih program studi. Jurusan diturunkan agar selalu sinkron.
        if (isset($data['program_studi'])) {
            $data['jurusan'] = Kampus::jurusanDariProdi($data['program_studi']);
        }

        // Akun dan profil dibuat bersama: gagal salah satu, batal semua.
        $user = DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => $data['peran'], // dari daftar tertutup di RegisterRequest::PERAN
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
                ), // status_verifikasi otomatis "pending"
            };

            return $user;
        });

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
}
