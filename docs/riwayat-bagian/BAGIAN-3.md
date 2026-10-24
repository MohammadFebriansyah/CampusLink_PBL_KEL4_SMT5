# CampusLink — Bagian 3: Autentikasi & RBAC

Registrasi tiga peran, login (throttle + regenerasi session), logout, middleware peran,
dan status verifikasi mitra. Memakai email + password sesuai materi. SSO Google belum ada
(rencana 3b, lihat bagian "Keamanan" di bawah).

## Pasang

Salin ke proyek, timpa file yang sama:

    app/Http/Controllers/Auth/LoginController.php      (baru)
    app/Http/Controllers/Auth/RegisterController.php   (baru)
    app/Http/Middleware/EnsureRole.php                 (baru)
    app/Http/Requests/LoginRequest.php                 (baru)
    app/Http/Requests/RegisterRequest.php              (baru)
    app/Models/User.php                                (menimpa Bagian 1, + 2 method)
    lang/id/validation.php                             (baru, pesan validasi Indonesia)
    routes/web.php                                     (menimpa)
    resources/views/auth/login.blade.php               (baru)
    resources/views/auth/register.blade.php            (baru)
    resources/views/partials/field.blade.php           (baru)
    resources/views/partials/flash.blade.php           (baru)
    resources/views/partials/nav.blade.php             (menimpa Bagian 0)
    resources/views/layouts/app.blade.php              (menimpa Bagian 0)
    tests/Feature/AuthTest.php                         (baru)

Tambahkan di `.env`:

    APP_LOCALE=id
    APP_FALLBACK_LOCALE=en

Tidak perlu mengubah `bootstrap/app.php`: middleware dipasang lewat nama kelas di route.
Tidak ada migration baru.

## Uji otomatis

Buka `phpunit.xml` dan pastikan dua baris ini AKTIF (tidak dikomentari):

    <env name="DB_CONNECTION" value="sqlite"/>
    <env name="DB_DATABASE" value=":memory:"/>

Lalu jalankan:

    php artisan test --filter=AuthTest

Uji ini memakai RefreshDatabase yang menghapus seluruh tabel. Karena itu berkasnya
sengaja melewati (skip) semua uji bila DB_CONNECTION bukan sqlite, supaya Supabase aman.
Jika semua uji terlewat, itu tandanya baris di atas belum aktif.

16 uji mencakup: email non-polinema ditolak (termasuk subdomain dan domain palsu),
peran admin tidak bisa dipilih, mitra berstatus pending, login dibatasi 5 percobaan,
mitra pending/akun nonaktif tidak bisa login, akses lintas peran menghasilkan 403,
dan pengguna yang dinonaktifkan saat sesi berjalan langsung dikeluarkan.

## Cek manual

Seeder Bagian 1 (password `password`):

1. Masuk sebagai alya.putri@polinema.ac.id: diarahkan ke /profil (placeholder), nav menampilkan
   "Profil" dan "Keluar". Buka /admin: 403.
2. Masuk sebagai admin@campuslink.test: ke /admin. Buka /profil: 403.
3. Masuk sebagai info@kreasimuda.example (mitra pending): ditolak dengan pesan menunggu verifikasi.
4. Masuk sebagai hrd@nusantaratech.example (mitra disetujui): ke /mitra.
5. Daftar sebagai mahasiswa dengan email gmail: ditolak. Daftar sebagai mitra dengan gmail: berhasil, tidak langsung masuk.
6. Salah password 5 kali: percobaan ke-6 ditolak walau password benar.

## Keputusan desain

- **Peran dipilih lewat tab** (Mahasiswa / Dosen / Mitra), dirender server. Tanpa JavaScript,
  jadi tidak ada input ganda dari tab tersembunyi. Berpindah tab memuat ulang halaman.
- **`role` tidak pernah dibaca dari input bebas.** RegisterRequest membatasi pada daftar
  tertutup mahasiswa/dosen/mitra. Admin hanya lewat seeder.
- **Akun + profil dibuat dalam satu transaksi.** Gagal di tengah berarti tidak ada akun yatim.
- **Pesan login tidak membocorkan akun.** Password salah dan akun nonaktif sama-sama
  "Email atau kata sandi salah". Pesan "menunggu verifikasi" hanya muncul setelah password benar.
- **Throttle per email + IP**, 5 percobaan per 60 detik, memakai cache store.
- **Status akun diperiksa di setiap request terlindungi** (EnsureRole), bukan hanya saat login.
- **Jurusan dan program studi masih teks bebas.** Risiko: penulisan berbeda ("TI" vs
  "Teknologi Informasi") merusak filter direktori di Bagian 7. Usul: ubah jadi pilihan
  tetap saat Bagian 4 (edit profil). Perlu daftar resmi dari Anda.

## Keamanan: hal yang perlu keputusan Anda

1. **Pemeriksaan domain bukan bukti kepemilikan email.** Sistem hanya memastikan teks email
   berakhiran @polinema.ac.id. Siapa pun bisa mendaftar memakai email polinema milik orang lain
   (atau tebakan) karena tidak ada langkah yang membuktikan ia pemiliknya. Ini memenuhi NF-07
   (domain selain polinema.ac.id ditolak 100%), tetapi tidak mencegah penyamaran. Dua jalan keluar:
   verifikasi email (butuh SMTP) atau SSO Google (3b), yang sekaligus membuktikan kepemilikan.
2. **Proposal masih menulis verifikasi email.** FM-02 dan FD-02 berbunyi "Sistem melakukan
   verifikasi akun melalui email", dan FM-03 menyebut login "mahasiswa yang akunnya
   terverifikasi". Implementasi ini mengikuti keputusan Anda (tanpa verifikasi). Ubah teks
   proposal, atau pertimbangkan 3b agar sesuai.
3. **Produksi:** aktifkan HTTPS, `SESSION_SECURE_COOKIE=true`, `APP_DEBUG=false`.

## Tambahan di luar materi

- `lang/id/validation.php` (terjemahan pesan; file lang biasa).
- Berkas uji PHPUnit.
