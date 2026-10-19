<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Support\Kampus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class RegisterRequest extends FormRequest
{
    /** Peran yang boleh dipilih lewat registrasi. Admin tidak termasuk. */
    public const PERAN = [User::ROLE_MAHASISWA, User::ROLE_DOSEN, User::ROLE_MITRA];

    public function authorize(): bool
    {
        return true; // akses dibatasi middleware guest di route
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['email' => Str::lower(trim((string) $this->input('email')))]);
    }

    public function rules(): array
    {
        $umum = [
            'peran' => ['required', Rule::in(self::PERAN)],
            'name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];

        $emailKampus = fn(string $peran) => [
            'required',
            'email',
            'max:255',
            'unique:users,email',
            function ($atribut, $nilai, $gagal) use ($peran) {
                if (! Kampus::emailSesuaiPeran((string) $nilai, $peran)) {
                    $gagal(ucfirst($peran) . ' wajib memakai email ' . Kampus::labelDomain($peran) . '.');
                }
            },
        ];

        return match ($this->input('peran')) {
            User::ROLE_MAHASISWA => $umum + [
                'email' => $emailKampus(User::ROLE_MAHASISWA),
                'nim' => ['required', 'string', 'max:20', 'unique:mahasiswa,nim'],
                'kelas' => ['required', 'string', 'max:20'],
                'program_studi' => ['required', Rule::in(Kampus::semuaProdi())],
                'angkatan' => ['required', 'integer', 'between:2000,' . date('Y')],
            ],
            User::ROLE_DOSEN => $umum + [
                'email' => $emailKampus(User::ROLE_DOSEN),
                'nidn' => ['required', 'string', 'max:20', 'unique:dosen,nidn'],
                'program_studi' => ['required', Rule::in(Kampus::semuaProdi())],
            ],
            User::ROLE_MITRA => $umum + [
                'email' => ['required', 'email', 'max:255', 'unique:users,email'],
                'nama_organisasi' => ['required', 'string', 'max:255'],
                'kontak' => ['required', 'string', 'max:255'],
            ],
            // Peran tidak dikenal (termasuk "admin"): hanya kolom umum, dan 'peran' akan gagal.
            default => $umum + [
                'email' => ['required', 'email', 'max:255'],
            ],
        };
    }
}
