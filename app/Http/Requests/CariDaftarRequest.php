<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Parameter umum daftar yang dirender Alpine: kata kunci, status, dan urutan. Semua opsional.
 * Akses dibatasi middleware di route; kunci urut yang tidak dikenal diabaikan oleh App\Support\Urutan.
 */
class CariDaftarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'string', 'max:20'],
            'bagian' => ['nullable', Rule::in(['pendaftaran', 'bimbingan'])],
            'urut' => ['nullable', 'string', 'max:30'],
            'arah' => ['nullable', Rule::in(['asc', 'desc'])],
            'per_halaman' => ['nullable', 'integer'], // di luar 10/20/50 memakai jumlah bawaan daftar (Daftar::perHalaman)
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return ['q' => 'kata kunci'];
    }
}
