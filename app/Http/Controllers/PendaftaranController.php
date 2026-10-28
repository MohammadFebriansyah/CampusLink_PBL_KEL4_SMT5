<?php

namespace App\Http\Controllers;

use App\Http\Requests\CariDaftarRequest;
use App\Http\Resources\BimbinganSayaResource;
use App\Http\Resources\PendaftaranSayaResource;
use App\Models\Pendaftaran;
use App\Support\Daftar;
use App\Support\Informasi;
use App\Support\StatusPeninjauan;
use App\Support\Urutan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/** Pendaftaran oleh mahasiswa: daftar dengan CV, batal, dan halaman "Kegiatan saya". */
class PendaftaranController extends Controller
{
    /**
     * Kegiatan saya: pendaftaran dan permintaan bimbingan milik mahasiswa, dipilih lewat ?bagian=.
     * Pencarian mencocokkan judul kegiatan (dan nama dosen untuk bimbingan).
     */
    public function index(CariDaftarRequest $request): JsonResponse|Response
    {
        $user = $request->user();
        $f = $request->validated();
        $bagian = $f['bagian'] ?? 'pendaftaran';
        $status = array_key_exists($f['status'] ?? '', StatusPeninjauan::LABEL) ? $f['status'] : '';
        $judulCocok = fn (string $v) => fn (Builder $q) => $q->whereLike('judul', "%{$v}%");

        $query = $bagian === 'bimbingan'
            ? $user->bimbinganDiajukan()->with(['informasi', 'dosen'])
                ->when($f['q'] ?? null, fn ($q, $v) => $q->where(fn ($q) => $q
                    ->whereHasMorph('informasi', array_values(Informasi::MODEL), $judulCocok($v))
                    ->orWhereHas('dosen', fn ($d) => $d->whereLike('name', "%{$v}%"))))
            : $user->pendaftaran()->with('informasi')
                ->when($f['q'] ?? null, fn ($q, $v) => $q->whereHasMorph('informasi', array_values(Informasi::MODEL), $judulCocok($v)));
        $query->when($status, fn ($q, $v) => $q->where('status', $v));

        $perHalaman = Daftar::perHalaman($f['per_halaman'] ?? null, 10);
        $urutan = Urutan::terapkan($query, $f['urut'] ?? null, $f['arah'] ?? null, [
            'dikirim' => 'created_at',
        ], ['dikirim', 'desc']);

        $items = $query->paginate($perHalaman)->withQueryString();
        $resource = $bagian === 'bimbingan' ? BimbinganSayaResource::class : PendaftaranSayaResource::class;

        $tambahan = [
            'jumlah_bagian' => ['pendaftaran' => $user->pendaftaran()->count(), 'bimbingan' => $user->bimbinganDiajukan()->count()],
            'jumlah_status' => $user->pendaftaran()->selectRaw('status, count(*) as jumlah')->groupBy('status')->pluck('jumlah', 'status'),
        ];

        // Kegiatan yang detailnya dibuka di panel samping (layar lebar): dari ?pilih=magang-3 bila
        // ada di halaman ini dan masih bisa dibuka, selain itu yang pertama. Yang disembunyikan admin dilewati.
        $detail = null;
        if (! $request->wantsJson()) {
            $bisaDibuka = $items->getCollection()->pluck('informasi')
                ->filter(fn ($i) => $i && $i->status === 'tayang')
                ->keyBy(fn ($i) => $i->kategori().'-'.$i->id);
            $terpilih = $bisaDibuka->get((string) $request->query('pilih')) ?? $bisaDibuka->first();
            $detail = $terpilih
                ? app(InformasiController::class)->dataDetail($terpilih->kategori(), ['label' => Informasi::LABEL[$terpilih->kategori()]], $terpilih)
                : null;
        }

        return Daftar::tanggapi(
            $request,
            $resource::collection($items),
            'kegiatan.index',
            ['q' => $f['q'] ?? '', 'bagian' => $bagian, 'status' => $status] + $urutan + ['per_halaman' => $perHalaman],
            tambahan: $tambahan,
            dataView: ['detail' => $detail, 'jumlahStatus' => $tambahan['jumlah_status']],
        );
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

        $nama = 'CV '.preg_replace('/[^A-Za-z0-9 ._-]/', '', $pendaftaran->user->name).'.pdf';

        return response()->download($disk->path($pendaftaran->cv), $nama, [
            'Content-Type' => 'application/pdf',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
