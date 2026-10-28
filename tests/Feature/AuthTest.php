<?php

namespace Tests\Feature;

use App\Mail\KodeVerifikasiMail;
use App\Models\Mitra;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Symfony\Component\Mailer\Exception\TransportException;
use Tests\TestCase;

class AuthTest extends TestCase
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

        Mail::fake();
    }

    private function dataMahasiswa(array $ubah = []): array
    {
        return array_merge([
            'peran' => 'mahasiswa',
            'name' => 'Alya Putri',
            'email' => 'alya@student.polinema.ac.id',
            'nim' => '2441079001',
            'kelas' => 'SIB-3A',
            'jurusan' => 'Teknologi Informasi',
            'program_studi' => 'Sistem Informasi Bisnis',
            'angkatan' => 2024,
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ], $ubah);
    }

    private function dataMitra(array $ubah = []): array
    {
        return array_merge([
            'peran' => 'mitra',
            'name' => 'Budi',
            'email' => 'budi@gmail.com',
            'nama_organisasi' => 'PT Contoh',
            'kontak' => '0341-000',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ], $ubah);
    }

    /** Kirim formulir registrasi, pastikan diarahkan ke halaman kode, lalu ambil kode dari email palsu. */
    private function daftarDanAmbilKode(array $data): string
    {
        $this->post(route('register.store'), $data)->assertRedirect(route('register.verifikasi'));

        return Mail::sent(KodeVerifikasiMail::class)->last()->kode;
    }

    private function buatMitra(string $status): User
    {
        $user = User::factory()->createOne(['role' => User::ROLE_MITRA, 'email' => 'hrd@mitra.example']);
        $mitra = $user->mitra()->create(['nama_organisasi' => 'PT Contoh', 'kontak' => '0341-000']);
        $mitra->status_verifikasi = $status;
        $mitra->save();

        return $user;
    }

    // ---------- Registrasi ----------

    public function test_halaman_masuk_dan_daftar_tampil(): void
    {
        $this->get(route('login'))->assertOk();
        $this->get(route('register'))->assertOk();
        $this->get(route('register', ['peran' => 'mitra']))->assertOk()->assertSee('Nama organisasi');
    }

    public function test_pendaftaran_mengirim_kode_dan_belum_membuat_akun(): void
    {
        $kode = $this->daftarDanAmbilKode($this->dataMahasiswa());

        $this->assertMatchesRegularExpression('/^\d{6}$/', $kode);
        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    }

    public function test_kode_benar_membuat_akun_dan_langsung_masuk(): void
    {
        $kode = $this->daftarDanAmbilKode($this->dataMahasiswa());

        $this->post(route('register.konfirmasi'), ['kode' => $kode])
            ->assertRedirect(route('profil.show'));

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'alya@student.polinema.ac.id', 'role' => 'mahasiswa']);
        $this->assertDatabaseHas('mahasiswa', ['nim' => '2441079001']);
        $this->assertNotSame('rahasia123', User::first()->password); // tersimpan sebagai hash
    }

    public function test_kode_salah_ditolak_dan_lima_kali_salah_membatalkan_pendaftaran(): void
    {
        $kode = $this->daftarDanAmbilKode($this->dataMahasiswa());
        $salah = $kode === '000000' ? '111111' : '000000';

        $this->post(route('register.konfirmasi'), ['kode' => $salah])->assertSessionHasErrors('kode');

        for ($i = 0; $i < 4; $i++) {
            $this->post(route('register.konfirmasi'), ['kode' => $salah]);
        }

        // Sesi sudah dihapus, kode yang benar pun tidak berlaku lagi.
        $this->post(route('register.konfirmasi'), ['kode' => $kode])->assertRedirect(route('register'));
        $this->assertDatabaseCount('users', 0);
    }

    public function test_kode_kedaluwarsa_setelah_10_menit(): void
    {
        $kode = $this->daftarDanAmbilKode($this->dataMahasiswa());

        $this->travel(11)->minutes();

        $this->post(route('register.konfirmasi'), ['kode' => $kode])->assertRedirect(route('register'));
        $this->assertDatabaseCount('users', 0);
    }

    public function test_kirim_ulang_membuat_kode_baru_dan_kode_lama_tidak_berlaku(): void
    {
        $lama = $this->daftarDanAmbilKode($this->dataMahasiswa());

        $this->post(route('register.kirim-ulang'))->assertSessionHas('status');
        $baru = Mail::sent(KodeVerifikasiMail::class)->last()->kode;

        if ($lama !== $baru) {
            $this->post(route('register.konfirmasi'), ['kode' => $lama])->assertSessionHasErrors('kode');
        }

        $this->post(route('register.konfirmasi'), ['kode' => $baru])->assertRedirect(route('profil.show'));
    }

    public function test_kirim_kode_dibatasi_tiga_kali_per_email(): void
    {
        $this->daftarDanAmbilKode($this->dataMahasiswa());
        $this->post(route('register.kirim-ulang'));
        $this->post(route('register.kirim-ulang'));

        $this->post(route('register.kirim-ulang'))->assertSessionHasErrors('kode');
        Mail::assertSent(KodeVerifikasiMail::class, 3);
    }

    public function test_kode_harus_6_angka_dan_halaman_verifikasi_butuh_sesi(): void
    {
        $this->get(route('register.verifikasi'))->assertRedirect(route('register'));

        $this->daftarDanAmbilKode($this->dataMahasiswa());
        $this->post(route('register.konfirmasi'), ['kode' => '12ab'])->assertSessionHasErrors('kode');
    }

    public function test_mahasiswa_dan_dosen_ditolak_bila_email_bukan_polinema(): void
    {
        $tidakValid = [
            'alya@gmail.com',
            'alya@mail.polinema.ac.id',      // subdomain tidak termasuk
            'alya@student.polinema.ac.id.evil.com',     // domain palsu
            'alya@evilstudent.polinema.ac.id',          // akhiran mirip, domain berbeda
            'alya@x.student.polinema.ac.id',            // subdomain dari domain mahasiswa
            'alya@polinema.ac.id',                      // domain milik dosen, bukan mahasiswa
            'alya@student.polinema.ac.id@gmail.com',    // dua tanda @
        ];

        foreach ($tidakValid as $email) {
            $this->post(route('register.store'), $this->dataMahasiswa(['email' => $email]))
                ->assertSessionHasErrors('email');
        }

        $this->post(route('register.store'), [
            'peran' => 'dosen',
            'name' => 'Dr. Rina',
            'email' => 'rina@gmail.com',
            'nidn' => '0701019001',
            'jurusan' => 'TI',
            'program_studi' => 'SIB',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
        Mail::assertNothingSent(); // email tidak valid tidak boleh memicu pengiriman kode
    }

    public function test_email_polinema_huruf_besar_dinormalkan(): void
    {
        $kode = $this->daftarDanAmbilKode($this->dataMahasiswa(['email' => 'ALYA@Student.Polinema.AC.ID']));

        $this->post(route('register.konfirmasi'), ['kode' => $kode])
            ->assertRedirect(route('profil.show'));

        $this->assertDatabaseHas('users', ['email' => 'alya@student.polinema.ac.id']);
    }

    public function test_peran_admin_tidak_bisa_dipilih_lewat_registrasi(): void
    {
        $this->post(route('register.store'), $this->dataMahasiswa(['peran' => 'admin']))
            ->assertSessionHasErrors('peran');

        $this->assertDatabaseCount('users', 0);
        Mail::assertNothingSent();
    }

    public function test_mitra_boleh_email_bebas_berstatus_pending_dan_tidak_langsung_masuk(): void
    {
        $kode = $this->daftarDanAmbilKode($this->dataMitra());

        $this->post(route('register.konfirmasi'), ['kode' => $kode])->assertRedirect(route('login'));

        $this->assertGuest();
        $this->assertDatabaseHas('users', ['email' => 'budi@gmail.com', 'role' => 'mitra']);
        $this->assertDatabaseHas('mitra', ['nama_organisasi' => 'PT Contoh', 'status_verifikasi' => 'pending']);
    }

    public function test_data_tidak_valid_ditolak_dan_tidak_menyisakan_akun(): void
    {
        $this->post(route('register.store'), $this->dataMahasiswa(['password_confirmation' => 'beda', 'angkatan' => 1990]))
            ->assertSessionHasErrors(['password', 'angkatan']);

        $this->assertDatabaseCount('users', 0);
        Mail::assertNothingSent();
    }

    // ---------- Login ----------

    public function test_mahasiswa_bisa_login_dan_logout(): void
    {
        $user = User::factory()->createOne(['email' => 'alya@polinema.ac.id']);

        $this->post(route('login.store'), ['email' => 'ALYA@polinema.ac.id', 'password' => 'password'])
            ->assertRedirect(route('profil.show'));
        $this->assertAuthenticatedAs($user);

        $this->post(route('logout'))->assertRedirect(route('home'));
        $this->assertGuest();
    }

    public function test_password_salah_ditolak(): void
    {
        User::factory()->create(['email' => 'alya@polinema.ac.id']);

        $this->post(route('login.store'), ['email' => 'alya@polinema.ac.id', 'password' => 'salah'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_mitra_pending_tidak_bisa_login_tetapi_mitra_disetujui_bisa(): void
    {
        $this->buatMitra(Mitra::STATUS_PENDING);

        $this->post(route('login.store'), ['email' => 'hrd@mitra.example', 'password' => 'password'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();

        Mitra::query()->update(['status_verifikasi' => Mitra::STATUS_DISETUJUI]);

        $this->post(route('login.store'), ['email' => 'hrd@mitra.example', 'password' => 'password'])
            ->assertRedirect(route('mitra.dashboard'));
        $this->assertAuthenticated();
    }

    public function test_akun_nonaktif_tidak_bisa_login(): void
    {
        User::factory()->create(['email' => 'alya@polinema.ac.id', 'status' => User::STATUS_NONAKTIF]);

        $this->post(route('login.store'), ['email' => 'alya@polinema.ac.id', 'password' => 'password'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_login_dibatasi_setelah_lima_percobaan_gagal(): void
    {
        User::factory()->create(['email' => 'alya@polinema.ac.id']);

        for ($i = 0; $i < 5; $i++) {
            $this->post(route('login.store'), ['email' => 'alya@polinema.ac.id', 'password' => 'salah']);
        }

        // Percobaan ke-6 ditolak walau password BENAR.
        $this->post(route('login.store'), ['email' => 'alya@polinema.ac.id', 'password' => 'password'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
        $this->assertStringContainsString(
            'Terlalu banyak percobaan',
            session('errors')->first('email')
        );
    }

    // ---------- Hak akses (RBAC) ----------

    public function test_tamu_dialihkan_ke_halaman_masuk(): void
    {
        $this->get(route('profil.show'))->assertRedirect(route('login'));
        $this->get(route('admin.dashboard'))->assertRedirect(route('login'));
    }

    public function test_peran_hanya_bisa_membuka_halamannya_sendiri(): void
    {
        $mahasiswa = User::factory()->createOne(['role' => User::ROLE_MAHASISWA]);
        $mahasiswa->mahasiswa()->create([
            'nim' => '2441079001',
            'kelas' => 'SIB-3A',
            'jurusan' => 'Teknologi Informasi',
            'program_studi' => 'Sistem Informasi Bisnis',
            'angkatan' => 2024,
        ]);
        $admin = User::factory()->createOne(['role' => User::ROLE_ADMIN]);

        $this->actingAs($mahasiswa)->get(route('profil.show'))->assertOk();
        $this->actingAs($mahasiswa)->get(route('admin.dashboard'))->assertForbidden();
        $this->actingAs($mahasiswa)->get(route('mitra.dashboard'))->assertForbidden();

        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();
        $this->actingAs($admin)->get(route('profil.show'))->assertForbidden();
    }

    public function test_akun_yang_dinonaktifkan_saat_sesi_berjalan_langsung_dikeluarkan(): void
    {
        $user = User::factory()->createOne(['role' => User::ROLE_MAHASISWA]);
        $this->actingAs($user);

        $user->update(['status' => User::STATUS_NONAKTIF]);

        $this->get(route('profil.show'))->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_mitra_yang_verifikasinya_dicabut_saat_sesi_berjalan_dikeluarkan(): void
    {
        $mitra = $this->buatMitra(Mitra::STATUS_DISETUJUI);
        $this->actingAs($mitra)->get(route('mitra.dashboard'))->assertOk();

        Mitra::query()->update(['status_verifikasi' => Mitra::STATUS_DITOLAK]);
        $mitra->unsetRelation('mitra'); // pada request sungguhan, user dimuat ulang tiap request

        $this->get(route('mitra.dashboard'))->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_mahasiswa_boleh_email_student(): void
    {
        $kode = $this->daftarDanAmbilKode($this->dataMahasiswa(['email' => 'alya@student.polinema.ac.id']));

        $this->post(route('register.konfirmasi'), ['kode' => $kode])->assertRedirect(route('profil.show'));
        $this->assertDatabaseHas('users', ['email' => 'alya@student.polinema.ac.id', 'role' => 'mahasiswa']);
    }

    public function test_dosen_dengan_email_student_ditolak(): void
    {
        $this->post(route('register.store'), [
            'peran' => 'dosen',
            'name' => 'Dr. Rina',
            'email' => 'rina@student.polinema.ac.id',
            'nidn' => '0701019001',
            'program_studi' => 'Sistem Informasi Bisnis',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ])->assertSessionHasErrors('email');

        Mail::assertNothingSent();
    }

    public function test_dosen_dengan_email_polinema_bisa_mendaftar(): void
    {
        $kode = $this->daftarDanAmbilKode([
            'peran' => 'dosen',
            'name' => 'Dr. Rina',
            'email' => 'Rina@Polinema.ac.id',
            'nidn' => '0701019001',
            'program_studi' => 'Sistem Informasi Bisnis',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ]);

        $this->post(route('register.konfirmasi'), ['kode' => $kode])->assertRedirect(route('profil.show'));
        $this->assertDatabaseHas('users', ['email' => 'rina@polinema.ac.id', 'role' => 'dosen']);
    }

    // ---------- OTP: sekali pakai, galat pengiriman, pembatasan ----------

    public function test_kode_hanya_berlaku_sekali_dan_akun_tercatat_terverifikasi(): void
    {
        $kode = $this->daftarDanAmbilKode($this->dataMahasiswa());
        $this->post(route('register.konfirmasi'), ['kode' => $kode])->assertRedirect(route('profil.show'));
        $this->assertNotNull(User::sole()->email_verified_at);

        // Kode yang sama dipakai lagi (mis. tombol kirim ditekan dua kali): sesi pendaftaran sudah habis.
        $this->post(route('logout'));
        $this->post(route('register.konfirmasi'), ['kode' => $kode])
            ->assertRedirect(route('register'))->assertSessionHas('galat');
        $this->assertDatabaseCount('users', 1);
    }

    public function test_kode_tidak_disimpan_sebagai_teks_di_sesi(): void
    {
        $kode = $this->daftarDanAmbilKode($this->dataMahasiswa());
        $pending = session('registrasi_pending');

        $this->assertNotSame($kode, $pending['kode']);
        $this->assertTrue(Hash::check($kode, $pending['kode']));
        $this->assertTrue(Hash::check('rahasia123', $pending['data']['password'])); // kata sandi juga hash
    }

    public function test_server_email_gagal_ditangani_tanpa_menghabiskan_jatah_kirim(): void
    {
        Mail::shouldReceive('to')->andThrow(new TransportException('SMTP tidak bisa dihubungi'));

        $this->from(route('register'))->post(route('register.store'), $this->dataMahasiswa())
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors(['email' => 'Email berisi kode verifikasi gagal dikirim. Periksa alamat email Anda, lalu coba lagi beberapa saat lagi.'])
            ->assertSessionHasInput('email', 'alya@student.polinema.ac.id')
            ->assertSessionMissing('registrasi_pending');
        $this->assertSame(0, RateLimiter::attempts('kode-email|alya@student.polinema.ac.id'));
        $this->assertDatabaseCount('users', 0);
    }

    public function test_kirim_ulang_yang_gagal_tidak_mengganti_kode_lama(): void
    {
        $kode = $this->daftarDanAmbilKode($this->dataMahasiswa());

        Mail::shouldReceive('to')->andThrow(new TransportException('SMTP tidak bisa dihubungi'));
        $this->from(route('register.verifikasi'))->post(route('register.kirim-ulang'))->assertSessionHasErrors('kode');

        // Kode lama masih berlaku karena kode baru tidak pernah sampai.
        $this->post(route('register.konfirmasi'), ['kode' => $kode])->assertRedirect(route('profil.show'));
    }

    public function test_pengiriman_kode_dibatasi_per_ip(): void
    {
        foreach (range(1, 6) as $i) {
            $this->post(route('register.store'), $this->dataMahasiswa(['email' => "mhs{$i}@student.polinema.ac.id", 'nim' => "NIM{$i}"]))
                ->assertRedirect(route('register.verifikasi'));
        }

        // Alamat ke-7 dari IP yang sama ditolak walau emailnya baru.
        $this->from(route('register'))->post(route('register.store'), $this->dataMahasiswa(['email' => 'mhs7@student.polinema.ac.id', 'nim' => 'NIM7']))
            ->assertRedirect(route('register'))->assertSessionHasErrors('email');
        Mail::assertSent(KodeVerifikasiMail::class, 6);
    }

    public function test_email_kode_berisi_masa_berlaku_dan_peringatan_tanpa_kode_di_subjek(): void
    {
        $email = new KodeVerifikasiMail('123456', 'Alya', 10);

        $email->assertSeeInHtml('123456')
            ->assertSeeInHtml('10 menit')
            ->assertSeeInHtml('Jangan bagikan kode ini kepada siapa pun')
            ->assertSeeInText('123456')
            ->assertSeeInText('JANGAN BAGIKAN KODE INI');
        $this->assertStringNotContainsString('123456', $email->envelope()->subject);
    }

    // ---------- Login Fortify dan verifikasi email ----------

    public function test_login_dan_logout_ditangani_fortify(): void
    {
        $aksi = fn (string $nama) => app('router')->getRoutes()->getByName($nama)->getActionName();

        $this->assertStringStartsWith('Laravel\Fortify\Http\Controllers\AuthenticatedSessionController', $aksi('login.store'));
        $this->assertStringStartsWith('Laravel\Fortify\Http\Controllers\AuthenticatedSessionController', $aksi('logout'));
        $this->assertSame('/masuk', parse_url(route('login.store'), PHP_URL_PATH));
        $this->assertSame('/keluar', parse_url(route('logout'), PHP_URL_PATH));
    }

    public function test_akun_dengan_email_belum_terverifikasi_tidak_bisa_masuk(): void
    {
        User::factory()->create(['email' => 'alya@student.polinema.ac.id', 'email_verified_at' => null]);

        $this->post(route('login.store'), ['email' => 'alya@student.polinema.ac.id', 'password' => 'password'])
            ->assertSessionHasErrors(['email' => 'Email akun ini belum diverifikasi. Masuk dengan Google memakai email yang sama, atau hubungi admin CampusLink.']);
        $this->assertGuest();
    }

    public function test_akun_belum_terverifikasi_yang_sedang_masuk_dikeluarkan_dari_halaman_terlindungi(): void
    {
        $user = User::factory()->create(['email_verified_at' => null]);

        $this->actingAs($user)->get(route('magang.index'))->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_pesan_login_salah_sama_untuk_email_tidak_terdaftar_dan_akun_nonaktif(): void
    {
        User::factory()->create(['email' => 'nonaktif@student.polinema.ac.id', 'status' => User::STATUS_NONAKTIF]);

        $this->post(route('login.store'), ['email' => 'tidak-ada@student.polinema.ac.id', 'password' => 'password'])
            ->assertSessionHasErrors(['email' => 'Email atau kata sandi salah.']);
        $this->post(route('login.store'), ['email' => 'nonaktif@student.polinema.ac.id', 'password' => 'password'])
            ->assertSessionHasErrors(['email' => 'Email atau kata sandi salah.']);
    }
}
