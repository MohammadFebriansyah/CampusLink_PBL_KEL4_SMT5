<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Support\Kampus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/** Admin mengubah identitas yang tidak bisa diubah pengguna sendiri: email, NIM, dan NIDN. */
class UbahPenggunaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // akses dibatasi middleware EnsureRole:admin di route
    }

    protected function prepareForValidation(): void
    {
        $merge = [];
        foreach (['nim', 'nidn'] as $field) {
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
        // Akun admin tidak dikelola lewat panel ini, jadi 404.
        $user = User::where('role', '!=', User::ROLE_ADMIN)->findOrFail($this->route('id'));

        $email = ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)];

        // Mahasiswa dan dosen tetap wajib berdomain kampus, sama seperti saat registrasi.
        if ($user->role !== User::ROLE_MITRA) {
            $peran = $user->role;

            $email[] = function ($atribut, $nilai, $gagal) use ($peran) {
                if (! Kampus::emailSesuaiPeran((string) $nilai, $peran)) {
                    $gagal(ucfirst($peran).' wajib memakai email '.Kampus::labelDomain($peran).'.');
                }
            };
        }

        return match ($user->role) {
            User::ROLE_MAHASISWA => [
                'email' => $email,
                'nim' => ['required', 'string', 'regex:/^[0-9]{8,12}$/', Rule::unique('mahasiswa', 'nim')->ignore($user->mahasiswa?->id)],
            ],
            User::ROLE_DOSEN => [
                'email' => $email,
                'nidn' => ['required', 'digits:10', Rule::unique('dosen', 'nidn')->ignore($user->dosen?->id)],
            ],
            default => ['email' => $email],
        };
    }

    public function messages(): array
    {
        return [
            'nim.regex' => 'NIM hanya boleh berisi angka (8 sampai 12 digit).',
            'nidn.digits' => 'NIDN harus tepat 10 digit angka.',
        ];
    }
}
