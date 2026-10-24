<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminCariRequest;
use App\Http\Requests\UbahPenggunaRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Kelola pengguna mahasiswa, dosen, dan mitra (FA-02, FA-08, UC-09).
 * Akun admin tidak ikut dikelola di sini. Admin hanya menonaktifkan, tidak menghapus akun.
 */
class AdminPenggunaController extends Controller
{
    public function index(AdminCariRequest $request): View
    {
        $f = $request->validated();

        $pengguna = User::query()
            ->where('role', '!=', User::ROLE_ADMIN)
            ->when($f['peran'] ?? null, fn ($q, $v) => $q->where('role', $v))
            ->when($f['akun'] ?? null, fn ($q, $v) => $q->where('status', $v))
            ->when($f['q'] ?? null, fn ($q, $v) => $q->where(
                fn ($q) => $q->whereLike('name', "%{$v}%")->orWhereLike('email', "%{$v}%")
            ))
            ->with(['mahasiswa', 'dosen', 'mitra'])   // eager loading, tanpa N+1
            ->orderBy('name')
            ->orderBy('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.pengguna.index', ['pengguna' => $pengguna, 'filter' => $f]);
    }

    public function show(string $id): View
    {
        $user = $this->pengguna($id)->load(['mahasiswa', 'dosen', 'mitra', 'keahlian']);

        return view('admin.pengguna.show', [
            'user' => $user,
            'profil' => match ($user->role) {
                User::ROLE_MAHASISWA => $user->mahasiswa,
                User::ROLE_DOSEN => $user->dosen,
                default => null,
            },
        ]);
    }

    public function edit(string $id): View
    {
        $user = $this->pengguna($id)->load(['mahasiswa', 'dosen']);

        return view('admin.pengguna.edit', ['user' => $user]);
    }

    public function update(UbahPenggunaRequest $request, string $id): RedirectResponse
    {
        $user = $this->pengguna($id)->load(['mahasiswa', 'dosen']);
        $data = $request->validated();

        DB::transaction(function () use ($user, $data) {
            // Verifikasi Google berlaku untuk email lama. Bila email berubah, kepemilikan email baru
            // harus dibuktikan lagi (masuk dengan Google memakai email baru).
            if ($data['email'] !== $user->email) {
                $user->google_id = null;
            }

            $user->email = $data['email'];
            $user->save();

            if ($user->role === User::ROLE_MAHASISWA) {
                $user->mahasiswa->update(['nim' => $data['nim']]);
            } elseif ($user->role === User::ROLE_DOSEN) {
                $user->dosen->update(['nidn' => $data['nidn']]);
            }
        });

        return redirect()->route('admin.pengguna.show', $user->id)->with('status', 'Data pengguna disimpan.');
    }

    public function aktifkan(string $id): RedirectResponse
    {
        $user = $this->pengguna($id);
        $user->status = User::STATUS_AKTIF;
        $user->save();

        $pesan = 'Akun diaktifkan.';
        if ($user->role === User::ROLE_MITRA) {
            $pesan .= ' Informasi milik mitra yang pernah disembunyikan tidak ikut ditampilkan kembali; atur lewat menu informasi.';
        }

        return back()->with('status', $pesan);
    }

    public function nonaktifkan(Request $request, string $id): RedirectResponse
    {
        $user = $this->pengguna($id);

        DB::transaction(function () use ($request, $user) {
            $user->status = User::STATUS_NONAKTIF;
            $user->save();

            // Untuk mitra, informasinya tetap tayang kecuali admin memilih menyembunyikannya sekaligus.
            if ($user->role === User::ROLE_MITRA && $request->boolean('sembunyikan_informasi')) {
                foreach (['magang', 'lomba', 'beasiswa'] as $kategori) {
                    // Lewat query builder relasi: status memang tidak ada di $fillable model.
                    $user->{$kategori}()->update(['status' => 'disembunyikan']);
                }
            }
        });

        return back()->with('status', 'Akun dinonaktifkan. Pengguna langsung tidak bisa memakai sistem.');
    }

    public function sembunyikanProfil(string $id): RedirectResponse
    {
        return $this->ubahPenandaProfil($id, true, 'Profil disembunyikan dari direktori.');
    }

    public function tampilkanProfil(string $id): RedirectResponse
    {
        return $this->ubahPenandaProfil($id, false, 'Profil kembali dapat tampil di direktori (bila pemiliknya mengizinkan).');
    }

    private function ubahPenandaProfil(string $id, bool $sembunyi, string $pesan): RedirectResponse
    {
        $user = $this->pengguna($id)->load(['mahasiswa', 'dosen']);
        $profil = $user->role === User::ROLE_DOSEN ? $user->dosen : $user->mahasiswa;

        abort_if($profil === null, 404); // mitra tidak punya profil direktori

        $profil->disembunyikan_admin = $sembunyi; // bukan $fillable: hanya admin
        $profil->save();

        return back()->with('status', $pesan);
    }

    /** Hanya mahasiswa, dosen, dan mitra. Akun admin atau id yang tidak ada menghasilkan 404. */
    private function pengguna(string $id): User
    {
        return User::where('role', '!=', User::ROLE_ADMIN)->findOrFail($id);
    }
}
