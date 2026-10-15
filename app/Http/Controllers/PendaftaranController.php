<?php

namespace App\Http\Controllers;

use App\Models\Pendaftaran;
use App\Support\Informasi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/** Pendaftaran oleh mahasiswa: daftar dengan CV, batal, dan halaman "Kegiatan saya". */
class PendaftaranController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $pendaftaran = $user->pendaftaran()->with('informasi')->latest()->get();
        $bimbingan = $user->bimbinganDiajukan()->with(['informasi', 'dosen'])->latest()->get();

        // Kegiatan yang detailnya dibuka di panel samping (layar lebar): dari ?pilih=magang-3 bila
        // masih bisa dibuka, selain itu kegiatan pertama di daftar. Yang disembunyikan admin dilewati.
        $bisaDibuka = $pendaftaran->pluck('informasi')->merge($bimbingan->pluck('informasi'))
            ->filter(fn($i) => $i && $i->status === 'tayang')
            ->keyBy(fn($i) => $i->kategori() . '-' . $i->id);
        $terpilih = $bisaDibuka->get((string) $request->query('pilih')) ?? $bisaDibuka->first();

        return view('kegiatan.index', [
            'pendaftaran' => $pendaftaran,
            'bimbingan' => $bimbingan,
            'bisaDibuka' => $bisaDibuka->keys()->all(),
            'detail' => $terpilih
                ? app(InformasiController::class)->dataDetail($terpilih->kategori(), ['label' => Informasi::LABEL[$terpilih->kategori()]], $terpilih)
                : null,
        ]);
    }

    public function store(Request $request, string $kategori, string $id): RedirectResponse
    {
        $informasi = Informasi::cari($kategori, $id);

        abort_unless($informasi->lewatCampuslink(), 404); // kegiatan luar didaftar di situs penyelenggara

        if (! $informasi->terbuka()) {
            return back()->with('galat', 'Pendaftaran untuk kegiatan ini sudah ditutup.');
        }

        $sudah = $informasi->pendaftaran()->where('user_id', $request->user()->id)->exists();
        if ($sudah) {
            return back()->with('galat', 'Anda sudah mendaftar ke kegiatan ini.');
        }

        $request->validate(
            ['cv' => ['required', 'file', 'mimes:pdf', 'max:2048']],
            [
                'cv.required' => 'Unggah CV dalam format PDF.',
                'cv.mimes' => 'CV harus berupa berkas PDF.',
                'cv.max' => 'Ukuran CV maksimal 2 MB.',
            ],
        );

        // Disimpan di disk privat (storage/app/private/cv), hanya bisa diunduh lewat route yang diperiksa.
        $path = $request->file('cv')->store('cv', 'local');

        $pendaftaran = new Pendaftaran(['cv' => $path]);
        $pendaftaran->user()->associate($request->user());
        $pendaftaran->informasi()->associate($informasi);
        $pendaftaran->save();

        return back()->with('status', 'Pendaftaran terkirim. Anda bisa memantau statusnya di Kegiatan saya.');
    }

    public function destroy(Request $request, string $id): RedirectResponse
    {
        $pendaftaran = $request->user()->pendaftaran()->findOrFail($id); // hanya milik sendiri

        if ($pendaftaran->status !== Pendaftaran::STATUS_MENUNGGU) {
            return back()->with('galat', 'Pendaftaran yang sudah ditinjau tidak bisa dibatalkan.');
        }

        Storage::disk('local')->delete($pendaftaran->cv);
        $pendaftaran->delete();

        return back()->with('status', 'Pendaftaran dibatalkan.');
    }

    /** Unduh CV: pemilik pendaftaran atau peninjau informasinya (mitra pembuat / admin untuk kegiatan kampus). */
    public function cv(Request $request, string $id): BinaryFileResponse
    {
        $pendaftaran = Pendaftaran::with(['informasi', 'user'])->findOrFail($id);
        $user = $request->user();

        $boleh = (int) $pendaftaran->user_id === (int) $user->id
            || ($pendaftaran->informasi && $pendaftaran->informasi->bisaDitinjauOleh($user));
        abort_unless($boleh, 403);

        $disk = Storage::disk('local');
        abort_unless($disk->exists($pendaftaran->cv), 404);

        $nama = 'CV ' . preg_replace('/[^A-Za-z0-9 ._-]/', '', $pendaftaran->user->name) . '.pdf';

        return response()->download($disk->path($pendaftaran->cv), $nama, [
            'Content-Type' => 'application/pdf',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
