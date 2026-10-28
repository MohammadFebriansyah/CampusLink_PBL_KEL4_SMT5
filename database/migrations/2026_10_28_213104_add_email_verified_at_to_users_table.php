<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Waktu email terbukti dimiliki pengguna: kode OTP pendaftaran benar, atau Google menyatakan
     * email terverifikasi. Akun dengan kolom kosong tidak bisa masuk (User::alasanTidakBisaMasuk()).
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('email_verified_at')->nullable()->after('email');
        });

        // Semua akun lama sudah terverifikasi: sebelum migration ini akun hanya dibuat setelah kode OTP
        // benar atau lewat Google dengan email terverifikasi, dan sisanya dibuat pengelola (seeder/admin).
        DB::table('users')->whereNull('email_verified_at')->update([
            'email_verified_at' => DB::raw('COALESCE(created_at, CURRENT_TIMESTAMP)'),
        ]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('email_verified_at');
        });
    }
};
