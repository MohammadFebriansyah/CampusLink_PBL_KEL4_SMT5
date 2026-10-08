<?php

namespace App\Http\Requests\Auth;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RegisterLecturerRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'nidn_nip' => ['required', 'string', 'max:100', 'unique:lecturers,nidn'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:users,email',
                'regex:/^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.ac\.id$/i',
            ],
            'homebase' => ['required', 'string', 'max:255'],
            'expertise' => ['required', 'string', 'max:500'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'terms' => ['accepted'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama lengkap & gelar akademik wajib diisi.',
            'nidn_nip.required' => 'NIDN / NIP wajib diisi.',
            'nidn_nip.unique' => 'NIDN / NIP tersebut sudah terdaftar.',
            'email.required' => 'Email institusi dosen wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.regex' => 'Email institusi harus menggunakan domain (.ac.id).',
            'email.unique' => 'Email institusi tersebut sudah terdaftar.',
            'homebase.required' => 'Homebase perguruan tinggi & fakultas / jurusan wajib diisi.',
            'expertise.required' => 'Bidang keahlian utama wajib diisi.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
            'terms.accepted' => 'Anda harus menyetujui pakta integritas bimbingan riset.',
        ];
    }
}
