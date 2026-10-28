<?php

namespace App\Http\Resources;

use App\Support\Informasi;
use App\Support\StatusPeninjauan;
use App\Support\Tanggal;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** Permintaan bimbingan milik mahasiswa di halaman Kegiatan saya. */
class BimbinganSayaResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $info = $this->informasi;
        $bisaDibuka = $info !== null && $info->status === 'tayang';

        return [
            'id' => $this->id,
            'kunci' => $this->informasi_type.'-'.$this->informasi_id,
            'dosen' => $this->dosen?->name ?? 'Dosen tidak ditemukan',
            'kategori' => Informasi::LABEL[$this->informasi_type] ?? '',
            'judul' => $info?->judul ?? 'Informasi sudah dihapus',
            'status' => StatusPeninjauan::data($this->status),
            'waktu' => 'Diajukan '.Tanggal::panjang($this->created_at),
            'catatan' => $this->catatan,
            'url' => $bisaDibuka ? route($this->informasi_type.'.show', $info->id) : null,
        ];
    }
}
