<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminCariRequest;
use App\Http\Requests\AdminSimpanInformasiRequest;
use App\Http\Requests\SimpanInformasiRequest;
use App\Models\Beasiswa;
use App\Models\Lomba;
use App\Models\Magang;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

/** Kelola dan moderasi semua informasi (FA-04 sampai FA-06, FA-09, UC-10). */
class AdminInformasiController extends Controller
{
    private const LABEL = ['magang' => 'Magang', 'lomba' => 'Lomba', 'beasiswa' => 'Beasiswa'];

    public function index(AdminCariRequest $request, string $kategori): View
    {
        $model = $this->model($kategori);
        $f = $request->validated();
        $hariIni = today()->toDateString();

        $items = $model::query()
            ->with('user:id,name,role')   // nama pembuat, tanpa N+1
            ->withCount('pendaftaran')
            ->when($f['q'] ?? null, fn($q, $v) => $q->where(
                fn($q) => $q->whereLike('judul', "%{$v}%")->orWhereLike('penyelenggara', "%{$v}%")
            ))
            ->when($f['tayang'] ?? null, fn($q, $v) => match ($v) {
                'disembunyikan' => $q->where('status', $model::STATUS_DISEMBUNYIKAN),
                'dibuka' => $q->where('status', $model::STATUS_TAYANG)->where('batas_pendaftaran', '>=', $hariIni),
                'ditutup' => $q->where('status', $model::STATUS_TAYANG)->where('batas_pendaftaran', '<', $hariIni),
            })
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->paginate(15)
            ->withQueryString();

        return view('admin.informasi.index', [
            'kategori' => $kategori,
            'label' => self::LABEL[$kategori],
            'daftarKategori' => self::LABEL,
            'items' => $items,
            'filter' => $f,
        ]);
    }

    public function create(string $kategori): View
    {
        Gate::authorize('create', $this->model($kategori));

        return view('admin.informasi.form', $this->dataForm($kategori, null));
    }

    public function store(AdminSimpanInformasiRequest $request, string $kategori): RedirectResponse
    {
        // user_id = admin yang menambahkan. penyelenggara diisi admin di formulir.
        $data = $request->validated();
        $data['cara_daftar'] = $data['cara_daftar'] ?? 'eksternal';
        $request->user()->{$kategori}()->create($data);

        return redirect()->route('admin.informasi.index', $kategori)->with('status', 'Informasi ditambahkan dan langsung tayang.');
    }

    public function edit(string $kategori, string $id): View
    {
        $item = $this->model($kategori)::findOrFail($id);
        Gate::authorize('update', $item);

        return view('admin.informasi.form', $this->dataForm($kategori, $item));
    }

    public function update(AdminSimpanInformasiRequest $request, string $kategori, string $id): RedirectResponse
    {
        $data = $request->validated();
        if (($data['cara_daftar'] ?? null) === null) {
            unset($data['cara_daftar']); // tidak dikirim: cara daftar lama dipertahankan
        }
        $this->model($kategori)::findOrFail($id)->update($data);

        return redirect()->route('admin.informasi.index', $kategori)->with('status', 'Perubahan disimpan.');
    }

    public function destroy(string $kategori, string $id): RedirectResponse
    {
        $item = $this->model($kategori)::findOrFail($id);
        Gate::authorize('delete', $item);
        $item->delete();

        return redirect()->route('admin.informasi.index', $kategori)->with('status', 'Informasi dihapus.');
    }

    public function sembunyikan(string $kategori, string $id): RedirectResponse
    {
        return $this->ubahStatus($kategori, $id, 'disembunyikan', 'Informasi disembunyikan dari pembaca.');
    }

    public function tampilkan(string $kategori, string $id): RedirectResponse
    {
        return $this->ubahStatus($kategori, $id, 'tayang', 'Informasi ditampilkan kembali.');
    }

    private function ubahStatus(string $kategori, string $id, string $status, string $pesan): RedirectResponse
    {
        $item = $this->model($kategori)::findOrFail($id);

        $item->status = $status; // status tidak ada di $fillable: hanya admin yang mengubahnya
        $item->save();

        return back()->with('status', $pesan);
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
