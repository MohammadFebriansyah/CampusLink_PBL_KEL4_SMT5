<?php

namespace App\Http\Resources;

use App\Support\Inisial;
use App\Support\Tanggal;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** Kartu hasil di daftar informasi untuk pembaca (/magang, /lomba, /beasiswa). */
class InformasiResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $kategori = $this->kategori();
        $sisa = Tanggal::sisaHari($this->batas_pendaftaran);

        // Rincian singkat per kategori. ikon: lokasi | kalender | piala
        $rincian = match ($kategori) {
            'magang' => [
                ['ikon' => 'lokasi', 'teks' => $this->lokasi ?? 'Lokasi belum ditentukan'],
                ['ikon' => 'kalender', 'teks' => Tanggal::rentang($this->periode_mulai, $this->periode_selesai)],
            ],
            'lomba' => [
                ['ikon' => 'piala', 'teks' => 'Tingkat '.ucfirst($this->tingkat)],
                ['ikon' => 'kalender', 'teks' => Tanggal::rentang($this->tanggal_mulai, $this->tanggal_selesai)],
            ],
            default => [
                ['ikon' => 'kalender', 'teks' => 'Batas '.Tanggal::panjang($this->batas_pendaftaran)],
            ],
        };

        return [
            'id' => $this->id,
            'judul' => $this->judul,
            'penyelenggara' => $this->penyelenggara,
            'inisial' => Inisial::organisasi($this->penyelenggara),
            'ringkasan' => mb_strimwidth($this->deskripsi, 0, 200, '...'),
            // Warna tenggat: dekat (≤ 7 hari), sedang (≤ 30 hari), lama.
            'sisa' => ['label' => Tanggal::labelSisa($this->batas_pendaftaran), 'varian' => match (true) {
                $sisa <= 7 => 'dekat',
                $sisa <= 30 => 'sedang',
                default => 'lama',
            }],
            'rincian' => $rincian,
            'lewat_campuslink' => $this->lewatCampuslink(),
            'url' => route($kategori.'.show', $this->id),
        ];
    }
}
