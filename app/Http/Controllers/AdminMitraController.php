<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminCariRequest;
use App\Models\Mitra;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

/** Verifikasi akun mitra: setujui atau tolak (FA-03, UC-08). */
class AdminMitraController extends Controller
{
    public function index(AdminCariRequest $request): View
    {
        $status = $request->validated('verifikasi') ?? Mitra::STATUS_PENDING;

        $daftar = Mitra::with('user')
            ->where('status_verifikasi', $status)
            ->orderBy('created_at')   // yang mendaftar lebih dulu ditangani lebih dulu
            ->orderBy('id')
            ->paginate(10)
            ->withQueryString();

        $jumlah = Mitra::query()
            ->select('status_verifikasi')->selectRaw('count(*) as jumlah')
            ->groupBy('status_verifikasi')->pluck('jumlah', 'status_verifikasi');

        return view('admin.mitra.index', ['daftar' => $daftar, 'status' => $status, 'jumlah' => $jumlah]);
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
