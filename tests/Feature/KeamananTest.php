<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KeamananTest extends TestCase
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

    public function test_header_keamanan_dasar_ada_di_setiap_respons_web(): void
    {
        foreach (['/', '/masuk', '/tidak-ada-halaman-ini'] as $url) {
            $this->get($url)
                ->assertHeader('X-Content-Type-Options', 'nosniff')
                ->assertHeader('X-Frame-Options', 'DENY')
                ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        }
    }

    public function test_hsts_hanya_dikirim_lewat_https(): void
    {
        $this->get('/masuk')->assertHeaderMissing('Strict-Transport-Security');
        $this->get('https://localhost/masuk')->assertHeader('Strict-Transport-Security');
    }

    public function test_cookie_sesi_httponly_dan_samesite(): void
    {
        $cookie = collect($this->get('/masuk')->headers->getCookies())
            ->first(fn ($c) => $c->getName() === config('session.cookie'));

        $this->assertNotNull($cookie);
        $this->assertTrue($cookie->isHttpOnly());
        $this->assertSame('lax', $cookie->getSameSite());
    }

    // Penolakan CSRF (419) tidak bisa diuji di PHPUnit: Laravel mematikannya saat uji berjalan.
    // Dicek manual dengan curl, lihat SERAH-TERIMA.md.
}
