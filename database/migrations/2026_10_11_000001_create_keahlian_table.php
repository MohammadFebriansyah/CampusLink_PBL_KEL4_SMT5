<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('keahlian', function (Blueprint $table) {
            $table->id();
            $table->string('nama')->unique();
            $table->timestamps();
        });

        // Pivot many-to-many: pengguna (mahasiswa/dosen) <-> keahlian.
        Schema::create('pengguna_keahlian', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('keahlian_id')->constrained('keahlian')->cascadeOnDelete();
            $table->primary(['user_id', 'keahlian_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengguna_keahlian');
        Schema::dropIfExists('keahlian');
    }
};
