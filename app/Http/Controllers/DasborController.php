<?php

namespace App\Http\Controllers;

use App\Models\Beasiswa;
use App\Models\Dosen;
use App\Models\Lomba;
use App\Models\Magang;
use App\Models\Mahasiswa;
use App\Models\User;
use App\Support\Informasi;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

/** Ringkasan panel mahasiswa dan dosen: kegiatan atau bimbingan, kelengkapan profil, dan informasi terdekat. Hanya membaca data. */
class DasborController extends Controller
{
    public function __invoke(Request $request): View
    {
        $user = $request->user();

        return $user->role === User::ROLE_DOSEN ? $this->dosen($user) : $this->mahasiswa($user);
    }

    private function mahasiswa(User $user): View
    {
        $profil = Mahasiswa::where('user_id', $user->id)->firstOrFail();

        return view('dasbor.mahasiswa', [
            'profil' => $profil,
            'pendaftaran' => $this->perStatus($user->pendaftaran()),
            'bimbingan' => $this->perStatus($user->bimbinganDiajukan()),
            'terbaru' => $user->pendaftaran()->with('informasi')->latest()->limit(5)->get(),
            'kelengkapan' => $this->kelengkapan($user, $profil, [
                ['Daftar pengalaman (sertifikasi, proyek, dan lainnya)', $user->riwayat()->exists()],
            ]),
            'informasi' => $this->informasiTerdekat(),
        ]);
    }

    private function dosen(User $user): View
    {
        $profil = Dosen::where('user_id', $user->id)->firstOrFail();

        return view('dasbor.dosen', [
            'profil' => $profil,
            'bimbingan' => $this->perStatus($user->bimbinganMasuk()),
            'permintaan' => $user->bimbinganMasuk()->where('status', 'menunggu')
                ->with(['mahasiswa.mahasiswa', 'informasi'])->latest()->limit(5)->get(),
            'grafik' => $this->grafikBimbingan($user),
            'kelengkapan' => $this->kelengkapan($user, $profil, [
                ['Bersedia membimbing magang atau lomba', $profil->bersedia_membimbing_magang || $profil->bersedia_membimbing_lomba],
            ]),
            'informasi' => $this->informasiTerdekat(),
        ]);
    }

    /** @return array{menunggu: int, diterima: int, ditolak: int} */
    private function perStatus(HasMany $relasi): array
    {
        $jumlah = $relasi->select('status')->selectRaw('count(*) as jumlah')->groupBy('status')->pluck('jumlah', 'status');

        return [
            'menunggu' => (int) ($jumlah['menunggu'] ?? 0),
            'diterima' => (int) ($jumlah['diterima'] ?? 0),
            'ditolak' => (int) ($jumlah['ditolak'] ?? 0),
        ];
    }

    /**
     * Daftar periksa profil yang membantu pengguna ditemukan di direktori.
     *
     * @param  list<array{string, bool}>  $tambahan
     * @return list<array{string, bool}>
     */
    private function kelengkapan(User $user, Mahasiswa|Dosen $profil, array $tambahan): array
    {
        return [
            ['Foto profil', (bool) $profil->foto],
            ['Tentang saya', filled($profil->tentang)],
            ['Kontak', filled($profil->kontak)],
            ['Bidang keahlian', $user->keahlian()->exists()],
            ...$tambahan,
            ['Tampil di direktori', $profil->tampil_di_direktori && ! $profil->disembunyikan_admin],
            ['Terverifikasi dengan Google', $user->terverifikasiGoogle()],
        ];
    }

    /**
     * Informasi yang masih dibuka dengan batas pendaftaran terdekat, dari ketiga kategori.
     *
     * @return Collection<int, Magang|Lomba|Beasiswa>
     */
    private function informasiTerdekat(): Collection
    {
        $hariIni = today()->toDateString();

        return collect(Informasi::MODEL)
            ->flatMap(fn (string $model) => $model::query()
                ->where('status', $model::STATUS_TAYANG)
                ->where('batas_pendaftaran', '>=', $hariIni)
                ->orderBy('batas_pendaftaran')
                ->limit(5)
                ->get())
            ->sortBy('batas_pendaftaran')
            ->take(5)
            ->values();
    }

    /**
     * Permintaan bimbingan yang masuk dan yang sudah dijawab, per minggu selama 8 minggu terakhir.
     * Dikelompokkan di PHP agar sama di PostgreSQL dan SQLite (seperti grafik dasbor admin).
     *
     * @return list<array{minggu: string, masuk: int, dijawab: int}>
     */
    private function grafikBimbingan(User $dosen): array
    {
        $mulai = today()->startOfWeek()->subWeeks(7);
        $masuk = $dosen->bimbinganMasuk()->where('created_at', '>=', $mulai)->pluck('created_at');
        $dijawab = $dosen->bimbinganMasuk()->where('ditinjau_pada', '>=', $mulai)->pluck('ditinjau_pada');
        $mingguKe = fn ($waktu) => (int) floor($mulai->diffInDays(Carbon::parse($waktu)->startOfDay()) / 7);

        $grafik = [];
        for ($i = 0; $i < 8; $i++) {
            $grafik[$i] = ['minggu' => $mulai->copy()->addWeeks($i)->locale('id')->translatedFormat('j M'), 'masuk' => 0, 'dijawab' => 0];
        }
        foreach (['masuk' => $masuk, 'dijawab' => $dijawab] as $kunci => $daftar) {
            foreach ($daftar as $waktu) {
                $grafik[min($mingguKe($waktu), 7)][$kunci]++;
            }
        }

        return $grafik;
    }
}
