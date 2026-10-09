<?php

namespace App\Http\Requests\Auth;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class RegisterStudentRequest extends FormRequest
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
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                'unique:users,email',
                'regex:/^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\.ac\.id$/i',
            ],
            'nim' => ['required', 'string', 'max:100', 'unique:students,nim'],
            'study_program' => ['required', 'string', 'max:255'],
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
            'name.required' => 'Nama lengkap (sesuai KTM / SIAKAD) wajib diisi.',
            'email.required' => 'Email institusi wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.regex' => 'Email institusi harus menggunakan domain (.ac.id).',
            'email.unique' => 'Email institusi tersebut sudah terdaftar.',
            'nim.required' => 'Nomor Induk Mahasiswa (NIM) wajib diisi.',
            'nim.unique' => 'NIM tersebut sudah terdaftar.',
            'study_program.required' => 'Program studi / kampus wajib diisi.',
            'password.required' => 'Kata sandi wajib diisi.',
            'password.min' => 'Kata sandi minimal 8 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
            'terms.accepted' => 'Anda harus menyetujui Ketentuan Layanan & Kebijakan Privasi Riset Sivitas Kampus.',
        ];
    }
}
