<?php

namespace Tests\Feature;

use App\Models\RiwayatProfil;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RiwayatProfilTest extends TestCase
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
    }

    // ---------- Pembantu ----------

    private function buatMahasiswa(array $profil = [], ?string $nama = null): User
    {
        $n = ++$this->urutan;
        $u = User::factory()->create(['role' => User::ROLE_MAHASISWA, 'email' => "mhs{$n}@polinema.ac.id", 'name' => $nama ?? "Mahasiswa Nomor {$n}"]);
        $u->mahasiswa()->create(array_merge([
            'nim' => "NIM{$n}", 'kelas' => 'SIB-3A', 'jurusan' => 'Teknologi Informasi',
            'program_studi' => 'Sistem Informasi Bisnis', 'angkatan' => 2024, 'tampil_di_direktori' => true,
        ], $profil));

        return $u;
    }

    private function entri(User $user, string $jenis, array $data = []): RiwayatProfil
    {
        $r = $user->riwayat()->make(array_merge(['judul' => ucfirst($jenis) . ' ' . (++$this->urutan)], $data));
        $r->jenis = $jenis;
        $r->save();

        return $r;
    }

    private function dataValid(array $ubah = []): array
    {
        return array_merge([
            'jenis' => 'sertifikasi',
            'judul' => 'AWS Cloud Practitioner',
            'penerbit' => 'Amazon Web Services',
            'tanggal_mulai' => '2025-03-01',
            'tanggal_selesai' => '2028-03-01',
            'deskripsi' => 'Dasar layanan AWS.',
            'tautan' => 'https://www.credly.com/badges/contoh',
        ], $ubah);
    }

    // ---------- Tambah, ubah, hapus milik sendiri ----------

    public function test_mahasiswa_menambah_entri_milik_sendiri(): void
    {
        $mhs = $this->buatMahasiswa();

        $this->actingAs($mhs)->post(route('profil.riwayat.store'), $this->dataValid())
            ->assertRedirect(route('profil.show') . '#riwayat-sertifikasi')
            ->assertSessionHas('status', 'Sertifikasi ditambahkan.');

        $r = $mhs->riwayat()->sole();
        $this->assertSame('sertifikasi', $r->jenis);
        $this->assertSame('AWS Cloud Practitioner', $r->judul);
        $this->assertSame('2025-03-01', $r->tanggal_mulai->format('Y-m-d'));
        $this->assertSame('https://www.credly.com/badges/contoh', $r->tautan);
    }

    public function test_tambah_lewat_fetch_membalas_html_bagian_yang_diperbarui(): void
    {
        $mhs = $this->buatMahasiswa();

        $this->actingAs($mhs)->postJson(route('profil.riwayat.store'), $this->dataValid(['jenis' => 'proyek', 'judul' => 'Sistem Inventaris']))
            ->assertOk()
            ->assertJsonPath('html', fn (string $html) => str_contains($html, 'id="riwayat-proyek"')
                && str_contains($html, 'Sistem Inventaris') && str_contains($html, 'Proyek ditambahkan.'));
    }

    public function test_user_id_tidak_bisa_dititipkan_lewat_masukan(): void
    {
        $mhs = $this->buatMahasiswa();
        $lain = $this->buatMahasiswa();

        $this->actingAs($mhs)->post(route('profil.riwayat.store'), $this->dataValid(['user_id' => $lain->id]));

        $this->assertSame(1, $mhs->riwayat()->count());
        $this->assertSame(0, $lain->riwayat()->count());
    }

    public function test_mahasiswa_mengubah_entri_milik_sendiri_tanpa_mengubah_jenis(): void
    {
        $mhs = $this->buatMahasiswa();
        $r = $this->entri($mhs, 'pelatihan', ['judul' => 'Judul lama']);

        $this->actingAs($mhs)->put(route('profil.riwayat.update', $r->id), [
            'jenis' => 'pencapaian', // diabaikan: jenis tidak bisa diubah
            'judul' => 'Bootcamp Laravel',
            'penerbit' => 'Dicoding',
        ])->assertRedirect(route('profil.show') . '#riwayat-pelatihan');

        $r->refresh();
        $this->assertSame('Bootcamp Laravel', $r->judul);
        $this->assertSame('Dicoding', $r->penerbit);
        $this->assertSame('pelatihan', $r->jenis);
    }

    public function test_mahasiswa_menghapus_entri_milik_sendiri(): void
    {
        $mhs = $this->buatMahasiswa();
        $r = $this->entri($mhs, 'proyek');

        $this->actingAs($mhs)->delete(route('profil.riwayat.destroy', $r->id))
            ->assertRedirect(route('profil.show') . '#riwayat-proyek');

        $this->assertModelMissing($r);
    }

    // ---------- Kepemilikan dan peran ----------

    public function test_tidak_bisa_mengubah_atau_menghapus_milik_orang_lain(): void
    {
        $pemilik = $this->buatMahasiswa();
        $penyusup = $this->buatMahasiswa();
        $r = $this->entri($pemilik, 'sertifikasi', ['judul' => 'Asli']);

        $this->actingAs($penyusup)->put(route('profil.riwayat.update', $r->id), ['judul' => 'Diganti'])->assertNotFound();
        $this->actingAs($penyusup)->putJson(route('profil.riwayat.update', $r->id), ['judul' => 'Diganti'])->assertNotFound();
        $this->actingAs($penyusup)->delete(route('profil.riwayat.destroy', $r->id))->assertNotFound();

        $this->assertSame('Asli', $r->fresh()->judul);
    }

    public function test_hanya_mahasiswa_yang_boleh_mengelola_riwayat(): void
    {
        $this->post(route('profil.riwayat.store'), $this->dataValid())->assertRedirect(route('login'));

        foreach ([User::ROLE_DOSEN, User::ROLE_ADMIN] as $peran) {
            $u = User::factory()->create(['role' => $peran, 'email' => "{$peran}@polinema.ac.id"]);
            $this->actingAs($u)->post(route('profil.riwayat.store'), $this->dataValid())->assertForbidden();
        }

        $this->assertSame(0, RiwayatProfil::count());
    }

    // ---------- Validasi ----------

    public function test_tautan_selain_http_dan_https_ditolak(): void
    {
        $mhs = $this->buatMahasiswa();

        foreach (['javascript:alert(1)', 'JaVaScRiPt:alert(1)', 'data:text/html;base64,PHNjcmlwdD4=', 'ftp://contoh.example/a', 'bukan tautan'] as $tautan) {
            $this->actingAs($mhs)->postJson(route('profil.riwayat.store'), $this->dataValid(['tautan' => $tautan]))
                ->assertUnprocessable()->assertJsonValidationErrors('tautan');
        }

        $this->actingAs($mhs)->post(route('profil.riwayat.store'), $this->dataValid(['tautan' => 'http://contoh.example/sertifikat']))
            ->assertSessionHasNoErrors();
        $this->assertSame(1, $mhs->riwayat()->count());
    }

    public function test_tautan_javascript_juga_ditolak_saat_mengubah(): void
    {
        $mhs = $this->buatMahasiswa();
        $r = $this->entri($mhs, 'proyek', ['tautan' => 'https://github.com/contoh']);

        $this->actingAs($mhs)->put(route('profil.riwayat.update', $r->id), ['judul' => 'X', 'tautan' => 'javascript:alert(1)'])
            ->assertSessionHasErrors('tautan');
        $this->assertSame('https://github.com/contoh', $r->fresh()->tautan);
    }

    public function test_judul_wajib_dan_tanggal_selesai_tidak_boleh_sebelum_mulai(): void
    {
        $mhs = $this->buatMahasiswa();

        $this->actingAs($mhs)->postJson(route('profil.riwayat.store'), $this->dataValid(['judul' => '   ']))
            ->assertJsonValidationErrors('judul');
        $this->actingAs($mhs)->postJson(route('profil.riwayat.store'), $this->dataValid(['judul' => str_repeat('a', 151)]))
            ->assertJsonValidationErrors('judul');
        $this->actingAs($mhs)->postJson(route('profil.riwayat.store'), $this->dataValid(['tanggal_mulai' => '2025-05-01', 'tanggal_selesai' => '2025-04-30']))
            ->assertJsonValidationErrors(['tanggal_selesai' => 'Tanggal selesai tidak boleh sebelum tanggal mulai.']);
        $this->actingAs($mhs)->postJson(route('profil.riwayat.store'), $this->dataValid(['deskripsi' => str_repeat('a', 1001)]))
            ->assertJsonValidationErrors('deskripsi');
        $this->actingAs($mhs)->postJson(route('profil.riwayat.store'), $this->dataValid(['jenis' => 'pengalaman']))
            ->assertJsonValidationErrors('jenis');

        // Tanggal selesai tanpa tanggal mulai, dan tanggal yang sama, tetap boleh.
        $this->actingAs($mhs)->postJson(route('profil.riwayat.store'), $this->dataValid(['tanggal_mulai' => null, 'tanggal_selesai' => '2025-04-30']))->assertOk();
        $this->actingAs($mhs)->postJson(route('profil.riwayat.store'), $this->dataValid(['tanggal_mulai' => '2025-04-30', 'tanggal_selesai' => '2025-04-30']))->assertOk();
        $this->assertSame(2, $mhs->riwayat()->count());
    }

    public function test_batas_sepuluh_entri_per_jenis(): void
    {
        $mhs = $this->buatMahasiswa();
        foreach (range(1, 10) as $i) {
            $this->entri($mhs, 'sertifikasi');
        }

        $this->actingAs($mhs)->postJson(route('profil.riwayat.store'), $this->dataValid())
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['jenis' => 'Maksimal 10 entri sertifikasi.']);
        $this->assertSame(10, $mhs->riwayat()->where('jenis', 'sertifikasi')->count());

        // Batas dihitung per jenis, dan mengubah entri yang ada tetap boleh.
        $this->actingAs($mhs)->postJson(route('profil.riwayat.store'), $this->dataValid(['jenis' => 'proyek']))->assertOk();
        $r = $mhs->riwayat()->where('jenis', 'sertifikasi')->first();
        $this->actingAs($mhs)->putJson(route('profil.riwayat.update', $r->id), ['judul' => 'Diubah'])->assertOk();

        // Halaman profil menyembunyikan tombol tambah untuk jenis yang penuh.
        $this->actingAs($mhs)->get(route('profil.show'))
            ->assertSee('Batas 10 sertifikasi tercapai')
            ->assertSee('Tambah proyek');
    }

    // ---------- Halaman profil ----------

    public function test_halaman_profil_menampilkan_empat_bagian_dan_entri(): void
    {
        $mhs = $this->buatMahasiswa();
        $this->entri($mhs, 'pencapaian', ['judul' => 'Juara 1 Gemastik']);

        $this->actingAs($mhs)->get(route('profil.show'))
            ->assertOk()
            ->assertSee('id="riwayat-sertifikasi"', false)
            ->assertSee('id="riwayat-pelatihan"', false)
            ->assertSee('id="riwayat-proyek"', false)
            ->assertSee('id="riwayat-pencapaian"', false)
            ->assertSee('Juara 1 Gemastik')
            ->assertSee('Tambah sertifikasi');
    }

    public function test_dosen_tidak_melihat_bagian_riwayat(): void
    {
        $dosen = User::factory()->create(['role' => User::ROLE_DOSEN, 'email' => 'dosen@polinema.ac.id']);
        $dosen->dosen()->create(['nidn' => '0701019001', 'jurusan' => 'Teknologi Informasi', 'program_studi' => 'Sistem Informasi Bisnis']);

        $this->actingAs($dosen)->get(route('profil.show'))->assertOk()->assertDontSee('id="riwayat-sertifikasi"', false);
    }

    public function test_simpan_profil_utama_tidak_menyentuh_riwayat(): void
    {
        $mhs = $this->buatMahasiswa();
        $this->entri($mhs, 'proyek', ['judul' => 'Tetap Ada']);

        $this->actingAs($mhs)->put(route('profil.update'), [
            'name' => 'Nama Baru', 'program_studi' => 'Sistem Informasi Bisnis', 'kelas' => 'SIB-3A',
            'angkatan' => 2024, 'tampil_di_direktori' => 1,
        ])->assertSessionHasNoErrors();

        $this->assertSame('Tetap Ada', $mhs->riwayat()->sole()->judul);
    }

    // ---------- Direktori ----------

    public function test_tampil_di_profil_direktori_per_jenis_terbaru_dulu_dengan_tautan_aman(): void
    {
        $pembaca = $this->buatMahasiswa();
        $mhs = $this->buatMahasiswa();
        $this->entri($mhs, 'sertifikasi', ['judul' => 'Sertifikat Lama', 'tanggal_mulai' => '2022-01-10']);
        $this->entri($mhs, 'sertifikasi', ['judul' => 'Sertifikat Baru', 'tanggal_mulai' => '2025-06-10', 'tautan' => 'https://contoh.example/kredensial']);
        $this->entri($mhs, 'proyek', ['judul' => 'Proyek Kampus', 'penerbit' => 'Polinema', 'tanggal_mulai' => '2024-02-01', 'tanggal_selesai' => '2024-06-30']);

        $respons = $this->actingAs($pembaca)->get(route('direktori.show', $mhs->id))->assertOk()
            ->assertSeeInOrder(['Sertifikasi', 'Sertifikat Baru', 'Sertifikat Lama', 'Proyek', 'Proyek Kampus'])
            ->assertSee('Polinema')
            ->assertSee('Februari 2024 sampai Juni 2024')
            ->assertDontSee('Pelatihan</h2>', false); // jenis kosong tidak ditampilkan

        $html = $respons->getContent();
        $this->assertMatchesRegularExpression(
            '#<a href="https://contoh\.example/kredensial" target="_blank" rel="noopener noreferrer"#', $html);
    }

    public function test_isian_riwayat_di_escape(): void
    {
        $pembaca = $this->buatMahasiswa();
        $mhs = $this->buatMahasiswa();
        $this->entri($mhs, 'proyek', ['judul' => '<script>alert(1)</script>', 'penerbit' => '<b>Tebal</b>', 'deskripsi' => '<img src=x onerror=alert(1)>']);

        $this->actingAs($pembaca)->get(route('direktori.show', $mhs->id))->assertOk()
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertDontSee('<b>Tebal</b>', false)
            ->assertDontSee('<img src=x', false)
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false);
    }

    public function test_tautan_lama_yang_tidak_aman_tidak_dijadikan_link(): void
    {
        // Misalnya data yang masuk lewat jalur lain sebelum validasi ada: tetap tidak boleh jadi href.
        $pembaca = $this->buatMahasiswa();
        $mhs = $this->buatMahasiswa();
        $this->entri($mhs, 'proyek', ['judul' => 'Proyek X', 'tautan' => 'javascript:alert(1)']);

        $this->actingAs($pembaca)->get(route('direktori.show', $mhs->id))->assertOk()
            ->assertSee('Proyek X')->assertDontSee('href="javascript:', false);
    }

    public function test_profil_yang_disembunyikan_tetap_404_walau_punya_riwayat(): void
    {
        $pembaca = $this->buatMahasiswa();
        $disembunyikan = $this->buatMahasiswa(['tampil_di_direktori' => false]);
        $this->entri($disembunyikan, 'sertifikasi');
        $olehAdmin = $this->buatMahasiswa();
        $olehAdmin->mahasiswa->forceFill(['disembunyikan_admin' => true])->save();
        $this->entri($olehAdmin, 'proyek');

        $this->actingAs($pembaca)->get(route('direktori.show', $disembunyikan->id))->assertNotFound();
        $this->actingAs($pembaca)->get(route('direktori.show', $olehAdmin->id))->assertNotFound();
    }

    public function test_jumlah_query_profil_direktori_tidak_bertambah_seiring_jumlah_riwayat(): void
    {
        $pembaca = $this->buatMahasiswa();
        $mhs = $this->buatMahasiswa();
        $hitung = function () use ($pembaca, $mhs): int {
            DB::flushQueryLog();
            DB::enableQueryLog();
            $this->actingAs($pembaca)->get(route('direktori.show', $mhs->id))->assertOk();
            $jumlah = count(DB::getQueryLog());
            DB::disableQueryLog();

            return $jumlah;
        };

        $this->entri($mhs, 'sertifikasi');
        $sedikit = $hitung();

        foreach (array_keys(RiwayatProfil::JENIS) as $jenis) {
            foreach (range(1, 9) as $i) {
                $this->entri($mhs, $jenis, ['tautan' => 'https://contoh.example/' . $i]);
            }
        }
        $banyak = $hitung();

        $this->assertSame($sedikit, $banyak, 'Query bertambah seiring jumlah riwayat: ada N+1.');

        // Riwayat harus di-eager load di controller (query "user_id in (...)"), bukan dimuat malas oleh view
        // ("user_id = ?"). preventLazyLoading tidak menangkap ini karena hanya berlaku untuk model yang diambil berkelompok.
        DB::flushQueryLog();
        DB::enableQueryLog();
        $this->actingAs($pembaca)->get(route('direktori.show', $mhs->id))->assertOk();
        $kueriRiwayat = collect(DB::getQueryLog())->pluck('query')->filter(fn ($q) => str_contains($q, 'riwayat_profil'));
        DB::disableQueryLog();

        $this->assertCount(1, $kueriRiwayat);
        $this->assertMatchesRegularExpression('/user_id" in \(/', $kueriRiwayat->first(), 'Riwayat tidak di-eager load di DirektoriController::show.');
    }

    // ---------- Migration data ----------

    public function test_migration_memindahkan_teks_lama_satu_entri_per_jenis_dan_aman_diulang(): void
    {
        $mhs = $this->buatMahasiswa([
            'sertifikasi' => "AWS Cloud Practitioner\nDicoding Laravel",
            'proyek' => 'Sistem inventaris kampus.',
            'pelatihan' => "   \n ",
            'pencapaian' => str_repeat('Juara ', 40), // lebih dari 150 karakter
        ]);
        $kosong = $this->buatMahasiswa();

        $migration = require database_path('migrations/2026_10_24_000002_pindahkan_teks_profil_ke_riwayat_profil.php');
        $migration->up();
        $migration->up(); // dijalankan ulang: tidak menggandakan

        $riwayat = $mhs->riwayat()->get()->keyBy('jenis');
        $this->assertEqualsCanonicalizing(['sertifikasi', 'proyek', 'pencapaian'], $riwayat->keys()->all());
        $this->assertSame('AWS Cloud Practitioner', $riwayat['sertifikasi']->judul);
        $this->assertSame('Dicoding Laravel', $riwayat['sertifikasi']->deskripsi);
        $this->assertSame('Sistem inventaris kampus.', $riwayat['proyek']->judul);
        $this->assertNull($riwayat['proyek']->deskripsi);
        $this->assertLessThanOrEqual(150, mb_strlen($riwayat['pencapaian']->judul));
        $this->assertStringContainsString(trim(str_repeat('Juara ', 40)), $riwayat['pencapaian']->deskripsi); // teks utuh disimpan
        $this->assertSame(0, $kosong->riwayat()->count());

        // Kolom lama tidak diubah.
        $this->assertSame("AWS Cloud Practitioner\nDicoding Laravel", $mhs->mahasiswa->fresh()->sertifikasi);
    }
}
