<?php

namespace App\Http\Resources;

use App\Models\User;
use App\Support\StatusInformasi;
use App\Support\Tanggal;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** Baris daftar kelola informasi (magang/lomba/beasiswa) di panel admin. */
class InformasiAdminResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $kategori = $this->kategori();
        $status = StatusInformasi::data($this->resource);
        $parameter = [$kategori, $this->id];

        $jenis = match (true) {
            ! $this->lewatCampuslink() => ['varian' => 'outline', 'label' => 'Kegiatan luar · tautan penyelenggara'],
            $this->user?->role === User::ROLE_ADMIN => ['varian' => 'info', 'label' => 'Kegiatan kampus · '.$this->pendaftaran_count.' pendaftar'],
            default => ['varian' => 'violet', 'label' => 'Mitra · '.$this->pendaftaran_count.' pendaftar'],
        };

        return [
            'id' => $this->id,
            'judul' => $this->judul,
            'penyelenggara' => $this->penyelenggara,
            'pembuat' => $this->user?->name ?? 'Pengguna terhapus',
            'jenis' => $jenis,
            'batas' => Tanggal::panjang($this->batas_pendaftaran),
            // Kegiatan luar didaftar di situs penyelenggara, jadi tidak punya pendaftar di CampusLink.
            'pendaftar' => $this->lewatCampuslink() ? $this->pendaftaran_count : null,
            'status' => $status,
            'url' => [
                // Yang disembunyikan tidak bisa dibuka pembaca, jadi tautan Lihat tidak diberikan.
                'lihat' => $status['kunci'] === StatusInformasi::DISEMBUNYIKAN ? null : route($kategori.'.show', $this->id),
                'ubah' => route('admin.informasi.edit', $parameter),
                'sembunyikan' => route('admin.informasi.sembunyikan', $parameter),
                'tampilkan' => route('admin.informasi.tampilkan', $parameter),
                'hapus' => route('admin.informasi.destroy', $parameter),
            ],
        ];
    }
}
