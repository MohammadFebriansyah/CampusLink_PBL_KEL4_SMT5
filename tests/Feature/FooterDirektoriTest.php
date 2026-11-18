<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class FooterDirektoriTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        // Pengujian tidak boleh membangun ulang database Supabase di .env.
        if (($_ENV['DB_CONNECTION'] ?? getenv('DB_CONNECTION')) !== 'sqlite'
            || ($_ENV['DB_DATABASE'] ?? getenv('DB_DATABASE')) !== ':memory:') {
            $this->markTestSkipped('Uji navigasi hanya berjalan dengan SQLite memori.');
        }

        parent::setUp();

        $this->withoutVite();
    }

    /** @return array<string, array{0: ?string, 1: bool}> */
    public static function peranDirektori(): array
    {
        return [
            'tamu' => [null, true],
            'mahasiswa' => [User::ROLE_MAHASISWA, true],
            'dosen' => [User::ROLE_DOSEN, true],
            'admin' => [User::ROLE_ADMIN, false],
            'mitra' => [User::ROLE_MITRA, false],
        ];
    }

    #[DataProvider('peranDirektori')]
    public function test_tautan_direktori_di_footer_dan_navbar_sesuai_peran(?string $peran, bool $bolehDirektori): void
    {
        if ($peran !== null) {
            $this->actingAs(User::factory()->create(['role' => $peran]));
        }

        // Beranda memakai navbar dan footer publik; daftar informasi memakai layout panel.
        $respons = $this->get(route('home'))->assertOk();
        $tautan = 'href="'.route('direktori.index').'"';

        foreach (['footer', 'nav'] as $bagian) {
            $jumlah = preg_match('/<'.$bagian.'\b[^>]*>(.*?)<\/'.$bagian.'>/s', $respons->getContent(), $cocok);
            $this->assertSame(1, $jumlah, "Bagian {$bagian} harus tersedia.");

            if ($bolehDirektori) {
                $this->assertStringContainsString($tautan, $cocok[1], "Tautan harus terlihat pada {$bagian}.");
            } else {
                $this->assertStringNotContainsString($tautan, $cocok[1], "Tautan tidak boleh terlihat pada {$bagian}.");
            }
        }
    }

    /**
     * Tombol "Buka direktori" di isi beranda (bukan hanya navbar dan footer) mengikuti aturan yang sama:
     * admin dan mitra membukanya menghasilkan 403, jadi tombolnya tidak boleh ada.
     */
    #[DataProvider('peranDirektori')]
    public function test_beranda_tidak_memuat_tombol_direktori_untuk_peran_yang_tidak_berhak(?string $peran, bool $bolehDirektori): void
    {
        if ($peran !== null) {
            $this->actingAs(User::factory()->create(['role' => $peran]));
        }

        $respons = $this->get(route('home'))->assertOk();

        if ($bolehDirektori) {
            $this->assertSame(2, substr_count($respons->getContent(), '>Buka direktori'), 'Dua tombol Buka direktori harus tampil.');
        } else {
            $respons->assertDontSee('Buka direktori');
            $this->assertStringNotContainsString('href="'.route('direktori.index').'"', $respons->getContent());
        }
    }
}
