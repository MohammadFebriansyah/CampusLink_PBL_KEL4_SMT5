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
        $merge = [];
        foreach (['name', 'nim', 'nidn', 'kelas', 'nama_organisasi', 'kontak'] as $field) {
            if ($this->has($field)) {
                $merge[$field] = trim((string) $this->input($field));
            }
        }
        if ($this->has('email')) {
            $merge['email'] = Str::lower(trim((string) $this->input('email')));
        }
        $this->merge($merge);
    }

    public function rules(): array
    {
        $umum = [
            'peran' => ['required', Rule::in(self::PERAN)],
            'name' => ['required', 'string', 'max:255', 'regex:/^[\pL][\pL\s.,\'\-]*$/u'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];

        $emailKampus = fn (string $peran) => [
            'required',
            'email',
            'max:255',
            'unique:users,email',
            function ($atribut, $nilai, $gagal) use ($peran) {
                if (! Kampus::emailSesuaiPeran((string) $nilai, $peran)) {
                    $gagal(ucfirst($peran).' wajib memakai email '.Kampus::labelDomain($peran).'.');
                }
            },
        ];

        return match ($this->input('peran')) {
            User::ROLE_MAHASISWA => $umum + [
                'email' => $emailKampus(User::ROLE_MAHASISWA),
                'nim' => ['required', 'string', 'regex:/^[0-9]{8,12}$/', 'unique:mahasiswa,nim'],
                'kelas' => ['required', 'string', 'max:20', 'regex:/^[\pL\pN][\pL\pN \-]{0,19}$/u'],
                'program_studi' => ['required', Rule::in(Kampus::semuaProdi())],
                'angkatan' => ['required', 'integer', 'between:2000,'.date('Y')],
            ],
            User::ROLE_DOSEN => $umum + [
                'email' => $emailKampus(User::ROLE_DOSEN),
                'nidn' => ['required', 'digits:10', 'unique:dosen,nidn'],
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

    public function messages(): array
    {
        return [
            'name.regex' => 'Nama harus diawali huruf dan hanya boleh berisi huruf, spasi, titik, koma, apostrof, dan tanda hubung.',
            'nim.regex' => 'NIM hanya boleh berisi angka (8 sampai 12 digit).',
            'nidn.digits' => 'NIDN harus tepat 10 digit angka.',
            'kelas.regex' => 'Kelas hanya boleh berisi huruf, angka, spasi, dan tanda hubung.',
        ];
    }
}
