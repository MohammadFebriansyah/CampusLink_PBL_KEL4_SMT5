# CampusLink — Bagian 5: Informasi magang, lomba, dan beasiswa (sisi pembaca)

Daftar, pencarian, dan detail untuk ketiga kategori (FM-06 sampai FM-10, FD-06, FD-07).
Baris informasi di landing page kini menjadi tautan ke halaman detail.

## Pasang

Salin ke proyek, timpa file yang sama:

    app/Support/Tanggal.php                              (baru, format tanggal + sisa hari)
    app/Http/Controllers/InformasiController.php         (baru, melayani 3 kategori)
    app/Http/Requests/CariInformasiRequest.php           (baru)
    routes/web.php                                       (menimpa)
    resources/views/informasi/index.blade.php            (baru)
    resources/views/informasi/show.blade.php             (baru)
    resources/views/informasi/_baris.blade.php           (baru)
    resources/views/partials/pager.blade.php             (baru)
    resources/views/home.blade.php                       (menimpa Bagian 2, baris jadi tautan)
    tests/Feature/InformasiTest.php                      (baru)
    tests/Unit/TanggalTest.php                           (baru)

Tidak ada migration. TAMBAHKAN di `.env` (penting, lihat catatan zona waktu):

    APP_TIMEZONE=Asia/Jakarta

## Uji otomatis

    php artisan test --filter=InformasiTest
    php artisan test --filter=TanggalTest

InformasiTest memakai SQLite (syarat sama seperti bagian sebelumnya). TanggalTest tidak memakai
database sama sekali. 22 + 4 uji mencakup: tamu diarahkan ke login, hanya informasi yang dibuka
dan tayang yang tampil, urutan batas terdekat, pencarian di 5 kolom tanpa membedakan huruf,
penomoran 10 per halaman, filter tingkat lomba, masukan tidak valid ditolak, karakter khusus
dan HTML aman, detail lengkap, detail yang ditutup tanpa tautan, yang disembunyikan 404, tautan
`javascript:` tidak pernah jadi href, dan tautan di landing page.

## Cek manual (setelah migrate:fresh --seed, password `password`)

1. Tanpa masuk, buka /magang: diarahkan ke halaman masuk. Dari landing page, klik salah satu baris
   informasi: hal yang sama. Setelah masuk, Anda dikembalikan ke halaman tujuan.
2. Masuk sebagai alya.putri@polinema.ac.id. /magang menampilkan 3 informasi, terdekat paling atas.
3. Cari "laravel": 1 hasil. Cari "SURABAYA": tetap ketemu (tidak membedakan huruf, lokasi ikut dicari).
4. Pindah tab Lomba: kata kunci terbawa. Pilih tingkat Nasional: yang bertingkat lain hilang.
5. Buka sebuah detail: deskripsi, persyaratan, batas waktu, kontak, dan tombol pendaftaran
   (tab baru, ke situs penyelenggara).
6. Ubah batas_pendaftaran sebuah baris ke kemarin lewat Supabase Table Editor: hilang dari daftar,
   detailnya tetap terbuka dengan keterangan "Pendaftaran sudah ditutup" tanpa tombol.

## Keputusan desain (cek apakah sesuai)

- **Wajib masuk, semua peran boleh membaca.** Proposal menyebut mahasiswa dan dosen sebagai
  pembaca dan tidak mendefinisikan peran "pengunjung". Mitra dan admin ikut boleh membaca (tanpa
  itu mereka mendapat 403 saat mengklik menu yang sama). Membatasinya hanya untuk mahasiswa dan
  dosen cukup mengubah satu string di routes/web.php (`:mahasiswa,dosen,mitra,admin`). Landing page
  tetap publik dan hanya menampilkan judul, penyelenggara, dan sisa hari; kontak dan tautan
  pendaftaran hanya ada setelah masuk.
- **Daftar hanya memuat yang masih dibuka** (batas pendaftaran hari ini atau lebih). Proposal
  menyebut "informasi yang tersedia". Yang sudah lewat masih bisa dibuka lewat tautan langsung.
  Yang berstatus `disembunyikan` (moderasi admin, Bagian 8) tidak tampil dan detailnya 404.
- **Satu kolom kata kunci untuk semuanya.** Mencari di judul, penyelenggara, deskripsi, persyaratan,
  dan (khusus magang) lokasi. Tidak ada filter lokasi terpisah. Filter tingkat khusus lomba adalah
  tambahan kecil di luar proposal (yang hanya menyebut kata kunci dan kategori).
- **"Kategori" = tab Magang / Lomba / Beasiswa** dengan kata kunci terbawa saat berpindah. Tidak ada
  pencarian lintas kategori sekaligus (butuh menggabungkan tiga tabel). Bila proposal Anda
  mengharuskannya, kabari saya.
- **Penomoran halaman dibuat sendiri** (`partials/pager`). Tampilan bawaan paginator Laravel memakai
  kelas Tailwind dari folder vendor yang tidak dipindai Tailwind v4, sehingga tampil tanpa gaya.
- **Tautan pendaftaran hanya dibuat jika berawalan http/https**, dibuka di tab baru dengan
  `noopener noreferrer`. Teks dari mitra selalu di-escape, jadi HTML/skrip di dalamnya tidak berjalan.
- **Pencarian memakai `LIKE '%kata%'`** tanpa indeks. Aman untuk target 100 data (NF-06) dengan
  penomoran halaman. Bila data tumbuh jauh lebih besar, pindah ke pencarian teks penuh PostgreSQL.

## Zona waktu (kenapa APP_TIMEZONE perlu diatur)

Bawaan Laravel adalah UTC. Antara 00.00 dan 07.00 WIB, "hari ini" versi UTC masih kemarin, jadi
informasi yang batasnya hari ini bisa dianggap sudah ditutup lebih awal dan hitungan sisa hari
meleset satu. Dengan `Asia/Jakarta`, batas pendaftaran berlaku sampai akhir hari WIB.

## Perlu diketahui

- Bagian 6 (panel mitra) dan 8 (admin) membuat serta memoderasi data yang dibaca halaman ini.
  Sampai saat itu data hanya berasal dari seeder.
- /direktori masih placeholder dan belum dibatasi login. Itu dikerjakan di Bagian 7.
- Belum bisa saya jalankan: controller, query, dan seluruh uji (Laravel tidak bisa dipasang di
  lingkungan saya). Yang sudah saya periksa: lint PHP, logika Blade lewat render dengan data
  contoh (sisa hari, keadaan kosong, pager), dan tampilan desktop serta mobile.
