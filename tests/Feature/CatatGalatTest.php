<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Events\Failed;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use RuntimeException;
use Tests\TestCase;

/**
 * Log terstruktur (JSON), Request ID, dan penyamaran data sensitif. Log ditulis ke berkas sungguhan
 * di folder sementara memakai konfigurasi channel yang sama dengan produksi (config/logging.php).
 */
class CatatGalatTest extends TestCase
{
    use RefreshDatabase;

    private string $folder;

    protected function setUp(): void
    {
        if (($_ENV['DB_CONNECTION'] ?? getenv('DB_CONNECTION')) !== 'sqlite') {
            $this->markTestSkipped('Uji ini hanya berjalan dengan SQLite (RefreshDatabase). Atur DB_CONNECTION=sqlite di phpunit.xml.');
        }

        parent::setUp();
        $this->withoutVite();

        $this->folder = sys_get_temp_dir().'/campuslink-log-'.bin2hex(random_bytes(4));
        File::ensureDirectoryExists($this->folder);
        $this->arahkanLog();

        config(['app.debug' => false, 'app.env' => 'production']);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->folder);
        parent::tearDown();
    }

    /** Memakai channel produksi (json + galat) tetapi menulis ke folder sementara. */
    private function arahkanLog(int $hari = 14): void
    {
        config([
            'logging.default' => 'stack',
            'logging.channels.stack.channels' => ['json', 'galat'],
            'logging.channels.json.path' => $this->folder.'/campuslink.log',
            'logging.channels.json.days' => $hari,
            'logging.channels.galat.path' => $this->folder.'/galat.log',
        ]);

        foreach (['stack', 'json', 'galat'] as $channel) {
            app('log')->forgetChannel($channel);
        }
    }

    private function berkas(string $nama): string
    {
        return $this->folder.'/'.$nama.'-'.now()->format('Y-m-d').'.log';
    }

    /** @return list<array<string, mixed>> */
    private function entri(string $nama = 'campuslink'): array
    {
        $berkas = $this->berkas($nama);

        return is_file($berkas)
            ? array_map(fn ($baris) => json_decode($baris, true, 512, JSON_THROW_ON_ERROR), array_values(array_filter(explode("\n", file_get_contents($berkas)))))
            : [];
    }

    private function isiMentah(): string
    {
        return collect(File::files($this->folder))->map(fn ($f) => file_get_contents($f->getPathname()))->implode("\n");
    }

    // ---------- Request ID ----------

    public function test_setiap_respons_membawa_request_id_baru(): void
    {
        $a = $this->get('/masuk')->headers->get('X-Request-ID');
        $b = $this->get('/masuk')->headers->get('X-Request-ID');

        $this->assertMatchesRegularExpression('/^req-[0-9a-f]{16}$/', $a);
        $this->assertNotSame($a, $b);
    }

    public function test_request_id_dari_klien_dipakai_hanya_jika_bentuknya_aman(): void
    {
        $this->withHeader('X-Request-ID', 'nginx-1a2b3c4d5e')->get('/masuk')->assertHeader('X-Request-ID', 'nginx-1a2b3c4d5e');

        foreach (['pendek', 'ada spasi dan "tanda kutip"', str_repeat('a', 65), "baris\nbaru-injeksi"] as $jahat) {
            $id = $this->withHeader('X-Request-ID', $jahat)->get('/masuk')->headers->get('X-Request-ID');
            $this->assertMatchesRegularExpression('/^req-[0-9a-f]{16}$/', $id, 'ID tidak aman harus diganti: '.json_encode($jahat));
        }
    }

    public function test_halaman_galat_membawa_request_id_di_header_dan_kode_referensi(): void
    {
        $respons = $this->get('/halaman-tidak-ada')->assertNotFound();
        $id = $respons->headers->get('X-Request-ID');

        $this->assertNotEmpty($id);
        $respons->assertSeeText('Kode referensi: '.$id);
    }

    // ---------- Exception tak tertangani ----------

    public function test_exception_tercatat_json_lengkap_dengan_request_id_dan_pengguna_tidak_melihat_detail(): void
    {
        Route::get('/_uji/galat', fn () => throw new RuntimeException('Detail internal rahasia'));
        $pengguna = User::factory()->create(['role' => User::ROLE_MAHASISWA]);

        $respons = $this->actingAs($pengguna)->get('/_uji/galat?token=abc123')->assertStatus(500);

        // Respons production aman: tanpa pesan exception, tanpa stack trace.
        $respons->assertDontSee('Detail internal rahasia')->assertDontSee('RuntimeException')->assertDontSee('#0 ');
        $respons->assertSeeText('Terjadi kesalahan di server kami');

        $entri = collect($this->entri())->firstWhere('exception', RuntimeException::class);
        $this->assertNotNull($entri, 'Exception harus tercatat di log JSON.');
        $this->assertSame('ERROR', $entri['level']);
        $this->assertSame('production', $entri['environment']);
        $this->assertSame($respons->headers->get('X-Request-ID'), $entri['request_id']);
        $this->assertSame('GET', $entri['method']);
        $this->assertSame('/_uji/galat', $entri['url']); // tanpa query string
        $this->assertSame(500, $entri['status_code']);
        $this->assertSame($pengguna->id, $entri['user_id']);
        $this->assertSame('Detail internal rahasia', $entri['exception_message']);
        $this->assertStringContainsString('CatatGalatTest.php', $entri['file']);
        $this->assertIsInt($entri['line']);
        $this->assertStringContainsString('#0 ', $entri['trace']);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}[+-]\d{2}:\d{2}$/', $entri['timestamp']);

        // Error juga masuk ke channel khusus galat; token di query string tidak ikut tercatat.
        $this->assertNotEmpty(collect($this->entri('galat'))->firstWhere('request_id', $entri['request_id']));
        $this->assertStringNotContainsString('abc123', $this->isiMentah());
    }

    public function test_exception_database_tercatat_tanpa_nilai_yang_diikat(): void
    {
        Route::get('/_uji/db', fn () => DB::select('select * from tabel_tidak_ada where email = ?', ['rahasia@contoh.test']));

        $this->get('/_uji/db')->assertStatus(500);

        $entri = collect($this->entri())->first(fn ($e) => str_contains($e['exception'] ?? '', 'QueryException'));
        $this->assertNotNull($entri, 'QueryException harus tercatat.');
        $this->assertSame('select * from tabel_tidak_ada where email = ?', $entri['sql']);
        $this->assertStringNotContainsString('rahasia@contoh.test', $this->isiMentah());
    }

    public function test_kegagalan_api_eksternal_tercatat(): void
    {
        Http::fake(['api.contoh.test/*' => Http::response('gagal', 503)]);
        Route::get('/_uji/api', fn () => Http::get('https://api.contoh.test/data')->throw()->body());

        $this->get('/_uji/api')->assertStatus(500);

        $entri = collect($this->entri())->first(fn ($e) => str_contains($e['exception'] ?? '', 'RequestException'));
        $this->assertNotNull($entri, 'Kegagalan HTTP ke layanan luar harus tercatat.');
        $this->assertNotEmpty($entri['request_id']);
    }

    // ---------- 4xx, autentikasi, otorisasi ----------

    public function test_akses_ditolak_403_tercatat_sebagai_peringatan_dengan_user_id(): void
    {
        $mhs = User::factory()->create(['role' => User::ROLE_MAHASISWA]);

        $this->actingAs($mhs)->get('/admin')->assertForbidden();

        $entri = collect($this->entri())->firstWhere('status_code', 403);
        $this->assertNotNull($entri);
        $this->assertSame('WARNING', $entri['level']);
        $this->assertSame('/admin', $entri['url']);
        $this->assertSame($mhs->id, $entri['user_id']);
    }

    public function test_login_gagal_tercatat_tanpa_menulis_email_atau_kata_sandi(): void
    {
        event(new Failed('web', null, ['email' => 'korban@contoh.test', 'password' => 'KataSandiSalah123']));

        $entri = collect($this->entri())->firstWhere('message', 'Login gagal');
        $this->assertNotNull($entri);
        $this->assertSame('WARNING', $entri['level']);
        $this->assertSame(12, strlen($entri['context']['email_hash']));
        $this->assertStringNotContainsString('korban@contoh.test', $this->isiMentah());
        $this->assertStringNotContainsString('KataSandiSalah123', $this->isiMentah());
    }

    public function test_404_tidak_dicatat_kecuali_diaktifkan(): void
    {
        $this->get('/tidak-ada-1')->assertNotFound();
        $this->assertSame([], collect($this->entri())->where('status_code', 404)->all());

        config(['logging.catat_404' => true]);
        $this->get('/tidak-ada-2')->assertNotFound();
        $this->assertNotEmpty(collect($this->entri())->where('status_code', 404)->all());
    }

    // ---------- Data sensitif ----------

    public function test_data_sensitif_tidak_pernah_masuk_log(): void
    {
        Log::error('Uji penyamaran', [
            'password' => 'RahasiaPassword1',
            'nested' => ['api_key' => 'AKIA-RAHASIA-KEY', 'Authorization' => 'Bearer tokenrahasia', 'aman' => 'nilai-aman'],
            'cookie' => 'laravel_session=SESIRAHASIA',
            'kode' => '123456',
        ]);
        Log::info('Header Authorization: Bearer abc.def.ghi dan password=sandi-di-teks');

        $isi = $this->isiMentah();
        foreach (['RahasiaPassword1', 'AKIA-RAHASIA-KEY', 'tokenrahasia', 'SESIRAHASIA', '123456', 'abc.def.ghi', 'sandi-di-teks'] as $rahasia) {
            $this->assertStringNotContainsString($rahasia, $isi, "Nilai sensitif bocor ke log: {$rahasia}");
        }
        $this->assertStringContainsString('nilai-aman', $isi); // data biasa tetap tercatat
    }

    public function test_isi_permintaan_header_dan_cookie_tidak_dicatat_saat_error(): void
    {
        Route::post('/_uji/kirim', fn () => throw new RuntimeException('gagal saat memproses'));

        $this->withoutMiddleware(PreventRequestForgery::class)
            ->withHeaders(['Authorization' => 'Bearer tokenheaderrahasia'])
            ->withUnencryptedCookie('sesi_uji', 'cookierahasia')
            ->post('/_uji/kirim', ['password' => 'PasswordBodyRahasia', 'email' => 'pribadi@contoh.test'])
            ->assertStatus(500);

        $isi = $this->isiMentah();
        $this->assertNotEmpty($this->entri());
        foreach (['tokenheaderrahasia', 'cookierahasia', 'PasswordBodyRahasia', 'pribadi@contoh.test'] as $rahasia) {
            $this->assertStringNotContainsString($rahasia, $isi, "Data permintaan bocor ke log: {$rahasia}");
        }
    }

    // ---------- Rotasi, retensi, ketahanan ----------

    public function test_rotasi_harian_dan_retensi_menghapus_berkas_lama(): void
    {
        foreach (['2020-01-01', '2020-01-02', '2020-01-03'] as $tanggal) {
            file_put_contents($this->folder."/campuslink-{$tanggal}.log", "{}\n");
        }
        $this->arahkanLog(hari: 2);

        Log::info('menulis hari ini');

        $this->assertFileExists($this->berkas('campuslink')); // nama berkas memuat tanggal
        $sisa = collect(File::files($this->folder))->map->getFilename()->filter(fn ($n) => str_starts_with($n, 'campuslink-'))->values();
        $this->assertCount(2, $sisa, 'Hanya 2 berkas (hari ini + 1) yang disimpan: '.$sisa->implode(', '));
        $this->assertFileDoesNotExist($this->folder.'/campuslink-2020-01-01.log');
    }

    public function test_konfigurasi_produksi_channel_log(): void
    {
        $json = config('logging.channels.json');
        $galat = config('logging.channels.galat');

        $this->assertSame('daily', $json['driver']);
        $this->assertSame('daily', $galat['driver']);
        $this->assertSame('error', $galat['level']);
        $this->assertSame(0640, $json['permission']); // tidak terbaca pengguna lain di server
        $this->assertSame(0640, $galat['permission']);
        $this->assertTrue(config('logging.channels.stack.ignore_exceptions'));
        $this->assertNotEmpty($json['tap']);
    }

    public function test_info_tidak_masuk_channel_galat(): void
    {
        Log::info('hanya info');
        Log::error('ini error');

        $this->assertCount(1, $this->entri('galat'));
        $this->assertSame('ini error', $this->entri('galat')[0]['message']);
        $this->assertCount(2, $this->entri());
    }

    public function test_gagal_menulis_log_tidak_menjatuhkan_permintaan(): void
    {
        // Folder log berupa berkas biasa, jadi penulisan pasti gagal.
        file_put_contents($this->folder.'/bukan-folder', 'x');
        config(['logging.channels.json.path' => $this->folder.'/bukan-folder/campuslink.log', 'logging.channels.galat.path' => $this->folder.'/bukan-folder/galat.log']);
        foreach (['stack', 'json', 'galat'] as $channel) {
            app('log')->forgetChannel($channel);
        }
        Route::get('/_uji/galat-lagi', fn () => throw new RuntimeException('x'));

        $this->get('/_uji/galat-lagi')->assertStatus(500)->assertSeeText('Terjadi kesalahan di server kami');
        $this->get('/masuk')->assertOk();
    }
}
