<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dosen', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('nidn', 20)->unique();
            $table->string('jurusan');
            $table->string('program_studi');
            $table->string('foto')->nullable();
            $table->string('kontak')->nullable();
            $table->text('tentang')->nullable();
            $table->boolean('bersedia_membimbing_magang')->default(false);
            $table->boolean('bersedia_membimbing_lomba')->default(false);
            $table->boolean('tampil_di_direktori')->default(true);
            $table->timestamps();

            $table->unique('user_id');
            $table->index('program_studi');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dosen');
    }
};
