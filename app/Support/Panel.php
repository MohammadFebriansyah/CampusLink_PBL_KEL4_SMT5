<?php

namespace App\Support;

use App\Models\Mitra;
use App\Models\User;

/**
 * Isi bingkai panel (layouts.panel) untuk tiap peran: nama panel, menu sidebar, notifikasi, dan pencarian cepat.
 * Tampilan panel sama untuk semua peran; yang berbeda hanya isi di sini, sesuai hak akses masing-masing.
 *
 * Bentuk kembalian:
 *   label      nama panel (mis. "Panel admin")
 *   beranda    URL dasbor peran ini (logo sidebar)
 *   menu       judul grup => daftar [nama, url, aktif, ikon, lencana]
 *   notifikasi daftar [judul, teks, url, ikon], atau null bila peran ini tidak punya antrean (tombol lonceng disembunyikan)
 *   cari       ['url' => ..., 'label' => ...] untuk pencarian cepat di bar atas, atau null
 *   profil     URL profil untuk menu akun, atau null
 */
class Panel
{
    public static function untuk(User $user): array
    {
        return match ($user->role) {
            User::ROLE_ADMIN => self::admin($user),
            User::ROLE_MITRA => self::mitra($user),
            User::ROLE_DOSEN => self::dosen($user),
            default => self::mahasiswa(),
        };
    }

    private static function admin(User $admin): array
    {
        $mitraMenunggu = Mitra::where('status_verifikasi', Mitra::STATUS_PENDING)->count();
        $pendaftarMenunggu = Informasi::pendaftarMenunggu($admin);
        $aktifInfo = fn (string $k) => request()->routeIs('admin.informasi.*') && request()->route('kategori') === $k;

        return [
            'label' => 'Panel admin',
            'beranda' => route('admin.dashboard'),
            'menu' => [
                'Ringkasan' => [
                    ['Dashboard', route('admin.dashboard'), request()->routeIs('admin.dashboard'), 'dashboard', null],
                ],
                'Verifikasi' => [
                    ['Verifikasi mitra', route('admin.mitra.index'), request()->routeIs('admin.mitra.*'), 'shield-check', $mitraMenunggu],
                    ['Pendaftar', route('admin.pendaftar.index'), request()->routeIs('admin.pendaftar.*'), 'clipboard', null],
                ],
                'Pengguna' => [
                    ['Pengguna', route('admin.pengguna.index'), request()->routeIs('admin.pengguna.*'), 'users', null],
                ],
                'Konten' => [
                    ['Kelola magang', route('admin.informasi.index', 'magang'), $aktifInfo('magang'), 'magang', null],
                    ['Kelola lomba', route('admin.informasi.index', 'lomba'), $aktifInfo('lomba'), 'lomba', null],
                    ['Kelola beasiswa', route('admin.informasi.index', 'beasiswa'), $aktifInfo('beasiswa'), 'beasiswa', null],
                    ['Keahlian', route('admin.keahlian.index'), request()->routeIs('admin.keahlian.*'), 'star', null],
                ],
            ],
            'notifikasi' => array_values(array_filter([
                $mitraMenunggu > 0 ? [$mitraMenunggu.' akun mitra menunggu verifikasi', 'Mitra belum bisa masuk sebelum disetujui.', route('admin.mitra.index'), 'shield-check'] : null,
                $pendaftarMenunggu > 0 ? [$pendaftarMenunggu.' pendaftar kegiatan kampus', 'Menunggu ditinjau admin.', route('admin.pendaftar.index'), 'clipboard'] : null,
            ])),
            'cari' => ['url' => route('admin.pengguna.index'), 'label' => 'Cari nama atau email pengguna'],
            'profil' => null,
        ];
    }

    private static function mitra(User $mitra): array
    {
        $pendaftarMenunggu = Informasi::pendaftarMenunggu($mitra);
        $aktifInfo = fn (string $k) => request()->routeIs('mitra.informasi.*') && request()->route('kategori') === $k;

        return [
            'label' => 'Panel mitra',
            'beranda' => route('mitra.dashboard'),
            'menu' => [
                'Ringkasan' => [
                    ['Dashboard', route('mitra.dashboard'), request()->routeIs('mitra.dashboard'), 'dashboard', null],
                ],
                'Peninjauan' => [
                    ['Pendaftar', route('mitra.pendaftar.index'), request()->routeIs('mitra.pendaftar.*'), 'clipboard', $pendaftarMenunggu],
                ],
                'Konten' => [
                    ['Kelola magang', route('mitra.informasi.index', 'magang'), $aktifInfo('magang'), 'magang', null],
                    ['Kelola lomba', route('mitra.informasi.index', 'lomba'), $aktifInfo('lomba'), 'lomba', null],
                    ['Kelola beasiswa', route('mitra.informasi.index', 'beasiswa'), $aktifInfo('beasiswa'), 'beasiswa', null],
                ],
                'Akun' => [
                    ['Profil organisasi', route('mitra.profil.show'), request()->routeIs('mitra.profil.*'), 'building', null],
                ],
            ],
            'notifikasi' => $pendaftarMenunggu > 0
                ? [[$pendaftarMenunggu.' pendaftar menunggu ditinjau', 'Lihat profil dan CV, lalu terima atau tolak.', route('mitra.pendaftar.index'), 'clipboard']]
                : [],
            'cari' => ['url' => route('mitra.pendaftar.index'), 'label' => 'Cari nama atau email pendaftar'],
            'profil' => route('mitra.profil.show'),
        ];
    }

    private static function mahasiswa(): array
    {
        return [
            'label' => 'Panel mahasiswa',
            'beranda' => route('dasbor'),
            'menu' => [
                'Ringkasan' => [
                    ['Dashboard', route('dasbor'), request()->routeIs('dasbor'), 'dashboard', null],
                ],
                'Aktivitas' => [
                    ['Kegiatan saya', route('kegiatan.index'), request()->routeIs('kegiatan.*'), 'clipboard', null],
                ],
                'Jelajahi' => self::jelajahi(),
                'Akun' => [
                    ['Profil saya', route('profil.show'), request()->routeIs('profil.*'), 'user', null],
                ],
            ],
            'notifikasi' => null,
            'cari' => ['url' => route('direktori.index'), 'label' => 'Cari mahasiswa atau dosen di direktori'],
            'profil' => route('profil.show'),
        ];
    }

    private static function dosen(User $dosen): array
    {
        $bimbinganMenunggu = $dosen->bimbinganMasuk()->where('status', 'menunggu')->count();

        return [
            'label' => 'Panel dosen',
            'beranda' => route('dasbor'),
            'menu' => [
                'Ringkasan' => [
                    ['Dashboard', route('dasbor'), request()->routeIs('dasbor'), 'dashboard', null],
                ],
                'Bimbingan' => [
                    ['Permintaan bimbingan', route('bimbingan.index'), request()->routeIs('bimbingan.*'), 'users', $bimbinganMenunggu],
                ],
                'Jelajahi' => self::jelajahi(),
                'Akun' => [
                    ['Profil saya', route('profil.show'), request()->routeIs('profil.*'), 'user', null],
                ],
            ],
            'notifikasi' => $bimbinganMenunggu > 0
                ? [[$bimbinganMenunggu.' permintaan bimbingan', 'Mahasiswa menunggu jawaban Anda.', route('bimbingan.index'), 'users']]
                : [],
            'cari' => ['url' => route('direktori.index'), 'label' => 'Cari mahasiswa atau dosen di direktori'],
            'profil' => route('profil.show'),
        ];
    }

    /** Papan informasi dan direktori (halaman situs) untuk mahasiswa dan dosen. */
    private static function jelajahi(): array
    {
        return [
            ['Magang', route('magang.index'), false, 'magang', null],
            ['Lomba', route('lomba.index'), false, 'lomba', null],
            ['Beasiswa', route('beasiswa.index'), false, 'beasiswa', null],
            ['Direktori', route('direktori.index'), false, 'search', null],
        ];
    }
}
