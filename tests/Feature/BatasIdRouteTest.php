<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Tests\TestCase;

/**
 * BUG_CL_0009 – Pastikan rute ber-{id} menolak ID yang melebihi bigint PostgreSQL.
 *
 * ID 20-digit (99999999999999999999) > bigint max (9223372036854775807) akan memicu
 * SQLSTATE[22003] di PostgreSQL bila diteruskan ke query. Pola global [0-9]{1,18} di
 * Route::pattern() menghentikannya di lapisan routing sehingga hasilnya 404, bukan 500.
 */
class BatasIdRouteTest extends TestCase
{
    use RefreshDatabase;

    private const ID_TERLALU_BESAR = '99999999999999999999'; // 20 digit, > bigint max
    private const ID_WAJAR = '1';

    protected function setUp(): void
    {
        if (($_ENV['DB_CONNECTION'] ?? getenv('DB_CONNECTION')) !== 'sqlite') {
            $this->markTestSkipped(
                'Uji ini hanya berjalan dengan SQLite. Atur DB_CONNECTION=sqlite dan DB_DATABASE=:memory: di phpunit.xml.'
            );
        }

        parent::setUp();
        $this->withoutVite();
    }

    // -------------------------------------------------------------------------
    // Pembantu
    // -------------------------------------------------------------------------

    private function buatMahasiswa(): User
    {
        return User::factory()->create(['role' => User::ROLE_MAHASISWA]);
    }

    private function buatAdmin(): User
    {
        return User::factory()->create(['role' => User::ROLE_ADMIN]);
    }

    // =========================================================================
    // 1. Rute /magang/{id} (dan /lomba, /beasiswa via loop yang sama)
    // =========================================================================

    public function test_id_oversized_tidak_cocok_dengan_rute_magang(): void
    {
        // Route::getRoutes()->match() harus melempar NotFoundHttpException
        // karena {id} 20-digit tidak cocok dengan pola [0-9]{1,18}.
        $this->expectException(NotFoundHttpException::class);
        Route::getRoutes()->match(Request::create('/magang/' . self::ID_TERLALU_BESAR));
    }

    public function test_mahasiswa_mendapat_404_bukan_500_untuk_magang_id_oversized(): void
    {
        // HTTP layer harus mengembalikan 404; tidak boleh ada QueryException yang lolos.
        $this->actingAs($this->buatMahasiswa())
            ->get('/magang/' . self::ID_TERLALU_BESAR)
            ->assertNotFound();
    }

    public function test_id_wajar_cocok_dengan_rute_magang_dan_menghasilkan_404_data(): void
    {
        // Data tidak ada → 404 dari controller, bukan dari routing. Artinya rute cocok.
        $this->actingAs($this->buatMahasiswa())
            ->get('/magang/' . self::ID_WAJAR)
            ->assertNotFound();
    }

    // =========================================================================
    // 2. Rute /direktori/{id}
    // =========================================================================

    public function test_id_oversized_tidak_cocok_dengan_rute_direktori(): void
    {
        $this->expectException(NotFoundHttpException::class);
        Route::getRoutes()->match(Request::create('/direktori/' . self::ID_TERLALU_BESAR));
    }

    public function test_mahasiswa_mendapat_404_bukan_500_untuk_direktori_id_oversized(): void
    {
        $this->actingAs($this->buatMahasiswa())
            ->get('/direktori/' . self::ID_TERLALU_BESAR)
            ->assertNotFound();
    }

    public function test_id_wajar_cocok_dengan_rute_direktori_dan_menghasilkan_404_data(): void
    {
        $this->actingAs($this->buatMahasiswa())
            ->get('/direktori/' . self::ID_WAJAR)
            ->assertNotFound();
    }

    // =========================================================================
    // 3. Rute /admin/pengguna/{id}
    // =========================================================================

    public function test_id_oversized_tidak_cocok_dengan_rute_admin_pengguna(): void
    {
        $this->expectException(NotFoundHttpException::class);
        Route::getRoutes()->match(Request::create('/admin/pengguna/' . self::ID_TERLALU_BESAR));
    }

    public function test_admin_mendapat_404_bukan_500_untuk_pengguna_id_oversized(): void
    {
        $this->actingAs($this->buatAdmin())
            ->get('/admin/pengguna/' . self::ID_TERLALU_BESAR)
            ->assertNotFound();
    }

    public function test_id_wajar_cocok_dengan_rute_admin_pengguna_dan_menghasilkan_404_data(): void
    {
        $this->actingAs($this->buatAdmin())
            ->get('/admin/pengguna/' . self::ID_WAJAR)
            ->assertNotFound();
    }

    // =========================================================================
    // 4. Rute /pendaftaran/{id}/cv
    // =========================================================================

    public function test_id_oversized_tidak_cocok_dengan_rute_pendaftaran_cv(): void
    {
        $this->expectException(NotFoundHttpException::class);
        Route::getRoutes()->match(Request::create('/pendaftaran/' . self::ID_TERLALU_BESAR . '/cv'));
    }

    // =========================================================================
    // 5. Rute /bimbingan/{id}/bukti
    // =========================================================================

    public function test_id_oversized_tidak_cocok_dengan_rute_bimbingan_bukti(): void
    {
        $this->expectException(NotFoundHttpException::class);
        Route::getRoutes()->match(Request::create('/bimbingan/' . self::ID_TERLALU_BESAR . '/bukti'));
    }

    // =========================================================================
    // 6. Batas tepat: 18 digit masih diterima, 19 digit ditolak
    // =========================================================================

    public function test_id_18_digit_masih_cocok_dengan_rute(): void
    {
        // 18 digit = panjang maksimum yang diizinkan pola [0-9]{1,18}
        $id18 = '123456789012345678'; // 18 digit, tidak ada data tapi rute cocok
        // actingAs agar middleware auth tidak mengarahkan ke login sebelum routing
        $this->actingAs($this->buatMahasiswa())
            ->get('/magang/' . $id18)
            ->assertNotFound(); // 404 dari controller (data tidak ada), bukan routing
    }

    public function test_id_19_digit_tidak_cocok_dengan_rute(): void
    {
        $id19 = '1234567890123456789'; // 19 digit, harus ditolak routing
        $this->expectException(NotFoundHttpException::class);
        Route::getRoutes()->match(Request::create('/magang/' . $id19));
    }
}
