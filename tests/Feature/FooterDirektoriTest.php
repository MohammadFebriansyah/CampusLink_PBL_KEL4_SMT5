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
}
