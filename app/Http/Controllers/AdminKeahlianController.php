<?php

namespace App\Http\Controllers;

use App\Http\Requests\CariDaftarRequest;
use App\Http\Requests\SimpanKeahlianRequest;
use App\Http\Resources\KeahlianResource;
use App\Models\Keahlian;
use App\Support\Daftar;
use App\Support\Urutan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\View\View;

/** Daftar bidang keahlian yang bisa dipilih pengguna (data master). */
class AdminKeahlianController extends Controller
{
    public function index(CariDaftarRequest $request): JsonResponse|Response
    {
        $f = $request->validated();

        $query = Keahlian::withCount('pengguna')
            ->when($f['q'] ?? null, fn ($q, $v) => $q->whereLike('nama', "%{$v}%"));

        $perHalaman = Daftar::perHalaman($f['per_halaman'] ?? null, 20);
        $urutan = Urutan::terapkan($query, $f['urut'] ?? null, $f['arah'] ?? null, [
            'nama' => 'nama',
            'dipakai' => 'pengguna_count',
        ], ['nama', 'asc']);

        return Daftar::tanggapi(
            $request,
            KeahlianResource::collection($query->paginate($perHalaman)->withQueryString()),
            'admin.keahlian.index',
            ['q' => $f['q'] ?? ''] + $urutan + ['per_halaman' => $perHalaman],
        );
    }

    public function store(SimpanKeahlianRequest $request): RedirectResponse
    {
        Keahlian::create($request->validated());

        return redirect()->route('admin.keahlian.index')->with('status', 'Keahlian ditambahkan.');
    }

    public function edit(string $id): View
    {
        return view('admin.keahlian.edit', ['keahlian' => Keahlian::withCount('pengguna')->findOrFail($id)]);
    }

    public function update(SimpanKeahlianRequest $request, string $id): RedirectResponse
    {
        Keahlian::findOrFail($id)->update($request->validated());

        return redirect()->route('admin.keahlian.index')->with('status', 'Nama keahlian diubah.');
    }

    public function destroy(string $id): RedirectResponse
    {
        // Keterkaitan di tabel pivot ikut terhapus (cascadeOnDelete), profil pengguna tidak terhapus.
        Keahlian::findOrFail($id)->delete();

        return redirect()->route('admin.keahlian.index')->with('status', 'Keahlian dihapus dan dilepas dari profil yang memakainya.');
    }
}
