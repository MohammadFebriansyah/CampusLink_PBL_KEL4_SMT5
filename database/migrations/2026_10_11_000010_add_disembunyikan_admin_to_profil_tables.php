<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Terpisah dari "tampil_di_direktori" (pilihan pengguna) supaya pengguna tidak bisa
        // menampilkan kembali profil yang disembunyikan admin (FA-08).
        foreach (['mahasiswa', 'dosen'] as $tabel) {
            Schema::table($tabel, function (Blueprint $table) {
                $table->boolean('disembunyikan_admin')->default(false);
            });
        }
    }

    public function down(): void
    {
        foreach (['mahasiswa', 'dosen'] as $tabel) {
            Schema::table($tabel, function (Blueprint $table) {
                $table->dropColumn('disembunyikan_admin');
            });
        }
    }
};
