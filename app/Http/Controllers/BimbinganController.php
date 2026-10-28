<?php

namespace App\Http\Controllers;

use App\Http\Requests\CariDaftarRequest;
use App\Http\Resources\BimbinganMasukResource;
use App\Models\Beasiswa;
use App\Models\Bimbingan;
use App\Models\Lomba;
use App\Models\Magang;
use App\Models\User;
use App\Support\Daftar;
use App\Support\Informasi;
use App\Support\Urutan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * Bimbingan magang dan lomba: mahasiswa mengajukan ke dosen, dosen menerima atau menolak.
 * Hanya bisa diajukan setelah mahasiswa diterima di kegiatannya (lihat syaratDiterima()).
 */
class BimbinganController extends Controller
{
    /** Dosen aktif yang bersedia membimbing kategori ini. */
    public static function dosenTersedia(string $kategori)
    {
        $kolom = $kategori === 'lomba' ? 'bersedia_membimbing_lomba' : 'bersedia_membimbing_magang';

        return User::query()
            ->where('role', User::ROLE_DOSEN)
            ->where('status', User::STATUS_AKTIF)
            ->whereHas('dosen', fn ($q) => $q->where($kolom, true))
            ->with('dosen:id,user_id,program_studi,jurusan')
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    // ---------- Mahasiswa ----------

    /**
     * Apakah mahasiswa sudah memenuhi syarat diterima untuk mengajukan bimbingan.
     * Kegiatan di CampusLink: pendaftarannya harus berstatus diterima.
     * Kegiatan luar: selalu true di sini, karena syaratnya berupa unggahan bukti di formulir.
     */
    public static function syaratDiterima(Magang|Lomba|Beasiswa $informasi, User $mahasiswa): bool
    {
        if (! $informasi->lewatCampuslink()) {
            return true;
        }

        return $informasi->pendaftaran()
            ->where('user_id', $mahasiswa->id)
            ->where('status', 'diterima')
            ->exists();
    }

    public function store(Request $request, string $kategori, string $id): RedirectResponse
    {
        $informasi = Informasi::cari($kategori, $id);
        abort_unless($informasi->bisaBimbingan(), 404);

        $mahasiswa = $request->user();

        if (! self::syaratDiterima($informasi, $mahasiswa)) {
            return back()->with('galat', 'Bimbingan bisa diajukan setelah pendaftaran Anda diterima.');
        }

        $luar = ! $informasi->lewatCampuslink();
        $tersedia = self::dosenTersedia($kategori)->pluck('id')->all();
        $data = $request->validate([
            'dosen_id' => ['required', Rule::in($tersedia)],
            'pesan' => ['nullable', 'string', 'max:1000'],
            // Kegiatan luar: bukti diterima wajib (surat, email, atau tangkapan layar).
            'bukti' => $luar ? ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'] : ['prohibited'],
        ], [
            'dosen_id.required' => 'Pilih dosen pembimbing.',
            'dosen_id.in' => 'Dosen ini tidak tersedia untuk membimbing '.$kategori.'.',
            'bukti.required' => 'Unggah bukti bahwa Anda sudah diterima oleh penyelenggara.',
            'bukti.mimes' => 'Bukti harus berupa PDF, JPG, atau PNG.',
            'bukti.max' => 'Ukuran bukti maksimal 2 MB.',
        ]);

        $lama = $informasi->bimbingan()->where('mahasiswa_id', $mahasiswa->id)->first();

        if ($lama && $lama->status !== Bimbingan::STATUS_DITOLAK) {
            return back()->with('galat', 'Anda sudah mengajukan bimbingan untuk kegiatan ini.');
        }

        // Ditolak sebelumnya: ganti dengan pengajuan baru (boleh ke dosen lain).
        if ($lama) {
            $lama->hapusBukti();
            $lama->delete();
        }

        $bimbingan = new Bimbingan(['pesan' => $data['pesan'] ?? null]);
        $bimbingan->mahasiswa_id = $mahasiswa->id;
        $bimbingan->dosen_id = (int) $data['dosen_id'];
        $bimbingan->bukti = $luar ? $request->file('bukti')->store('bukti-bimbingan', 'local') : null;
        $bimbingan->informasi()->associate($informasi);
        $bimbingan->save();

        return back()->with('status', 'Permintaan bimbingan terkirim ke dosen.');
    }

    /** Lihat bukti diterima: mahasiswa pengaju atau dosen yang dituju. */
    public function bukti(Request $request, string $id): BinaryFileResponse
    {
        $bimbingan = Bimbingan::findOrFail($id);
        $user = $request->user();

        abort_unless(in_array((int) $user->id, [(int) $bimbingan->mahasiswa_id, (int) $bimbingan->dosen_id], true), 403);

        $disk = Storage::disk('local');
        abort_unless($bimbingan->bukti && $disk->exists($bimbingan->bukti), 404);

        // Ditampilkan di browser (gambar/PDF), tidak dijalankan sebagai HTML.
        return response()->file($disk->path($bimbingan->bukti), ['X-Content-Type-Options' => 'nosniff']);
    }

    public function destroy(Request $request, string $id): RedirectResponse
    {
        $bimbingan = $request->user()->bimbinganDiajukan()->findOrFail($id);

        if ($bimbingan->status === Bimbingan::STATUS_DITERIMA) {
            return back()->with('galat', 'Bimbingan yang sudah diterima tidak bisa dibatalkan. Hubungi dosen Anda.');
        }

        $bimbingan->hapusBukti();
        $bimbingan->delete();

        return back()->with('status', 'Permintaan bimbingan dibatalkan.');
    }

    // ---------- Dosen ----------

    public function index(CariDaftarRequest $request): JsonResponse|Response
    {
        $f = $request->validated();
        $status = in_array($f['status'] ?? null, ['menunggu', 'diterima', 'ditolak'], true) ? $f['status'] : Bimbingan::STATUS_MENUNGGU;

        $semua = $request->user()->bimbinganMasuk();
        $jumlah = (clone $semua)->selectRaw('status, count(*) as jumlah')->groupBy('status')->pluck('jumlah', 'status');

        $query = (clone $semua)->where('status', $status)
            ->with(['informasi.user', 'mahasiswa.mahasiswa', 'mahasiswa.keahlian'])
            ->when($f['q'] ?? null, fn ($q, $v) => $q->whereHas('mahasiswa', fn ($m) => $m->whereLike('name', "%{$v}%")));

        $perHalaman = Daftar::perHalaman($f['per_halaman'] ?? null, 15);
        $urutan = Urutan::terapkan($query, $f['urut'] ?? null, $f['arah'] ?? null, [
            'diajukan' => 'created_at',
        ], ['diajukan', 'desc']);

        return Daftar::tanggapi(
            $request,
            BimbinganMasukResource::collection($query->paginate($perHalaman)->withQueryString()),
            'bimbingan.index',
            ['q' => $f['q'] ?? '', 'status' => $status] + $urutan + ['per_halaman' => $perHalaman],
            tambahan: ['jumlah' => $jumlah],
            dataView: ['jumlah' => $jumlah],
        );
    }

    public function terima(Request $request, string $id): RedirectResponse
    {
        return $this->putuskan($request, $id, Bimbingan::STATUS_DITERIMA, 'Permintaan bimbingan diterima.');
    }

    public function tolak(Request $request, string $id): RedirectResponse
    {
        return $this->putuskan($request, $id, Bimbingan::STATUS_DITOLAK, 'Permintaan bimbingan ditolak.');
    }

    private function putuskan(Request $request, string $id, string $status, string $pesan): RedirectResponse
    {
        $bimbingan = $request->user()->bimbinganMasuk()->findOrFail($id); // hanya yang ditujukan ke dosen ini
        $data = $request->validate(['catatan' => ['nullable', 'string', 'max:1000']]);

        $bimbingan->status = $status;
        $bimbingan->catatan = $data['catatan'] ?? null;
        $bimbingan->ditinjau_pada = now();
        $bimbingan->save();

        return back()->with('status', $pesan);
    }
}
