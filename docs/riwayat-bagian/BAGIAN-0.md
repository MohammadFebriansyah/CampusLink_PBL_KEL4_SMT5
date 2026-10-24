# CampusLink — Bagian 0: Fondasi

Isi: token desain (warna, font, radius, bayangan), layout Blade, navigasi pill, footer,
dan halaman beranda sementara. Belum ada database/autentikasi (itu Bagian 1 dan 3).

## Cara pasang

1. Buat proyek Laravel baru (versi sesuai materi, pastikan PHP memenuhi syaratnya):

       composer create-project laravel/laravel campuslink
       cd campuslink

2. Salin isi folder ini ke dalam proyek, timpa file yang sama:

       vite.config.js
       routes/web.php
       resources/css/app.css
       resources/js/app.js
       resources/views/ (home, placeholder, layouts/, partials/)

   Catatan: `app.js` bawaan Laravel memuat `bootstrap.js` (axios). Itu tidak dipakai
   karena aplikasi ini MPA berbasis Blade, jadi baris tersebut sengaja dihilangkan.

3. Pasang dependensi frontend:

       npm install
       npm install alpinejs @fontsource-variable/inter

   Jika `tailwindcss` / `@tailwindcss/vite` belum ada di package.json bawaan:

       npm install -D tailwindcss @tailwindcss/vite

4. Jalankan (dua terminal):

       npm run dev
       php artisan serve

   `.env` biarkan bawaan dulu. Supabase dipasang di Bagian 1.

## Yang perlu terlihat di http://127.0.0.1:8000

- Navigasi berbentuk pill di atas, tombol Daftar berwarna Iris, menu hamburger di layar sempit.
- Judul besar dengan huruf Inter ketat, tombol "Buat akun" Lavender dengan teks Carbon.
- Tiga ikon kategori, kotak pemeriksaan komponen (tab, input, tag), footer.
- Klik tautan nav: tampil halaman placeholder (route sudah bernama: magang.index, lomba.index, dst.).

## Tambahan di luar materi (sudah disetujui)

- `@fontsource-variable/inter` (paket npm) untuk font Inter, pengganti OpenRunde.
- Kelas komponen `.btn`, `.card`, `.field`, dst. di `app.css` memakai `@apply`
  agar utility Tailwind tidak diulang di setiap halaman.
