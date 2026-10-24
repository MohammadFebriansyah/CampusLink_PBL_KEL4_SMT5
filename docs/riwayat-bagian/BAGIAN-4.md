# CampusLink — Bagian 4: Profil

Edit profil mahasiswa, dosen, dan mitra; pilihan keahlian; upload foto; program studi
sebagai pilihan tetap (jurusan diturunkan otomatis).

## Pasang

Salin ke proyek, timpa file yang sama:

    config/kampus.php                                  (baru, DAFTAR JURUSAN & PRODI)
    app/Support/Kampus.php                             (baru)
    app/Http/Controllers/ProfilController.php          (baru)
    app/Http/Controllers/MitraProfilController.php     (baru)
    app/Http/Controllers/Auth/RegisterController.php   (menimpa Bagian 3)
    app/Http/Requests/UpdateProfilRequest.php          (baru)
    app/Http/Requests/UpdateMitraProfilRequest.php     (baru)
    app/Http/Requests/RegisterRequest.php              (menimpa Bagian 3)
    app/Models/Mahasiswa.php, Dosen.php                (menimpa Bagian 1, + fotoUrl())
    database/seeders/PenggunaSeeder.php                (menimpa Bagian 1, nama prodi disamakan)
    routes/web.php                                     (menimpa)
    resources/views/profil/edit.blade.php              (baru)
    resources/views/mitra/profil.blade.php             (baru)
    resources/views/auth/register.blade.php            (menimpa Bagian 3)
    resources/views/partials/field.blade.php           (menimpa, + parameter nilai)
    resources/views/partials/textarea.blade.php        (baru)
    resources/views/partials/prodi-select.blade.php    (baru)
    resources/views/partials/nav.blade.php             (menimpa, + tautan Profil mitra)
    tests/Feature/AuthTest.php                         (menimpa Bagian 3, 1 uji disesuaikan)
    tests/Feature/ProfilTest.php                       (baru)

Tidak ada migration baru. Jalankan sekali:

    php artisan storage:link
    php artisan config:clear

Karena seeder berubah (nama prodi), ulangi data contoh:

    php artisan migrate:fresh --seed

(Hati-hati: menghapus semua tabel di schema public Supabase.)

## WAJIB: koreksi config/kampus.php

Daftar jurusan (7) sudah sesuai situs Polinema. Daftar PROGRAM STUDI (28 item) belum
lengkap: Polinema mencatat 44 sampai 46 prodi di kampus utama dan PSDKU, dan beberapa nama
berbeda antar sumber (ditandai komentar "perlu dicek"). Bila prodi Anda tidak ada, mahasiswa
tidak bisa mendaftar. Tambah/koreksi baris di berkas itu, lalu `php artisan config:clear`.
Nama prodi harus unik di seluruh jurusan.

Akun yang sudah dibuat di Bagian 3 menyimpan prodi sebagai teks bebas. Bila tidak ada di daftar,
kolom prodi di halaman profilnya tampil kosong dan harus dipilih ulang saat menyimpan.

## Uji otomatis

Syarat sama seperti Bagian 3 (phpunit.xml memakai sqlite :memory:). Tambahan: ekstensi PHP GD
(untuk gambar palsu di uji foto).

    php artisan test --filter=ProfilTest
    php artisan test --filter=AuthTest

15 uji baru: jurusan diturunkan dari prodi dan kiriman jurusan diabaikan, prodi karangan ditolak,
email/NIM/peran/status tidak bisa diubah lewat profil, sinkronisasi keahlian, upload foto (berkas
lama terhapus), hapus foto, PDF dan 3 MB ditolak, kesediaan dosen, mitra tidak bisa mengubah
status verifikasi, website `javascript:` ditolak, dan pembatasan akses antar peran.

## Cek manual (setelah migrate:fresh --seed, password `password`)

1. Masuk sebagai alya.putri@polinema.ac.id, buka Profil. Ubah kelas, pilih 3 keahlian,
   unggah foto JPG, simpan. Foto tampil dan keahlian tetap terpilih setelah simpan.
2. Unggah foto lain: berkas lama hilang dari storage/app/public/foto-profil.
3. Kosongkan kelas lalu simpan: pesan galat muncul dan isian lain tidak hilang.
4. Masuk sebagai hrd@nusantaratech.example: nav menampilkan Dasbor, Profil, Keluar.
   Isi website `javascript:alert(1)`: ditolak.
5. Daftar akun mahasiswa baru: kolom Jurusan tidak ada, hanya pilihan Program studi.

## Keputusan desain

- **Pengguna memilih program studi saja, jurusan diturunkan di server.** Keduanya tersimpan di
  database (sesuai ERD) tetapi tidak mungkin tidak sinkron, penting untuk filter direktori.
- **Email, NIM, NIDN, peran, dan status tidak bisa diubah sendiri.** Hanya admin (Bagian 8).
- **Nama berkas foto diacak oleh Laravel**, bukan nama dari pengguna. Hanya JPG/PNG/WebP, maksimal
  2 MB dan 4000 piksel per sisi (SVG ditolak karena bisa memuat skrip).
- **Berkas lama dihapus setelah database berhasil diperbarui.** Bila penyimpanan gagal, berkas baru
  dibersihkan agar tidak ada berkas yatim.
- **Website mitra hanya http/https**, agar tautan tidak bisa berisi `javascript:`.
- **Kontak bersifat opsional** dan diberi peringatan bahwa isinya tampil di direktori.
- **Chip keahlian memakai `has-[:checked]`** (CSS modern). Berjalan di browser terbaru, sesuai NF-03.

## Perlu diketahui

- **Foto tersimpan di disk lokal server.** Beberapa hosting (mis. platform dengan filesystem
  sementara) menghapusnya saat deploy ulang. Bila itu hosting Anda, foto perlu dipindah ke
  penyimpanan objek (mis. Supabase Storage). Itu bagian tambahan tersendiri.
- Direktori (Bagian 7) akan memakai `tampil_di_direktori`, `kontak`, dan `fotoUrl()` dari sini.

## Tambahan di luar materi (sudah disetujui)

- Upload file dan disk `public` (`storage:link`).
- `config/kampus.php` dan kelas `App\Support\Kampus`.
- Berkas uji dengan fake storage.
