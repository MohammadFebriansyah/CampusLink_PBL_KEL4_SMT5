<?php

namespace Database\Seeders;

use App\Models\Keahlian;
use App\Models\Mitra;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Data contoh untuk pengembangan. Semua akun memakai password "password".
 * Seeder ini TIDAK untuk lingkungan produksi. Semua nama dan nomor bersifat fiktif.
 */
class PenggunaSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('password');

        // Admin dibuat pengelola sistem, tidak lewat registrasi.
        User::create([
            'name' => 'Admin CampusLink',
            'email' => 'admin@campuslink.test',
            'password' => $password,
            'role' => User::ROLE_ADMIN,
        ]);

        $this->seedMahasiswa($password);
        $this->seedDosen($password);
        $this->seedMitra($password);
    }

    private function seedMahasiswa(string $password): void
    {
        $data = [
            ['Alya Putri Ramadhani', 'alya.putri@polinema.ac.id', '2441079001', 'SIB-3A', 'Teknologi Informasi', 'Sistem Informasi Bisnis', 2024, ['Web Development', 'UI/UX Design']],
            ['Bima Aditya Pratama', 'bima.aditya@polinema.ac.id', '2441079002', 'SIB-3B', 'Teknologi Informasi', 'Sistem Informasi Bisnis', 2024, ['Basis Data', 'Analisis Data']],
            ['Citra Maharani', 'citra.maharani@polinema.ac.id', '2341079003', 'TI-5A', 'Teknologi Informasi', 'Teknik Informatika', 2023, ['Mobile Development', 'Machine Learning']],
            ['Dimas Raka Saputra', 'dimas.raka@polinema.ac.id', '2341079004', 'TI-5B', 'Teknologi Informasi', 'Teknik Informatika', 2023, ['Jaringan Komputer', 'Keamanan Siber']],
            ['Eka Nur Fadilah', 'eka.nur@polinema.ac.id', '2541079005', 'AK-1A', 'Akuntansi', 'Akuntansi Manajemen', 2025, ['Analisis Bisnis', 'Kewirausahaan']],
            ['Farhan Maulana', 'farhan.maulana@polinema.ac.id', '2241079006', 'AN-7A', 'Administrasi Niaga', 'Administrasi Bisnis', 2022, ['Public Speaking', 'Manajemen Proyek']],
        ];

        foreach ($data as $i => [$nama, $email, $nim, $kelas, $jurusan, $prodi, $angkatan, $keahlian]) {
            $user = User::create([
                'name' => $nama,
                'email' => $email,
                'password' => $password,
                'role' => User::ROLE_MAHASISWA,
            ]);

            $user->mahasiswa()->create([
                'nim' => $nim,
                'kelas' => $kelas,
                'jurusan' => $jurusan,
                'program_studi' => $prodi,
                'angkatan' => $angkatan,
                'kontak' => '08120000010' . $i,
                'tentang' => "Mahasiswa {$prodi} angkatan {$angkatan}, tertarik pada " . implode(' dan ', $keahlian) . '.',
            ]);

            $user->keahlian()->attach(Keahlian::whereIn('nama', $keahlian)->pluck('id'));
        }
    }

    private function seedDosen(string $password): void
    {
        $data = [
            ['Dr. Rina Kusumawati, S.Kom., M.T.', 'rina.kusumawati@polinema.ac.id', '0701019001', 'Teknologi Informasi', 'Sistem Informasi Bisnis', true, true, ['Basis Data', 'Manajemen Proyek']],
            ['Hendra Wijaya, S.T., M.Kom.', 'hendra.wijaya@polinema.ac.id', '0702029002', 'Teknologi Informasi', 'Teknik Informatika', true, false, ['Web Development', 'Keamanan Siber']],
            ['Sari Lestari, S.E., M.M.', 'sari.lestari@polinema.ac.id', '0703039003', 'Administrasi Niaga', 'Administrasi Bisnis', false, true, ['Kewirausahaan', 'Public Speaking']],
        ];

        foreach ($data as $i => [$nama, $email, $nidn, $jurusan, $prodi, $magang, $lomba, $keahlian]) {
            $user = User::create([
                'name' => $nama,
                'email' => $email,
                'password' => $password,
                'role' => User::ROLE_DOSEN,
            ]);

            $user->dosen()->create([
                'nidn' => $nidn,
                'jurusan' => $jurusan,
                'program_studi' => $prodi,
                'kontak' => '08130000020' . $i,
                'tentang' => 'Dosen ' . $prodi . ', bidang ' . implode(' dan ', $keahlian) . '.',
                'bersedia_membimbing_magang' => $magang,
                'bersedia_membimbing_lomba' => $lomba,
            ]);

            $user->keahlian()->attach(Keahlian::whereIn('nama', $keahlian)->pluck('id'));
        }
    }

    private function seedMitra(string $password): void
    {
        $data = [
            ['PT Nusantara Teknologi', 'hrd@nusantaratech.example', 'Perusahaan pengembang perangkat lunak.', '0341-000001', 'https://nusantaratech.example', Mitra::STATUS_DISETUJUI],
            ['Yayasan Kreasi Muda', 'info@kreasimuda.example', 'Yayasan pendukung talenta muda.', '0341-000002', null, Mitra::STATUS_PENDING],
        ];

        foreach ($data as [$organisasi, $email, $deskripsi, $kontak, $website, $status]) {
            $user = User::create([
                'name' => $organisasi,
                'email' => $email,
                'password' => $password,
                'role' => User::ROLE_MITRA,
            ]);

            $mitra = $user->mitra()->create([
                'nama_organisasi' => $organisasi,
                'deskripsi' => $deskripsi,
                'kontak' => $kontak,
                'website' => $website,
            ]);

            // status_verifikasi tidak ada di $fillable, jadi diisi eksplisit.
            $mitra->status_verifikasi = $status;
            $mitra->save();
        }
    }
}
