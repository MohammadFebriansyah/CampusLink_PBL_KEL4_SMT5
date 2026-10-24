# CampusLink — Bagian 2: Landing page

Menimpa beranda sementara dari Bagian 0 dengan landing page final. Data tampil dari
database (hasil seeder Bagian 1), bukan teks tetap.

## Pasang

Salin ke proyek, timpa file yang sama:

    app/Http/Controllers/HomeController.php   (baru, single-action controller)
    routes/web.php                            (route "/" kini memakai HomeController)
    resources/views/home.blade.php            (menimpa beranda sementara)

Tidak ada perubahan CSS, JS, atau database. Jalankan seperti biasa:

    npm run dev
    php artisan serve

## Yang perlu terlihat di http://127.0.0.1:8000

1. Hero tengah: chip "Baru", judul besar, dua tombol, dan nama mitra terverifikasi
   (hanya "PT Nusantara Teknologi". Yayasan Kreasi Muda masih pending, jadi tidak muncul).
2. Pita gradien Sky ke Lavender berisi panel dengan tab Magang / Lomba / Beasiswa.
   Tiap tab menampilkan 3 informasi dengan batas pendaftaran terdekat dan sisa harinya.
   Tautan "Lihat semua ..." di kanan atas berganti mengikuti tab.
3. Tiga poin alasan, bagian Direktori (3 profil mahasiswa dari seeder, lengkap dengan
   keahlian), dan bagian mitra dengan 3 langkah.
4. Setelah `migrate:fresh` tanpa `--seed`, semua daftar menampilkan keadaan kosong
   ("Belum ada informasi ... yang dibuka"), bukan error.

## Catatan teknis

- `HomeController` hanya menyusun data dan memanggil view (thin controller). Profil memakai
  eager loading berlapis `with('user.keahlian')` agar tidak terjadi N+1: 4 query untuk
  profil, tidak bertambah seiring jumlah baris.
- Hanya informasi berstatus `tayang` dan belum lewat batas pendaftaran yang tampil.
- Baris informasi belum berupa tautan. Halaman detail dibuat di Bagian 5.
- Tombol "Daftar sebagai mitra" mengirim `?peran=mitra`. Dipakai nanti di Bagian 3 untuk
  memilih peran awal pada form registrasi.
- Panel pratinjau direktori hanya menampilkan mahasiswa, karena dosen dan filter
  lengkap baru ada di Bagian 7.
