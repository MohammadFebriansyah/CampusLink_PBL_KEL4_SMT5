<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Support\Kampus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Pembaruan profil mahasiswa dan dosen. NIM, NIDN, email, dan peran tidak bisa diubah di sini. */
class UpdateProfilRequest extends FormRequest
{
    public function authorize(): bool
    {
        return in_array($this->user()?->role, [User::ROLE_MAHASISWA, User::ROLE_DOSEN], true);
    }

    public function rules(): array
    {
        $umum = [
            'name' => ['required', 'string', 'max:255'],
            'program_studi' => ['required', Rule::in(Kampus::semuaProdi())],
            'kontak' => ['nullable', 'string', 'max:255'],
            'tentang' => ['nullable', 'string', 'max:500'],
            'foto' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048', 'dimensions:max_width=4000,max_height=4000'],
            'hapus_foto' => ['nullable', 'boolean'],
            'tampil_di_direktori' => ['required', 'boolean'],
            'keahlian' => ['nullable', 'array', 'max:10'],
            'keahlian.*' => ['integer', 'distinct', 'exists:keahlian,id'],
        ];

        if ($this->user()->role === User::ROLE_DOSEN) {
            return $umum + [
                'bersedia_membimbing_magang' => ['required', 'boolean'],
                'bersedia_membimbing_lomba' => ['required', 'boolean'],
            ];
        }

        return $umum + [
            'kelas' => ['required', 'string', 'max:20'],
            'angkatan' => ['required', 'integer', 'between:2000,' . date('Y')],
            'sertifikasi' => ['nullable', 'string', 'max:2000'],
            'pelatihan' => ['nullable', 'string', 'max:2000'],
            'proyek' => ['nullable', 'string', 'max:2000'],
            'pencapaian' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'keahlian' => 'keahlian',
            'keahlian.*' => 'keahlian',
            'tampil_di_direktori' => 'pengaturan direktori',
            'bersedia_membimbing_magang' => 'kesediaan membimbing magang',
            'bersedia_membimbing_lomba' => 'kesediaan membimbing lomba',
        ];
    }
}
