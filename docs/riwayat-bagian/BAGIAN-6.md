# CampusLink — Bagian 6: Panel Mitra

Dasbor, daftar informasi milik sendiri (FP-09), serta tambah, ubah, dan hapus untuk magang,
lomba, dan beasiswa (FP-05 sampai FP-08), dengan Policy agar mitra hanya menyentuh miliknya.

## Pasang

Salin ke proyek, timpa file yang sama:

    app/Policies/InformasiPolicy.php                   (baru, kelas dasar)
    app/Policies/MagangPolicy.php, LombaPolicy.php, BeasiswaPolicy.php   (baru, turunan)
    app/Support/StatusInformasi.php                    (baru)
    app/Http/Requests/SimpanInformasiRequest.php       (baru)
    app/Http/Controllers/MitraDashboardController.php  (baru)
    app/Http/Controllers/MitraInformasiController.php  (baru)
    lang/id/validation.php                             (menimpa, + aturan tanggal dan nama atribut)
    routes/web.php                                     (menimpa)
    resources/views/mitra/dashboard.blade.php          (baru)
    resources/views/mitra/informasi/index.blade.php    (baru)
    resources/views/mitra/informasi/form.blade.php     (baru)
    resources/views/mitra/informasi/_hapus.blade.php   (baru)
    resources/views/partials/select.blade.php          (baru)
    tests/Feature/MitraInformasiTest.php               (baru)

Tidak ada migration dan tidak ada perubahan `.env`. Policy ditemukan otomatis lewat nama kelas
(MagangPolicy untuk model Magang, dst.), jadi AppServiceProvider tidak perlu diubah.

## Uji otomatis

    php artisan test --filter=MitraInformasiTest

Syarat sama seperti bagian sebelumnya (SQLite di phpunit.xml). 21 metode uji, beberapa dijalankan
untuk ketiga kategori sekaligus. Yang terpenting: mitra A tidak bisa membuka form ubah, mengubah,
atau menghapus informasi mitra B (403) di ketiga kategori; kiriman `user_id`, `status`, dan
`penyelenggara` palsu diabaikan; 12 jenis masukan tidak valid ditolak tanpa menyimpan apa pun;
aturan Policy diuji langsung untuk pemilik, mitra lain, admin, dan mahasiswa.

## Cek manual (setelah migrate:fresh --seed, password `password`)

1. Masuk sebagai hrd@nusantaratech.example. Anda diarahkan ke dasbor: Magang 2, Lomba 1, Beasiswa 1
   (informasi buatan admin tidak ikut terhitung).
2. Kelola > Magang: hanya milik PT Nusantara Teknologi. Klik Tambah, isi form, simpan. Langsung
   muncul di daftar, dan terlihat oleh mahasiswa di /magang.
3. Coba batas pendaftaran kemarin saat tambah: ditolak. Saat ubah: diterima dan statusnya
   berubah Ditutup (inilah cara menutup pendaftaran lebih awal).
4. Hapus: klik Hapus lalu "Ya, hapus". Hilang permanen. "Batal" membatalkan.
5. Masuk sebagai info@kreasimuda.example (mitra pending): ditolak.
6. Daftar mitra kedua (email bebas) dan setujui manual di database
   (`update mitra set status_verifikasi='disetujui'`; admin baru ada di Bagian 8). Dengan akun itu,
   tempel alamat /mitra/magang/{id}/ubah milik mitra pertama: harus 403.

## Keputusan desain

- **Hapus permanen untuk milik sendiri, dengan konfirmasi dua langkah.** Sesuai FP-05 sampai FP-07
  ("menambah, mengubah, dan menghapus"). Skema belum punya status draf, jadi tidak ada pembedaan
  draf dan tayang. Menutup pendaftaran dilakukan dengan mengubah batas pendaftaran. Tidak ada
  riwayat penghapusan (bukan soft delete). Bila Anda ingin jejak audit, itu perubahan terpisah.
- **Langsung tayang saat disimpan**, tanpa persetujuan admin. Moderasi dilakukan sesudahnya lewat
  status `disembunyikan` (Bagian 8). Proposal hanya mensyaratkan verifikasi akun mitra, bukan
  verifikasi per informasi.
- **Mengubah tidak membuka kembali informasi yang disembunyikan admin**: status tidak bisa diatur
  mitra. Mitra tetap bisa memperbaiki isinya dan melihat penandanya "Disembunyikan admin".
- **Penyelenggara selalu nama organisasi dari profil mitra**, bukan isian formulir, dan tidak
  berubah saat informasi diedit. Bila nama organisasi diganti kemudian, informasi lama tetap
  memakai nama lama (salinan saat tayang).
- **Kepemilikan diperiksa sebelum validasi.** Mitra lain yang mengirim data apa pun ke informasi
  orang selalu menerima 403, bukan pesan galat validasi.
- **Policy mengizinkan admin** mengubah dan menghapus semuanya (FA-04 sampai FA-06). Controller admin
  di Bagian 8 tinggal memakainya.

## Satu kesalahan yang saya tangkap sebelum penyerahan

Pada penulisan awal, route memakai `Route::prefix(...)->whereIn(...)`. `RouteRegistrar` tidak punya
`whereIn`, jadi aplikasi akan error saat dijalankan. Sudah diganti `->where(['kategori' => 'magang|lomba|beasiswa'])`.
Karena Laravel tidak bisa dijalankan di lingkungan saya, kelas kesalahan seperti ini hanya bisa
tertangkap lewat pembacaan teliti. Bila Anda menemukan error serupa, kirim pesannya.

## Belum teruji

Controller, Policy, route, dan seluruh uji belum pernah dijalankan. Yang sudah saya periksa: lint
PHP, logika Blade lewat render dengan data contoh, konfirmasi hapus dengan Alpine asli di browser,
dan tampilan desktop serta mobile.
