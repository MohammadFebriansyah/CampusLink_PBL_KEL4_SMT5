<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/** Validasi parameter pencarian (query string) pada daftar informasi. */
class CariInformasiRequest extends FormRequest
{
    public const TINGKAT_LOMBA = ['kampus', 'regional', 'nasional', 'internasional'];

    public function authorize(): bool
    {
        return true; // akses dibatasi middleware auth di route
    }

    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:100'],
            'urut' => ['nullable', 'string', 'max:30'], // kunci yang tidak dikenal diabaikan Urutan::terapkan()
            'arah' => ['nullable', Rule::in(['asc', 'desc'])],
            'per_halaman' => ['nullable', 'integer'], // di luar 10/20/50 memakai jumlah bawaan daftar (Daftar::perHalaman)
            'tingkat' => ['nullable', Rule::in(self::TINGKAT_LOMBA)],
            'pilih' => ['nullable', 'integer', 'min:1'], // informasi yang terbuka di panel samping
        ];
    }

    public function attributes(): array
    {
        return ['q' => 'kata kunci'];
    }
}
