<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

class UpdateMitraProfilRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === User::ROLE_MITRA;
    }

    protected function prepareForValidation(): void
    {
        $merge = [];
        foreach (['name', 'nama_organisasi', 'kontak', 'website'] as $field) {
            if ($this->has($field)) {
                $merge[$field] = trim((string) $this->input($field));
            }
        }
        $this->merge($merge);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', 'regex:/^[\pL][\pL\s.,\'\-]*$/u'],
            'nama_organisasi' => ['required', 'string', 'max:255'],
            'kontak' => ['required', 'string', 'max:255'],
            // Hanya http/https, supaya tautan tidak bisa berisi "javascript:" saat ditampilkan.
            'website' => ['nullable', 'url:http,https', 'max:255'],
            'deskripsi' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.regex' => 'Nama harus diawali huruf dan hanya boleh berisi huruf, spasi, titik, koma, apostrof, dan tanda hubung.',
        ];
    }

    public function attributes(): array
    {
        return ['name' => 'nama penanggung jawab'];
    }
}
