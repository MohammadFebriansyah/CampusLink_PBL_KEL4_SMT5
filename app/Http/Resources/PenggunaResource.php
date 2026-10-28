<?php

namespace App\Http\Resources;

use App\Models\User;
use App\Support\Inisial;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** Baris daftar pengguna di panel admin. @mixin User */
class PenggunaResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $profil = $this->role === User::ROLE_DOSEN ? $this->dosen : $this->mahasiswa;
        $verifikasi = $this->role === User::ROLE_MITRA && $this->mitra && $this->mitra->status_verifikasi !== 'disetujui'
            ? ($this->mitra->status_verifikasi === 'pending' ? 'Menunggu verifikasi' : 'Ditolak')
            : null;

        return [
            'id' => $this->id,
            'nama' => $this->name,
            'email' => $this->email,
            'inisial' => Inisial::orang($this->name),
            'peran' => [
                'kunci' => $this->role,
                'label' => ucfirst($this->role),
                'varian' => ['mahasiswa' => 'info', 'dosen' => 'warning', 'mitra' => 'violet'][$this->role] ?? 'neutral',
            ],
            'google' => $this->terverifikasiGoogle(),
            'aktif' => $this->status === User::STATUS_AKTIF,
            'verifikasi' => $verifikasi,
            'profil_disembunyikan' => (bool) $profil?->disembunyikan_admin,
            'url' => route('admin.pengguna.show', $this->id),
        ];
    }
}
