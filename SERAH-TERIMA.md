# CampusLink — Dokumen serah terima (Bagian 9)

Dokumen ini menggantikan petunjuk pemasangan di `docs/riwayat-bagian/BAGIAN-*.md`
(dibiarkan sebagai riwayat; beberapa langkahnya sudah tidak perlu, mis. salin-timpa per bagian).

## 1. Status

Seluruh Bagian 0–8 sudah digabung menjadi satu proyek Laravel 13 yang utuh di folder ini.
Untuk pertama kalinya kode **benar-benar dijalankan**, bukan hanya diperiksa sintaksnya.

| Pemeriksaan | Hasil |
|---|---|
| Uji otomatis (PHPUnit) | **171 uji, 924 pernyataan, semua lulus** |
| …di SQLite (konfigurasi bawaan `phpunit.xml`) | lulus |
| …di PostgreSQL 16 sungguhan (migration + RLS + seeder + seluruh uji) | lulus |
| Seluruh halaman × 5 peran (tamu, mahasiswa, dosen, mitra, admin) × desktop & ponsel, di browser Chromium | 240 kunjungan, tanpa error 500, tanpa halaman putih, tanpa luapan horizontal |
| Matriks hak akses (siapa boleh membuka apa) | sesuai rancangan, termasuk 403/404/pengalihan ke login |
| Kinerja dengan 300 mahasiswa + 300 informasi | paling banyak 11 query per halaman; tidak naik seiring jumlah data |
| Perlindungan CSRF | kiriman tanpa token ditolak (419), dicek dengan curl |

Catatan kinerja: waktu pembuatan halaman 2–22 ms di mesin uji (database lokal). Pada Supabase,
setiap query menambah latensi jaringan (umumnya puluhan ms), jadi halaman terberat (beranda 4 query,
dasbor admin 11 query) wajar berada di kisaran beberapa ratus ms. Jumlah query-nya yang sudah dijaga ketat.

## 2. Temuan dan perbaikan dalam Bagian 9

Menjalankan kode sungguhan langsung menemukan hal yang tidak tertangkap pemeriksaan statis:

1. **Halaman detail magang/lomba/beasiswa selalu 404** (5 uji gagal pada jalan pertama). Penyebab: urutan
   argumen `InformasiController::show` tertukar dengan urutan parameter route. Sudah diperbaiki.
2. **`APP_TIMEZONE` tidak berpengaruh.** `config/app.php` bawaan Laravel 13 mengunci zona waktu ke `UTC`,
   sehingga petunjuk Bagian 5 tidak bekerja. Sekarang `config/app.php` membaca `APP_TIMEZONE`
   (bawaan `Asia/Jakarta`). Tanpa ini, antara pukul 00.00 dan 07.00 WIB "hari ini" masih dihitung sebagai kemarin: informasi yang batasnya kemarin tampak masih dibuka, dan sisa hari meleset satu. (Catatan: Bagian 5 menulis arah kesalahannya terbalik.)
3. **Alamat callback Google bawaan salah** di `config/services.php` (`/masuk/google/callback`; yang benar
   `/auth/google/callback`). Bawaan itu dihapus; alamat kini wajib diisi lewat `GOOGLE_REDIRECT_URI`.
4. **Tabel di panel admin melebarkan halaman di ponsel** (elemen `sr-only` keluar dari kotak gulir).
   Diperbaiki dengan `relative` pada pembungkus tabel.
5. **Tambahan (di luar materi): header keamanan** lewat `App\Http\Middleware\SecurityHeaders`:
   `X-Content-Type-Options`, `X-Frame-Options: DENY`, `Referrer-Policy`, `Permissions-Policy`, dan HSTS hanya
   untuk permintaan HTTPS. Ada 3 uji. Content-Security-Policy **sengaja tidak dipasang**: Alpine.js versi
   standar memakai `eval`, jadi CSP yang ketat butuh build Alpine khusus.

## 3. Pemasangan dari nol

Prasyarat: PHP 8.3+ dengan ekstensi `pdo_pgsql`, `mbstring`, `gd`, `intl`, `zip`; Composer; Node 20+.

    composer install
    npm install
    npm run build
    cp .env.example .env
    php artisan key:generate

Isi `.env` (bagian Supabase dan Google). Lalu:

    php artisan migrate --seed          # hanya untuk pengembangan, lihat peringatan di bawah
    php artisan storage:link
    php artisan serve                   # buka http://localhost:8000 (bukan 127.0.0.1, demi SSO Google)

Menjalankan uji (SQLite di memori, tidak menyentuh Supabase):

    php artisan test      # atau: vendor/bin/phpunit

Uji sengaja **melewati diri sendiri** bila `DB_CONNECTION` bukan `sqlite`, supaya `RefreshDatabase`
tidak pernah menghapus database Supabase Anda.

Akun contoh (hanya pengembangan, password semuanya `password`): `admin@campuslink.test`,
`alya.putri@polinema.ac.id` (mahasiswa), `rina.kusumawati@polinema.ac.id` (dosen),
`hrd@nusantaratech.example` (mitra disetujui), `info@kreasimuda.example` (mitra pending).

Setelah `composer install` di komputer Anda, versi paket yang terpasang berasal dari Packagist dan bisa sedikit
berbeda dari yang saya pakai untuk menguji (saya membangun dari tag GitHub karena Packagist tidak terjangkau
dari lingkungan saya). **Jalankan `php artisan test` sekali setelah pemasangan** untuk memastikan.

## 4. Daftar periksa sebelum produksi

### Wajib, tidak bergantung hosting

- [ ] `APP_ENV=production`, `APP_DEBUG=false`, `APP_KEY` baru (`php artisan key:generate`), `LOG_LEVEL=warning`.
- [ ] `APP_URL` memakai `https://…`, dan situs hanya melayani HTTPS.
- [ ] `SESSION_SECURE_COOKIE=true`.
- [ ] **Jangan jalankan `--seed` di produksi.** Seeder membuat `admin@campuslink.test` berpassword `password`.
      Gunakan `php artisan migrate --force` (jangan `migrate:fresh`: itu menghapus semua tabel).
      Buat admin sungguhan satu kali lewat tinker, lalu simpan passwordnya di pengelola kata sandi:

          php artisan tinker
          >>> App\Models\User::create(['name'=>'Nama Admin','email'=>'email@anda','password'=>Hash::make('KATA-SANDI-PANJANG'),'role'=>'admin']);

- [ ] Google Cloud Console: tambahkan alamat produksi ke *Authorized redirect URIs*
      (`https://domain-anda/auth/google/callback`) dan isi `GOOGLE_REDIRECT_URI` dengan alamat yang sama persis.
- [ ] Lengkapi daftar program studi di `config/kampus.php` (saat ini 28, belum lengkap dan belum resmi).
- [ ] `npm run build` dijalankan di server/CI; `public/build` ikut terdeploy.
- [ ] Setelah deploy: `php artisan config:cache && php artisan route:cache && php artisan view:cache`.
- [ ] `php.ini`: `upload_max_filesize` dan `post_max_size` minimal 4M (batas foto 2 MB).
- [ ] Supabase: pakai Session pooler (5432) dengan `DB_SSLMODE=require`. RLS sudah aktif di semua tabel tanpa
      policy, artinya akses lewat API publik Supabase (anon key) tertutup; aplikasi memakai koneksi database langsung.
      Jangan pernah menaruh `service_role` key di frontend.
- [ ] Aktifkan backup otomatis di Supabase dan uji pemulihannya sekali.

### Bergantung pada hosting (tandai setelah Anda memutuskan tempat hosting)

- [ ] **Penyimpanan foto.** Foto disimpan di disk lokal `public`. Di hosting dengan filesystem sementara
      (kontainer/serverless/PaaS tertentu) foto **hilang saat deploy ulang**. Anda meminta file tetap ada,
      jadi bila hosting seperti itu, foto harus dipindah ke penyimpanan objek (mis. Supabase Storage atau S3).
      Itu pekerjaan tambahan yang belum ada di kode ini. Di VPS/hosting biasa dengan disk tetap, tidak perlu.
- [ ] **Proxy terbalik / penyeimbang beban.** Bila aplikasi di belakang proxy, atur *trusted proxies*
      (`$middleware->trustProxies(...)` di `bootstrap/app.php`). Tanpa itu, Laravel melihat alamat IP proxy,
      sehingga pembatas percobaan masuk (5 percobaan/menit per email+IP) menghitung semua orang sebagai satu IP,
      dan `isSecure()` bisa salah sehingga HSTS tidak terkirim.
- [ ] **Lebih dari satu server.** `SESSION_DRIVER=file` dan `CACHE_STORE=file` tidak dibagi antarserver.
      Pakai driver `database`/`redis` bila server lebih dari satu.
- [ ] Izin tulis `storage/` dan `bootstrap/cache/` untuk pengguna web server.
- [ ] Alamat email keluar (`MAIL_*`) — saat ini aplikasi tidak mengirim email, jadi `MAIL_MAILER=log` cukup.

## 5. Yang belum terverifikasi (jujur)

- **SSO Google dengan akun Google sungguhan.** Alurnya diuji dengan Socialite palsu (21 uji, termasuk
  syarat domain `polinema.ac.id`, `email_verified`, dan klaim `hd`). Belum pernah berbicara dengan Google.
  Uji sekali manual dengan akun @polinema.ac.id dan satu akun Gmail biasa (harus ditolak).
- **Koneksi ke project Supabase Anda.** Migration dan seluruh uji lulus di PostgreSQL 16 lokal, termasuk
  perintah RLS. Perilaku jaringan Supabase (pooler, batas koneksi) belum teruji.
- **Browser selain Chromium.** Tampilan diperiksa di Chromium pada lebar 1280 px dan 375 px. Firefox dan Safari
  belum dicoba. Kode tidak memakai fitur eksotis (Tailwind v4 butuh browser modern: Safari 16.4+, Chrome 111+, Firefox 128+).
- **Audit kerentanan paket (`composer audit`)** tidak bisa saya jalankan. Jalankan sendiri setelah `composer install`.
- **Beban banyak pengguna bersamaan.** Yang diukur hanya jumlah query dan waktu per permintaan, bukan uji beban.
- **Aksesibilitas** hanya diperiksa lewat markup (label, `aria-current`, `sr-only`), tidak dengan pembaca layar.

## 6. Keputusan dan batasan yang perlu Anda ketahui

- Kebijakan baca: informasi magang/lomba/beasiswa wajib masuk (semua peran boleh baca); direktori hanya
  mahasiswa dan dosen; NIM/NIDN/email tidak pernah tampil di direktori (Bagian 5 dan 7).
- Registrasi password untuk @polinema.ac.id tetap dibuka berdampingan dengan Google. Celahnya: pemeriksaan
  domain tidak membuktikan kepemilikan email, jadi seseorang bisa mendaftar dengan email rekan. Mitigasi yang ada:
  penanda "Terverifikasi" pada akun yang tertaut Google, dan penautan Google menghapus password akun penyamar.
  Bila ingin menutup celah sepenuhnya, matikan registrasi password untuk domain kampus (perubahan kecil).
- Admin tidak dapat mengelola akun admin lain (404); hanya menonaktifkan/mengaktifkan akun biasa.
- **Belum ada** (tidak diminta proposal atau di luar materi): email pemberitahuan ke mitra, alasan penolakan
  mitra, riwayat tindakan admin, ganti password dari dalam aplikasi, lupa password, pencarian lintas kategori,
  pemindahan foto ke penyimpanan objek.
- Berkas `docs/riwayat-bagian/BAGIAN-5.md` menyebut `APP_TIMEZONE` di `.env`; sekarang itu benar karena
  `config/app.php` sudah diperbaiki (bagian 2 di atas).

## 7. Peta isi

| Bagian | Isi | Uji |
|---|---|---|
| 0 | Token desain, layout, navigasi, footer | — |
| 1 | 13 migration, 8 model, seeder, RLS Supabase | seeder dijalankan di SQLite & PostgreSQL |
| 2 | Landing page | `InformasiTest`, `DirektoriTest` |
| 3, 3b | Login, registrasi 3 peran, SSO Google, RBAC | `AuthTest` 16, `GoogleAuthTest` 21 |
| 4 | Profil, foto, profil mitra | `ProfilTest` 15 |
| 5 | Daftar, cari, detail informasi | `InformasiTest` 22, `TanggalTest` 4 |
| 6 | Panel mitra: kelola informasi | `MitraInformasiTest` 21 |
| 7 | Direktori + filter | `DirektoriTest` 18 |
| 8 | Panel admin | `AdminTest` 26 |
| 9 | Penggabungan, perbaikan, header keamanan | `KeamananTest` 3 |
