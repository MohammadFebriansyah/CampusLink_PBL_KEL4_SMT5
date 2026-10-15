<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lomba', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('judul');
            $table->string('penyelenggara');
            $table->text('deskripsi');
            $table->text('persyaratan')->nullable();
            $table->enum('tingkat', ['kampus', 'regional', 'nasional', 'internasional']);
            $table->date('tanggal_mulai');
            $table->date('tanggal_selesai');
            $table->date('batas_pendaftaran');
            $table->string('kontak');
            $table->string('tautan_pendaftaran', 500);
            $table->enum('status', ['tayang', 'disembunyikan'])->default('tayang');
            $table->timestamps();

            $table->index('batas_pendaftaran');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lomba');
    }
};
