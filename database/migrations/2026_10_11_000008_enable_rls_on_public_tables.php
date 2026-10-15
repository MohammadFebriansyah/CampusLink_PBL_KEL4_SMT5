<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Khusus Supabase (PostgreSQL). Tabel di schema "public" bisa diakses lewat REST API
 * publik Supabase. Mengaktifkan RLS tanpa policy menutup akses itu, sedangkan Laravel
 * tetap bisa membaca/menulis karena memakai koneksi database langsung (role pemilik tabel).
 * Dilewati otomatis jika driver bukan pgsql (misalnya SQLite lokal).
 *
 * Tabel baru yang dibuat kemudian perlu RLS juga (tambahkan di migration-nya).
 */
return new class extends Migration
{
    public function up(): void
    {
        $this->ubahRls('enable');
    }

    public function down(): void
    {
        $this->ubahRls('disable');
    }

    private function ubahRls(string $aksi): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            return;
        }

        $tabel = DB::select("select tablename from pg_tables where schemaname = 'public'");

        foreach ($tabel as $baris) {
            DB::statement('alter table "' . $baris->tablename . '" ' . $aksi . ' row level security');
        }
    }
};
