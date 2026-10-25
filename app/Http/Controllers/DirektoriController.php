<?php

namespace App\Http\Controllers;

use App\Http\Requests\CariDirektoriRequest;
use App\Models\Keahlian;
use App\Models\Mahasiswa;
use App\Models\User;
use App\Support\Kampus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;

/**
 * Direktori mahasiswa dan dosen (FM-11 sampai FM-13, FD-08).
 * Satu aturan "siapa yang terlihat" dipakai untuk daftar dan detail, jadi profil yang tidak
 * boleh tampil tidak bisa dibuka lewat tautan langsung.
 */
class DirektoriController extends Controller
{
    public function index(CariDirektoriRequest $request): View
    {
        $filter = $request->validated();

        $profil = $this->kueri($filter)
            ->with(['mahasiswa', 'dosen', 'keahlian']) // eager loading: jumlah query tidak naik dengan jumlah baris
            ->orderBy('name')
            ->orderBy('id')
            ->paginate(12)
            ->withQueryString();

        $terlihat = fn () => Mahasiswa::where('tampil_di_direktori', true)->where('disembunyikan_admin', false);

        return view('direktori.index', [
            'profil' => $profil,
            'filter' => $filter,
            'adaFilter' => collect($filter)->filter(fn ($v) => $v !== null && $v !== '')->isNotEmpty(),
            'daftarKeahlian' => Keahlian::orderBy('nama')->get(['id', 'nama']),
            'daftarJurusan' => array_keys(Kampus::jurusan()),
            'daftarProdi' => Kampus::jurusan(),
            'daftarKelas' => $terlihat()->distinct()->orderBy('kelas')->pluck('kelas'),
            'daftarAngkatan' => $terlihat()->distinct()->orderByDesc('angkatan')->pluck('angkatan'),
        ]);
    }

    public function show(string $id): View
    {
        // Tanpa filter, tetapi dengan aturan keterlihatan yang sama. Hasilnya 404 untuk akun yang
        // tidak ada, nonaktif, bukan mahasiswa/dosen, atau profilnya disembunyikan (tidak membedakan alasan).
        $user = $this->kueri([])
            ->with(['mahasiswa', 'dosen', 'keahlian', 'riwayat']) // riwayat: satu query, berapa pun jumlah entrinya
            ->whereKey($id)
            ->firstOrFail();

        return view('direktori.show', [
            'user' => $user,
            'profil' => $user->role === User::ROLE_DOSEN ? $user->dosen : $user->mahasiswa,
        ]);
    }

    /**
     * Pengguna yang boleh tampil di direktori, dengan filter opsional.
     * Filter diterapkan per jenis profil. Kelas dan angkatan hanya dimiliki mahasiswa, jadi bila salah satunya
     * dipakai, dosen tidak ikut.
     */
    private function kueri(array $filter): Builder
    {
        $jenis = $filter['jenis'] ?? null;
        $filterKhususMahasiswa = ! empty($filter['kelas']) || ! empty($filter['angkatan']);

        $umum = fn ($p) => $p
            ->where('tampil_di_direktori', true)
            ->where('disembunyikan_admin', false)
            ->when($filter['jurusan'] ?? null, fn ($p, $v) => $p->where('jurusan', $v))
            ->when($filter['program_studi'] ?? null, fn ($p, $v) => $p->where('program_studi', $v));

        $mahasiswa = fn ($p) => $umum($p)
            ->when($filter['kelas'] ?? null, fn ($p, $v) => $p->where('kelas', $v))
            ->when($filter['angkatan'] ?? null, fn ($p, $v) => $p->where('angkatan', $v));

        return User::query()
            ->where('status', User::STATUS_AKTIF)
            ->where(function (Builder $q) use ($jenis, $filterKhususMahasiswa, $mahasiswa, $umum) {
                $cabang = 0;

                if ($jenis !== 'dosen') {
                    $q->orWhere(fn ($m) => $m->where('role', User::ROLE_MAHASISWA)->whereHas('mahasiswa', $mahasiswa));
                    $cabang++;
                }

                if ($jenis !== 'mahasiswa' && ! $filterKhususMahasiswa) {
                    $q->orWhere(fn ($d) => $d->where('role', User::ROLE_DOSEN)->whereHas('dosen', $umum));
                    $cabang++;
                }

                // Tanpa cabang, grup kosong akan diabaikan dan SEMUA pengguna lolos. Paksa kosong.
                if ($cabang === 0) {
                    $q->whereIn('id', []);
                }
            })
            ->when($filter['keahlian'] ?? null, fn ($q, $id) => $q->whereHas('keahlian', fn ($k) => $k->where('keahlian.id', $id)))
            ->when($filter['q'] ?? null, fn ($q, $nama) => $q->whereLike('name', "%{$nama}%"));
    }
}
