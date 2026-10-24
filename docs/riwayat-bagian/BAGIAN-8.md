# CampusLink — Bagian 8: Panel Admin

Verifikasi mitra, kelola pengguna, kelola dan moderasi informasi, sembunyikan profil dari direktori,
kelola keahlian, dan ringkasan jumlah data (FA-02 sampai FA-09, UC-08 sampai UC-10).
FA-01 (login/logout admin) sudah ada sejak Bagian 3.

## Pasang

Salin ke proyek, timpa file yang sama:

    app/Http/Requests/AdminCariRequest.php, AdminSimpanInformasiRequest.php,
                      UbahPenggunaRequest.php, SimpanKeahlianRequest.php          (baru)
    app/Http/Controllers/AdminDashboardController.php, AdminMitraController.php,
                          AdminPenggunaController.php, AdminInformasiController.php,
                          AdminKeahlianController.php                              (baru)
    lang/id/validation.php                               (menimpa, + atribut penyelenggara)
    routes/web.php                                       (menimpa; placeholder admin diganti panel)
    resources/views/admin/...                            (baru, 10 berkas)
    resources/views/partials/form-informasi.blade.php    (baru, kolom isian bersama)
    resources/views/partials/aksi.blade.php              (baru, tombol aksi + konfirmasi)
    resources/views/mitra/informasi/form.blade.php       (menimpa Bagian 6; kini memakai partial bersama)
    tests/Feature/AdminTest.php                          (baru)

Tidak ada migration dan tidak ada perubahan `.env`.

## Uji otomatis

    php artisan test --filter=AdminTest

Syarat sama seperti bagian sebelumnya (SQLite di phpunit.xml). 26 uji mencakup: hanya admin yang
bisa membuka panel (tamu ke login, mahasiswa/dosen/mitra 403), mitra tidak bisa menyetujui dirinya atau
orang lain, ringkasan dasbor, setujui dan tolak mitra beserta dampaknya pada login, daftar pengguna
dengan filter dan tanpa N+1, akun admin tidak bisa dikelola lewat panel (404), ubah email/NIM dengan
aturan unik dan domain, verifikasi Google dibatalkan saat email berubah, nonaktifkan akun (login gagal,
sesi aktif langsung dikeluarkan, hilang dari direktori), sembunyikan informasi mitra sekaligus,
sembunyikan profil direktori, kelola informasi milik mitra, moderasi, dan CRUD keahlian.

## Cek manual (setelah migrate:fresh --seed, password `password`)

1. Masuk sebagai admin@campuslink.test. Dasbor menampilkan "1 akun mitra menunggu verifikasi".
2. Verifikasi mitra > Setujui info@kreasimuda.example. Lalu masuk sebagai mitra itu di jendela lain: berhasil.
3. Pengguna: cari "alya", buka, Ubah: ganti NIM, simpan. Coba email gmail untuk mahasiswa: ditolak.
4. Buka hrd@nusantaratech.example, Nonaktifkan dengan kotak "sembunyikan juga semua informasi" tercentang.
   Di jendela mitra yang sedang masuk, muat ulang halaman: langsung dialihkan ke login.
   Magang/Lomba/Beasiswa dari mitra itu hilang dari daftar pembaca.
5. Aktifkan kembali: mitra bisa masuk, tetapi informasinya tetap tersembunyi. Tampilkan satu per satu
   lewat menu Magang/Lomba/Beasiswa.
6. Pengguna > mahasiswa > "Sembunyikan dari direktori": profil hilang dari direktori. Masuk sebagai
   mahasiswa itu: ada pemberitahuan, dan mengaktifkan "tampilkan di direktori" tidak berpengaruh.
7. Keahlian: tambah "Desain Produk", coba tambah "desain produk" (ditolak, tidak membedakan huruf).

## Keputusan desain (cek apakah sesuai)

- **Admin menonaktifkan, tidak menghapus akun** (sesuai keputusan Anda). Penghapusan akun akan ikut menghapus
  profil, keahlian, dan semua informasi buatannya secara permanen. Akun nonaktif langsung tidak bisa
  login dan sesinya yang sedang berjalan ditolak pada request berikutnya.
- **Akun admin tidak muncul dan tidak bisa dikelola di panel ini** (404). Mencegah admin mengunci admin
  lain atau dirinya sendiri. Akun admin dibuat lewat seeder atau database.
- **Menonaktifkan mitra menawarkan "sembunyikan juga semua informasinya"** (tercentang bawaan). Tanpa itu,
  informasi mitra yang sudah dinonaktifkan tetap tayang. Mengaktifkan kembali tidak otomatis menampilkan
  ulang informasinya.
- **Mengubah email membatalkan verifikasi Google.** Kepemilikan email baru harus dibuktikan lagi dengan masuk
  Google memakai email itu. Ini mengikuti pesan di halaman profil Bagian 4 ("hubungi admin untuk mengubah
  email atau NIM"). Mahasiswa dan dosen tetap wajib email polinema.ac.id; mitra bebas.
- **Aksi dibuat sebagai route terpisah** (`/setujui`, `/tolak`, `/nonaktifkan`, `/sembunyikan-profil`, ...),
  bukan satu route yang menerima nilai status dari formulir. Tidak ada masukan yang bisa dimanipulasi, dan
  tindakan berbahaya dikonfirmasi dua langkah.
- **Policy yang sama dipakai ulang.** InformasiPolicy (Bagian 6) sudah mengizinkan admin mengubah dan
  menghapus semua informasi. Formulir informasi mitra dan admin kini berbagi satu partial, jadi aturan
  isian dan tampilannya tidak bisa menyimpang.
- **Keahlian tidak membedakan huruf besar/kecil** saat dicek duplikat. Menghapus keahlian melepasnya dari
  profil yang memakainya (jumlah pemakai ditampilkan sebelum konfirmasi).

## Belum ada (perlu keputusan bila dibutuhkan)

- **Mitra tidak diberi tahu** saat disetujui atau ditolak, karena belum ada pengiriman email. Mitra baru
  tahu saat mencoba masuk. Alasan penolakan juga tidak disimpan (tidak ada kolomnya).
- **Tidak ada riwayat tindakan admin** (siapa menyembunyikan apa, kapan).
- **Ganti password admin** belum ada fitur. Password seeder adalah `password`: ubah sebelum dipakai nyata.

## Belum teruji

Controller, request, query, dan seluruh uji belum pernah dijalankan (Laravel tidak bisa dipasang di
lingkungan saya). Yang sudah saya periksa: lint PHP, logika Blade lewat render dengan data contoh,
konfirmasi aksi dengan Alpine asli di browser, dan tampilan halaman admin.
