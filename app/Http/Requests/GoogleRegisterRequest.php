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

    public function rules(): array
    {
        $umum = [
            'peran' => ['required', Rule::in(self::PERAN)],
            'name' => ['required', 'string', 'max:255'],
        ];
        $prodi = ['required', Rule::in(Kampus::semuaProdi())];

        return match ($this->input('peran')) {
            User::ROLE_MAHASISWA => $umum + [
                'program_studi' => $prodi,
                'nim' => ['required', 'string', 'max:20', 'unique:mahasiswa,nim'],
                'kelas' => ['required', 'string', 'max:20'],
                'angkatan' => ['required', 'integer', 'between:2000,' . date('Y')],
            ],
            User::ROLE_DOSEN => $umum + [
                'program_studi' => $prodi,
                'nidn' => ['required', 'string', 'max:20', 'unique:dosen,nidn'],
            ],
            User::ROLE_MITRA => $umum + [
                'nama_organisasi' => ['required', 'string', 'max:255'],
                'kontak' => ['required', 'string', 'max:255'],
            ],
            default => $umum,
        };
    }
}
