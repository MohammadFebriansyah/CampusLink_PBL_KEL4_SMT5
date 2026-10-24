<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Parameter pencarian untuk daftar-daftar di panel admin. Semua opsional; tiap daftar memakai yang relevan. */
class AdminCariRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // akses dibatasi middleware EnsureRole:admin di route
    }

    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:100'],
            'peran' => ['nullable', Rule::in(['mahasiswa', 'dosen', 'mitra'])],
            'akun' => ['nullable', Rule::in(['aktif', 'nonaktif'])],
            'tayang' => ['nullable', Rule::in(['dibuka', 'ditutup', 'disembunyikan'])],
            'verifikasi' => ['nullable', Rule::in(['pending', 'disetujui', 'ditolak'])],
        ];
    }

    public function attributes(): array
    {
        return ['q' => 'kata kunci'];
    }
}
