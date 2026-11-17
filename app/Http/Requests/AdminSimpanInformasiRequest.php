<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

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

    /** Cegah perubahan cara daftar dari internal ke eksternal bila sudah ada pendaftar. */
    public function after(): array
    {
        return [
            function (Validator $validator) {
                $id = $this->route('id');
                if ($id === null || $validator->errors()->has('cara_daftar')) {
                    return;
                }

                $model = self::MODEL[$this->route('kategori')] ?? null;
                if (! $model) {
                    return;
                }

                $item = $model::find($id);
                if (! $item) {
                    return;
                }

                // Cara daftar tidak boleh diubah ke eksternal agar pendaftar tidak hilang dari peninjauan.
                if ($item->cara_daftar === 'internal'
                    && $this->input('cara_daftar') === 'eksternal'
                    && $item->pendaftaran()->exists()
                ) {
                    $validator->errors()->add('cara_daftar', 'Cara daftar tidak bisa diubah karena kegiatan ini sudah punya pendaftar.');
                }
            },
        ];
    }
}
