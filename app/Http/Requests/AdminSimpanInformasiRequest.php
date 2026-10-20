<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

/**
 * Tambah/ubah informasi oleh admin. Aturannya sama dengan mitra, ditambah penyelenggara
 * (mitra tidak mengisinya karena diambil dari profil organisasi). Kepemilikan tidak membatasi
 * admin: InformasiPolicy mengizinkan admin mengubah semuanya.
 */
class AdminSimpanInformasiRequest extends SimpanInformasiRequest
{
    public function messages(): array
    {
        return parent::messages() + [
            'tautan_pendaftaran.required_unless' => 'Tautan pendaftaran wajib diisi untuk kegiatan luar kampus.',
        ];
    }

    public function rules(): array
    {
        return [
            'penyelenggara' => ['required', 'string', 'max:255'],
            // internal = kegiatan kampus (daftar di CampusLink, admin meninjau);
            // eksternal = kegiatan luar (diarahkan ke tautan penyelenggara). Kosong = eksternal.
            'cara_daftar' => ['nullable', Rule::in(['internal', 'eksternal'])],
            // Tautan wajib untuk kegiatan luar, karena pembaca diarahkan ke sana.
            'tautan_pendaftaran' => ['required_unless:cara_daftar,internal', 'nullable', 'url:http,https', 'max:500'],
        ] + parent::rules();
    }
}
