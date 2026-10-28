<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminCariRequest;
use App\Http\Resources\MitraVerifikasiResource;
use App\Models\Mitra;
use App\Support\Daftar;
use App\Support\Urutan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;

/** Verifikasi akun mitra: setujui atau tolak (FA-03, UC-08). */
class AdminMitraController extends Controller
{
    public function index(AdminCariRequest $request): JsonResponse|Response
    {
        $f = $request->validated();
        $status = $f['verifikasi'] ?? Mitra::STATUS_PENDING;

        $query = Mitra::with('user')
            ->where('status_verifikasi', $status)
            ->when($f['q'] ?? null, fn ($q, $v) => $q->where(
                fn ($q) => $q->whereLike('nama_organisasi', "%{$v}%")
                    ->orWhereHas('user', fn ($u) => $u->whereLike('name', "%{$v}%")->orWhereLike('email', "%{$v}%"))
            ));

        // Bawaan: yang mendaftar lebih dulu ditangani lebih dulu.
        $perHalaman = Daftar::perHalaman($f['per_halaman'] ?? null, 10);
        $urutan = Urutan::terapkan($query, $f['urut'] ?? null, $f['arah'] ?? null, [
            'mendaftar' => 'created_at',
            'nama' => 'nama_organisasi',
        ], ['mendaftar', 'asc']);

        $jumlah = Mitra::query()
            ->select('status_verifikasi')->selectRaw('count(*) as jumlah')
            ->groupBy('status_verifikasi')->pluck('jumlah', 'status_verifikasi');

        return Daftar::tanggapi(
            $request,
            MitraVerifikasiResource::collection($query->paginate($perHalaman)->withQueryString()),
            'admin.mitra.index',
            ['q' => $f['q'] ?? '', 'verifikasi' => $status] + $urutan + ['per_halaman' => $perHalaman],
            tambahan: ['jumlah' => $jumlah],
            dataView: ['jumlah' => $jumlah],
        );
    }

    public function setujui(string $id): RedirectResponse
    {
        return $this->ubahStatus($id, Mitra::STATUS_DISETUJUI, 'Akun mitra disetujui. Mitra kini bisa masuk.');
    }

    public function tolak(string $id): RedirectResponse
    {
        return $this->ubahStatus($id, Mitra::STATUS_DITOLAK, 'Akun mitra ditolak. Mitra tidak bisa masuk.');
    }

    private function ubahStatus(string $id, string $status, string $pesan): RedirectResponse
    {
        $mitra = Mitra::findOrFail($id);

        // status_verifikasi tidak ada di $fillable, jadi diisi eksplisit.
        $mitra->status_verifikasi = $status;
        $mitra->save();

        return back()->with('status', $pesan);
    }
}
