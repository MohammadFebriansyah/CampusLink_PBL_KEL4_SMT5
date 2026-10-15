<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('magang', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // pembuat: mitra atau admin
            $table->string('judul');
            $table->string('penyelenggara');
            $table->text('deskripsi');
            $table->text('persyaratan');
            $table->string('lokasi')->nullable();
            $table->date('periode_mulai');
            $table->date('periode_selesai');
            $table->date('batas_pendaftaran');
            $table->string('kontak');
            $table->string('tautan_pendaftaran', 500);
            $table->enum('status', ['tayang', 'disembunyikan'])->default('tayang'); // moderasi admin
            $table->timestamps();

            $table->index('batas_pendaftaran');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('magang');
    }
};
