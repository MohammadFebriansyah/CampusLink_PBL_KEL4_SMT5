<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mahasiswa', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('nim', 20)->unique();
            $table->string('kelas', 20);
            $table->string('jurusan');
            $table->string('program_studi');
            $table->unsignedSmallInteger('angkatan');
            $table->string('foto')->nullable();
            $table->string('kontak')->nullable();
            $table->text('tentang')->nullable();
            $table->text('sertifikasi')->nullable();
            $table->text('pelatihan')->nullable();
            $table->text('proyek')->nullable();
            $table->text('pencapaian')->nullable();
            $table->boolean('tampil_di_direktori')->default(true);
            $table->timestamps();

            $table->unique('user_id');          // satu akun = satu profil mahasiswa
            $table->index('program_studi');     // filter direktori
            $table->index('angkatan');          // filter direktori
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mahasiswa');
    }
};
