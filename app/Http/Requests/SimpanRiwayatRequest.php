<?php

namespace App\Http\Requests;

use App\Models\RiwayatProfil;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/** Tambah atau ubah satu entri riwayat profil. Jenis hanya dipilih saat menambah dan tidak bisa diubah. */
class SimpanRiwayatRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // akses dibatasi middleware EnsureRole:mahasiswa di route; kepemilikan diperiksa di controller
    }

    protected function prepareForValidation(): void
    {
        $this->merge(collect(['judul', 'penerbit', 'deskripsi', 'tautan'])
            ->mapWithKeys(fn ($k) => [$k => is_string($this->input($k)) ? trim($this->input($k)) : $this->input($k)])
            ->all());
    }

    public function rules(): array
    {
        $aturan = [
            'judul' => ['required', 'string', 'max:150'],
            'penerbit' => ['nullable', 'string', 'max:150'],
            'tanggal_mulai' => ['nullable', 'date', 'before_or_equal:2100-12-31', 'after_or_equal:1950-01-01'],
            'tanggal_selesai' => array_filter([
                'nullable', 'date', 'before_or_equal:2100-12-31', 'after_or_equal:1950-01-01',
                // Dibandingkan hanya bila tanggal mulai diisi; tanpa itu Laravel menganggap "tanggal_mulai" sebagai teks tanggal.
                $this->filled('tanggal_mulai') ? 'after_or_equal:tanggal_mulai' : null,
            ]),
            'deskripsi' => ['nullable', 'string', 'max:1000'],
            // Hanya http/https: menolak javascript:, data:, dan skema lain yang bisa dijalankan saat diklik.
            'tautan' => ['nullable', 'string', 'max:500', 'url:http,https'],
        ];

        if ($this->isMethod('post')) {
            $aturan['jenis'] = ['required', Rule::in(array_keys(RiwayatProfil::JENIS))];
        }

        return $aturan;
    }

    /** Batas entri per jenis, hanya saat menambah. */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                $jenis = $this->input('jenis');
                if (! $this->isMethod('post') || $validator->errors()->has('jenis') || ! isset(RiwayatProfil::JENIS[$jenis])) {
                    return;
                }

                if ($this->user()->riwayat()->where('jenis', $jenis)->count() >= RiwayatProfil::BATAS_PER_JENIS) {
                    $validator->errors()->add('jenis', 'Maksimal ' . RiwayatProfil::BATAS_PER_JENIS . ' entri '
                        . RiwayatProfil::JENIS[$jenis]['kecil'] . '. Hapus salah satu sebelum menambah yang baru.');
                }
            },
        ];
    }

    public function attributes(): array
    {
        return [
            'judul' => 'judul',
            'penerbit' => 'penerbit atau penyelenggara',
            'tanggal_mulai' => 'tanggal mulai',
            'tanggal_selesai' => 'tanggal selesai',
            'deskripsi' => 'deskripsi',
            'tautan' => 'tautan',
        ];
    }

    public function messages(): array
    {
        return [
            'tautan.url' => 'Tautan harus alamat web lengkap yang diawali http:// atau https://.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
        ];
    }
}
