<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Akun yang masuk lewat Google tidak punya kata sandi.
            $table->string('password')->nullable()->change();

            // ID unik akun Google ("sub"). Terisi berarti kepemilikan email sudah dibuktikan Google.
            $table->string('google_id')->nullable()->unique();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['google_id']);
            $table->dropColumn('google_id');
            // Kolom password sengaja dibiarkan nullable: mengembalikannya ke NOT NULL
            // gagal bila sudah ada akun Google tanpa kata sandi.
        });
    }
};
