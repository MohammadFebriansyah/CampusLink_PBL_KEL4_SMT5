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
            $mitra->update(Arr::except($data, ['name']));
        });

        return redirect()->route('mitra.profil.show')->with('status', 'Profil organisasi berhasil diperbarui.');
    }
}
