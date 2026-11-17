<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateMitraProfilRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/** Profil organisasi milik mitra yang sedang masuk. */
class MitraProfilController extends Controller
{
    public function show(Request $request): View
    {
        $user = $request->user();
        abort_if($user->mitra === null, 404);

        return view('mitra.profil', ['user' => $user, 'mitra' => $user->mitra]);
    }

    public function update(UpdateMitraProfilRequest $request): RedirectResponse
    {
        $user = $request->user();
        $mitra = $user->mitra;
        abort_if($mitra === null, 404);

        $data = $request->validated();

        // status_verifikasi tidak ada di $fillable Mitra: hanya admin yang boleh mengubahnya.
        DB::transaction(function () use ($user, $mitra, $data) {
            $user->update(['name' => $data['name']]);

            $namaLama = $mitra->nama_organisasi;
            $mitra->update(Arr::except($data, ['name']));

            // Bila nama organisasi berubah, sinkronkan kolom penyelenggara di semua
            // informasi milik mitra ini. Informasi milik admin atau mitra lain tidak terpengaruh.
            if (isset($data['nama_organisasi']) && $data['nama_organisasi'] !== $namaLama) {
                $namaBaru = $data['nama_organisasi'];
                foreach (['magang', 'lomba', 'beasiswa'] as $relasi) {
                    $user->{$relasi}()->update(['penyelenggara' => $namaBaru]);
                }
            }
        });

        return redirect()->route('mitra.profil.show')->with('status', 'Profil organisasi berhasil diperbarui.');
    }
}
