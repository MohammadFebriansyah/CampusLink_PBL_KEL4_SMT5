<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

/** Ringkasan informasi milik mitra yang sedang masuk. */
class MitraDashboardController extends Controller
{
    private const KATEGORI = ['magang' => 'Magang', 'lomba' => 'Lomba', 'beasiswa' => 'Beasiswa'];

    public function __invoke(Request $request): View
    {
        $user = $request->user();
        $hariIni = today()->toDateString();

        $ringkasan = [];
        foreach (self::KATEGORI as $kunci => $label) {
            $total = $user->{$kunci}()->count();
            $dibuka = $user->{$kunci}()
                ->where('status', 'tayang')
                ->where('batas_pendaftaran', '>=', $hariIni)
                ->count();
            $disembunyikan = $user->{$kunci}()->where('status', 'disembunyikan')->count();

            $ringkasan[$kunci] = [
                'label' => $label,
                'total' => $total,
                'dibuka' => $dibuka,
                'disembunyikan' => $disembunyikan,
                'ditutup' => $total - $dibuka - $disembunyikan,
            ];
        }

        return view('mitra.dashboard', [
            'mitra' => $user->mitra,
            'ringkasan' => $ringkasan,
            'pendaftarMenunggu' => \App\Support\Informasi::pendaftarMenunggu($user),
        ]);
    }
}
