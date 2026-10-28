<?php

namespace App\Http\Controllers;

use App\Http\Requests\CariDaftarRequest;
use App\Http\Requests\CariInformasiRequest;
use App\Http\Requests\SimpanInformasiRequest;
use App\Http\Resources\InformasiMitraResource;
use App\Models\Beasiswa;
use App\Models\Lomba;
use App\Models\Magang;
use App\Support\Daftar;
use App\Support\Urutan;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * Kelola informasi milik mitra (FP-05 sampai FP-09). Satu controller untuk tiga kategori;
 * nama kategori datang dari route dan dibatasi route ke magang|lomba|beasiswa.
 */
class MitraInformasiController extends Controller
{
    private const LABEL = ['magang' => 'Magang', 'lomba' => 'Lomba', 'beasiswa' => 'Beasiswa'];

    public function index(CariDaftarRequest $request, string $kategori): JsonResponse|Response
    {
        $model = $this->model($kategori);
        Gate::authorize('viewAny', $model);
        $f = $request->validated();

        // Hanya milik mitra ini.
        $query = $request->user()->{$kategori}()
            ->withCount(['pendaftaran', 'pendaftaran as menunggu_count' => fn ($q) => $q->where('status', 'menunggu')])
            ->when($f['q'] ?? null, fn ($q, $v) => $q->whereLike('judul', "%{$v}%"));

        // Bawaan: terbaru di atas.
        $perHalaman = Daftar::perHalaman($f['per_halaman'] ?? null, 10);
        $urutan = Urutan::terapkan($query, $f['urut'] ?? null, $f['arah'] ?? null, [
            'dibuat' => 'created_at',
            'batas' => 'batas_pendaftaran',
            'judul' => 'judul',
            'pendaftar' => 'pendaftaran_count',
        ], ['dibuat', 'desc']);

        return Daftar::tanggapi(
            $request,
            InformasiMitraResource::collection($query->paginate($perHalaman)->withQueryString()),
            'mitra.informasi.index',
            ['q' => $f['q'] ?? ''] + $urutan + ['per_halaman' => $perHalaman],
            dataView: ['kategori' => $kategori, 'label' => self::LABEL[$kategori]],
        );
    }

    public function create(string $kategori): View
    {
        Gate::authorize('create', $this->model($kategori));

        return view('mitra.informasi.form', $this->dataForm($kategori, null));
    }

    public function store(SimpanInformasiRequest $request, string $kategori): RedirectResponse
    {
        $user = $request->user();

        // user_id terisi otomatis oleh relasi. penyelenggara diambil dari profil mitra, bukan dari
        // input. status tidak ada di $fillable, jadi selalu "tayang" (default database).
        // Informasi mitra selalu didaftar lewat CampusLink; mitra yang menerima atau menolak pendaftar.
        $user->{$kategori}()->create(
            $request->validated() + ['penyelenggara' => $user->mitra->nama_organisasi, 'cara_daftar' => 'internal']
        );

        return redirect()->route('mitra.informasi.index', $kategori)
            ->with('status', 'Informasi berhasil ditambahkan dan langsung tayang.');
    }

    public function edit(string $kategori, string $id): View
    {
        $model = $this->model($kategori);
        $item = $model::findOrFail($id);
        Gate::authorize('update', $item);

        return view('mitra.informasi.form', $this->dataForm($kategori, $item));
    }

    public function update(SimpanInformasiRequest $request, string $kategori, string $id): RedirectResponse
    {
        // Kepemilikan sudah diperiksa di SimpanInformasiRequest::authorize().
        $item = $this->model($kategori)::findOrFail($id);
        $item->update($request->validated()); // status dan penyelenggara tidak ikut berubah

        return redirect()->route('mitra.informasi.index', $kategori)
            ->with('status', 'Perubahan disimpan.');
    }

    public function destroy(string $kategori, string $id): RedirectResponse
    {
        $item = $this->model($kategori)::findOrFail($id);
        Gate::authorize('delete', $item);

        $item->delete();

        return redirect()->route('mitra.informasi.index', $kategori)
            ->with('status', 'Informasi dihapus.');
    }

    private function model(string $kategori): string
    {
        return SimpanInformasiRequest::MODEL[$kategori] ?? abort(404);
    }

    private function dataForm(string $kategori, Magang|Lomba|Beasiswa|null $item): array
    {
        return [
            'kategori' => $kategori,
            'label' => self::LABEL[$kategori],
            'item' => $item,
            'daftarTingkat' => CariInformasiRequest::TINGKAT_LOMBA,
        ];
    }
}
