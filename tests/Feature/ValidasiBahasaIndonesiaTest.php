<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ValidasiBahasaIndonesiaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        if (($_ENV['DB_CONNECTION'] ?? getenv('DB_CONNECTION')) !== 'sqlite') {
            $this->markTestSkipped('Uji ini hanya berjalan dengan SQLite.');
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

    public function test_pesan_validasi_foto_berbahasa_indonesia_tanpa_kata_the_atau_field(): void
    {
        $user = $this->buatMahasiswa();

        $dataDasar = [
            'name' => 'Alya Putri',
            'program_studi' => 'Sistem Informasi Bisnis',
            'kelas' => 'SIB-3A',
            'angkatan' => 2024,
            'tampil_di_direktori' => '1',
        ];

        // 1. Foto GIF (mimes:jpg,jpeg,png,webp)
        $responGif = $this->actingAs($user)->put(route('profil.update'), array_merge($dataDasar, [
            'foto' => UploadedFile::fake()->create('foto.gif', 100, 'image/gif'),
        ]));
        $responGif->assertSessionHasErrors('foto');
        $pesanGif = session('errors')->first('foto');
        $this->assertStringNotContainsStringIgnoringCase('The', $pesanGif);
        $this->assertStringNotContainsStringIgnoringCase('field', $pesanGif);

        // 2. Foto lebih dari 2 MB (max:2048)
        $responBesar = $this->actingAs($user)->put(route('profil.update'), array_merge($dataDasar, [
            'foto' => UploadedFile::fake()->image('besar.jpg', 200, 200)->size(3000),
        ]));
        $responBesar->assertSessionHasErrors('foto');
        $pesanBesar = session('errors')->first('foto');
        $this->assertStringNotContainsStringIgnoringCase('The', $pesanBesar);
        $this->assertStringNotContainsStringIgnoringCase('field', $pesanBesar);

        // 3. Foto lebih dari 4000 piksel (dimensions:max_width=4000,max_height=4000)
        $responDimensi = $this->actingAs($user)->put(route('profil.update'), array_merge($dataDasar, [
            'foto' => UploadedFile::fake()->image('lebar.jpg', 4001, 100),
        ]));
        $responDimensi->assertSessionHasErrors('foto');
        $pesanDimensi = session('errors')->first('foto');
        $this->assertStringNotContainsStringIgnoringCase('The', $pesanDimensi);
        $this->assertStringNotContainsStringIgnoringCase('field', $pesanDimensi);
    }

    public function test_tanggal_riwayat_lampau_atau_masa_depan_jauh_menghasilkan_pesan_indonesia(): void
    {
        $user = $this->buatMahasiswa();

        $respon = $this->actingAs($user)->postJson(route('profil.riwayat.store'), [
            'jenis' => 'sertifikasi',
            'judul' => 'Sertifikat AWS',
            'tanggal_mulai' => '3000-01-01',
        ]);

        $respon->assertStatus(422);
        $pesan = $respon->json('errors.tanggal_mulai.0');
        $this->assertNotNull($pesan);
        $this->assertStringNotContainsStringIgnoringCase('The', $pesan);
        $this->assertStringNotContainsStringIgnoringCase('field', $pesan);
        $this->assertStringContainsString('tanggal mulai', strtolower($pesan));
    }

    public function test_semua_aturan_validasi_di_form_request_memiliki_terjemahan_di_lang_id(): void
    {
        $kamus = require base_path('lang/id/validation.php');

        $aturanDipakai = [
            'required',
            'string',
            'max',
            'min',
            'email',
            'unique',
            'regex',
            'between',
            'integer',
            'boolean',
            'image',
            'mimes',
            'dimensions',
            'array',
            'distinct',
            'exists',
            'date',
            'before_or_equal',
            'after_or_equal',
            'url',
            'prohibited',
            'digits',
            'required_unless',
            'in',
            'file',
            'confirmed',
        ];

        foreach ($aturanDipakai as $aturan) {
            $this->assertArrayHasKey(
                $aturan,
                $kamus,
                "Aturan [{$aturan}] yang digunakan di app/Http/Requests belum ada di lang/id/validation.php"
            );
        }

        $berkasRequest = glob(app_path('Http/Requests/*.php'));
        $aturanDitemukan = [];

        foreach ($berkasRequest as $berkas) {
            $konten = file_get_contents($berkas);
            if (preg_match_all("/'([a-z][a-z0-9_]*)(?::[^']*)?'/", $konten, $cocok)) {
                foreach ($cocok[1] as $calonAturan) {
                    if (in_array($calonAturan, $aturanDipakai, true)) {
                        $aturanDitemukan[$calonAturan] = true;
                    }
                }
            }
        }

        foreach (array_keys($aturanDitemukan) as $aturan) {
            $this->assertArrayHasKey(
                $aturan,
                $kamus,
                "Aturan [{$aturan}] yang ditemukan di berkas Request belum ada di lang/id/validation.php"
            );
        }
    }
}
