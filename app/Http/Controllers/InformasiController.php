<?php

namespace App\Http\Controllers;

use App\Http\Requests\CariInformasiRequest;
use App\Models\Beasiswa;
use App\Models\Lomba;
use App\Models\Magang;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

/**
 * Daftar dan detail informasi magang, lomba, dan beasiswa untuk pembaca.
 * Satu controller melayani ketiga kategori karena perilakunya sama; route memberi nama kategori.
 */
class InformasiController extends Controller
{
    private const KATEGORI = [
        'magang' => [
            'model' => Magang::class,
            'label' => 'Magang',
            'kolom_cari' => ['judul', 'penyelenggara', 'deskripsi', 'persyaratan', 'lokasi'],
        ],
        'lomba' => [
            'model' => Lomba::class,
            'label' => 'Lomba',
            'kolom_cari' => ['judul', 'penyelenggara', 'deskripsi', 'persyaratan'],
        ],
        'beasiswa' => [
            'model' => Beasiswa::class,
            'label' => 'Beasiswa',
            'kolom_cari' => ['judul', 'penyelenggara', 'deskripsi', 'persyaratan'],
        ],
    ];

    public function index(CariInformasiRequest $request, string $kategori): View
    {
        $meta = self::KATEGORI[$kategori] ?? abort(404);
        $model = $meta['model'];

        $kata = trim((string) $request->validated('q', ''));
        $tingkat = $request->validated('tingkat');

        $items = $model::query()
            ->where('status', $model::STATUS_TAYANG)
            // Hanya yang masih dibuka. Yang sudah lewat tetap bisa dibuka lewat tautan langsung.
            ->where('batas_pendaftaran', '>=', today()->toDateString())
            ->when($kata !== '', function ($query) use ($kata, $meta) {
                $query->where(function ($query) use ($kata, $meta) {
                    foreach ($meta['kolom_cari'] as $i => $kolom) {
                        // whereLike tidak membedakan huruf besar/kecil (ILIKE di PostgreSQL).
                        $i === 0 ? $query->whereLike($kolom, "%{$kata}%") : $query->orWhereLike($kolom, "%{$kata}%");
                    }
                });
            })
            ->when($kategori === 'lomba' && $tingkat, fn($query) => $query->where('tingkat', $tingkat))
            ->orderBy('batas_pendaftaran')
            ->orderBy('id')
            ->paginate(10)
            ->withQueryString();

        // Informasi yang dibuka di panel samping (layar lebar): pilihan dari ?pilih bila ada di halaman ini,
        // selain itu yang pertama. Di HP panel tidak tampil; kartu membuka halaman detail.
        $terpilih = $items->firstWhere('id', (int) $request->validated('pilih')) ?? $items->first();

        return view('informasi.index', [
            'kategori' => $kategori,
            'daftarKategori' => array_map(fn($m) => $m['label'], self::KATEGORI),
            'label' => $meta['label'],
            'items' => $items,
            'kata' => $kata,
            'tingkat' => $tingkat,
            'daftarTingkat' => CariInformasiRequest::TINGKAT_LOMBA,
            'detail' => $terpilih ? $this->dataDetail($kategori, $meta, $terpilih) : null,
        ]);
    }

    // Urutan argumen mengikuti urutan parameter route: {id} dari URI lebih dulu, lalu 'kategori' dari defaults().
    public function show(Request $request, string $id, string $kategori): View|Response
    {
        $meta = self::KATEGORI[$kategori] ?? abort(404);
        $model = $meta['model'];

        // Yang disembunyikan admin tidak bisa dibuka. Yang sudah ditutup tetap bisa, dengan penanda.
        $item = $model::where('status', $model::STATUS_TAYANG)->findOrFail($id);

        // Permintaan dari panel samping halaman daftar: kirim isi detailnya saja, tanpa layout.
        if ($request->header('X-Panel') === '1') {
            return response()
                ->view('informasi._detail', $this->dataDetail($kategori, $meta, $item) + ['panel' => true])
                ->header('Vary', 'X-Panel');
        }

        return view('informasi.show', $this->dataDetail($kategori, $meta, $item));
    }

    /**
     * Data yang dibutuhkan tampilan detail (halaman sendiri, panel samping daftar informasi,
     * dan panel di halaman Kegiatan saya).
     */
    public function dataDetail(string $kategori, array $meta, Magang|Lomba|Beasiswa $item): array
    {
        $user = request()->user();
        $mahasiswa = $user?->role === User::ROLE_MAHASISWA;

        return [
            'kategori' => $kategori,
            'label' => $meta['label'],
            'item' => $item,
            'terbuka' => $item->batas_pendaftaran->copy()->endOfDay()->isFuture(),
            // Hanya http/https yang dijadikan tautan, agar "javascript:" tidak pernah menjadi href.
            'tautanAman' => (bool) preg_match('#^https?://#i', (string) $item->tautan_pendaftaran),
            // Khusus mahasiswa: status pendaftaran dan bimbingan miliknya untuk kegiatan ini.
            'pendaftaranSaya' => $mahasiswa ? $item->pendaftaran()->where('user_id', $user->id)->first() : null,
            'bimbinganSaya' => $mahasiswa && $item->bisaBimbingan()
                ? $item->bimbingan()->where('mahasiswa_id', $user->id)->with('dosen')->first() : null,
            'dosenTersedia' => $mahasiswa && $item->bisaBimbingan() ? BimbinganController::dosenTersedia($kategori) : collect(),
            // Bimbingan baru boleh diajukan setelah diterima (kegiatan CampusLink); kegiatan luar butuh bukti.
            'bolehBimbingan' => $mahasiswa && $item->bisaBimbingan() && BimbinganController::syaratDiterima($item, $user),
            'peran' => $user?->role,
        ];
    }
}
