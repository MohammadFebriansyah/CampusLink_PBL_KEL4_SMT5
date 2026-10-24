<?php

namespace App\Http\Controllers;

use App\Http\Requests\SimpanKeahlianRequest;
use App\Models\Keahlian;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/** Daftar bidang keahlian yang bisa dipilih pengguna (data master). */
class AdminKeahlianController extends Controller
{
    public function index(): View
    {
        return view('admin.keahlian.index', [
            'daftar' => Keahlian::withCount('pengguna')->orderBy('nama')->get(),
        ]);
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
