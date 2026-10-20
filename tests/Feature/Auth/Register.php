<?php

namespace Tests\Feature\Auth;

use App\Mail\KodeVerifikasiMail;
use App\Models\Mitra;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
        Mail::fake();
    }

    private function dataMahasiswa(array $ubah = []): array
    {
        return array_merge([
            'peran' => 'mahasiswa',
            'name' => 'Alya Putri',
            'email' => 'alya@polinema.ac.id',
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

    public function test_halaman_daftar_tampil(): void
    {
        $this->get(route('register'))->assertOk();
        $this->get(route('register', ['peran' => 'mitra']))->assertOk()->assertSee('Nama organisasi');
        $this->get(route('register', ['peran' => 'dosen']))->assertOk()->assertSee('NIDN');
        $this->get(route('register', ['peran' => 'mahasiswa']))->assertOk()->assertSee('NIM');
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
        $this->assertDatabaseHas('users', ['email' => 'alya@polinema.ac.id', 'role' => 'mahasiswa']);
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
            'alya@polinema.ac.id.evil.com',     // domain palsu
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
            'jurusan' => 'Teknologi Informasi',
            'program_studi' => 'Sistem Informasi Bisnis',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
        Mail::assertNothingSent(); // email tidak valid tidak boleh memicu pengiriman kode
    }

    public function test_email_polinema_huruf_besar_dinormalkan(): void
    {
        $kode = $this->daftarDanAmbilKode($this->dataMahasiswa(['email' => 'ALYA@Polinema.AC.ID']));

        $this->post(route('register.konfirmasi'), ['kode' => $kode])
            ->assertRedirect(route('profil.show'));

        $this->assertDatabaseHas('users', ['email' => 'alya@polinema.ac.id']);
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
}
