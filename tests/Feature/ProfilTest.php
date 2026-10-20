<?php

namespace Tests\Feature;

use App\Models\Keahlian;
use App\Models\Mitra;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Catatan: UploadedFile::fake()->image() membutuhkan ekstensi PHP GD.
 */
class ProfilTest extends TestCase
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
        Storage::fake('public');
    }

    private function buatMahasiswa(): User
    {
        $user = User::factory()->create(['role' => User::ROLE_MAHASISWA, 'email' => 'alya@polinema.ac.id']);
        $user->mahasiswa()->create([
            'nim' => '2441079001', 'kelas' => 'SIB-3A', 'jurusan' => 'Teknologi Informasi',
            'program_studi' => 'Sistem Informasi Bisnis', 'angkatan' => 2024,
        ]);

        return $user;
    }

    private function buatDosen(): User
    {
        $user = User::factory()->create(['role' => User::ROLE_DOSEN, 'email' => 'rina@polinema.ac.id']);
        $user->dosen()->create([
            'nidn' => '0701019001', 'jurusan' => 'Teknologi Informasi', 'program_studi' => 'Teknik Informatika',
        ]);

        return $user;
    }

    private function dataProfil(array $ubah = []): array
    {
        return array_merge([
            'name' => 'Alya Putri Ramadhani',
            'program_studi' => 'Akuntansi Manajemen',
            'kelas' => 'AK-3A',
            'angkatan' => 2024,
            'kontak' => '081200000001',
            'tentang' => 'Suka data.',
            'tampil_di_direktori' => '1',
        ], $ubah);
    }

    // ---------- Mahasiswa ----------

    public function test_halaman_profil_tampil(): void
    {
        $user = $this->buatMahasiswa();

        $this->actingAs($user)->get(route('profil.show'))
            ->assertOk()
            ->assertSee('2441079001')
            ->assertSee('alya@polinema.ac.id');
    }

    public function test_mahasiswa_memperbarui_profil_dan_jurusan_diturunkan_dari_prodi(): void
    {
        $user = $this->buatMahasiswa();

        $this->actingAs($user)->put(route('profil.update'), $this->dataProfil())
            ->assertRedirect(route('profil.show'))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('mahasiswa', [
            'user_id' => $user->id,
            'program_studi' => 'Akuntansi Manajemen',
            'jurusan' => 'Akuntansi',          // diturunkan, bukan dikirim pengguna
            'kelas' => 'AK-3A',
        ]);
        $this->assertSame('Alya Putri Ramadhani', $user->fresh()->name);
    }

    public function test_jurusan_kiriman_pengguna_diabaikan(): void
    {
        $user = $this->buatMahasiswa();

        $this->actingAs($user)->put(route('profil.update'), $this->dataProfil(['jurusan' => 'Teknik Sipil']));

        $this->assertDatabaseHas('mahasiswa', ['user_id' => $user->id, 'jurusan' => 'Akuntansi']);
    }

    public function test_program_studi_di_luar_daftar_ditolak(): void
    {
        $user = $this->buatMahasiswa();

        $this->actingAs($user)->put(route('profil.update'), $this->dataProfil(['program_studi' => 'Prodi Karangan']))
            ->assertSessionHasErrors('program_studi');

        $this->assertDatabaseHas('mahasiswa', ['user_id' => $user->id, 'program_studi' => 'Sistem Informasi Bisnis']);
    }

    public function test_email_nim_dan_peran_tidak_bisa_diubah_lewat_profil(): void
    {
        $user = $this->buatMahasiswa();

        $this->actingAs($user)->put(route('profil.update'), $this->dataProfil([
            'email' => 'baru@gmail.com',
            'nim' => '999',
            'role' => 'admin',
            'status' => 'nonaktif',
        ]))->assertRedirect(route('profil.show'));

        $user->refresh();
        $this->assertSame('alya@polinema.ac.id', $user->email);
        $this->assertSame('mahasiswa', $user->role);
        $this->assertSame('aktif', $user->status);
        $this->assertDatabaseHas('mahasiswa', ['user_id' => $user->id, 'nim' => '2441079001']);
    }

    public function test_keahlian_disinkronkan_dan_id_palsu_ditolak(): void
    {
        $user = $this->buatMahasiswa();
        $a = Keahlian::create(['nama' => 'Basis Data']);
        $b = Keahlian::create(['nama' => 'UI/UX Design']);

        $this->actingAs($user)->put(route('profil.update'), $this->dataProfil(['keahlian' => [$a->id, $b->id]]));
        $this->assertEqualsCanonicalizing([$a->id, $b->id], $user->keahlian()->pluck('keahlian.id')->all());

        // Mengirim keahlian lebih sedikit menghapus yang tidak dikirim.
        $this->actingAs($user)->put(route('profil.update'), $this->dataProfil(['keahlian' => [$b->id]]));
        $this->assertSame([$b->id], $user->keahlian()->pluck('keahlian.id')->all());

        $this->actingAs($user)->put(route('profil.update'), $this->dataProfil(['keahlian' => [99999]]))
            ->assertSessionHasErrors('keahlian.0');
    }

    public function test_menyembunyikan_profil_dari_direktori(): void
    {
        $user = $this->buatMahasiswa();

        $this->actingAs($user)->put(route('profil.update'), $this->dataProfil(['tampil_di_direktori' => '0']));

        $this->assertDatabaseHas('mahasiswa', ['user_id' => $user->id, 'tampil_di_direktori' => false]);
    }

    // ---------- Foto ----------

    public function test_unggah_foto_menyimpan_berkas_dan_mengganti_yang_lama(): void
    {
        $user = $this->buatMahasiswa();

        $this->actingAs($user)->put(route('profil.update'), $this->dataProfil([
            'foto' => UploadedFile::fake()->image('pertama.jpg', 300, 300),
        ]))->assertSessionHasNoErrors();

        $pertama = $user->mahasiswa()->first()->foto;
        $this->assertNotNull($pertama);
        $this->assertStringStartsWith('foto-profil/', $pertama);
        Storage::disk('public')->assertExists($pertama);

        $this->actingAs($user)->put(route('profil.update'), $this->dataProfil([
            'foto' => UploadedFile::fake()->image('kedua.png', 300, 300),
        ]));

        $kedua = $user->mahasiswa()->first()->foto;
        $this->assertNotSame($pertama, $kedua);
        Storage::disk('public')->assertMissing($pertama);   // berkas lama dibersihkan
        Storage::disk('public')->assertExists($kedua);
    }

    public function test_hapus_foto(): void
    {
        $user = $this->buatMahasiswa();
        $this->actingAs($user)->put(route('profil.update'), $this->dataProfil([
            'foto' => UploadedFile::fake()->image('a.jpg', 200, 200),
        ]));
        $path = $user->mahasiswa()->first()->foto;

        $this->actingAs($user)->put(route('profil.update'), $this->dataProfil(['hapus_foto' => '1']));

        $this->assertNull($user->mahasiswa()->first()->foto);
        Storage::disk('public')->assertMissing($path);
    }

    public function test_berkas_bukan_gambar_dan_terlalu_besar_ditolak(): void
    {
        $user = $this->buatMahasiswa();

        $this->actingAs($user)->put(route('profil.update'), $this->dataProfil([
            'foto' => UploadedFile::fake()->create('dokumen.pdf', 10, 'application/pdf'),
        ]))->assertSessionHasErrors('foto');

        $this->actingAs($user)->put(route('profil.update'), $this->dataProfil([
            'foto' => UploadedFile::fake()->image('besar.jpg', 200, 200)->size(3000), // 3 MB
        ]))->assertSessionHasErrors('foto');

        $this->assertNull($user->mahasiswa()->first()->foto);
    }

    // ---------- Dosen ----------

    public function test_dosen_memperbarui_kesediaan_membimbing(): void
    {
        $user = $this->buatDosen();

        $this->actingAs($user)->put(route('profil.update'), [
            'name' => 'Dr. Rina Kusumawati',
            'program_studi' => 'Sistem Informasi Bisnis',
            'tampil_di_direktori' => '1',
            'bersedia_membimbing_magang' => '1',
            'bersedia_membimbing_lomba' => '0',
        ])->assertRedirect(route('profil.show'));

        $this->assertDatabaseHas('dosen', [
            'user_id' => $user->id,
            'bersedia_membimbing_magang' => true,
            'bersedia_membimbing_lomba' => false,
            'jurusan' => 'Teknologi Informasi',
        ]);
    }

    // ---------- Mitra ----------

    private function buatMitraDisetujui(): User
    {
        $user = User::factory()->create(['role' => User::ROLE_MITRA, 'email' => 'hrd@mitra.example']);
        $mitra = $user->mitra()->create(['nama_organisasi' => 'PT Contoh', 'kontak' => '0341-000']);
        $mitra->status_verifikasi = Mitra::STATUS_DISETUJUI;
        $mitra->save();

        return $user;
    }

    public function test_mitra_memperbarui_profil_tetapi_tidak_bisa_mengubah_status_verifikasi(): void
    {
        $user = $this->buatMitraDisetujui();

        $this->actingAs($user)->put(route('mitra.profil.update'), [
            'name' => 'Budi Santoso',
            'nama_organisasi' => 'PT Contoh Maju',
            'kontak' => '0341-111',
            'website' => 'https://contoh.example',
            'deskripsi' => 'Perusahaan perangkat lunak.',
            'status_verifikasi' => 'ditolak', // harus diabaikan
        ])->assertRedirect(route('mitra.profil.show'));

        $this->assertDatabaseHas('mitra', [
            'user_id' => $user->id,
            'nama_organisasi' => 'PT Contoh Maju',
            'status_verifikasi' => 'disetujui',
        ]);
    }

    public function test_website_mitra_hanya_menerima_http_atau_https(): void
    {
        $user = $this->buatMitraDisetujui();

        $this->actingAs($user)->put(route('mitra.profil.update'), [
            'name' => 'Budi', 'nama_organisasi' => 'PT Contoh', 'kontak' => '0341-000',
            'website' => 'javascript:alert(1)',
        ])->assertSessionHasErrors('website');
    }

    // ---------- Hak akses ----------

    public function test_profil_hanya_untuk_peran_yang_sesuai(): void
    {
        $mahasiswa = $this->buatMahasiswa();
        $mitra = $this->buatMitraDisetujui();

        $this->actingAs($mahasiswa)->get(route('mitra.profil.show'))->assertForbidden();
        $this->actingAs($mitra)->get(route('profil.show'))->assertForbidden();
        $this->actingAs($mitra)->get(route('mitra.profil.show'))->assertOk();
    }

    public function test_tamu_tidak_bisa_memperbarui_profil(): void
    {
        $this->put(route('profil.update'), $this->dataProfil())->assertRedirect(route('login'));
    }
}
