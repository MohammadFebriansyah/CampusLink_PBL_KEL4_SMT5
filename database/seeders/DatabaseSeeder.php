<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Urutan penting: keahlian dulu, karena dipakai profil pengguna.
        $this->call([
            KeahlianSeeder::class,
            PenggunaSeeder::class,
            InformasiSeeder::class,
        ]);
    }
}
