<?php

namespace App\Http\Controllers;

use App\Http\Requests\SimpanRiwayatRequest;
use App\Models\RiwayatProfil;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Sertifikasi, pelatihan, proyek, dan pencapaian milik mahasiswa yang sedang masuk.
 * Dipanggil dengan fetch dari halaman profil (balasan JSON berisi HTML bagian yang diperbarui),
 * atau sebagai form biasa bila JavaScript mati (balasan redirect ke halaman profil).
 */
class RiwayatProfilController extends Controller
{
    public function store(SimpanRiwayatRequest $request): JsonResponse|RedirectResponse
    {
        $riwayat = new RiwayatProfil($request->safe()->except('jenis'));
        $riwayat->jenis = $request->validated('jenis');
        $riwayat->user()->associate($request->user());
        $riwayat->save();

        return $this->balas($request, $riwayat->jenis, RiwayatProfil::JENIS[$riwayat->jenis]['nama'] . ' ditambahkan.');
    }

    public function update(SimpanRiwayatRequest $request, string $id): JsonResponse|RedirectResponse
    {
        $riwayat = $this->milikSendiri($request->user(), $id);
        $riwayat->update($request->validated());

        return $this->balas($request, $riwayat->jenis, RiwayatProfil::JENIS[$riwayat->jenis]['nama'] . ' diperbarui.');
    }

    public function destroy(Request $request, string $id): JsonResponse|RedirectResponse
    {
        $riwayat = $this->milikSendiri($request->user(), $id);
        $riwayat->delete();

        return $this->balas($request, $riwayat->jenis, RiwayatProfil::JENIS[$riwayat->jenis]['nama'] . ' dihapus.');
    }

    /** Entri milik orang lain (atau yang tidak ada) sama-sama 404, tanpa membedakan alasannya. */
    private function milikSendiri(User $user, string $id): RiwayatProfil
    {
        return $user->riwayat()->findOrFail($id);
    }

    private function balas(Request $request, string $jenis, string $pesan): JsonResponse|RedirectResponse
    {
        if ($request->expectsJson()) {
            $daftar = RiwayatProfil::kelompokkan($request->user()->riwayat()->where('jenis', $jenis)->get())[$jenis];

            return response()->json([
                'html' => view('profil._riwayat-bagian', ['jenis' => $jenis, 'daftar' => $daftar, 'pesan' => $pesan])->render(),
            ]);
        }

        return redirect()->to(route('profil.show') . '#riwayat-' . $jenis)->with('status', $pesan);
    }
}
