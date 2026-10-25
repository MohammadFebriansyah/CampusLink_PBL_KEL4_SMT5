<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateProfilRequest;
use App\Models\Keahlian;
use App\Models\RiwayatProfil;
use App\Models\User;
use App\Support\Kampus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/** Profil milik pengguna yang sedang masuk (mahasiswa atau dosen). */
class ProfilController extends Controller
{
    public function show(Request $request): View
    {
        $user = $request->user();
        $profil = $this->profilDari($user);

        return view('profil.edit', [
            'user' => $user,
            'profil' => $profil,
            'peran' => $user->role,
            'semuaKeahlian' => Keahlian::orderBy('nama')->get(),
            'keahlianTerpilih' => $user->keahlian()->pluck('keahlian.id')->all(),
            'riwayat' => $user->role === User::ROLE_MAHASISWA ? RiwayatProfil::kelompokkan($user->riwayat()->get()) : [],
        ]);
    }

    public function update(UpdateProfilRequest $request): RedirectResponse
    {
        $user = $request->user();
        $profil = $this->profilDari($user);
        $data = $request->validated();

        $atribut = Arr::except($data, ['name', 'keahlian', 'foto', 'hapus_foto']);
        $atribut['jurusan'] = Kampus::jurusanDariProdi($data['program_studi']);

        $fotoLama = $profil->foto;
        $fotoBaru = $request->hasFile('foto')
            ? $request->file('foto')->store('foto-profil', 'public') // nama acak, bukan nama dari pengguna
            : null;

        if ($fotoBaru) {
            $atribut['foto'] = $fotoBaru;
        } elseif ($request->boolean('hapus_foto')) {
            $atribut['foto'] = null;
        }

        try {
            DB::transaction(function () use ($user, $profil, $data, $atribut) {
                $user->update(['name' => $data['name']]);
                $profil->update($atribut);
                $user->keahlian()->sync($data['keahlian'] ?? []);
            });
        } catch (\Throwable $e) {
            if ($fotoBaru) {
                Storage::disk('public')->delete($fotoBaru); // jangan tinggalkan berkas yatim
            }

            throw $e;
        }

        // Berkas lama baru dihapus setelah database berhasil diperbarui.
        if ($fotoLama && array_key_exists('foto', $atribut)) {
            Storage::disk('public')->delete($fotoLama);
        }

        return redirect()->route('profil.show')->with('status', 'Profil berhasil diperbarui.');
    }

    private function profilDari(User $user)
    {
        $profil = $user->role === User::ROLE_DOSEN ? $user->dosen : $user->mahasiswa;

        abort_if($profil === null, 404);

        return $profil;
    }
}
