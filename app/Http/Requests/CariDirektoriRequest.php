<?php

namespace App\Http\Requests;

use App\Support\Kampus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Validasi filter direktori (query string). Semua opsional, boleh dikombinasikan. */
class CariDirektoriRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // akses dibatasi middleware di route
    }

    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:100'],
            'urut' => ['nullable', 'string', 'max:30'], // kunci yang tidak dikenal diabaikan Urutan::terapkan()
            'arah' => ['nullable', Rule::in(['asc', 'desc'])],
            'per_halaman' => ['nullable', 'integer'], // di luar 10/20/50 memakai jumlah bawaan daftar (Daftar::perHalaman)
            'jenis' => ['nullable', Rule::in(['mahasiswa', 'dosen'])],
            'keahlian' => ['nullable', 'integer', 'exists:keahlian,id'],
            'jurusan' => ['nullable', Rule::in(array_keys(Kampus::jurusan()))],
            'program_studi' => ['nullable', Rule::in(Kampus::semuaProdi())],
            'kelas' => ['nullable', 'string', 'max:20'],
            'angkatan' => ['nullable', 'integer', 'between:2000,'.date('Y')],
        ];
    }

    public function attributes(): array
    {
        return ['q' => 'nama', 'jenis' => 'jenis'];
    }
}
