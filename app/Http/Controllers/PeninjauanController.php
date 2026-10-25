<?php

namespace App\Http\Controllers;

use App\Models\Beasiswa;
use App\Models\Lomba;
use App\Models\Magang;
use App\Models\Pendaftaran;
use App\Models\User;
use App\Support\Informasi;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Peninjauan pendaftar oleh mitra (informasi miliknya) dan admin (kegiatan kampus buatan admin).
 * Satu controller untuk dua panel; route memberi tahu panel lewat defaults('panel').
 */
class PeninjauanController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $status = $request->query('status', Pendaftaran::STATUS_MENUNGGU);
        $status = array_key_exists($status, Pendaftaran::LABEL_STATUS) ? $status : Pendaftaran::STATUS_MENUNGGU;

        $semua = $this->milikPeninjau($user);

        $jumlah = (clone $semua)->selectRaw('status, count(*) as jumlah')->groupBy('status')->pluck('jumlah', 'status');

        $daftar = (clone $semua)
            ->with(['informasi', 'user.mahasiswa'])
            ->where('status', $status)
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('peninjauan.index', [
            'panel' => $this->panel($request),
            'status' => $status,
            'jumlah' => $jumlah,
            'daftar' => $daftar,
        ]);
    }

    public function show(Request $request, string $id): View
    {
        $pendaftaran = $this->cari($request, $id);
        $pendaftaran->load(['user.mahasiswa', 'user.keahlian', 'user.riwayat', 'informasi', 'peninjau']);

        return view('peninjauan.show', [
            'panel' => $this->panel($request),
            'p' => $pendaftaran,
        ]);
    }

    public function terima(Request $request, string $id): RedirectResponse
    {
        return $this->putuskan($request, $id, Pendaftaran::STATUS_DITERIMA, 'Pendaftar diterima.');
    }

    public function tolak(Request $request, string $id): RedirectResponse
    {
        return $this->putuskan($request, $id, Pendaftaran::STATUS_DITOLAK, 'Pendaftar ditolak.');
    }

    private function putuskan(Request $request, string $id, string $status, string $pesan): RedirectResponse
    {
        $pendaftaran = $this->cari($request, $id);
        $data = $request->validate(['catatan' => ['nullable', 'string', 'max:1000']]);

        $pendaftaran->status = $status;
        $pendaftaran->catatan = $data['catatan'] ?? null;
        $pendaftaran->ditinjau_oleh = $request->user()->id;
        $pendaftaran->ditinjau_pada = now();
        $pendaftaran->save();

        return back()->with('status', $pesan);
    }

    /** Pendaftaran yang boleh ditinjau pengguna ini, atau 404 (tidak membocorkan keberadaan data lain). */
    private function cari(Request $request, string $id): Pendaftaran
    {
        return $this->milikPeninjau($request->user())->findOrFail($id);
    }

    private function milikPeninjau(User $user): Builder
    {
        return Pendaftaran::query()->whereHasMorph(
            'informasi',
            [Magang::class, Lomba::class, Beasiswa::class],
            fn(Builder $q) => Informasi::batasiPeninjau($q, $user),
        );
    }

    private function panel(Request $request): string
    {
        return $request->route('panel') === 'admin' ? 'admin' : 'mitra';
    }
}
