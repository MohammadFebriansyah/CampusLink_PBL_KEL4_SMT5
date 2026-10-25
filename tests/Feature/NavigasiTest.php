<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

// TAMBAHAN: uji navbar publik yang diringkas (Papan Informasi sebagai dropdown, Profil/Bimbingan/Keluar di menu akun).
class NavigasiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        // RefreshDatabase menghapus seluruh tabel. Jangan sampai terjadi di Supabase.
        if (($_ENV['DB_CONNECTION'] ?? getenv('DB_CONNECTION')) !== 'sqlite') {
            $this->markTestSkipped('Uji ini hanya berjalan dengan SQLite. Atur DB_CONNECTION=sqlite dan DB_DATABASE=:memory: di phpunit.xml.');
        }

        parent::setUp();

        $this->withoutVite();
    }

    /** Potongan HTML deret utama navbar desktop. */
    private function deretUtama(TestResponse $respons): string
    {
        preg_match('/<ul class="hidden items-center gap-1 md:flex">(.*?)<\/ul>/s', $respons->getContent(), $cocok);

        return $cocok[1] ?? '';
    }

    private function pengguna(string $peran): User
    {
        return User::factory()->create(['role' => $peran, 'email' => "{$peran}@polinema.ac.id"]);
    }

    public function test_tamu_melihat_papan_informasi_direktori_masuk_dan_daftar(): void
    {
        $respons = $this->get(route('home'))->assertOk();
        $deret = $this->deretUtama($respons);

        $this->assertStringContainsString('Papan Informasi', $deret);
        $this->assertStringContainsString('Direktori', $deret);
        $this->assertStringNotContainsString('Kegiatan saya', $deret);
        $respons->assertSee('Masuk')->assertSee('Daftar')->assertDontSee('Keluar');
    }

    public function test_papan_informasi_berisi_magang_lomba_beasiswa_dengan_atribut_aksesibilitas(): void
    {
        $deret = $this->deretUtama($this->get(route('home')));

        $this->assertStringContainsString('aria-haspopup="true"', $deret);
        $this->assertStringContainsString('aria-expanded="false"', $deret);
        foreach (['magang', 'lomba', 'beasiswa'] as $kategori) {
            $this->assertStringContainsString('href="' . route("{$kategori}.index") . '"', $deret);
        }
    }

    public function test_deret_utama_paling_banyak_lima_item_untuk_semua_peran(): void
    {
        $respons = $this->get(route('home'));
        $this->assertLessThanOrEqual(5, substr_count($this->deretUtama($respons), '<li') + 1);

        foreach (['mahasiswa', 'dosen', 'mitra', 'admin'] as $peran) {
            $respons = $this->actingAs($this->pengguna($peran))->get(route('home'))->assertOk();
            // +1 untuk menu akun di sebelah kanan.
            $this->assertLessThanOrEqual(5, substr_count($this->deretUtama($respons), '<li') + 1, "Peran {$peran}");
        }
    }

    public function test_mahasiswa_melihat_kegiatan_saya_dan_profil_keluar_di_menu_akun(): void
    {
        $mahasiswa = $this->pengguna('mahasiswa');
        $respons = $this->actingAs($mahasiswa)->get(route('home'))->assertOk();
        $deret = $this->deretUtama($respons);

        $this->assertStringContainsString('Kegiatan saya', $deret);
        $this->assertStringContainsString('Direktori', $deret);
        $this->assertStringNotContainsString('Profil', $deret);
        $this->assertStringNotContainsString('Bimbingan', $deret);
        $respons->assertSee('id="menu-akun"', false)->assertSee('Keluar')->assertSee(route('profil.show'), false)
            ->assertDontSee(route('bimbingan.index'), false);
    }

    public function test_dosen_melihat_bimbingan_di_menu_akun_bukan_di_deret_utama(): void
    {
        $respons = $this->actingAs($this->pengguna('dosen'))->get(route('home'))->assertOk();
        $deret = $this->deretUtama($respons);

        $this->assertStringContainsString('Direktori', $deret);
        $this->assertStringNotContainsString('Kegiatan saya', $deret);
        $this->assertStringNotContainsString('Bimbingan', $deret);
        $respons->assertSee(route('bimbingan.index'), false);
    }

    public function test_mitra_dan_admin_tidak_melihat_direktori(): void
    {
        foreach (['mitra', 'admin'] as $peran) {
            $pengguna = $this->pengguna($peran);
            $respons = $this->actingAs($pengguna)->get(route('home'))->assertSee('Menu akun ' . $pengguna->name);
            $deret = $this->deretUtama($respons);
            $this->assertStringNotContainsString('Direktori', $deret, "Peran {$peran}");
            $this->assertStringNotContainsString('Kegiatan saya', $deret, "Peran {$peran}");
        }
    }

    public function test_papan_informasi_dan_itemnya_aktif_di_halaman_kategori(): void
    {
        $respons = $this->actingAs($this->pengguna('mahasiswa'))->get(route('lomba.index'))->assertOk();
        $deret = $this->deretUtama($respons);

        // Tombol Papan Informasi memakai kelas aktif, dan hanya tautan Lomba yang diberi aria-current.
        $this->assertMatchesRegularExpression('/bg-primary\/10 text-primary">\s*Papan Informasi/', $deret);
        $this->assertMatchesRegularExpression('/href="' . preg_quote(route('lomba.index'), '/') . '"[^>]*aria-current="page"/', $deret);
        $this->assertDoesNotMatchRegularExpression('/href="' . preg_quote(route('magang.index'), '/') . '"[^>]*aria-current="page"/', $deret);

        // Di layar sempit akordeon Papan Informasi langsung terbuka.
        $respons->assertSee('x-data="{ buka: true }"', false)->assertSee('aria-controls="akordeon-papan"', false);
    }
}
