<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Tests\TestCase;

class HalamanGalatTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['app.debug' => false]);
        $this->withoutVite();
    }

    public function test_alamat_tidak_ada_menampilkan_404_indonesia(): void
    {
        $this->get('/halaman-tidak-ada')
            ->assertNotFound()
            ->assertSeeText('Halaman tidak ditemukan')
            ->assertSeeText('Kembali ke beranda')
            ->assertDontSeeText('Not Found');
    }

    public function test_mahasiswa_membuka_admin_menampilkan_403_indonesia(): void
    {
        $mahasiswa = User::factory()->make(['role' => User::ROLE_MAHASISWA]);

        $this->actingAs($mahasiswa)->get('/admin')
            ->assertForbidden()
            ->assertSeeText('Anda tidak punya akses ke halaman ini')
            ->assertDontSeeText('Forbidden');
    }

    public function test_post_tanpa_csrf_menampilkan_419_dengan_tautan_muat_ulang_get(): void
    {
        // Laravel melewati CSRF saat pengujian; aktifkan kembali hanya untuk uji ini.
        $this->app->bind(PreventRequestForgery::class, fn ($app) => new class($app, $app['encrypter']) extends PreventRequestForgery
        {
            protected function runningUnitTests()
            {
                return false;
            }
        });

        $this->post('/daftar')
            ->assertStatus(419)
            ->assertSeeText('Sesi Anda sudah berakhir. Muat ulang halaman lalu coba lagi.')
            ->assertSee('href="'.url('/daftar').'"', false)
            ->assertSeeText('Muat ulang halaman')
            ->assertDontSeeText('Page Expired');
    }

    public static function galatMandiri(): array
    {
        return [
            '403' => [403, 'Anda tidak punya akses ke halaman ini'],
            '404' => [404, 'Halaman tidak ditemukan'],
            '419' => [419, 'Sesi Anda sudah berakhir. Muat ulang halaman lalu coba lagi.'],
            '429' => [429, 'Terlalu banyak permintaan. Coba lagi sebentar lagi.'],
            '500' => [500, 'Terjadi kesalahan di server kami'],
            '503' => [503, 'Sedang dalam pemeliharaan'],
        ];
    }

    #[DataProvider('galatMandiri')]
    public function test_halaman_galat_mandiri_tanpa_database_atau_sesi(int $status, string $pesan): void
    {
        Route::get('/uji-galat', fn () => abort($status, 'DETAIL-TEKNIS-RAHASIA'));

        // Siapkan layanan HTTP sebelum menonaktifkan akses sesi untuk render galat.
        $this->app->make('redirect');
        $this->mock('db')->shouldNotReceive('connection');
        $this->mock('session')->shouldNotReceive('driver');

        $this->get('/uji-galat')
            ->assertStatus($status)
            ->assertSeeText($pesan)
            ->assertSeeText('CampusLink')
            ->assertSee('lang="id"', false)
            ->assertSee('href="'.url('/').'"', false)
            ->assertDontSee('DETAIL-TEKNIS-RAHASIA')
            ->assertDontSee('Stack trace');
    }

    public function test_kesalahan_server_tidak_membocorkan_detail_exception(): void
    {
        Route::get('/uji-exception', fn () => throw new RuntimeException('DETAIL-TEKNIS-RAHASIA'));

        $this->get('/uji-exception')
            ->assertStatus(500)
            ->assertSeeText('Terjadi kesalahan di server kami')
            ->assertDontSee('DETAIL-TEKNIS-RAHASIA')
            ->assertDontSee('RuntimeException');
    }
}
