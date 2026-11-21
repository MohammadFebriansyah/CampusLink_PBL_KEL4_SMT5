# Pencatatan error (logging) produksi

Semua error penting dicatat ke berkas di server (VPS) dalam format JSON, satu entri per baris, dan setiap permintaan punya
Request ID untuk menelusuri satu kejadian dari awal sampai akhir. Tidak ada dashboard atau layanan tambahan: cukup SSH dan baca berkas.

## Arsitektur

| Bagian | Berkas | Fungsi |
| --- | --- | --- |
| Channel `json` | `config/logging.php` | Log harian semua level dari `LOG_LEVEL` ke atas (bawaan produksi `info`) |
| Channel `galat` | `config/logging.php` | Hanya `error` ke atas, disimpan lebih lama |
| Channel `stack` | `config/logging.php` | Menggabungkan keduanya (`LOG_STACK=json,galat`). `ignore_exceptions` aktif: gagal menulis log tidak menjatuhkan halaman |
| Format JSON | `app/Logging/FormatJsonLog.php` | Bidang seragam di setiap entri, tanpa argumen fungsi pada stack trace |
| Penyamaran data | `app/Logging/Sanitasi.php` | Kunci seperti password, token, cookie, authorization, session, otp, api_key dan sejenisnya diganti `[DISEMBUNYIKAN]`; nilai yang diikat pada SQL dibuang |
| Request ID | `app/Http/Middleware/IdPermintaan.php` | ID unik per permintaan, dikirim di header `X-Request-ID`, ikut ke job antrean |
| Penolakan HTTP | `app/Http/Middleware/CatatGalatHttp.php` | Mencatat 401, 403, 419, 429 (dan 404 bila `LOG_404=true`, serta 5xx tanpa exception) |
| Kejadian lain | `app/Providers/AppServiceProvider.php` | Login gagal, login dikunci, job antrean gagal, tugas terjadwal gagal |
| Filesystem | `config/filesystems.php` | `report => true` pada disk `local` dan `public`: gagal baca/tulis berkas (CV, foto, bukti) tercatat |

Yang dicatat otomatis: exception yang tidak tertangani (termasuk error database, layanan luar seperti SMTP/Google/HTTP, dan filesystem),
penolakan akses, login gagal, serta kegagalan job dan tugas terjadwal. Yang **tidak** dicatat: isi permintaan (body), cookie, header
Authorization, ID sesi, dan query string URL. Email pada login gagal hanya dicatat sebagai hash 12 karakter.

## Format entri

```json
{"timestamp":"2026-11-21T13:53:55+07:00","level":"ERROR","environment":"production","request_id":"req-a81f29c03b7d41e2","method":"POST","url":"/daftar","user_id":15,"status_code":500,"message":"SQLSTATE[08006] ... (Connection: pgsql)","exception":"Illuminate\\Database\\QueryException","exception_message":"...","file":"vendor/laravel/framework/src/Illuminate/Database/Connection.php","line":857,"sql":"select * from users where email = ?","trace":"#0 vendor/...\n#1 app/Http/Controllers/Auth/RegisterController.php(131): ..."}
```

Bidang bernilai kosong tidak ditulis (misalnya `user_id` bila belum login). `file` dan `line` menunjuk lokasi exception dilempar;
baris pertama berkas `app/` ada di `trace`. Data tambahan ada di `context`.

## Lokasi berkas di VPS

`/var/www/campuslink/storage/logs/` (di luar folder `public/`, tidak bisa diakses lewat web):

- `campuslink-YYYY-MM-DD.log`: log aplikasi harian (info ke atas)
- `galat-YYYY-MM-DD.log`: hanya error ke atas
- `laravel.log`: hanya dipakai Laravel sebagai cadangan darurat bila channel utama gagal dibuat

## Variabel environment (`.env` produksi)

```dotenv
APP_ENV=production
APP_DEBUG=false          # wajib false: stack trace tidak boleh tampil ke pengguna
LOG_CHANNEL=stack
LOG_STACK=json,galat
LOG_LEVEL=info
LOG_DAILY_DAYS=14        # lama simpan log harian (hari)
LOG_ERROR_DAYS=30        # lama simpan log error (hari)
LOG_404=false            # true bila ingin 404 ikut dicatat (berisik karena pemindai bot)
```

Berkas dibuat dengan izin `0640`. Pemilik harus pengguna PHP-FPM (`www-data`):

```bash
sudo chown -R www-data:www-data /var/www/campuslink/storage /var/www/campuslink/bootstrap/cache
sudo chmod -R ug+rwX /var/www/campuslink/storage
```

Agar akun `ubuntu` tetap bisa membaca log, tambahkan ke grup: `sudo usermod -aG www-data ubuntu` (login ulang), atau baca dengan `sudo`.

## Perintah SSH

```bash
cd /var/www/campuslink/storage/logs

# pantau realtime
tail -f campuslink-$(date +%F).log
tail -f galat-$(date +%F).log

# 100 baris terakhir
tail -n 100 galat-$(date +%F).log

# cari kata kunci di semua berkas
grep -i "QueryException" *.log
grep -h '"level":"ERROR"' galat-*.log | tail -20

# telusuri satu kejadian dengan Request ID (dari header X-Request-ID atau "Kode referensi" di halaman error)
grep -h "req-a81f29c03b7d41e2" *.log

# baca rapi (jq): ringkasan error hari ini
jq -r 'select(.level=="ERROR") | [.timestamp,.request_id,.url,.exception,.exception_message] | @tsv' galat-$(date +%F).log

# ukuran dan jumlah berkas
du -sh .
ls -lh | sort -k5 -h | tail
```

Pengguna yang melapor error cukup menyebutkan **Kode referensi** yang tampil di halaman 500/404. Itu Request ID untuk perintah `grep` di atas.

## Rotasi, retensi, dan batas ukuran

- Rotasi: otomatis per hari lewat driver `daily` (berkas baru tiap tanggal baru).
- Retensi: Laravel menghapus berkas yang melewati `LOG_DAILY_DAYS` (14 hari) dan `LOG_ERROR_DAYS` (30 hari) saat berkas hari baru dibuat.
- Batas ukuran: ditentukan oleh retensi (hari x ukuran harian). Karena stack trace tanpa argumen dan tanpa body, 1 error sekitar 3 KB.
  Pantau dengan `du -sh storage/logs`. Untuk pengaman tambahan, bisa dipasang cron mingguan yang menghapus berkas log lebih tua dari 60 hari:
  `find /var/www/campuslink/storage/logs -name '*.log' -mtime +60 -delete`
- Kegagalan menulis log (disk penuh, izin salah) tidak membuat halaman error; Laravel mengabaikannya. Cek `df -h` dan izin folder secara berkala.

## Respons ke pengguna

Pengguna production hanya melihat halaman error yang aman ("Terjadi kesalahan di server kami") beserta kode referensi.
Detail exception dan stack trace hanya ada di log. Status HTTP dipertahankan (500, 403, 404, dan seterusnya).

## Langkah deploy dan verifikasi di AWS VPS

```bash
cd /var/www/campuslink
git pull
composer install --no-dev --optimize-autoloader
# edit .env sesuai bagian "Variabel environment" di atas
php artisan optimize:clear      # penting: hapus cache config/route lama
php artisan optimize
sudo chown -R www-data:www-data storage bootstrap/cache
sudo systemctl reload php8.4-fpm
```

Verifikasi:

```bash
# 1. header Request ID ada
curl -sI https://campus-link.page/masuk | grep -i x-request-id

# 2. satu permintaan 404 membawa ID, dan tidak ada stack trace di body
curl -si https://campus-link.page/tidak-ada | grep -iE "x-request-id|Kode referensi|Stack trace"

# 3. entri log terbentuk dan berformat JSON valid
tail -n 3 storage/logs/campuslink-$(date +%F).log | jq .

# 4. izin berkas 0640 dan pemilik www-data
ls -l storage/logs
```

Untuk memastikan error asli tercatat, cukup tunggu error nyata atau buka halaman yang memicu 403 (misalnya `/admin` sebagai mahasiswa) lalu
cari Request ID dari header-nya di `campuslink-*.log`.

## Catatan: CloudWatch dan stderr

Penyimpanan lokal sudah cukup untuk skala saat ini. CloudWatch Logs bisa ditambahkan nanti (agent membaca `storage/logs/*.log`),
tetapi berbayar per GB sehingga tidak dipasang tanpa persetujuan. Driver `stderr` tidak dipakai karena PHP-FPM
tidak meneruskannya ke berkas kecuali `catch_workers_output` diaktifkan.
