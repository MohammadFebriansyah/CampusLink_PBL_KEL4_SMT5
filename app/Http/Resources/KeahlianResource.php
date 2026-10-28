<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** Baris data master keahlian di panel admin. */
class KeahlianResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nama' => $this->nama,
            'dipakai' => $this->pengguna_count.' pengguna',
            'konfirmasi_hapus' => $this->pengguna_count > 0 ? 'Dilepas dari '.$this->pengguna_count.' profil. Hapus?' : 'Hapus?',
            'url' => [
                'ubah' => route('admin.keahlian.edit', $this->id),
                'hapus' => route('admin.keahlian.destroy', $this->id),
            ],
        ];
    }
}
