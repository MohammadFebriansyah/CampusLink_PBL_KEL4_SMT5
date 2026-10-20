<?php

namespace Tests\Feature\Auth;

use App\Http\Middleware\EnsureRole;
use App\Models\Mitra;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function buatMitra(string $statusVerifikasi): User
    {
        $user = User::factory()->create([
            'role' => User::ROLE_MITRA,
            'email' => 'hrd@mitra.example',
            'status' => User::STATUS_AKTIF,
        ]);

        $mitra = $user->mitra()->create([
            'nama_organisasi' => 'PT Mitra Contoh',
            'kontak' => '08123456789',
        ]);
        $mitra->status_verifikasi = $statusVerifikasi;
        $mitra->save();

        return $user;
    }

    public function test_halaman_masuk_dapat_ditampilkan(): void
    {
        $response = $this->get(route('login'));
        $response->assertOk();
        $response->assertSee('Masuk ke CampusLink');
        $response->assertSee('Mahasiswa dan dosen dapat memakai akun Google kampus.');
        $response->assertSee('Masuk dengan Google');
        $response->assertSee('Mahasiswa dan dosen wajib memakai @polinema.ac.id, mitra boleh memakai Gmail.');
        $response->assertSee('atau dengan email dan kata sandi');
        $response->assertSee('Email');
        $response->assertSee('Kata sandi');
        $response->assertSee('Masuk');
        $response->assertSee('Belum punya akun?');
        $response->assertSee('Daftar');

        // Kompatibilitas rute lama /login
        $this->get('/login')->assertOk();
    }

    public function test_login_berhasil_dan_logout(): void
    {
        $user = User::factory()->create([
            'email' => 'mahasiswa@polinema.ac.id',
            'password' => 'password123',
            'status' => User::STATUS_AKTIF,
        ]);

        $response = $this->post(route('login.store'), [
            'email' => 'mahasiswa@polinema.ac.id',
            'password' => 'password123',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('profil.show'));

        // Logout
        $logoutResponse = $this->post(route('logout'));
        $logoutResponse->assertRedirect(route('home'));
        $this->assertGuest();
    }

    public function test_email_huruf_besar_dinormalkan_saat_login(): void
    {
        $user = User::factory()->create([
            'email' => 'mahasiswa@polinema.ac.id',
            'password' => 'password123',
            'status' => User::STATUS_AKTIF,
        ]);

        $response = $this->post(route('login.store'), [
            'email' => 'MAHASISWA@POLINEMA.AC.ID',
            'password' => 'password123',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('profil.show'));
    }

    public function test_password_salah_ditolak(): void
    {
        User::factory()->create([
            'email' => 'mahasiswa@polinema.ac.id',
            'password' => 'password123',
            'status' => User::STATUS_AKTIF,
        ]);

        $response = $this->post(route('login.store'), [
            'email' => 'mahasiswa@polinema.ac.id',
            'password' => 'password-salah',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    public function test_pembatasan_lima_percobaan_login_per_menit(): void
    {
        User::factory()->create([
            'email' => 'mahasiswa@polinema.ac.id',
            'password' => 'password123',
            'status' => User::STATUS_AKTIF,
        ]);

        for ($i = 0; $i < 5; $i++) {
            $this->post(route('login.store'), [
                'email' => 'mahasiswa@polinema.ac.id',
                'password' => 'salah',
            ]);
        }

        // Percobaan ke-6 ditolak meskipun password BENAR
        $response = $this->post(route('login.store'), [
            'email' => 'mahasiswa@polinema.ac.id',
            'password' => 'password123',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
        $this->assertStringContainsString(
            'Terlalu banyak percobaan masuk',
            session('errors')->first('email')
        );
    }

    public function test_pembatasan_login_memperhitungkan_ip_dan_email(): void
    {
        User::factory()->create([
            'email' => 'mahasiswa@polinema.ac.id',
            'password' => 'password123',
            'status' => User::STATUS_AKTIF,
        ]);

        // IP Penyerang gagal 5 kali
        for ($i = 0; $i < 5; $i++) {
            $this->withServerVariables(['REMOTE_ADDR' => '192.168.1.100'])
                ->post(route('login.store'), [
                    'email' => 'mahasiswa@polinema.ac.id',
                    'password' => 'salah',
                ]);
        }

        // IP Sah dari perangkat lain tetap bisa masuk dengan password benar
        $response = $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.1'])
            ->post(route('login.store'), [
                'email' => 'mahasiswa@polinema.ac.id',
                'password' => 'password123',
            ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('profil.show'));
    }

    public function test_akun_nonaktif_tidak_bisa_login(): void
    {
        User::factory()->create([
            'email' => 'nonaktif@polinema.ac.id',
            'password' => 'password123',
            'status' => User::STATUS_NONAKTIF,
        ]);

        $response = $this->post(route('login.store'), [
            'email' => 'nonaktif@polinema.ac.id',
            'password' => 'password123',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
        // Pesan harus generik agar tidak membocorkan keberadaan akun
        $this->assertSame('Email atau kata sandi salah.', session('errors')->first('email'));
    }

    public function test_mitra_pending_dan_ditolak_tidak_bisa_login_tetapi_disetujui_bisa(): void
    {
        // 1. Mitra status pending
        $user = $this->buatMitra(Mitra::STATUS_PENDING);

        $responsePending = $this->post(route('login.store'), [
            'email' => 'hrd@mitra.example',
            'password' => 'password',
        ]);

        $this->assertGuest();
        $responsePending->assertSessionHasErrors('email');
        $this->assertSame('Akun mitra Anda masih menunggu verifikasi admin.', session('errors')->first('email'));

        // 2. Mitra status ditolak
        Mitra::where('user_id', $user->id)->update(['status_verifikasi' => Mitra::STATUS_DITOLAK]);
        $user->unsetRelation('mitra');

        $responseDitolak = $this->post(route('login.store'), [
            'email' => 'hrd@mitra.example',
            'password' => 'password',
        ]);

        $this->assertGuest();
        $responseDitolak->assertSessionHasErrors('email');
        $this->assertSame('Pendaftaran mitra Anda tidak disetujui. Hubungi admin CampusLink.', session('errors')->first('email'));

        // 3. Mitra status disetujui
        Mitra::where('user_id', $user->id)->update(['status_verifikasi' => Mitra::STATUS_DISETUJUI]);
        $user->unsetRelation('mitra');

        $responseDisetujui = $this->post(route('login.store'), [
            'email' => 'hrd@mitra.example',
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $responseDisetujui->assertRedirect(route('mitra.dashboard'));
    }

    public function test_middleware_ensure_role_menolak_role_yang_tidak_berhak(): void
    {
        Route::middleware(['web', 'auth', EnsureRole::class . ':admin'])->get('/_test_admin_only', fn () => 'admin_page');
        Route::middleware(['web', 'auth', EnsureRole::class . ':mahasiswa,dosen'])->get('/_test_civitas_only', fn () => 'civitas_page');

        $mahasiswa = User::factory()->create(['role' => User::ROLE_MAHASISWA, 'status' => User::STATUS_AKTIF]);
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN, 'status' => User::STATUS_AKTIF]);

        // Mahasiswa mencoba rute admin -> 403 Forbidden
        $this->actingAs($mahasiswa)->get('/_test_admin_only')->assertForbidden();

        // Mahasiswa ke rute civitas -> 200 OK
        $this->actingAs($mahasiswa)->get('/_test_civitas_only')->assertOk();

        // Admin ke rute admin -> 200 OK
        $this->actingAs($admin)->get('/_test_admin_only')->assertOk();

        // Admin ke rute civitas -> 403 Forbidden
        $this->actingAs($admin)->get('/_test_civitas_only')->assertForbidden();
    }

    public function test_middleware_ensure_role_mengeluarkan_akun_yang_dinonaktifkan_di_tengah_sesi(): void
    {
        Route::middleware(['web', 'auth', EnsureRole::class . ':mahasiswa'])->get('/_test_sesi_mahasiswa', fn () => 'ok');

        $user = User::factory()->create(['role' => User::ROLE_MAHASISWA, 'status' => User::STATUS_AKTIF]);

        $this->actingAs($user)->get('/_test_sesi_mahasiswa')->assertOk();

        // Akun dinonaktifkan oleh admin
        $user->update(['status' => User::STATUS_NONAKTIF]);

        // Request berikutnya langsung ditendang ke halaman login
        $response = $this->get('/_test_sesi_mahasiswa');
        $response->assertRedirect(route('login'));
        $this->assertGuest();
        $response->assertSessionHasErrors('email');
        $this->assertSame('Akun ini dinonaktifkan. Hubungi admin CampusLink.', session('errors')->first('email'));
    }

    public function test_middleware_ensure_role_mengeluarkan_mitra_yang_verifikasinya_dicabut_di_tengah_sesi(): void
    {
        Route::middleware(['web', 'auth', EnsureRole::class . ':mitra'])->get('/_test_sesi_mitra', fn () => 'ok');

        $mitraUser = $this->buatMitra(Mitra::STATUS_DISETUJUI);

        $this->actingAs($mitraUser)->get('/_test_sesi_mitra')->assertOk();

        // Verifikasi mitra dicabut
        Mitra::where('user_id', $mitraUser->id)->update(['status_verifikasi' => Mitra::STATUS_DITOLAK]);
        $mitraUser->unsetRelation('mitra');

        $response = $this->get('/_test_sesi_mitra');
        $response->assertRedirect(route('login'));
        $this->assertGuest();
        $response->assertSessionHasErrors('email');
        $this->assertSame('Pendaftaran mitra Anda tidak disetujui. Hubungi admin CampusLink.', session('errors')->first('email'));
    }
}
