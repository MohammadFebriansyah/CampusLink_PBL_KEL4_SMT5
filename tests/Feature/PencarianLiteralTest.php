<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PencarianLiteralTest extends TestCase
{
    use RefreshDatabase;

    private const NAMA = [
        'biasa' => 'Alya Putri Ramadhani',
        'persen' => 'Simbol%Nama',
        'garis' => 'Simbol_Nama',
        'miring' => 'Simbol\\Nama',
        'gabungan' => 'Gabung\\%_Nama',
    ];

    protected function setUp(): void
    {
        if (($_ENV['DB_CONNECTION'] ?? getenv('DB_CONNECTION')) !== 'sqlite'
            || ($_ENV['DB_DATABASE'] ?? getenv('DB_DATABASE')) !== ':memory:') {
            $this->markTestSkipped('Pengujian ini hanya boleh menggunakan SQLite memori.');
        }

        parent::setUp();
        $this->withoutVite();
    }

    public static function kataKunci(): array
    {
        return [
            'persen literal' => ['%', ['persen', 'gabungan']],
            'garis bawah literal' => ['_', ['garis', 'gabungan']],
            'garis miring literal' => ['\\', ['miring', 'gabungan']],
            'gabungan karakter escape' => ['\\%_', ['gabungan']],
            'huruf kecil' => ['alya', ['biasa']],
            'huruf besar' => ['ALYA', ['biasa']],
            'tidak cocok' => ['tidakada%_', []],
        ];
    }

    private function pengguna(string $nama, string $role = User::ROLE_MAHASISWA): User
    {
        $nomor = User::count() + 1;

        return User::factory()->create([
            'name' => $nama, 'role' => $role, 'email' => "pengguna{$nomor}@polinema.ac.id",
        ]);
    }

    #[DataProvider('kataKunci')]
    public function test_direktori_mencari_karakter_literal(string $kata, array $kunci): void
    {
        $pembaca = $this->pengguna('Pembaca');
        $ids = [];

        foreach (self::NAMA as $key => $nama) {
            // Data simbol dibuat langsung untuk menguji pencarian data lama, bukan validasi nama.
            $user = $this->pengguna($nama);
            $user->mahasiswa()->create([
                'nim' => str_pad((string) $user->id, 10, '0', STR_PAD_LEFT),
                'kelas' => 'SIB-3A', 'jurusan' => 'Teknologi Informasi',
                'program_studi' => 'Sistem Informasi Bisnis', 'angkatan' => 2024,
                'tampil_di_direktori' => true,
            ]);
            $ids[$key] = $user->id;
        }

        $respons = $this->actingAs($pembaca)
            ->getJson(route('direktori.index', ['q' => $kata]))
            ->assertOk()->assertJsonPath('meta.total', count($kunci));

        $this->assertEqualsCanonicalizing(
            array_map(fn ($key) => $ids[$key], $kunci),
            array_column($respons->json('data'), 'id'),
        );
    }

    #[DataProvider('kataKunci')]
    public function test_informasi_mencari_karakter_literal(string $kata, array $kunci): void
    {
        $admin = $this->pengguna('Pengelola', User::ROLE_ADMIN);
        $ids = [];

        foreach (self::NAMA as $key => $nama) {
            $item = $admin->magang()->create([
                'judul' => $nama, 'penyelenggara' => 'Penyelenggara',
                'deskripsi' => 'Deskripsi', 'persyaratan' => 'Persyaratan',
                'periode_mulai' => now()->addMonth()->toDateString(),
                'periode_selesai' => now()->addMonths(2)->toDateString(),
                'batas_pendaftaran' => now()->addDays(10)->toDateString(),
                'kontak' => 'Kontak', 'cara_daftar' => 'internal',
            ]);
            $ids[$key] = $item->id;
        }

        $respons = $this->actingAs($admin)->getJson(route('magang.index', ['q' => $kata]))
            ->assertOk()->assertJsonPath('meta.total', count($kunci));

        $this->assertEqualsCanonicalizing(
            array_map(fn ($key) => $ids[$key], $kunci),
            array_column($respons->json('data'), 'id'),
        );
    }

    #[DataProvider('kataKunci')]
    public function test_admin_pengguna_mencari_karakter_literal_termasuk_email(string $kata, array $kunci): void
    {
        $admin = $this->pengguna('Pengelola', User::ROLE_ADMIN);
        $ids = [];

        foreach (self::NAMA as $key => $nama) {
            $ids[$key] = $this->pengguna($nama)->id;
        }

        // Memastikan cabang OR pada email juga menganggap garis bawah sebagai teks biasa.
        $email = User::factory()->create(['name' => 'Pengguna Email', 'email' => 'akun_uji@polinema.ac.id']);
        $harusAda = array_map(fn ($key) => $ids[$key], $kunci);
        if ($kata === '_') {
            $harusAda[] = $email->id;
        }

        $respons = $this->actingAs($admin)->getJson(route('admin.pengguna.index', ['q' => $kata]))
            ->assertOk()->assertJsonPath('meta.total', count($harusAda));

        $this->assertEqualsCanonicalizing($harusAda, array_column($respons->json('data'), 'id'));
    }

    public function test_simbol_tidak_mencocokkan_semua_data_biasa(): void
    {
        $admin = $this->pengguna('Pengelola', User::ROLE_ADMIN);
        $this->pengguna('Bima Pratama');

        foreach (['%', '_', '\\'] as $kata) {
            $this->actingAs($admin)->getJson(route('admin.pengguna.index', ['q' => $kata]))
                ->assertOk()->assertJsonPath('meta.total', 0)->assertJsonCount(0, 'data');
        }
    }
}
