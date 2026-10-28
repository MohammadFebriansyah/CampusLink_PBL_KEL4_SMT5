<?php

namespace App\Http\Resources;

use App\Models\User;
use App\Support\Inisial;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** Kartu profil di direktori mahasiswa dan dosen. */
class ProfilDirektoriResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $dosen = $this->role === User::ROLE_DOSEN;
        $profil = $dosen ? $this->dosen : $this->mahasiswa;
        $kesediaan = $dosen
            ? collect([$profil->bersedia_membimbing_magang ? 'magang' : null, $profil->bersedia_membimbing_lomba ? 'lomba' : null])->filter()->implode(' dan ')
            : '';

        return [
            'id' => $this->id,
            'nama' => $this->name,
            'dosen' => $dosen,
            'peran' => $dosen ? 'Dosen' : 'Mahasiswa',
            'google' => $this->terverifikasiGoogle(),
            'inisial' => Inisial::orang($this->name),
            'foto' => $profil->fotoUrl(),
            'keterangan' => $dosen
                ? "{$profil->program_studi}, {$profil->jurusan}"
                : "{$profil->program_studi}, {$profil->kelas}, angkatan {$profil->angkatan}",
            'kesediaan' => $kesediaan !== '' ? 'Bersedia membimbing '.$kesediaan : null,
            'keahlian' => $this->keahlian->pluck('nama')->all(),
            'url' => route('direktori.show', $this->id),
        ];
    }
}
