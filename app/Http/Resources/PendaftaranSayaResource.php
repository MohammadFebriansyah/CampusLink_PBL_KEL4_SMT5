<?php

namespace App\Http\Resources;

use App\Support\Informasi;
use App\Support\StatusPeninjauan;
use App\Support\Tanggal;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** Pendaftaran milik mahasiswa di halaman Kegiatan saya. */
class PendaftaranSayaResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $info = $this->informasi;
        // Yang disembunyikan admin tidak bisa dibuka detailnya.
        $bisaDibuka = $info !== null && $info->status === 'tayang';

        return [
            'id' => $this->id,
            'kunci' => $this->informasi_type.'-'.$this->informasi_id,
            'kategori' => Informasi::LABEL[$this->informasi_type] ?? '',
            'judul' => $info?->judul ?? 'Informasi sudah dihapus',
            'penyelenggara' => $info?->penyelenggara,
            'status' => StatusPeninjauan::data($this->status),
            'waktu' => 'Dikirim '.Tanggal::panjang($this->created_at)
                .($this->ditinjau_pada ? ' · ditinjau '.Tanggal::panjang($this->ditinjau_pada) : '')
                .($info && ! $bisaDibuka ? ' · disembunyikan admin' : ''),
            'catatan' => $this->catatan,
            'url' => $bisaDibuka ? route($this->informasi_type.'.show', $info->id) : null,
        ];
    }
}
