<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Contoh informasi magang, lomba, dan beasiswa. Tanggal dihitung dari hari ini
 * agar data contoh selalu berada dalam masa pendaftaran. Semua tautan fiktif.
 * Milik mitra dan kegiatan kampus: daftar di CampusLink. Kegiatan luar milik admin: tautan penyelenggara.
 */
class InformasiSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', User::ROLE_ADMIN)->firstOrFail();
        $mitra = User::where('email', 'hrd@nusantaratech.example')->firstOrFail();

        // Magang
        $mitra->magang()->create([
            'judul' => 'Magang Web Developer Laravel',
            'penyelenggara' => 'PT Nusantara Teknologi',
            'deskripsi' => 'Terlibat dalam pengembangan aplikasi web internal bersama tim engineering.',
            'persyaratan' => "Mahasiswa semester 5 ke atas.\nMenguasai dasar PHP dan SQL.\nMemahami konsep MVC.",
            'lokasi' => 'Malang (hibrid)',
            'periode_mulai' => now()->addMonths(2)->startOfMonth(),
            'periode_selesai' => now()->addMonths(5)->startOfMonth(),
            'batas_pendaftaran' => now()->addDays(30),
            'kontak' => 'hrd@nusantaratech.example',
            'tautan_pendaftaran' => 'https://nusantaratech.example/magang/web',
            'cara_daftar' => 'internal',
        ]);

        $mitra->magang()->create([
            'judul' => 'Magang UI/UX Designer',
            'penyelenggara' => 'PT Nusantara Teknologi',
            'deskripsi' => 'Membantu merancang antarmuka dan melakukan uji kegunaan produk.',
            'persyaratan' => "Memiliki portofolio desain.\nTerbiasa memakai Figma.",
            'lokasi' => 'Remote',
            'periode_mulai' => now()->addMonths(2)->startOfMonth(),
            'periode_selesai' => now()->addMonths(4)->startOfMonth(),
            'batas_pendaftaran' => now()->addDays(21),
            'kontak' => 'hrd@nusantaratech.example',
            'tautan_pendaftaran' => 'https://nusantaratech.example/magang/uiux',
            'cara_daftar' => 'internal',
        ]);

        $admin->magang()->create([
            'judul' => 'Magang Analis Data',
            'penyelenggara' => 'PT Data Wawasan Indonesia',
            'deskripsi' => 'Mengolah dan memvisualisasikan data penjualan untuk laporan bulanan.',
            'persyaratan' => "Memahami SQL dasar.\nBisa memakai Excel atau Python untuk analisis.",
            'lokasi' => 'Surabaya',
            'periode_mulai' => now()->addMonths(3)->startOfMonth(),
            'periode_selesai' => now()->addMonths(6)->startOfMonth(),
            'batas_pendaftaran' => now()->addDays(45),
            'kontak' => 'karir@datawawasan.example',
            'tautan_pendaftaran' => 'https://datawawasan.example/karir/analis-data',
            'cara_daftar' => 'eksternal', // kegiatan luar: diarahkan ke situs penyelenggara
        ]);

        // Lomba
        $mitra->lomba()->create([
            'judul' => 'Hackathon Inovasi Kampus',
            'penyelenggara' => 'PT Nusantara Teknologi',
            'deskripsi' => 'Kompetisi 24 jam membangun prototipe solusi untuk masalah kampus.',
            'persyaratan' => 'Tim 3 sampai 5 mahasiswa aktif.',
            'tingkat' => 'nasional',
            'tanggal_mulai' => now()->addDays(60),
            'tanggal_selesai' => now()->addDays(61),
            'batas_pendaftaran' => now()->addDays(40),
            'kontak' => 'hackathon@nusantaratech.example',
            'tautan_pendaftaran' => 'https://nusantaratech.example/hackathon',
            'cara_daftar' => 'internal',
        ]);

        $admin->lomba()->create([
            'judul' => 'Lomba Desain Antarmuka Mahasiswa',
            'penyelenggara' => 'Komunitas Desain Digital',
            'deskripsi' => 'Merancang antarmuka aplikasi dengan tema layanan publik.',
            'persyaratan' => 'Individu atau tim maksimal 2 orang.',
            'tingkat' => 'regional',
            'tanggal_mulai' => now()->addDays(50),
            'tanggal_selesai' => now()->addDays(52),
            'batas_pendaftaran' => now()->addDays(35),
            'kontak' => 'panitia@desaindigital.example',
            'tautan_pendaftaran' => 'https://desaindigital.example/lomba-ui',
            'cara_daftar' => 'eksternal', // kegiatan luar: diarahkan ke situs penyelenggara
        ]);

        $admin->lomba()->create([
            'judul' => 'Kompetisi Rencana Bisnis Mahasiswa',
            'penyelenggara' => 'Pusat Kewirausahaan Kampus',
            'deskripsi' => 'Pemaparan rencana bisnis di depan juri praktisi.',
            'persyaratan' => null,
            'tingkat' => 'kampus',
            'tanggal_mulai' => now()->addDays(25),
            'tanggal_selesai' => now()->addDays(25),
            'batas_pendaftaran' => now()->addDays(14),
            'kontak' => 'kewirausahaan@kampus.example',
            'tautan_pendaftaran' => 'https://kampus.example/rencana-bisnis',
            'cara_daftar' => 'internal', // kegiatan kampus: daftar di CampusLink, admin meninjau
        ]);

        // Beasiswa
        $mitra->beasiswa()->create([
            'judul' => 'Beasiswa Talenta Digital',
            'penyelenggara' => 'PT Nusantara Teknologi',
            'deskripsi' => 'Bantuan biaya pendidikan bagi mahasiswa berprestasi bidang teknologi.',
            'persyaratan' => "IPK minimal 3,25.\nMahasiswa aktif semester 3 sampai 7.\nMelampirkan surat rekomendasi dosen.",
            'batas_pendaftaran' => now()->addDays(40),
            'kontak' => 'beasiswa@nusantaratech.example',
            'tautan_pendaftaran' => 'https://nusantaratech.example/beasiswa',
            'cara_daftar' => 'internal',
        ]);

        $admin->beasiswa()->create([
            'judul' => 'Beasiswa Peningkatan Prestasi Akademik',
            'penyelenggara' => 'Lembaga Pendidikan Mandiri',
            'deskripsi' => 'Beasiswa untuk mahasiswa dengan prestasi akademik konsisten.',
            'persyaratan' => "IPK minimal 3,5.\nTidak sedang menerima beasiswa lain.",
            'batas_pendaftaran' => now()->addDays(28),
            'kontak' => 'info@lpm.example',
            'tautan_pendaftaran' => 'https://lpm.example/beasiswa-prestasi',
            'cara_daftar' => 'eksternal', // kegiatan luar: diarahkan ke situs penyelenggara
        ]);
    }
}
