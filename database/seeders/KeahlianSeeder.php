<?php

namespace Database\Seeders;

use App\Models\Keahlian;
use Illuminate\Database\Seeder;

class KeahlianSeeder extends Seeder
{
    public function run(): void
    {
        $daftar = [
            'Web Development',
            'Mobile Development',
            'UI/UX Design',
            'Basis Data',
            'Analisis Data',
            'Machine Learning',
            'Jaringan Komputer',
            'Keamanan Siber',
            'Manajemen Proyek',
            'Analisis Bisnis',
            'Desain Grafis',
            'Penulisan Karya Ilmiah',
            'Public Speaking',
            'Kewirausahaan',
        ];

        foreach ($daftar as $nama) {
            Keahlian::create(['nama' => $nama]);
        }
    }
}
