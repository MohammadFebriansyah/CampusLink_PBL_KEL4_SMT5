# CampusLink — Bagian 7: Direktori mahasiswa dan dosen

Daftar profil singkat (FM-11), lima filter yang bisa dikombinasikan (FM-12, FD-08), dan profil
lengkap (FM-13). Disertai perbaikan privasi pada landing page dan penanda sembunyi oleh admin.

## Pasang

Salin ke proyek, timpa file yang sama:

    database/migrations/2026_10_11_000010_add_disembunyikan_admin_to_profil_tables.php   (baru)
    app/Models/Mahasiswa.php, Dosen.php                  (menimpa, + cast disembunyikan_admin)
    app/Http/Requests/CariDirektoriRequest.php           (baru)
    app/Http/Controllers/DirektoriController.php         (baru)
    app/Http/Controllers/HomeController.php              (menimpa, tidak lagi memuat profil)
    lang/id/validation.php                               (menimpa, + aturan exists)
    routes/web.php                                       (menimpa)
    resources/views/direktori/index.blade.php            (baru)
    resources/views/direktori/show.blade.php             (baru)
    resources/views/direktori/_kartu.blade.php           (baru)
    resources/views/home.blade.php                       (menimpa, kartu direktori jadi contoh fiktif)
    resources/views/partials/nav.blade.php               (menimpa, Direktori hanya untuk tamu/mahasiswa/dosen)
    resources/views/profil/edit.blade.php                (menimpa, + pemberitahuan sembunyi admin)
    tests/Feature/DirektoriTest.php                      (baru)

Jalankan migration (aman, tidak menghapus data):

    php artisan migrate

## Uji otomatis

    php artisan test --filter=DirektoriTest

Syarat sama seperti bagian sebelumnya (SQLite di phpunit.xml). 18 uji mencakup: akses (tamu ke login,
mitra dan admin 403), siapa yang terlihat (disembunyikan pemilik, disembunyikan admin, nonaktif,
mitra, admin), profil tersembunyi tidak bisa dibuka lewat tautan langsung, pengguna tidak bisa
menampilkan kembali profil yang disembunyikan admin, kelima filter tunggal dan kombinasinya,
dosen dikecualikan oleh filter kelas/angkatan, masukan filter tidak valid, karakter khusus,
penomoran 12 per halaman, jumlah query tidak naik seiring jumlah profil (uji N+1), penanda
terverifikasi, NIM/email tidak tampil di profil lengkap, dan landing page tidak menampilkan profil asli.

## Cek manual (setelah migrate:fresh --seed, password `password`)

1. Masuk sebagai alya.putri@polinema.ac.id, buka Direktori: 8 profil lain (profil sendiri ikut tampil).
2. Filter Keahlian = Basis Data: hanya yang memilikinya. Tambah Jurusan dan Angkatan: hasil menyempit.
3. Tab Dosen lalu pilih Kelas: hasil kosong dengan keterangan bahwa dosen tidak punya kelas.
4. Buka sebuah profil: kontak, keahlian, pengalaman. NIM dan email tidak ada.
5. Di Profil Anda, matikan "Tampilkan profil saya di direktori", simpan. Dari akun lain, profil itu
   hilang dari daftar dan tautannya (/direktori/{id}) menghasilkan 404.
6. Masuk sebagai hrd@nusantaratech.example (mitra), buka /direktori: 403. Menu Direktori tidak tampil di navigasi.
7. Tanpa masuk, buka beranda: kartu direktori berlabel "Contoh tampilan" dan tidak memuat nama asli.

## Perbaikan yang Anda perlu tahu

**Landing page (Bagian 2) menampilkan profil mahasiswa asli kepada pengunjung yang belum masuk.**
Saat itu datanya masih seeder, tetapi dengan data nyata ini membocorkan nama, program studi, dan keahlian
mahasiswa ke publik, bertentangan dengan UC-06 (direktori hanya untuk mahasiswa dan dosen). Kini kartu itu
berisi contoh fiktif berlabel "Contoh tampilan". Ada uji yang menjaganya.

## Keputusan desain (cek apakah sesuai)

- **Hanya mahasiswa dan dosen yang boleh membuka direktori** (UC-06). Mitra dan admin mendapat 403. Berbeda
  dengan informasi magang/lomba/beasiswa (Bagian 5) yang boleh dibaca semua peran, direktori memuat data
  pribadi mahasiswa. Mitra bisa berupa siapa saja dengan email bebas.
- **NIM, NIDN, dan email tidak ditampilkan.** Yang tampil: nama, foto, program studi, jurusan, kelas,
  angkatan, keahlian, isian profil, dan kontak. Kontak bersifat opsional dan hanya muncul bila pemiliknya
  mengisinya. Pemilik diberi peringatan di halaman profil bahwa kontak tampil di direktori.
- **Dua penanda sembunyi yang terpisah.** `tampil_di_direktori` adalah pilihan pemilik. `disembunyikan_admin`
  (kolom baru) hanya bisa diubah admin (FA-08) dan tidak ikut `$fillable`. Tanpa pemisahan ini, pemilik
  bisa menampilkan kembali profil yang disembunyikan admin. Antarmuka admin untuk mengubahnya ada di Bagian 8.
- **Satu aturan keterlihatan** dipakai untuk daftar dan detail. Profil yang tidak boleh tampil,
  akun nonaktif, akun yang tidak ada, dan akun mitra/admin semuanya menghasilkan 404 yang sama
  (tidak bisa dipakai untuk menebak siapa yang punya akun).
- **Filter bersifat DAN.** Kelas dan angkatan hanya ada pada mahasiswa, jadi bila salah satunya dipakai,
  dosen tidak ikut. Jurusan dan program studi dipilih terpisah (sesuai proposal), sehingga kombinasi yang
  saling bertentangan, misalnya jurusan Akuntansi dengan program studi Teknik Informatika, menghasilkan nol profil.
- **Pilihan kelas dan angkatan diambil dari data yang ada**, bukan ketikan bebas, sehingga tidak ada
  salah ketik di filter.
- **Tambahan di luar proposal:** tab Semua/Mahasiswa/Dosen dan kolom pencarian nama. Keduanya kecil dan
  membantu menemukan dosen pembimbing atau seseorang tertentu. Mudah dihapus bila tidak diinginkan.
- **Penanda "Terverifikasi"** muncul pada profil yang akunnya sudah tertaut Google (Bagian 3b), sehingga
  profil yang emailnya belum terbukti milik pemegangnya bisa dibedakan.

## Belum teruji

Controller, query (termasuk `whereHas` bercabang), migration, dan seluruh uji belum pernah dijalankan
(Laravel tidak bisa dipasang di lingkungan saya). Yang sudah saya periksa: lint PHP, logika Blade lewat
render dengan data contoh, dan tampilan desktop serta mobile. Yang paling layak Anda perhatikan saat
menjalankan uji: `test_jumlah_query_tidak_bertambah_seiring_jumlah_profil` dan `test_tab_jenis_dan_filter_khusus_mahasiswa_mengecualikan_dosen`.
