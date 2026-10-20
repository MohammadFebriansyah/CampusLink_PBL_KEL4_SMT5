<?php

namespace App\Http\Controllers;

use App\Http\Requests\SimpanInformasiRequest;
use App\Models\Beasiswa;
use App\Models\Lomba;
use App\Models\Magang;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/**
 * Kelola informasi milik mitra (FP-05 sampai FP-09). Satu controller untuk tiga kategori;
 * nama kategori datang dari route dan dibatasi route ke magang|lomba|beasiswa.
 */
class MitraInformasiController extends Controller
{
    private const LABEL = ['magang' => 'Magang', 'lomba' => 'Lomba', 'beasiswa' => 'Beasiswa'];

    public function index(Request $request, string $kategori): View
    {
        $model = $this->model($kategori);
        Gate::authorize('viewAny', $model);

        // Hanya milik mitra ini. Terbaru di atas.
        $items = $request->user()->{$kategori}()
            ->withCount(['pendaftaran', 'pendaftaran as menunggu_count' => fn($q) => $q->where('status', 'menunggu')])
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(10);

        return view('mitra.informasi.index', [
            'kategori' => $kategori,
            'label' => self::LABEL[$kategori],
            'daftarKategori' => self::LABEL,
            'items' => $items,
        ]);
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
            'daftarTingkat' => \App\Http\Requests\CariInformasiRequest::TINGKAT_LOMBA,
        ];
    }
}
