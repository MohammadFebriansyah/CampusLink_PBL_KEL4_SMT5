<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Cara mendaftar sebuah informasi:
 * - internal  : mahasiswa mendaftar di CampusLink (unggah CV), lalu pembuat informasi menerima atau menolak.
 *               Pembuat mitra -> mitra yang meninjau. Pembuat admin (kegiatan kampus) -> admin yang meninjau.
 * - eksternal : tombol "Daftar" membuka tautan penyelenggara di luar CampusLink (kegiatan luar yang
 *               ditambahkan admin). Tidak ada terima/tolak di CampusLink.
 *
 * Data lama: milik mitra menjadi internal, milik admin tetap eksternal (seperti perilaku sebelumnya).
 * Tautan pendaftaran kini boleh kosong karena hanya wajib untuk eksternal.
 */
return new class extends Migration
{
    private const TABEL = ['magang', 'lomba', 'beasiswa'];

    public function up(): void
    {
        foreach (self::TABEL as $tabel) {
            Schema::table($tabel, function (Blueprint $table) {
                $table->string('cara_daftar', 20)->default('eksternal')->after('tautan_pendaftaran');
                $table->string('tautan_pendaftaran', 500)->nullable()->change();
            });

            DB::table($tabel)
                ->whereIn('user_id', DB::table('users')->where('role', 'mitra')->select('id'))
                ->update(['cara_daftar' => 'internal']);
        }
    }

    public function down(): void
    {
        foreach (self::TABEL as $tabel) {
            Schema::table($tabel, function (Blueprint $table) {
                $table->dropColumn('cara_daftar');
            });
        }
    }
};
