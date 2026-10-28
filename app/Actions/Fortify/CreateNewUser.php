<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Laravel\Fortify\Contracts\CreatesNewUsers;

/**
 * Membuat akun CampusLink beserta profil perannya (kontrak Fortify CreatesNewUsers).
 *
 * Dipanggil SETELAH kepemilikan email terbukti, jadi akun langsung ditandai terverifikasi:
 *  - registrasi email: setelah kode OTP benar (App\Http\Controllers\Auth\RegisterController);
 *  - registrasi Google: setelah Google menyatakan email terverifikasi (App\Http\Controllers\Auth\GoogleController).
 *
 * Masukan sudah divalidasi sebelumnya (RegisterRequest / GoogleRegisterRequest), termasuk domain email
 * per peran, karena validasi harus terjadi sebelum kode dikirim. Kata sandi sudah berupa hash (registrasi
 * email) atau null (akun Google), agar teks kata sandi tidak pernah disimpan di sesi.
 */
class CreateNewUser implements CreatesNewUsers
{
    /**
     * @param  array{peran: string, name: string, email: string, password?: ?string, google_id?: ?string,
     *     nim?: string, kelas?: string, jurusan?: ?string, program_studi?: string, angkatan?: int,
     *     nidn?: string, nama_organisasi?: string, kontak?: string}  $input
     */
    public function create(array $input): User
    {
        // Akun dan profil dibuat bersama: gagal salah satu, batal semua.
        return DB::transaction(function () use ($input) {
            $user = User::create([
                'name' => $input['name'],
                'email' => $input['email'],
                'password' => $input['password'] ?? null,
                'role' => $input['peran'],
            ]);

            // Bukan $fillable: hanya diisi di sini, tidak pernah dari masukan formulir.
            $user->forceFill([
                'email_verified_at' => now(),
                'google_id' => $input['google_id'] ?? null,
            ])->save();

            match ($input['peran']) {
                User::ROLE_MAHASISWA => $user->mahasiswa()->create(
                    Arr::only($input, ['nim', 'kelas', 'jurusan', 'program_studi', 'angkatan'])
                ),
                User::ROLE_DOSEN => $user->dosen()->create(
                    Arr::only($input, ['nidn', 'jurusan', 'program_studi'])
                ),
                User::ROLE_MITRA => $user->mitra()->create(
                    Arr::only($input, ['nama_organisasi', 'kontak'])
                ), // status_verifikasi otomatis "pending"
                default => throw new \LogicException('Peran pendaftaran tidak valid.'),
            };

            return $user;
        });
    }
}
