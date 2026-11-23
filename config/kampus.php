<?php

/**
 * Daftar jurusan dan program studi untuk pilihan di formulir.
 *
 * PENTING: DAFTAR INI BELUM RESMI DAN BELUM LENGKAP.
 * Disusun dari situs Polinema dan sumber pihak ketiga (2021 sampai 2026). Polinema mencatat
 * 7 jurusan (konsisten di semua sumber) dengan 44 sampai 46 program studi (D2, D3, D4, S2)
 * di kampus utama dan PSDKU, jadi daftar program studi di bawah pasti kurang.
 * Tolong cocokkan dengan data akademik, lalu tambah atau koreksi baris di sini.
 *
 * Aturan:
 * - Nama program studi harus UNIK di seluruh jurusan (jurusan ditentukan dari program studi).
 * - Menulis ulang nama yang sudah dipakai pengguna berarti data lama tidak cocok lagi
 *   dengan filter direktori. Ubah dengan hati-hati setelah ada pengguna.
 * - Jalankan `php artisan config:clear` setelah mengubah berkas ini.
 */
return [
    /*
     * Domain email warga kampus per peran, dipakai registrasi email, Google SSO, dan ubah email oleh admin.
     * @student.polinema.ac.id hanya milik mahasiswa, @polinema.ac.id hanya milik dosen. Dicocokkan
     * persis dengan bagian setelah "@" (subdomain atau domain lain yang mirip tidak lolos).
     * Akun lama dengan domain lain tetap bisa masuk; aturan ini berlaku untuk pendaftaran dan
     * penautan Google yang baru.
     */
    'domain_email_per_peran' => [
        'mahasiswa' => ['student.polinema.ac.id'],
        'dosen' => ['polinema.ac.id'],
    ],

    /*
     * Wajibkan klaim "hd" (Google Workspace) bernilai domain di atas.
     * Per dokumentasi Google, klaim ini membedakan akun yang DIKELOLA organisasi dari akun Google
     * pribadi yang kebetulan memakai alamat email kampus. Biarkan true.
     * Jika SEMUA login Google dengan akun polinema ditolak, kemungkinan akun kampus bukan Workspace:
     * hubungi admin TI kampus, jangan langsung menjadikannya false (lebih lemah).
     */
    'google_wajib_hd' => true,

    /*
     * Batas pengiriman kode OTP pendaftaran per alamat IP dalam 10 menit.
     * Melindungi sistem dari penyalahgunaan satu IP yang mengirim ke banyak email,
     * sekaligus ramah untuk pengguna yang berbagi IP publik (mis. Wi-Fi kampus).
     */
    'batas_otp_per_ip' => (int) env('BATAS_OTP_PER_IP', 30),

    'jurusan' => [
        'Teknologi Informasi' => [
            'Sistem Informasi Bisnis',
            'Teknik Informatika',
        ],
    ],
];
