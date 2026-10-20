<?php

namespace App\Http\Requests;

use App\Models\Beasiswa;
use App\Models\Lomba;
use App\Models\Magang;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Tambah dan ubah informasi. Kategori dan (saat ubah) id diambil dari route.
 * Kepemilikan diperiksa di authorize(), SEBELUM validasi, sehingga pemilik lain selalu mendapat 403.
 */
class SimpanInformasiRequest extends FormRequest
{
    public const MODEL = [
        'magang' => Magang::class,
        'lomba' => Lomba::class,
        'beasiswa' => Beasiswa::class,
    ];

    public function authorize(): bool
    {
        $model = self::MODEL[$this->route('kategori')] ?? abort(404);
        $id = $this->route('id');

        if ($id === null) {
            return $this->user()->can('create', $model);
        }

        return $this->user()->can('update', $model::findOrFail($id));
    }

    public function rules(): array
    {
        // Saat tambah, batas pendaftaran tidak boleh lampau. Saat ubah dibolehkan, itulah cara
        // menutup pendaftaran lebih awal.
        $batas = ['required', 'date'];
        if ($this->isMethod('post')) {
            $batas[] = 'after_or_equal:today';
        }

        $umum = [
            'judul' => ['required', 'string', 'max:255'],
            'deskripsi' => ['required', 'string', 'max:5000'],
            'batas_pendaftaran' => $batas,
            'kontak' => ['required', 'string', 'max:255'],
            // Hanya http/https, agar tautan tidak bisa berisi "javascript:".
            // Mitra: opsional, karena pendaftaran berjalan di CampusLink (tautan hanya info tambahan).
            'tautan_pendaftaran' => ['nullable', 'url:http,https', 'max:500'],
        ];

        return match ($this->route('kategori')) {
            'magang' => $umum + [
                'persyaratan' => ['required', 'string', 'max:5000'],
                'lokasi' => ['nullable', 'string', 'max:255'],
                'periode_mulai' => ['required', 'date'],
                'periode_selesai' => ['required', 'date', 'after_or_equal:periode_mulai'],
            ],
            'lomba' => $umum + [
                'persyaratan' => ['nullable', 'string', 'max:5000'],
                'tingkat' => ['required', Rule::in(CariInformasiRequest::TINGKAT_LOMBA)],
                'tanggal_mulai' => ['required', 'date'],
                'tanggal_selesai' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            ],
            'beasiswa' => $umum + [
                'persyaratan' => ['required', 'string', 'max:5000'],
            ],
            default => abort(404),
        };
    }

    public function messages(): array
    {
        return [
            'batas_pendaftaran.after_or_equal' => 'Batas pendaftaran tidak boleh sebelum hari ini.',
            'periode_selesai.after_or_equal' => 'Periode selesai tidak boleh sebelum periode mulai.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
        ];
    }
}
