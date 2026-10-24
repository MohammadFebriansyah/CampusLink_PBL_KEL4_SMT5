<?php

namespace App\Http\Controllers;

use App\Models\Beasiswa;
use App\Models\Lomba;
use App\Models\Magang;
use App\Models\Mitra;
use App\Models\User;
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

        $hitung = fn(string $peran, ?string $status = null) => (int) $perPeran
            ->where('role', $peran)
            ->when($status, fn($c) => $c->where('status', $status))
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

        return view('admin.dashboard', [
            'pengguna' => $pengguna,
            'menunggu' => (int) ($verifikasi[Mitra::STATUS_PENDING] ?? 0),
            'informasi' => $informasi,
            'pendaftarMenunggu' => \App\Support\Informasi::pendaftarMenunggu(request()->user()),
        ]);
    }
}
