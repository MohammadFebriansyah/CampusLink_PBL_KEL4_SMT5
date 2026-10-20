<?php

namespace Tests\Feature;

use App\Models\Beasiswa;
use App\Models\Lomba;
use App\Models\Magang;
use App\Models\Mitra;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class MitraInformasiTest extends TestCase
{
    use RefreshDatabase;

    private const MODEL = ['magang' => Magang::class, 'lomba' => Lomba::class, 'beasiswa' => Beasiswa::class];

    protected function setUp(): void
    {
        // RefreshDatabase menghapus seluruh tabel. Jangan sampai terjadi di Supabase.
        if (($_ENV['DB_CONNECTION'] ?? getenv('DB_CONNECTION')) !== 'sqlite') {
            $this->markTestSkipped('Uji ini hanya berjalan dengan SQLite. Atur DB_CONNECTION=sqlite dan DB_DATABASE=:memory: di phpunit.xml.');
        }

        parent::setUp();

        $this->withoutVite();
    }

    // ---------- Pembantu ----------

    private function buatMitra(string $email = 'a@mitra.example', string $organisasi = 'PT Alpha', string $status = Mitra::STATUS_DISETUJUI): User
    {
        $user = User::factory()->create(['role' => User::ROLE_MITRA, 'email' => $email]);
        $mitra = $user->mitra()->create(['nama_organisasi' => $organisasi, 'kontak' => '0341-000']);
        $mitra->status_verifikasi = $status;
        $mitra->save();

        return $user;
    }

    private function payload(string $kategori, array $ubah = []): array
    {
        $umum = [
            'judul' => 'Judul Uji',
            'deskripsi' => 'Deskripsi uji.',
            'batas_pendaftaran' => now()->addDays(10)->toDateString(),
            'kontak' => 'kontak@contoh.example',
            'tautan_pendaftaran' => 'https://contoh.example/daftar',
        ];

        $khusus = match ($kategori) {
            'magang' => [
                'persyaratan' => 'Menguasai PHP.', 'lokasi' => 'Malang',
                'periode_mulai' => now()->addMonths(2)->toDateString(),
                'periode_selesai' => now()->addMonths(4)->toDateString(),
            ],
            'lomba' => [
                'persyaratan' => '', 'tingkat' => 'nasional',
                'tanggal_mulai' => now()->addDays(30)->toDateString(),
                'tanggal_selesai' => now()->addDays(31)->toDateString(),
            ],
            'beasiswa' => ['persyaratan' => 'IPK minimal 3,5.'],
        };

        return array_merge($umum, $khusus, $ubah);
    }

    /** Membuat informasi langsung di database untuk pemilik tertentu. */
    private function buatItem(User $pemilik, string $kategori, array $ubah = [])
    {
        $status = $ubah['status'] ?? null;
        unset($ubah['status']);

        $data = $this->payload($kategori, ['persyaratan' => 'Syarat uji.'] + $ubah) + ['penyelenggara' => 'PT Alpha'];
        $item = $pemilik->{$kategori}()->create($data);

        if ($status) {
            $item->status = $status;
            $item->save();
        }

        return $item;
    }

    public static function kategori(): array
    {
        return ['magang' => ['magang'], 'lomba' => ['lomba'], 'beasiswa' => ['beasiswa']];
    }

    // ---------- Akses ----------

    public function test_tamu_dan_peran_lain_tidak_bisa_masuk_panel_mitra(): void
    {
        foreach (['/mitra', '/mitra/magang', '/mitra/magang/buat'] as $url) {
            $this->get($url)->assertRedirect(route('login'));
        }

        foreach ([User::ROLE_MAHASISWA, User::ROLE_DOSEN, User::ROLE_ADMIN] as $peran) {
            $user = User::factory()->create(['role' => $peran, 'email' => "$peran@polinema.ac.id"]);

            foreach (['/mitra', '/mitra/magang', '/mitra/magang/buat'] as $url) {
                $this->actingAs($user)->get($url)->assertForbidden();
            }
        }
    }

    public function test_mitra_yang_belum_diverifikasi_tidak_bisa_masuk_panel(): void
    {
        $pending = $this->buatMitra('p@mitra.example', 'PT Pending', Mitra::STATUS_PENDING);

        $this->actingAs($pending)->get('/mitra/magang')->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_kategori_tidak_dikenal_menghasilkan_404(): void
    {
        $mitra = $this->buatMitra();

        $this->actingAs($mitra)->get('/mitra/podcast')->assertNotFound();
        $this->actingAs($mitra)->get('/mitra/podcast/buat')->assertNotFound();
        $this->actingAs($mitra)->get('/mitra/magang/abc/ubah')->assertNotFound();
    }

    // ---------- Dasbor dan daftar ----------

    public function test_dasbor_menampilkan_ringkasan_milik_sendiri(): void
    {
        $a = $this->buatMitra();
        $b = $this->buatMitra('b@mitra.example', 'PT Beta');

        $this->buatItem($a, 'magang');                                                                // dibuka
        $this->buatItem($a, 'magang', ['batas_pendaftaran' => now()->subDays(2)->toDateString()]);   // ditutup
        $this->buatItem($a, 'lomba', ['status' => Lomba::STATUS_DISEMBUNYIKAN]);                     // disembunyikan
        $this->buatItem($b, 'magang');                                                                // milik mitra lain

        $this->actingAs($a)->get(route('mitra.dashboard'))
            ->assertOk()
            ->assertSee('PT Alpha')
            ->assertViewHas('ringkasan', function (array $r) {
                return $r['magang'] == ['label' => 'Magang', 'total' => 2, 'dibuka' => 1, 'disembunyikan' => 0, 'ditutup' => 1]
                    && $r['lomba']['total'] === 1 && $r['lomba']['disembunyikan'] === 1 && $r['lomba']['dibuka'] === 0
                    && $r['beasiswa']['total'] === 0;
            });
    }

    public function test_daftar_hanya_memuat_milik_sendiri_dan_terbaru_di_atas(): void
    {
        $a = $this->buatMitra();
        $b = $this->buatMitra('b@mitra.example', 'PT Beta');

        $this->buatItem($a, 'magang', ['judul' => 'Magang Lama A']);
        $this->buatItem($a, 'magang', ['judul' => 'Magang Baru A']);
        $this->buatItem($b, 'magang', ['judul' => 'Magang Milik B']);

        $this->actingAs($a)->get(route('mitra.informasi.index', 'magang'))
            ->assertOk()
            ->assertSeeInOrder(['Magang Baru A', 'Magang Lama A'])
            ->assertDontSee('Magang Milik B');
    }

    public function test_daftar_menandai_status_dan_menyembunyikan_tautan_lihat_untuk_yang_disembunyikan(): void
    {
        $a = $this->buatMitra();
        $this->buatItem($a, 'magang', ['judul' => 'Sudah Lewat', 'batas_pendaftaran' => now()->subDay()->toDateString()]);
        $tersembunyi = $this->buatItem($a, 'magang', ['judul' => 'Kena Moderasi', 'status' => Magang::STATUS_DISEMBUNYIKAN]);

        $this->actingAs($a)->get(route('mitra.informasi.index', 'magang'))
            ->assertSee('Ditutup')
            ->assertSee('Disembunyikan admin')
            ->assertDontSee('href="' . route('magang.show', $tersembunyi) . '"', false);
    }

    public function test_keadaan_kosong_mengajak_menambah(): void
    {
        $this->actingAs($this->buatMitra())->get(route('mitra.informasi.index', 'beasiswa'))
            ->assertOk()->assertSee('Belum ada informasi beasiswa')->assertSee('Tambah beasiswa pertama');
    }

    // ---------- Tambah ----------

    #[DataProvider('kategori')]
    public function test_mitra_menambah_informasi_dan_penyelenggara_diambil_dari_profil(string $kategori): void
    {
        $a = $this->buatMitra();

        $this->actingAs($a)->post(route('mitra.informasi.store', $kategori), $this->payload($kategori))
            ->assertRedirect(route('mitra.informasi.index', $kategori))
            ->assertSessionHas('status');

        $this->assertDatabaseHas($kategori, [
            'user_id' => $a->id,
            'judul' => 'Judul Uji',
            'penyelenggara' => 'PT Alpha',
            'status' => 'tayang',
        ]);
    }

    #[DataProvider('kategori')]
    public function test_kiriman_yang_tidak_boleh_diatur_pengguna_diabaikan(string $kategori): void
    {
        $a = $this->buatMitra();
        $b = $this->buatMitra('b@mitra.example', 'PT Beta');

        $this->actingAs($a)->post(route('mitra.informasi.store', $kategori), $this->payload($kategori, [
            'user_id' => $b->id,
            'penyelenggara' => 'Penyelenggara Palsu',
            'status' => 'disembunyikan',
            'id' => 9999,
        ]));

        $this->assertDatabaseHas($kategori, ['user_id' => $a->id, 'penyelenggara' => 'PT Alpha', 'status' => 'tayang']);
        $this->assertDatabaseMissing($kategori, ['penyelenggara' => 'Penyelenggara Palsu']);
        $this->assertDatabaseMissing($kategori, ['user_id' => $b->id]);
    }

    public static function masukanTidakValid(): array
    {
        return [
            'batas pendaftaran sudah lewat' => ['magang', ['batas_pendaftaran' => '2000-01-01'], 'batas_pendaftaran'],
            'periode selesai sebelum mulai' => ['magang', ['periode_mulai' => '2030-05-01', 'periode_selesai' => '2030-04-01'], 'periode_selesai'],
            'tautan javascript' => ['magang', ['tautan_pendaftaran' => 'javascript:alert(1)'], 'tautan_pendaftaran'],
            'tautan ftp' => ['beasiswa', ['tautan_pendaftaran' => 'ftp://contoh.example/berkas'], 'tautan_pendaftaran'],
            'tautan bukan url' => ['lomba', ['tautan_pendaftaran' => 'bukan url'], 'tautan_pendaftaran'],
            'judul kosong' => ['magang', ['judul' => ''], 'judul'],
            'persyaratan magang kosong' => ['magang', ['persyaratan' => ''], 'persyaratan'],
            'persyaratan beasiswa kosong' => ['beasiswa', ['persyaratan' => ''], 'persyaratan'],
            'tingkat lomba tidak dikenal' => ['lomba', ['tingkat' => 'galaksi'], 'tingkat'],
            'tanggal selesai lomba sebelum mulai' => ['lomba', ['tanggal_mulai' => '2030-05-02', 'tanggal_selesai' => '2030-05-01'], 'tanggal_selesai'],
            'deskripsi terlalu panjang' => ['beasiswa', ['deskripsi' => 'x'], 'deskripsi'], // diganti di uji
            'kontak kosong' => ['lomba', ['kontak' => ''], 'kontak'],
        ];
    }

    #[DataProvider('masukanTidakValid')]
    public function test_masukan_tidak_valid_ditolak_dan_tidak_menyimpan_apa_pun(string $kategori, array $ubah, string $kolom): void
    {
        if ($kolom === 'deskripsi') {
            $ubah['deskripsi'] = str_repeat('x', 5001);
        }

        $this->actingAs($this->buatMitra())
            ->post(route('mitra.informasi.store', $kategori), $this->payload($kategori, $ubah))
            ->assertSessionHasErrors($kolom);

        $this->assertDatabaseCount($kategori, 0);
    }

    public function test_persyaratan_lomba_boleh_kosong(): void
    {
        $this->actingAs($this->buatMitra())
            ->post(route('mitra.informasi.store', 'lomba'), $this->payload('lomba', ['persyaratan' => '']))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseCount('lomba', 1);
    }

    public function test_informasi_baru_langsung_terlihat_oleh_mahasiswa(): void
    {
        $this->actingAs($this->buatMitra())
            ->post(route('mitra.informasi.store', 'magang'), $this->payload('magang', ['judul' => 'Magang Baru Tayang']));

        $mahasiswa = User::factory()->create(['role' => User::ROLE_MAHASISWA, 'email' => 'm@polinema.ac.id']);

        $this->actingAs($mahasiswa)->get(route('magang.index'))->assertSee('Magang Baru Tayang');
    }

    // ---------- Ubah ----------

    #[DataProvider('kategori')]
    public function test_mitra_mengubah_miliknya_sendiri(string $kategori): void
    {
        $a = $this->buatMitra();
        $item = $this->buatItem($a, $kategori);

        $this->actingAs($a)->put(route('mitra.informasi.update', [$kategori, $item->id]), $this->payload($kategori, [
            'judul' => 'Judul Diubah',
            'persyaratan' => 'Syarat baru.',
        ]))->assertRedirect(route('mitra.informasi.index', $kategori));

        $this->assertDatabaseHas($kategori, ['id' => $item->id, 'judul' => 'Judul Diubah', 'penyelenggara' => 'PT Alpha']);
    }

    public function test_mengubah_tidak_membuka_kembali_informasi_yang_disembunyikan_admin(): void
    {
        $a = $this->buatMitra();
        $item = $this->buatItem($a, 'magang', ['status' => Magang::STATUS_DISEMBUNYIKAN]);

        $this->actingAs($a)->put(route('mitra.informasi.update', ['magang', $item->id]), $this->payload('magang', [
            'status' => 'tayang',
        ]));

        $this->assertDatabaseHas('magang', ['id' => $item->id, 'status' => 'disembunyikan']);
    }

    public function test_menutup_pendaftaran_dengan_mengubah_batas_ke_tanggal_lampau_diperbolehkan(): void
    {
        $a = $this->buatMitra();
        $item = $this->buatItem($a, 'magang');
        $kemarin = now()->subDay()->toDateString();

        $this->actingAs($a)->put(route('mitra.informasi.update', ['magang', $item->id]), $this->payload('magang', [
            'batas_pendaftaran' => $kemarin,
        ]))->assertSessionHasNoErrors();

        $this->assertSame($kemarin, $item->fresh()->batas_pendaftaran->toDateString());
    }

    public function test_formulir_tambah_dan_ubah_tampil(): void
    {
        $a = $this->buatMitra();
        $item = $this->buatItem($a, 'magang', ['judul' => 'Magang Untuk Diubah']);

        $this->actingAs($a)->get(route('mitra.informasi.create', 'lomba'))
            ->assertOk()->assertSee('Tingkat')->assertSee('PT Alpha')->assertSee('Tayangkan');

        $this->actingAs($a)->get(route('mitra.informasi.edit', ['magang', $item->id]))
            ->assertOk()->assertSee('Magang Untuk Diubah')->assertSee('Simpan perubahan')->assertSee('permanen');
    }

    // ---------- Hapus ----------

    #[DataProvider('kategori')]
    public function test_mitra_menghapus_miliknya_sendiri(string $kategori): void
    {
        $a = $this->buatMitra();
        $item = $this->buatItem($a, $kategori);
        $lain = $this->buatItem($a, $kategori, ['judul' => 'Tetap Ada']);

        $this->actingAs($a)->delete(route('mitra.informasi.destroy', [$kategori, $item->id]))
            ->assertRedirect(route('mitra.informasi.index', $kategori))
            ->assertSessionHas('status');

        $this->assertDatabaseMissing($kategori, ['id' => $item->id]);
        $this->assertDatabaseHas($kategori, ['id' => $lain->id]); // yang lain tidak ikut terhapus
    }

    public function test_menghapus_informasi_yang_tidak_ada_menghasilkan_404(): void
    {
        $this->actingAs($this->buatMitra())->delete('/mitra/magang/99999')->assertNotFound();
    }

    // ---------- Hak akses antar mitra (NF-08) ----------

    #[DataProvider('kategori')]
    public function test_mitra_tidak_bisa_mengubah_atau_menghapus_milik_mitra_lain(string $kategori): void
    {
        $a = $this->buatMitra();
        $b = $this->buatMitra('b@mitra.example', 'PT Beta');
        $milikA = $this->buatItem($a, $kategori, ['judul' => 'Milik A']);

        $this->actingAs($b)->get(route('mitra.informasi.edit', [$kategori, $milikA->id]))->assertForbidden();

        $this->actingAs($b)->put(route('mitra.informasi.update', [$kategori, $milikA->id]), $this->payload($kategori, ['judul' => 'Dibajak']))
            ->assertForbidden();

        $this->actingAs($b)->delete(route('mitra.informasi.destroy', [$kategori, $milikA->id]))->assertForbidden();

        $this->assertDatabaseHas($kategori, ['id' => $milikA->id, 'judul' => 'Milik A']);
    }

    public function test_mitra_lain_tidak_dibocorkan_validasi_saat_mencoba_mengubah_milik_orang(): void
    {
        $a = $this->buatMitra();
        $b = $this->buatMitra('b@mitra.example', 'PT Beta');
        $milikA = $this->buatItem($a, 'magang');

        // Data tidak valid sekalipun, jawabannya tetap 403 (bukan 302 berisi galat validasi).
        $this->actingAs($b)->put(route('mitra.informasi.update', ['magang', $milikA->id]), ['judul' => ''])->assertForbidden();
    }

    public function test_aturan_policy(): void
    {
        $a = $this->buatMitra();
        $b = $this->buatMitra('b@mitra.example', 'PT Beta');
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN, 'email' => 'admin@campuslink.test']);
        $mahasiswa = User::factory()->create(['role' => User::ROLE_MAHASISWA, 'email' => 'm@polinema.ac.id']);

        foreach (self::MODEL as $kategori => $model) {
            $milikA = $this->buatItem($a, $kategori);

            $this->assertTrue(Gate::forUser($a)->allows('update', $milikA), "$kategori: pemilik boleh mengubah");
            $this->assertTrue(Gate::forUser($a)->allows('delete', $milikA), "$kategori: pemilik boleh menghapus");
            $this->assertFalse(Gate::forUser($b)->allows('update', $milikA), "$kategori: mitra lain tidak boleh mengubah");
            $this->assertFalse(Gate::forUser($b)->allows('delete', $milikA), "$kategori: mitra lain tidak boleh menghapus");
            $this->assertTrue(Gate::forUser($admin)->allows('update', $milikA), "$kategori: admin boleh");
            $this->assertFalse(Gate::forUser($mahasiswa)->allows('update', $milikA), "$kategori: mahasiswa tidak boleh");

            $this->assertTrue(Gate::forUser($a)->allows('create', $model));
            $this->assertFalse(Gate::forUser($mahasiswa)->allows('create', $model));
        }
    }
}
