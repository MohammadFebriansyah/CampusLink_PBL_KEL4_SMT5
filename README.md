# CampusLink

Platform profesional khusus mahasiswa Polinema: informasi magang, lomba, dan beasiswa; direktori
mahasiswa dan dosen; panel mitra dan admin. Laravel 13, Blade, Tailwind CSS v4, Alpine.js, Supabase (PostgreSQL).

- Pemasangan, daftar periksa produksi, dan batasan: lihat **[SERAH-TERIMA.md](SERAH-TERIMA.md)**.
- Riwayat pembangunan per bagian: `docs/riwayat-bagian/`.

Mulai cepat:

    composer install && npm install && npm run build
    cp .env.example .env && php artisan key:generate
    # isi DB_* dan GOOGLE_* di .env
    php artisan migrate --seed && php artisan storage:link
    php artisan serve
    php artisan test
