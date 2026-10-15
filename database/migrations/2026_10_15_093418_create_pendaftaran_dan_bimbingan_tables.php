<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Pendaftaran mahasiswa ke informasi yang cara daftarnya internal.
        Schema::create('pendaftaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // mahasiswa pendaftar
            $table->morphs('informasi');                                     // magang | lomba | beasiswa
            $table->string('cv');                                            // path di disk privat (local)
            $table->string('status', 20)->default('menunggu');               // menunggu | diterima | ditolak
            $table->text('catatan')->nullable();                             // catatan peninjau untuk pendaftar
            $table->foreignId('ditinjau_oleh')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('ditinjau_pada')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'informasi_type', 'informasi_id']); // satu pendaftaran per kegiatan
            $table->index('status');
        });

        // Permintaan bimbingan mahasiswa ke dosen untuk magang atau lomba.
        Schema::create('bimbingan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mahasiswa_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('dosen_id')->constrained('users')->cascadeOnDelete();
            $table->morphs('informasi');                       // magang | lomba
            $table->text('pesan')->nullable();                 // pesan mahasiswa ke dosen
            $table->string('status', 20)->default('menunggu'); // menunggu | diterima | ditolak
            $table->text('catatan')->nullable();               // tanggapan dosen
            $table->timestamp('ditinjau_pada')->nullable();
            $table->timestamps();

            $table->unique(['mahasiswa_id', 'informasi_type', 'informasi_id']); // satu pembimbing per kegiatan
            $table->index(['dosen_id', 'status']);
        });

        // Lihat migration enable_rls_on_public_tables: tabel baru di Supabase perlu RLS juga.
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('alter table "pendaftaran" enable row level security');
            DB::statement('alter table "bimbingan" enable row level security');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('bimbingan');
        Schema::dropIfExists('pendaftaran');
    }
};
