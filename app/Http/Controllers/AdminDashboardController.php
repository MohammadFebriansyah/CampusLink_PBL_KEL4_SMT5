<?php

namespace App\Http\Controllers;

use App\Models\Beasiswa;
use App\Models\Lomba;
use App\Models\Magang;
use App\Models\Mitra;
use App\Models\Pendaftaran;
use App\Models\User;
use App\Support\Informasi;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\View\View;

/** Ringkasan jumlah pengguna dan informasi (FA-07). */
class AdminDashboardController extends Controller
{
    public function __invoke(): View
    {
        // Satu query untuk semua kombinasi peran x status akun.
        $perPeran = User::query()
            ->select('role', 'status')->selectRaw('count(*) as jumlah')
            ->groupBy('role', 'status')->get();

        $hitung = fn (string $peran, ?string $status = null) => (int) $perPeran
            ->where('role', $peran)
            ->when($status, fn ($c) => $c->where('status', $status))
            ->sum('jumlah');

        $pengguna = [];
        foreach (['mahasiswa' => 'Mahasiswa', 'dosen' => 'Dosen', 'mitra' => 'Mitra'] as $peran => $label) {
            $pengguna[$peran] = [
                'label' => $label,
                'total' => $hitung($peran),
                'nonaktif' => $hitung($peran, User::STATUS_NONAKTIF),
            ];
        }

        $verifikasi = Mitra::query()
            ->select('status_verifikasi')->selectRaw('count(*) as jumlah')
            ->groupBy('status_verifikasi')->pluck('jumlah', 'status_verifikasi');

        $hariIni = today()->toDateString();
        $informasi = [];
        foreach (['magang' => [Magang::class, 'Magang'], 'lomba' => [Lomba::class, 'Lomba'], 'beasiswa' => [Beasiswa::class, 'Beasiswa']] as $kunci => [$model, $label]) {
            $total = $model::count();
            $disembunyikan = $model::where('status', $model::STATUS_DISEMBUNYIKAN)->count();
            $dibuka = $model::where('status', $model::STATUS_TAYANG)->where('batas_pendaftaran', '>=', $hariIni)->count();

            $informasi[$kunci] = [
                'label' => $label,
                'total' => $total,
                'dibuka' => $dibuka,
                'disembunyikan' => $disembunyikan,
                'ditutup' => $total - $dibuka - $disembunyikan,
            ];
        }

        $admin = request()->user();

        return view('admin.dashboard', [
            'pengguna' => $pengguna,
            'menunggu' => (int) ($verifikasi[Mitra::STATUS_PENDING] ?? 0),
            'informasi' => $informasi,
            'pendaftarMenunggu' => Informasi::pendaftarMenunggu($admin),
            'grafik' => $this->grafikPendaftar($admin),
            'kegiatan' => $this->kegiatanKampus($admin),
            'aktivitas' => $this->aktivitasTerbaru($admin),
        ]);
    }

    /**
     * Pendaftar kegiatan kampus yang masuk dan yang sudah ditinjau, per minggu selama 8 minggu terakhir.
     * Dikelompokkan di PHP agar sama di PostgreSQL dan SQLite.
     *
     * @return list<array{minggu: string, masuk: int, ditinjau: int}>
     */
    private function grafikPendaftar(User $admin): array
    {
        $mulai = today()->startOfWeek()->subWeeks(7);
        $milikAdmin = Pendaftaran::query()
            ->whereHasMorph('informasi', array_values(Informasi::MODEL), fn (Builder $q) => Informasi::batasiPeninjau($q, $admin));

        $masuk = (clone $milikAdmin)->where('created_at', '>=', $mulai)->pluck('created_at');
        $ditinjau = (clone $milikAdmin)->where('ditinjau_pada', '>=', $mulai)->pluck('ditinjau_pada');
        $mingguKe = fn ($waktu) => (int) floor($mulai->diffInDays(Carbon::parse($waktu)->startOfDay()) / 7);

        $grafik = [];
        for ($i = 0; $i < 8; $i++) {
            $grafik[$i] = ['minggu' => $mulai->copy()->addWeeks($i)->locale('id')->translatedFormat('j M'), 'masuk' => 0, 'ditinjau' => 0];
        }
        foreach (['masuk' => $masuk, 'ditinjau' => $ditinjau] as $kunci => $daftar) {
            foreach ($daftar as $waktu) {
                $grafik[min($mingguKe($waktu), 7)][$kunci]++;
            }
        }

        return $grafik;
    }

    /**
     * Kegiatan kampus (buatan admin, daftar lewat CampusLink) yang masih dibuka, dengan progres peninjauan.
     *
     * @return Collection<int, Magang|Lomba|Beasiswa>
     */
    private function kegiatanKampus(User $admin): Collection
    {
        return collect(Informasi::MODEL)
            ->flatMap(fn (string $model) => Informasi::batasiPeninjau($model::query(), $admin)
                ->where('status', $model::STATUS_TAYANG)
                ->where('batas_pendaftaran', '>=', today()->toDateString())
                ->withCount([
                    'pendaftaran',
                    'pendaftaran as ditinjau_count' => fn ($q) => $q->where('status', '!=', Pendaftaran::STATUS_MENUNGGU),
                ])
                ->with(['pendaftaran' => fn ($q) => $q->latest()->limit(4), 'pendaftaran.user.mahasiswa'])
                ->orderBy('batas_pendaftaran')
                ->limit(4)
                ->get())
            ->sortBy('batas_pendaftaran')
            ->take(4)
            ->values();
    }

    /**
     * Gabungan kejadian terbaru: akun baru, informasi baru, dan pendaftar kegiatan kampus.
     *
     * @return Collection<int, array{nama: string, aksi: string, waktu: Carbon}>
     */
    private function aktivitasTerbaru(User $admin): Collection
    {
        $jumlah = 6;
        $peran = ['mahasiswa' => 'mahasiswa', 'dosen' => 'dosen', 'mitra' => 'mitra'];

        $akun = User::query()->where('role', '!=', User::ROLE_ADMIN)->latest()->limit($jumlah)->get()
            ->map(fn (User $u) => ['nama' => $u->name, 'aksi' => 'mendaftar sebagai '.($peran[$u->role] ?? $u->role), 'waktu' => $u->created_at]);

        $info = collect(Informasi::MODEL)
            ->flatMap(fn (string $model, string $kategori) => $model::query()->with('user')->latest()->limit($jumlah)->get()
                ->map(fn ($item) => [
                    'nama' => $item->user?->name ?? 'Pengguna terhapus',
                    'aksi' => 'menambahkan '.mb_strtolower(Informasi::LABEL[$kategori]).' "'.$item->judul.'"',
                    'waktu' => $item->created_at,
                ]));

        $daftar = Pendaftaran::query()
            ->whereHasMorph('informasi', array_values(Informasi::MODEL), fn (Builder $q) => Informasi::batasiPeninjau($q, $admin))
            ->with(['user', 'informasi'])->latest()->limit($jumlah)->get()
            ->map(fn (Pendaftaran $p) => ['nama' => $p->user->name, 'aksi' => 'mendaftar ke "'.($p->informasi?->judul ?? '-').'"', 'waktu' => $p->created_at]);

        return $akun->concat($info)->concat($daftar)
            ->filter(fn (array $a) => $a['waktu'] !== null)
            ->sortByDesc('waktu')
            ->take($jumlah)
            ->values();
    }
}
