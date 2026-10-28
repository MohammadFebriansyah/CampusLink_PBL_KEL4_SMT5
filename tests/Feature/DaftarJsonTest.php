<?php

namespace Tests\Feature;

use App\Models\Bimbingan;
use App\Models\Keahlian;
use App\Models\Mitra;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Daftar yang dirender Alpine: URL halaman yang sama mengembalikan JSON bila diminta
 * ("Accept: application/json"), dengan pencarian, pengurutan, dan paginasi dari server.
 */
class DaftarJsonTest extends TestCase
{
    use RefreshDatabase;

    private int $urutan = 0;

    protected function setUp(): void
    {
        // RefreshDatabase menghapus seluruh tabel. Jangan sampai terjadi di Supabase.
        if (($_ENV['DB_CONNECTION'] ?? getenv('DB_CONNECTION')) !== 'sqlite') {
            $this->markTestSkipped('Uji ini hanya berjalan dengan SQLite. Atur DB_CONNECTION=sqlite dan DB_DATABASE=:memory: di phpunit.xml.');
        }

        parent::setUp();
        $this->withoutVite();
        Storage::fake('local');
    }

    // ---------- Pembantu ----------

    private function pengguna(string $peran, array $ubah = []): User
    {
        $n = ++$this->urutan;
        $domain = $peran === User::ROLE_MITRA ? 'mitra.example' : 'polinema.ac.id';

        return User::factory()->create(array_merge(['role' => $peran, 'email' => "{$peran}{$n}@{$domain}", 'name' => ucfirst($peran)." Nomor {$n}"], $ubah));
    }

    private function mahasiswa(array $ubah = []): User
    {
        $u = $this->pengguna(User::ROLE_MAHASISWA, $ubah);
        $u->mahasiswa()->create([
            'nim' => 'NIM'.$u->id, 'kelas' => 'SIB-3A', 'jurusan' => 'Teknologi Informasi',
            'program_studi' => 'Sistem Informasi Bisnis', 'angkatan' => 2024, 'tampil_di_direktori' => true,
        ]);

        return $u;
    }

    private function mitra(): User
    {
        $u = $this->pengguna(User::ROLE_MITRA);
        $m = $u->mitra()->create(['nama_organisasi' => 'PT Uji '.$u->id, 'kontak' => '0341']);
        $m->status_verifikasi = Mitra::STATUS_DISETUJUI;
        $m->save();

        return $u;
    }

    private function magang(User $pemilik, string $judul, int $hari = 10)
    {
        return $pemilik->magang()->create([
            'judul' => $judul, 'penyelenggara' => 'PT Uji', 'deskripsi' => 'Desk.', 'persyaratan' => 'Syarat.',
            'periode_mulai' => now()->addMonth()->toDateString(), 'periode_selesai' => now()->addMonths(2)->toDateString(),
            'batas_pendaftaran' => now()->addDays($hari)->toDateString(), 'kontak' => 'k@uji.example', 'cara_daftar' => 'internal',
        ]);
    }

    private function namaDi($respons): array
    {
        return array_column($respons->json('data'), 'nama');
    }

    // ---------- Bentuk respons ----------

    public function test_url_yang_sama_mengembalikan_json_bila_diminta(): void
    {
        $admin = $this->pengguna(User::ROLE_ADMIN);
        $this->mahasiswa(['name' => 'Alya Putri']);

        $this->actingAs($admin)->getJson(route('admin.pengguna.index'))
            ->assertOk()
            ->assertHeader('Vary', 'Accept')
            ->assertJsonStructure([
                'data' => [['id', 'nama', 'email', 'inisial', 'peran' => ['kunci', 'label', 'varian'], 'aktif', 'url']],
                'meta' => ['current_page', 'last_page', 'total', 'per_page'],
                'filter' => ['q', 'peran', 'akun', 'urut', 'arah'],
                'tambahan',
            ])
            ->assertJsonPath('data.0.nama', 'Alya Putri')
            ->assertJsonPath('filter.urut', 'nama');
    }

    public function test_kunjungan_biasa_memuat_data_awal_di_halaman(): void
    {
        $admin = $this->pengguna(User::ROLE_ADMIN);
        $this->mahasiswa(['name' => 'Alya Putri']);

        $this->actingAs($admin)->get(route('admin.pengguna.index'))
            ->assertOk()
            ->assertHeader('Vary', 'Accept')
            ->assertSee('<script type="application/json" id="data-pengguna">', false)
            ->assertSee('"nama":"Alya Putri"', false);
    }

    public function test_data_awal_tidak_bisa_menutup_tag_script(): void
    {
        $admin = $this->pengguna(User::ROLE_ADMIN);
        $this->mahasiswa(['name' => '</script><script>alert(1)</script>']);

        $this->actingAs($admin)->get(route('admin.pengguna.index'))
            ->assertOk()
            ->assertDontSee('</script><script>alert(1)', false);
    }

    // ---------- Pengurutan ----------

    public function test_pengurutan_mengikuti_kunci_dan_arah(): void
    {
        $admin = $this->pengguna(User::ROLE_ADMIN);
        foreach (['Bima', 'Alya', 'Citra'] as $nama) {
            $this->mahasiswa(['name' => $nama]);
        }

        $this->assertSame(['Alya', 'Bima', 'Citra'], $this->namaDi($this->actingAs($admin)->getJson(route('admin.pengguna.index'))));
        $this->assertSame(['Citra', 'Bima', 'Alya'], $this->namaDi($this->actingAs($admin)->getJson(route('admin.pengguna.index', ['urut' => 'nama', 'arah' => 'desc']))));
    }

    public function test_kunci_urut_yang_tidak_dikenal_memakai_urutan_bawaan(): void
    {
        $admin = $this->pengguna(User::ROLE_ADMIN);

        // Nama kolom tidak pernah diambil langsung dari masukan.
        $this->actingAs($admin)->getJson(route('admin.pengguna.index', ['urut' => 'password', 'arah' => 'desc']))
            ->assertOk()
            ->assertJsonPath('filter.urut', 'nama')
            ->assertJsonPath('filter.arah', 'asc');
    }

    public function test_masukan_tidak_valid_dibalas_422_json(): void
    {
        $admin = $this->pengguna(User::ROLE_ADMIN);

        $this->actingAs($admin)->getJson(route('admin.pengguna.index', ['q' => str_repeat('a', 101)]))
            ->assertUnprocessable()->assertJsonValidationErrors('q');
        $this->actingAs($admin)->getJson(route('admin.pengguna.index', ['arah' => 'samping']))
            ->assertUnprocessable()->assertJsonValidationErrors('arah');
    }

    public function test_informasi_publik_bisa_diurutkan_dan_dicari(): void
    {
        $mitra = $this->mitra();
        $this->magang($mitra, 'Magang Jauh', 30);
        $this->magang($mitra, 'Magang Dekat', 2);
        $pembaca = $this->mahasiswa();

        $judul = fn ($r) => array_column($r->json('data'), 'judul');

        $this->assertSame(['Magang Dekat', 'Magang Jauh'], $judul($this->actingAs($pembaca)->getJson(route('magang.index'))));
        $this->assertSame(['Magang Jauh', 'Magang Dekat'], $judul($this->actingAs($pembaca)->getJson(route('magang.index', ['urut' => 'batas', 'arah' => 'desc']))));
        $this->assertSame(['Magang Jauh'], $judul($this->actingAs($pembaca)->getJson(route('magang.index', ['q' => 'jauh']))));
    }

    public function test_paginasi_json(): void
    {
        $admin = $this->pengguna(User::ROLE_ADMIN);
        foreach (range(1, 22) as $i) {
            Keahlian::create(['nama' => sprintf('Keahlian %02d', $i)]);
        }

        $this->actingAs($admin)->getJson(route('admin.keahlian.index', ['page' => 2]))
            ->assertOk()
            ->assertJsonPath('meta.current_page', 2)
            ->assertJsonPath('meta.last_page', 2)
            ->assertJsonPath('meta.total', 22)
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.nama', 'Keahlian 21');
    }

    public function test_jumlah_per_halaman_bisa_dipilih(): void
    {
        $admin = $this->pengguna(User::ROLE_ADMIN);
        foreach (range(1, 55) as $i) {
            Keahlian::create(['nama' => sprintf('Keahlian %02d', $i)]);
        }
        $ambil = fn (array $query) => $this->actingAs($admin)->getJson(route('admin.keahlian.index', $query));

        $ambil([])->assertJsonPath('meta.per_page', 20)->assertJsonPath('filter.per_halaman', 20);
        $ambil(['per_halaman' => 10])->assertJsonCount(10, 'data')->assertJsonPath('meta.last_page', 6);
        $ambil(['per_halaman' => 50])->assertJsonCount(50, 'data')->assertJsonPath('meta.last_page', 2);

        // Nilai di luar 10/20/50 diabaikan (bawaan daftar dipakai), tidak ditolak dan tidak bisa meminta semua data sekaligus.
        $ambil(['per_halaman' => 5000])->assertOk()->assertJsonPath('meta.per_page', 20);
        $ambil(['per_halaman' => 'semua'])->assertUnprocessable()->assertJsonValidationErrors('per_halaman');
    }

    public function test_pilihan_jumlah_per_halaman_tampil_di_halaman(): void
    {
        $admin = $this->pengguna(User::ROLE_ADMIN);

        $this->actingAs($admin)->get(route('admin.pengguna.index'))
            ->assertSee('Per halaman')
            ->assertSee('<option value="15" selected>15</option>', false)
            ->assertSee('<option value="50" >50</option>', false);
    }

    // ---------- Semua daftar ----------

    public function test_setiap_daftar_melayani_json_untuk_perannya(): void
    {
        $admin = $this->pengguna(User::ROLE_ADMIN);
        $mitra = $this->mitra();
        $mhs = $this->mahasiswa();
        $dosen = $this->pengguna(User::ROLE_DOSEN);
        $dosen->dosen()->create(['nidn' => 'N1', 'jurusan' => 'Teknologi Informasi', 'program_studi' => 'Teknik Informatika', 'tampil_di_direktori' => true]);

        $item = $this->magang($mitra, 'Magang Bersama');
        $this->actingAs($mhs)->post(route('pendaftaran.store', ['magang', $item->id]), ['cv' => UploadedFile::fake()->create('cv.pdf', 10, 'application/pdf')]);

        $kasus = [
            [$admin, route('admin.pengguna.index')],
            [$admin, route('admin.informasi.index', 'magang')],
            [$admin, route('admin.mitra.index', ['verifikasi' => 'disetujui'])],
            [$admin, route('admin.keahlian.index')],
            [$admin, route('admin.pendaftar.index')],
            [$mitra, route('mitra.pendaftar.index')],
            [$mitra, route('mitra.informasi.index', 'magang')],
            [$mhs, route('magang.index')],
            [$mhs, route('direktori.index')],
            [$mhs, route('kegiatan.index')],
            [$mhs, route('kegiatan.index', ['bagian' => 'bimbingan'])],
            [$dosen, route('bimbingan.index')],
        ];

        foreach ($kasus as [$user, $url]) {
            $this->actingAs($user)->getJson($url)->assertOk()->assertJsonStructure(['data', 'meta' => ['total'], 'filter']);
        }

        $this->actingAs($mitra)->getJson(route('mitra.pendaftar.index'))->assertJsonPath('data.0.nama', $mhs->name);
        $this->actingAs($mhs)->getJson(route('kegiatan.index'))
            ->assertJsonPath('data.0.judul', 'Magang Bersama')
            ->assertJsonPath('tambahan.jumlah_status.menunggu', 1);
    }

    public function test_json_tetap_dibatasi_peran(): void
    {
        // Tamu dulu: actingAs() berlaku untuk permintaan berikutnya dalam satu uji.
        $this->getJson(route('magang.index'))->assertUnauthorized();

        $mhs = $this->mahasiswa();
        $this->actingAs($mhs)->getJson(route('admin.pengguna.index'))->assertForbidden();
        $this->actingAs($mhs)->getJson(route('bimbingan.index'))->assertForbidden();
    }

    public function test_pencarian_bimbingan_menurut_nama_mahasiswa(): void
    {
        $dosen = $this->pengguna(User::ROLE_DOSEN);
        $dosen->dosen()->create(['nidn' => 'N2', 'jurusan' => 'Teknologi Informasi', 'program_studi' => 'Teknik Informatika']);
        $mitra = $this->mitra();

        foreach (['Alya Putri', 'Bima Aditya'] as $nama) {
            $b = new Bimbingan(['pesan' => null]);
            $b->mahasiswa_id = $this->mahasiswa(['name' => $nama])->id;
            $b->dosen_id = $dosen->id;
            $b->informasi()->associate($this->magang($mitra, 'Magang '.$nama));
            $b->save();
        }

        $this->actingAs($dosen)->getJson(route('bimbingan.index', ['q' => 'bima']))
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.mahasiswa.nama', 'Bima Aditya')
            ->assertJsonPath('tambahan.jumlah.menunggu', 2);
    }
}
