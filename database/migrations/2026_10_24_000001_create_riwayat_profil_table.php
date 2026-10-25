<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Riwayat profil mahasiswa bergaya LinkedIn: satu tabel untuk empat jenis entri.
        Schema::create('riwayat_profil', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('jenis', ['sertifikasi', 'pelatihan', 'proyek', 'pencapaian']);
            $table->string('judul', 150);
            $table->string('penerbit', 150)->nullable();   // penerbit, penyelenggara, atau institusi
            $table->date('tanggal_mulai')->nullable();
            $table->date('tanggal_selesai')->nullable();
            $table->text('deskripsi')->nullable();         // maksimal 1000 karakter (divalidasi di FormRequest)
            $table->string('tautan', 500)->nullable();     // hanya http/https (divalidasi di FormRequest)
            $table->timestamps();

            $table->index(['user_id', 'jenis']);
        });

        // Lihat migration enable_rls_on_public_tables: tabel baru di Supabase perlu RLS juga.
        if (DB::getDriverName() === 'pgsql') {
            DB::statement('alter table "riwayat_profil" enable row level security');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('riwayat_profil');
    }
};
