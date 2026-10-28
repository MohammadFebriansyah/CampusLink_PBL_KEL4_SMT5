<?php

namespace App\Http\Resources;

use App\Support\StatusInformasi;
use App\Support\Tanggal;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** Kartu informasi milik mitra di panel mitra. */
class InformasiMitraResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $kategori = $this->kategori();
        $status = StatusInformasi::data($this->resource);
        $tersembunyi = $status['kunci'] === StatusInformasi::DISEMBUNYIKAN;

        return [
            'id' => $this->id,
            'judul' => $this->judul,
            'batas' => Tanggal::panjang($this->batas_pendaftaran),
            'sisa' => $status['kunci'] === StatusInformasi::DIBUKA ? Tanggal::labelSisa($this->batas_pendaftaran) : null,
            'status' => $status,
            'pendaftar' => $this->pendaftaran_count.' pendaftar',
            'menunggu' => $this->menunggu_count > 0 ? $this->menunggu_count.' menunggu' : null,
            'url' => [
                'lihat' => $tersembunyi ? null : route($kategori.'.show', $this->id),
                'ubah' => route('mitra.informasi.edit', [$kategori, $this->id]),
                'hapus' => route('mitra.informasi.destroy', [$kategori, $this->id]),
            ],
        ];
    }
}
