<?php

namespace App\Http\Resources;

use App\Support\Informasi;
use App\Support\Inisial;
use App\Support\StatusPeninjauan;
use App\Support\Tanggal;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** Kartu pendaftar kegiatan di halaman peninjauan (panel admin dan mitra). */
class PendaftarResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $mhs = $this->user->mahasiswa;
        $rute = $request->route('panel') === 'admin' ? 'admin.pendaftar' : 'mitra.pendaftar';

        return [
            'id' => $this->id,
            'nama' => $this->user->name,
            'inisial' => Inisial::orang($this->user->name),
            'foto' => $mhs?->fotoUrl(),
            'keterangan' => $mhs ? "{$mhs->program_studi}, {$mhs->kelas}, angkatan {$mhs->angkatan}" : null,
            'kategori' => Informasi::LABEL[$this->informasi_type] ?? '',
            'judul' => $this->informasi?->judul ?? 'Informasi sudah dihapus',
            'mendaftar' => Tanggal::panjang($this->created_at),
            'status' => StatusPeninjauan::data($this->status),
            'url' => route($rute.'.show', $this->id),
        ];
    }
}
