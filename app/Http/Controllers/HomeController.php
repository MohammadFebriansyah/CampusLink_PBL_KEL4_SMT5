<?php

namespace App\Http\Controllers;

use App\Models\Beasiswa;
use App\Models\Lomba;
use App\Models\Magang;
use App\Models\Mitra;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $kategori = [
            'magang' => 'Magang',
            'lomba' => 'Lomba',
            'beasiswa' => 'Beasiswa',
        ];

        $model = [
            'magang' => Magang::class,
            'lomba' => Lomba::class,
            'beasiswa' => Beasiswa::class,
        ];

        // Tiga informasi tayang dengan batas pendaftaran terdekat untuk tiap kategori.
        $informasi = [];
        $jumlah = [];
        foreach ($model as $kunci => $kelas) {
            // Jumlah informasi yang masih dibuka, untuk angka ringkas di hero.
            $jumlah[$kunci] = $kelas::where('status', $kelas::STATUS_TAYANG)
                ->where('batas_pendaftaran', '>=', today()->toDateString())
                ->count();

            $informasi[$kunci] = $kelas::where('status', $kelas::STATUS_TAYANG)
                ->where('batas_pendaftaran', '>=', today()->toDateString())
                ->orderBy('batas_pendaftaran')
                ->take(3)
                ->get();
        }

        $mitra = Mitra::where('status_verifikasi', Mitra::STATUS_DISETUJUI)
            ->orderBy('nama_organisasi')
            ->take(6)
            ->pluck('nama_organisasi');

        return view('home', compact('kategori', 'informasi', 'jumlah', 'mitra'));
    }
}
