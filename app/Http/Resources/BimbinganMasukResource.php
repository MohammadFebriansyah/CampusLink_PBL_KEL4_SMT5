<?php

namespace App\Http\Resources;

use App\Support\Informasi;
use App\Support\Inisial;
use App\Support\StatusPeninjauan;
use App\Support\Tanggal;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** Permintaan bimbingan yang masuk ke dosen. */
class BimbinganMasukResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $u = $this->mahasiswa;
        $mhs = $u?->mahasiswa;
        $info = $this->informasi;

        return [
            'id' => $this->id,
            'mahasiswa' => [
                'nama' => $u?->name ?? 'Akun dihapus',
                'inisial' => Inisial::orang($u?->name ?? '?'),
                'foto' => $mhs?->fotoUrl(),
                'keterangan' => $mhs ? "{$mhs->program_studi}, {$mhs->kelas}, angkatan {$mhs->angkatan}" : null,
                'kontak' => trim(($u?->email ?? '').($mhs?->kontak ? ' · '.$mhs->kontak : '')),
                'keahlian' => $u ? $u->keahlian->pluck('nama')->all() : [],
            ],
            'kategori' => Informasi::LABEL[$this->informasi_type] ?? '',
            'informasi' => $info ? [
                'judul' => $info->judul,
                'penyelenggara' => $info->penyelenggara,
                'url' => route($this->informasi_type.'.show', $info->id),
                // Kegiatan CampusLink: mahasiswa pasti sudah diterima (syarat mengajukan); kegiatan luar: lihat bukti.
                'lewat_campuslink' => $info->lewatCampuslink(),
            ] : null,
            'pesan' => $this->pesan,
            'bukti' => $this->bukti ? route('bimbingan.bukti', $this->id) : null,
            'diajukan' => Tanggal::panjang($this->created_at),
            'status' => StatusPeninjauan::data($this->status),
            'catatan' => $this->catatan ?? '',
            'url' => [
                'terima' => route('bimbingan.terima', $this->id),
                'tolak' => route('bimbingan.tolak', $this->id),
            ],
        ];
    }
}
