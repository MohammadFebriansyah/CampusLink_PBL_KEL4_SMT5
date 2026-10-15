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

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'nama_organisasi' => ['required', 'string', 'max:255'],
            'kontak' => ['required', 'string', 'max:255'],
            // Hanya http/https, supaya tautan tidak bisa berisi "javascript:" saat ditampilkan.
            'website' => ['nullable', 'url:http,https', 'max:255'],
            'deskripsi' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function attributes(): array
    {
        return ['name' => 'nama penanggung jawab'];
    }
}
