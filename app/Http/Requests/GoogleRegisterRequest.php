<?php

namespace App\Http\Requests;

use App\Models\User;
use App\Support\Kampus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Melengkapi pendaftaran setelah masuk dengan Google. Email TIDAK diminta dari formulir:
 * diambil dari sesi hasil verifikasi Google, supaya tidak bisa dipalsukan.
 */
class GoogleRegisterRequest extends FormRequest
{
    /** Hanya mahasiswa dan dosen yang masuk lewat Google. */
    public const PERAN = [User::ROLE_MAHASISWA, User::ROLE_DOSEN, User::ROLE_MITRA];

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $merge = [];
        foreach (['name', 'nim', 'nidn', 'kelas', 'nama_organisasi', 'kontak'] as $field) {
            if ($this->has($field)) {
                $merge[$field] = trim((string) $this->input($field));
            }
        }
        $this->merge($merge);
    }

    public function rules(): array
    {
        $umum = [
            'peran' => ['required', Rule::in(self::PERAN)],
            'name' => ['required', 'string', 'max:255', 'regex:/^[\pL][\pL\s.,\'\-]*$/u'],
        ];
        $prodi = ['required', Rule::in(Kampus::semuaProdi())];

        return match ($this->input('peran')) {
            User::ROLE_MAHASISWA => $umum + [
                'program_studi' => $prodi,
                'nim' => ['required', 'string', 'regex:/^[0-9]{8,12}$/', 'unique:mahasiswa,nim'],
                'kelas' => ['required', 'string', 'max:20', 'regex:/^[\pL\pN][\pL\pN \-]{0,19}$/u'],
                'angkatan' => ['required', 'integer', 'between:2000,'.date('Y')],
            ],
            User::ROLE_DOSEN => $umum + [
                'program_studi' => $prodi,
                'nidn' => ['required', 'digits:10', 'unique:dosen,nidn'],
            ],
            User::ROLE_MITRA => $umum + [
                'nama_organisasi' => ['required', 'string', 'max:255'],
                'kontak' => ['required', 'string', 'max:255'],
            ],
            default => $umum,
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
