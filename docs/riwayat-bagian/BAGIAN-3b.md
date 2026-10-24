# CampusLink — Bagian 3b: Masuk dan daftar dengan Google (SSO)

Mahasiswa dan dosen dapat masuk/daftar dengan akun Google @polinema.ac.id. Email + password
tetap tersedia berdampingan (keputusan Anda). Mitra dan admin tetap hanya email + password.

## Pasang

1. Paket (di luar materi, disetujui):

       composer require laravel/socialite

2. Salin ke proyek, timpa file yang sama:

       app/Http/Controllers/Auth/GoogleController.php   (baru)
       app/Http/Requests/GoogleRegisterRequest.php      (baru)
       app/Models/User.php                              (menimpa, + terverifikasiGoogle())
       config/kampus.php                                (menimpa, + domain_email & google_wajib_hd.
                                                         DAFTAR PRODI IKUT TERTIMPA: bila sudah Anda
                                                         koreksi, pindahkan koreksinya ke berkas ini)
       database/migrations/2026_10_11_000009_add_google_to_users_table.php   (baru)
       routes/web.php                                   (menimpa)
       resources/views/auth/login.blade.php             (menimpa)
       resources/views/auth/register.blade.php          (menimpa)
       resources/views/auth/google-lengkapi.blade.php   (baru)
       resources/views/profil/edit.blade.php            (menimpa, + kartu verifikasi)
       resources/views/partials/flash.blade.php         (menimpa, + pesan galat)
       tests/Feature/GoogleAuthTest.php                 (baru)

3. Tambahkan ke `config/services.php` (di dalam array return):

       'google' => [
           'client_id' => env('GOOGLE_CLIENT_ID'),
           'client_secret' => env('GOOGLE_CLIENT_SECRET'),
           'redirect' => env('GOOGLE_REDIRECT_URI'),
       ],

4. `.env` (jangan di-commit):

       GOOGLE_CLIENT_ID=...
       GOOGLE_CLIENT_SECRET=...
       GOOGLE_REDIRECT_URI=http://localhost:8000/auth/google/callback

   Buka situs lewat `http://localhost:8000`, JANGAN `127.0.0.1`. Host yang berbeda dari redirect
   membuat cookie sesi tidak terbawa dan Google menolak dengan "state tidak valid".

5. Migration (aman, tidak menghapus data):

       php artisan migrate

   Menambah kolom `google_id` (unik) dan membuat `password` boleh kosong.

## Siapkan OAuth di Google Cloud Console

Tampilan konsol Google sering berubah, jadi ikuti istilahnya, bukan letak menunya.

1. Buat project, lalu atur layar persetujuan (OAuth consent screen / Google Auth Platform).
   - Jika project berada di bawah organisasi Workspace Polinema, pilih jenis **Internal**
     (hanya akun kampus yang bisa masuk).
   - Jika tidak, pilih **External**. Selama berstatus "Testing", hanya akun yang didaftarkan sebagai
     test user yang bisa masuk. Untuk dipakai umum, status harus dipublikasikan (production).
   - Lingkup (scope) cukup bawaan: openid, email, profile.
2. Buat kredensial: OAuth client ID, tipe **Web application**.
3. Authorized redirect URI: persis sama dengan `GOOGLE_REDIRECT_URI`
   (satu URI untuk lokal, satu lagi untuk domain produksi berskema https).
4. Salin client ID dan secret ke `.env`.

## Uji otomatis

Syarat sama seperti bagian sebelumnya (phpunit.xml memakai sqlite :memory:).

    php artisan test --filter=GoogleAuthTest

Socialite ditiru (mock), jadi yang terbukti adalah keputusan server atas data dari Google, bukan
koneksi ke Google. 21 metode (5 di antaranya dijalankan dengan 5 variasi data) mencakup:
akun tanpa klaim `hd`, `hd` domain lain, email belum diverifikasi, subdomain, kegagalan dari Google,
pendaftaran mahasiswa/dosen, email tidak bisa dipalsukan lewat formulir, sesi melengkapi data
kedaluwarsa 15 menit, penautan akun lama (password lama dihapus), admin/mitra ditolak,
akun nonaktif ditolak, dan penautan saat sudah masuk.

## Cek manual dengan Google sungguhan

1. /masuk: klik "Masuk dengan Google", pilih akun @polinema.ac.id. Akun baru masuk ke formulir
   NIM/NIDN dan program studi, lalu langsung masuk.
2. Coba akun Gmail pribadi: ditolak dengan pesan "Hanya akun Google @polinema.ac.id".
3. Akun password lama (mis. alya.putri@polinema.ac.id dari seeder): masuk dengan Google memakai
   email yang sama. Berhasil, lalu password lama tidak bisa dipakai lagi.
4. Profil akun yang belum terverifikasi menampilkan kartu "Verifikasi dengan Google". Setelah
   dihubungkan, kartu berganti label "Terverifikasi dengan Google".

## Cara kerja dan keputusan desain

- **Tiga syarat berlapis di server**: `email_verified` benar, email berakhiran @polinema.ac.id,
  dan klaim `hd` sama dengan polinema.ac.id. Dokumentasi Google menyatakan domain di email saja
  tidak cukup membuktikan akun dikelola organisasi: akun Google pribadi bisa memakai alamat apa pun,
  sedangkan `hd` hanya ada pada akun Workspace. Parameter `hd` yang dikirim ke Google hanya
  petunjuk tampilan, bukan pengaman.
- **Email pendaftar Google berasal dari sesi hasil verifikasi**, bukan dari formulir. Peran hanya
  mahasiswa atau dosen. Data sementara kedaluwarsa dalam 15 menit.
- **Akun Google tidak punya password.** Mencoba masuk dengan password selalu gagal.
- **Menautkan akun lama menghapus password lamanya.** Alasannya: karena registrasi password tetap
  terbuka, seseorang bisa lebih dulu mendaftar memakai email polinema milik orang lain. Saat pemilik
  asli masuk dengan Google, kepemilikan terbukti, jadi password buatan orang lain dibuang agar akun
  itu tidak bisa dipakai penyamar lagi. Pemilik yang menautkan dari halaman profil (sudah masuk)
  tidak kehilangan password.
- **Admin dan mitra ditolak lewat Google**, walau emailnya sama. Mereka memakai email + password.

## Celah yang masih ada (akibat keputusan registrasi password tetap dibuka)

Akun mahasiswa/dosen yang mendaftar dengan password dan belum menautkan Google BELUM terbukti
milik pemegang email. Selama itu, siapa pun bisa mendaftar atas nama email polinema orang lain.
Yang sudah dilakukan: profil menawarkan verifikasi Google dan mencabut password penyamar saat
pemilik asli menautkan. Yang direncanakan: Bagian 7 (direktori) menandai profil terverifikasi,
sehingga profil tanpa tanda bisa dibedakan. Untuk menutup celah sepenuhnya, registrasi password
bagi email polinema perlu ditutup. Itu cukup satu perubahan di RegisterRequest bila Anda berubah pikiran.

## Jika SEMUA login Google akun polinema ditolak

Kemungkinan akun kampus bukan akun Google Workspace (tidak membawa klaim `hd`). Jangan langsung
mematikan pemeriksaan. Tanyakan ke admin TI kampus. Bila memang harus, atur
`'google_wajib_hd' => false` di config/kampus.php. Ini lebih lemah: akun Google pribadi yang memakai
alamat polinema akan lolos. Per Google, hasilnya kurang bisa dipercaya untuk email non-Gmail.

## Produksi

Gunakan HTTPS, daftarkan URI https di Google Cloud, `SESSION_SECURE_COOKIE=true`.

## Belum teruji

Alur OAuth ke Google sungguhan tidak bisa dijalankan di lingkungan saya. Yang belum pernah berjalan:
pengalihan nyata, kecocokan nama kunci data dari Google (`email_verified`, `hd`) pada versi Socialite
Anda, dan kode ini secara keseluruhan. Bila cek manual nomor 1 gagal, kirim pesan galatnya.
