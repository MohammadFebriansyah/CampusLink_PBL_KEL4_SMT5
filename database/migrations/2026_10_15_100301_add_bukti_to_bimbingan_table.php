<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bimbingan hanya boleh diajukan setelah mahasiswa diterima:
 * - kegiatan di CampusLink: dicek dari status pendaftaran (harus "diterima");
 * - kegiatan luar: mahasiswa mengunggah bukti diterima (PDF/JPG/PNG), disimpan di disk privat.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bimbingan', function (Blueprint $table) {
            $table->string('bukti')->nullable()->after('pesan');
        });
    }

    public function down(): void
    {
        Schema::table('bimbingan', function (Blueprint $table) {
            $table->dropColumn('bukti');
        });
    }
};
