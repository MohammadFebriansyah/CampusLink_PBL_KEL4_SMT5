<?php

namespace App\Http\Resources;

use App\Support\Inisial;
use App\Support\Tanggal;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** Kartu verifikasi mitra di panel admin. */
class MitraVerifikasiResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nama_organisasi' => $this->nama_organisasi,
            'inisial' => Inisial::organisasi($this->nama_organisasi),
            'penanggung' => $this->user->name,
            'email' => $this->user->email,
            'mendaftar' => Tanggal::panjang($this->created_at),
            'kontak' => $this->kontak,
            'website' => $this->website ?: '-',
            'deskripsi' => $this->deskripsi,
            'status' => $this->status_verifikasi,
            'url' => [
                'setujui' => route('admin.mitra.setujui', $this->id),
                'tolak' => route('admin.mitra.tolak', $this->id),
                'akun' => route('admin.pengguna.show', $this->user->id),
            ],
        ];
    }
}
