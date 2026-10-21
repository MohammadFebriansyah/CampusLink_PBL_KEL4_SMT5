<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\GoogleProvider;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Socialite ditiru (mock): yang diuji adalah keputusan server atas data dari Google,
 * bukan alur OAuth ke Google sungguhan.
 */
class GoogleAuthTest extends TestCase
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

    private function tiruGoogle(array $ubah = [], string $id = 'g-1'): void
    {
        $raw = array_merge([
            'sub' => $id,
            'name' => 'Alya Putri',
            'email' => 'alya@polinema.ac.id',
            'email_verified' => true,
            'hd' => 'polinema.ac.id',
        ], $ubah);

        $pengguna = (new SocialiteUser)->setRaw($raw)->map([
            'id' => $id,
            'name' => $raw['name'],
            'email' => $raw['email'],
        ]);

        $provider = Mockery::mock(GoogleProvider::class);
        $provider->shouldReceive('user')->andReturn($pengguna);
        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);
    }

    private function dataMahasiswa(array $ubah = []): array
    {
        return array_merge([
            'peran' => 'mahasiswa',
            'name' => 'Alya Putri',
            'nim' => '2441079001',
            'kelas' => 'SIB-3A',
            'program_studi' => 'Sistem Informasi Bisnis',
            'angkatan' => 2024,
        ], $ubah);
    }

    // ---------- Pengalihan ke Google ----------

    public function test_pengalihan_ke_google_membawa_petunjuk_domain(): void
    {
        $provider = Mockery::mock(GoogleProvider::class);
        $provider->shouldReceive('with')->once()
            ->with(['hd' => 'polinema.ac.id', 'prompt' => 'select_account'])->andReturnSelf();
        $provider->shouldReceive('redirect')->once()->andReturn(redirect('https://accounts.google.com/o/oauth2/auth'));
        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $this->get(route('google.redirect', ['peran' => 'dosen']))->assertRedirect('https://accounts.google.com/o/oauth2/auth');
    }
    public function test_pengalihan_untuk_mahasiswa_tanpa_petunjuk_domain(): void
    {
        $provider = \Mockery::mock(\Laravel\Socialite\Two\GoogleProvider::class);
        $provider->shouldReceive('with')->once()->with(['prompt' => 'select_account'])->andReturnSelf();
        $provider->shouldReceive('redirect')->once()->andReturn(redirect('https://accounts.google.com/o/oauth2/auth'));
        \Laravel\Socialite\Facades\Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $this->get(route('google.redirect', ['peran' => 'mahasiswa']))
            ->assertRedirect('https://accounts.google.com/o/oauth2/auth');
    }

    // ---------- Pemeriksaan akun dari Google ----------

    public static function akunTidakValid(): array
    {
        return [
            'tanpa klaim hd (akun Google pribadi)' => [['hd' => null]],
            'hd domain lain' => [['hd' => 'gmail.com', 'email' => 'alya@gmail.com']],
            'email belum diverifikasi Google' => [['email_verified' => false]],
            'email bukan polinema walau hd polinema' => [['email' => 'alya@gmail.com']],
            'subdomain polinema' => [['email' => 'alya@mail.polinema.ac.id']],
        ];
    }

    #[DataProvider('akunTidakValid')]
    public function test_akun_google_non_kampus_tidak_bisa_menjadi_mahasiswa_atau_dosen(array $ubah): void
    {
        $this->tiruGoogle($ubah);
        $respons = $this->get(route('google.callback'));

        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);

        if (session()->has('google_pending')) {
            // Lolos pemeriksaan email tetapi bukan akun kampus: hanya boleh mendaftar sebagai mitra.
            $respons->assertRedirect(route('google.lengkapi'));
            $this->assertFalse(session('google_pending.kampus'));

            $this->post(route('google.simpan'), $this->dataMahasiswa())
                ->assertRedirect(route('google.lengkapi', ['peran' => 'mitra']))
                ->assertSessionHas('galat');
        } else {
            $respons->assertRedirect(route('login'))->assertSessionHas('galat');
        }

        $this->assertDatabaseCount('users', 0);
    }

    public function test_kegagalan_dari_google_ditangani_tanpa_error(): void
    {
        $provider = Mockery::mock(GoogleProvider::class);
        $provider->shouldReceive('user')->andThrow(new \RuntimeException('state tidak cocok'));
        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $this->get(route('google.callback'))->assertRedirect(route('login'))->assertSessionHas('galat');
        $this->assertGuest();
    }

    // ---------- Pendaftaran baru lewat Google ----------

    public function test_akun_baru_diarahkan_untuk_melengkapi_data_dan_belum_dibuat(): void
    {
        $this->tiruGoogle();

        $this->get(route('google.callback'))->assertRedirect(route('google.lengkapi'));

        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
        $this->assertSame('alya@polinema.ac.id', session('google_pending.email'));

        $this->get(route('google.lengkapi'))->assertOk()->assertSee('alya@polinema.ac.id');
    }

    public function test_melengkapi_data_mahasiswa_membuat_akun_tanpa_password_dan_langsung_masuk(): void
    {
        $this->tiruGoogle();
        $this->get(route('google.callback'));

        $this->post(route('google.simpan'), $this->dataMahasiswa())->assertRedirect(route('profil.show'));

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'email' => 'alya@polinema.ac.id',
            'role' => 'mahasiswa',
            'google_id' => 'g-1',
        ]);
        $this->assertNull(User::where('email', 'alya@polinema.ac.id')->value('password'));
        $this->assertDatabaseHas('mahasiswa', ['nim' => '2441079001', 'jurusan' => 'Teknologi Informasi']);
        $this->assertNull(session('google_pending')); // sesi sementara dibersihkan
    }

    public function test_melengkapi_data_dosen(): void
    {
        $this->tiruGoogle(['name' => 'Rina', 'email' => 'rina@polinema.ac.id'], 'g-2');
        $this->get(route('google.callback'));

        $this->post(route('google.simpan'), [
            'peran' => 'dosen',
            'name' => 'Dr. Rina',
            'nidn' => '0701019001',
            'program_studi' => 'Teknik Informatika',
        ])->assertRedirect(route('profil.show'));

        $this->assertDatabaseHas('users', ['email' => 'rina@polinema.ac.id', 'role' => 'dosen']);
        $this->assertDatabaseHas('dosen', ['nidn' => '0701019001', 'jurusan' => 'Teknologi Informasi']);
    }

    public function test_peran_admin_atau_mitra_tidak_bisa_dipilih_lewat_google(): void
    {
        $this->tiruGoogle();
        $this->get(route('google.callback'));

        foreach (['admin'] as $peran) {
            $this->post(route('google.simpan'), $this->dataMahasiswa(['peran' => $peran]))
                ->assertSessionHasErrors('peran');
        }

        $this->assertDatabaseCount('users', 0);
    }

    public function test_email_tidak_bisa_dipalsukan_lewat_formulir(): void
    {
        $this->tiruGoogle();
        $this->get(route('google.callback'));

        $this->post(route('google.simpan'), $this->dataMahasiswa(['email' => 'orang-lain@polinema.ac.id', 'role' => 'admin']));

        $this->assertDatabaseHas('users', ['email' => 'alya@polinema.ac.id', 'role' => 'mahasiswa']);
        $this->assertDatabaseMissing('users', ['email' => 'orang-lain@polinema.ac.id']);
    }

    public function test_nim_ganda_ditolak(): void
    {
        $lain = User::factory()->create(['email' => 'lain@polinema.ac.id']);
        $lain->mahasiswa()->create([
            'nim' => '2441079001',
            'kelas' => 'SIB-3A',
            'jurusan' => 'Teknologi Informasi',
            'program_studi' => 'Sistem Informasi Bisnis',
            'angkatan' => 2024,
        ]);

        $this->tiruGoogle();
        $this->get(route('google.callback'));

        $this->post(route('google.simpan'), $this->dataMahasiswa())->assertSessionHasErrors('nim');
        $this->assertDatabaseMissing('users', ['email' => 'alya@polinema.ac.id']);
    }

    public function test_melengkapi_tanpa_sesi_google_dialihkan_ke_login(): void
    {
        $this->post(route('google.simpan'), $this->dataMahasiswa())
            ->assertRedirect(route('login'))
            ->assertSessionHas('galat');

        $this->assertDatabaseCount('users', 0);
    }

    public function test_sesi_melengkapi_data_kedaluwarsa_setelah_15_menit(): void
    {
        $this->tiruGoogle();
        $this->get(route('google.callback'));

        $this->travel(20)->minutes();

        $this->post(route('google.simpan'), $this->dataMahasiswa())->assertRedirect(route('login'));
        $this->assertDatabaseCount('users', 0);
    }

    // ---------- Masuk dengan akun yang sudah ada ----------

    public function test_akun_yang_sudah_terhubung_google_langsung_masuk(): void
    {
        $user = User::factory()->create(['email' => 'alya@polinema.ac.id']);
        $user->google_id = 'g-1';
        $user->save();

        $this->tiruGoogle();

        $this->get(route('google.callback'))->assertRedirect(route('profil.show'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_akun_password_lama_ditautkan_dan_password_lamanya_dihapus(): void
    {
        // Skenario penyamaran: seseorang mendaftar dengan email polinema orang lain dan password buatannya.
        $user = User::factory()->create(['email' => 'alya@polinema.ac.id']); // password "password"
        $this->tiruGoogle();

        $this->get(route('google.callback'))->assertRedirect(route('profil.show'));

        $this->assertAuthenticatedAs($user);
        $this->assertSame('g-1', $user->fresh()->google_id);
        $this->assertNull(User::where('email', 'alya@polinema.ac.id')->value('password'));

        // Password lama tidak bisa dipakai lagi.
        $this->post(route('logout'));
        $this->post(route('login.store'), ['email' => 'alya@polinema.ac.id', 'password' => 'password'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_admin_dan_mitra_tidak_bisa_masuk_lewat_google(): void
    {
        User::factory()->create(['email' => 'alya@polinema.ac.id', 'role' => User::ROLE_ADMIN]);
        $this->tiruGoogle();

        $this->get(route('google.callback'))->assertRedirect(route('login'))->assertSessionHas('galat');

        $this->assertGuest();
        $this->assertNull(User::where('email', 'alya@polinema.ac.id')->value('google_id'));
    }

    public function test_akun_nonaktif_tidak_bisa_masuk_lewat_google(): void
    {
        User::factory()->create(['email' => 'alya@polinema.ac.id', 'status' => User::STATUS_NONAKTIF]);
        $this->tiruGoogle();

        $this->get(route('google.callback'))->assertRedirect(route('login'))->assertSessionHas('galat');
        $this->assertGuest();
    }

    public function test_akun_google_tanpa_password_tidak_bisa_masuk_dengan_password(): void
    {
        $user = User::factory()->create(['email' => 'alya@polinema.ac.id']);
        User::whereKey($user->id)->update(['password' => null, 'google_id' => 'g-1']);

        foreach (['', 'password', 'apa-saja'] as $coba) {
            $this->post(route('login.store'), ['email' => 'alya@polinema.ac.id', 'password' => $coba])
                ->assertSessionHasErrors();
        }

        $this->assertGuest();
    }

    // ---------- Menghubungkan akun saat sudah masuk ----------

    public function test_pengguna_yang_sudah_masuk_bisa_menghubungkan_google_dan_password_tetap(): void
    {
        $user = User::factory()->create(['email' => 'alya@polinema.ac.id']);
        $this->tiruGoogle();

        $this->actingAs($user)->get(route('google.callback'))
            ->assertRedirect(route('profil.show'))
            ->assertSessionHas('status');

        $user->refresh();
        $this->assertTrue($user->terverifikasiGoogle());
        $this->assertNotNull($user->password); // tidak dihapus: pemiliknya sendiri yang menautkan
    }

    public function test_menghubungkan_google_dengan_email_berbeda_ditolak(): void
    {
        $user = User::factory()->create(['email' => 'alya@polinema.ac.id']);
        $this->tiruGoogle(['email' => 'lain@polinema.ac.id']);

        $this->actingAs($user)->get(route('google.callback'))->assertSessionHas('galat');

        $this->assertFalse($user->fresh()->terverifikasiGoogle());
    }

    public function test_google_id_tidak_bisa_dipakai_dua_akun(): void
    {
        $pertama = User::factory()->create(['email' => 'pertama@polinema.ac.id']);
        $pertama->google_id = 'g-1';
        $pertama->save();

        $kedua = User::factory()->create(['email' => 'alya@polinema.ac.id']);
        $this->tiruGoogle(); // id g-1 tetapi email alya

        // Google id g-1 milik akun pertama, jadi pengguna yang masuk sebagai "kedua" tidak boleh mengklaimnya.
        $this->actingAs($kedua)->get(route('google.callback'))->assertSessionHas('galat');

        $this->assertFalse($kedua->fresh()->terverifikasiGoogle());
    }

    // ---------- Tampilan ----------

    public function test_tombol_google_tampil_di_login_dan_semua_registrasi(): void
    {
        $this->get(route('login'))->assertSee('Masuk dengan Google');
        $this->get(route('register', ['peran' => 'mahasiswa']))->assertSee('Daftar dengan Google');
        $this->get(route('register', ['peran' => 'dosen']))->assertSee('Daftar dengan Google');
        $this->get(route('register', ['peran' => 'mitra']))->assertSee('Daftar dengan Google');
    }

    public function test_mitra_bisa_daftar_dengan_akun_google_apa_pun_dan_menunggu_admin(): void
    {
        $this->tiruGoogle(['email' => 'budi@gmail.com', 'hd' => null]);
        $this->get(route('google.callback'))->assertRedirect(route('google.lengkapi'));

        $this->post(route('google.simpan'), [
            'peran' => 'mitra',
            'name' => 'Budi',
            'nama_organisasi' => 'PT Contoh',
            'kontak' => '0341-000',
        ])->assertRedirect(route('login'));

        $this->assertGuest();
        $this->assertDatabaseHas('users', ['email' => 'budi@gmail.com', 'role' => 'mitra']);
        $this->assertDatabaseHas('mitra', ['nama_organisasi' => 'PT Contoh', 'status_verifikasi' => 'pending']);
    }

    public function test_akun_student_boleh_mahasiswa_tetapi_tidak_dosen(): void
    {
        $this->tiruGoogle(['email' => 'alya@student.polinema.ac.id', 'hd' => 'student.polinema.ac.id']);
        $this->get(route('google.callback'))->assertRedirect(route('google.lengkapi'));

        $this->post(route('google.simpan'), [
            'peran' => 'dosen',
            'name' => 'Rina',
            'nidn' => '0701019001',
            'program_studi' => 'Sistem Informasi Bisnis',
        ])->assertRedirect(route('google.lengkapi', ['peran' => 'dosen']))->assertSessionHas('galat');
        $this->assertDatabaseCount('users', 0);

        $this->post(route('google.simpan'), $this->dataMahasiswa())->assertRedirect(route('profil.show'));
        $this->assertDatabaseHas('users', ['email' => 'alya@student.polinema.ac.id', 'role' => 'mahasiswa']);
    }

    public function test_profil_menawarkan_verifikasi_google_bagi_yang_belum(): void
    {
        $user = User::factory()->create(['email' => 'alya@polinema.ac.id']);
        $user->mahasiswa()->create([
            'nim' => '2441079001',
            'kelas' => 'SIB-3A',
            'jurusan' => 'Teknologi Informasi',
            'program_studi' => 'Sistem Informasi Bisnis',
            'angkatan' => 2024,
        ]);

        $this->actingAs($user)->get(route('profil.show'))->assertSee('Verifikasi dengan Google');

        $user->google_id = 'g-1';
        $user->save();

        $this->actingAs($user->fresh())->get(route('profil.show'))
            ->assertSee('Terverifikasi dengan Google')
            ->assertDontSee('Verifikasi dengan Google');
    }
}
