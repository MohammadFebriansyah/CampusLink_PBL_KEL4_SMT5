<?php

namespace Tests\Feature;

use App\Models\Beasiswa;
use App\Models\Lomba;
use App\Models\Magang;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InformasiTest extends TestCase
{
    use RefreshDatabase;

    private User $pembuat;

    protected function setUp(): void
    {
        // RefreshDatabase menghapus seluruh tabel. Jangan sampai terjadi di Supabase.
        if (($_ENV['DB_CONNECTION'] ?? getenv('DB_CONNECTION')) !== 'sqlite') {
            $this->markTestSkipped('Uji ini hanya berjalan dengan SQLite. Atur DB_CONNECTION=sqlite dan DB_DATABASE=:memory: di phpunit.xml.');
        }

        parent::setUp();

        $this->withoutVite();
        $this->pembuat = User::factory()->create(['role' => User::ROLE_ADMIN, 'email' => 'admin@campuslink.test']);
    }

    private function pembaca(string $peran = User::ROLE_MAHASISWA): User
    {
        return User::factory()->create(['role' => $peran, 'email' => $peran . '@polinema.ac.id']);
    }

    private function buatMagang(array $ubah = []): Magang
    {
        $status = $ubah['status'] ?? null;
        unset($ubah['status']);

        $magang = $this->pembuat->magang()->create(array_merge([
            'judul' => 'Magang Web Developer',
            'penyelenggara' => 'PT Contoh',
            'deskripsi' => 'Mengembangkan aplikasi web.',
            'persyaratan' => "Menguasai PHP.\nMemahami SQL.",
            'lokasi' => 'Malang',
            'periode_mulai' => now()->addMonths(2)->toDateString(),
            'periode_selesai' => now()->addMonths(5)->toDateString(),
            'batas_pendaftaran' => now()->addDays(10)->toDateString(),
            'kontak' => 'hrd@contoh.example',
            'tautan_pendaftaran' => 'https://contoh.example/daftar',
        ], $ubah));

        if ($status) {
            $magang->status = $status;
            $magang->save();
        }

        return $magang;
    }

    private function buatLomba(array $ubah = []): Lomba
    {
        return $this->pembuat->lomba()->create(array_merge([
            'judul' => 'Lomba Desain',
            'penyelenggara' => 'Komunitas Desain',
            'deskripsi' => 'Merancang antarmuka.',
            'persyaratan' => null,
            'tingkat' => 'nasional',
            'tanggal_mulai' => now()->addDays(40)->toDateString(),
            'tanggal_selesai' => now()->addDays(41)->toDateString(),
            'batas_pendaftaran' => now()->addDays(20)->toDateString(),
            'kontak' => 'panitia@desain.example',
            'tautan_pendaftaran' => 'https://desain.example/daftar',
        ], $ubah));
    }

    private function buatBeasiswa(array $ubah = []): Beasiswa
    {
        return $this->pembuat->beasiswa()->create(array_merge([
            'judul' => 'Beasiswa Prestasi',
            'penyelenggara' => 'Lembaga Mandiri',
            'deskripsi' => 'Bantuan biaya pendidikan.',
            'persyaratan' => 'IPK minimal 3,5.',
            'batas_pendaftaran' => now()->addDays(15)->toDateString(),
            'kontak' => 'info@lembaga.example',
            'tautan_pendaftaran' => 'https://lembaga.example/beasiswa',
        ], $ubah));
    }

    // ---------- Akses ----------

    public function test_tamu_diarahkan_ke_login(): void
    {
        $magang = $this->buatMagang();

        $this->get(route('magang.index'))->assertRedirect(route('login'));
        $this->get(route('lomba.index'))->assertRedirect(route('login'));
        $this->get(route('beasiswa.index'))->assertRedirect(route('login'));
        $this->get(route('magang.show', $magang))->assertRedirect(route('login'));
    }

    public function test_semua_peran_yang_sudah_masuk_bisa_membaca(): void
    {
        $magang = $this->buatMagang();

        foreach ([User::ROLE_MAHASISWA, User::ROLE_DOSEN, User::ROLE_ADMIN] as $peran) {
            $user = User::factory()->create(['role' => $peran, 'email' => "$peran@polinema.ac.id"]);
            $this->actingAs($user)->get(route('magang.index'))->assertOk();
            $this->actingAs($user)->get(route('magang.show', $magang))->assertOk();
        }
    }

    public function test_akun_yang_dinonaktifkan_ditolak(): void
    {
        $user = $this->pembaca();
        $this->actingAs($user);
        $user->update(['status' => User::STATUS_NONAKTIF]);

        $this->get(route('magang.index'))->assertRedirect(route('login'));
        $this->assertGuest();
    }

    // ---------- Daftar ----------

    public function test_daftar_hanya_memuat_yang_dibuka_dan_tayang(): void
    {
        $this->buatMagang(['judul' => 'Magang Terbuka']);
        $this->buatMagang(['judul' => 'Magang Sudah Ditutup', 'batas_pendaftaran' => now()->subDay()->toDateString()]);
        $this->buatMagang(['judul' => 'Magang Disembunyikan', 'status' => Magang::STATUS_DISEMBUNYIKAN]);

        $this->actingAs($this->pembaca())->get(route('magang.index'))
            ->assertOk()
            ->assertSee('Magang Terbuka')
            ->assertDontSee('Magang Sudah Ditutup')
            ->assertDontSee('Magang Disembunyikan')
            ->assertSee('1 informasi dibuka');
    }

    public function test_batas_hari_ini_masih_tampil(): void
    {
        $this->buatMagang(['judul' => 'Magang Terakhir Hari Ini', 'batas_pendaftaran' => today()->toDateString()]);

        $this->actingAs($this->pembaca())->get(route('magang.index'))
            ->assertSee('Magang Terakhir Hari Ini')
            ->assertSee('Terakhir hari ini');
    }

    public function test_diurutkan_dari_batas_pendaftaran_terdekat(): void
    {
        $this->buatMagang(['judul' => 'Magang Jauh', 'batas_pendaftaran' => now()->addDays(30)->toDateString()]);
        $this->buatMagang(['judul' => 'Magang Dekat', 'batas_pendaftaran' => now()->addDays(2)->toDateString()]);

        $this->actingAs($this->pembaca())->get(route('magang.index'))
            ->assertSeeInOrder(['Magang Dekat', 'Magang Jauh']);
    }

    public function test_ketiga_kategori_tidak_tercampur(): void
    {
        $this->buatMagang(['judul' => 'Judul Magang']);
        $this->buatLomba(['judul' => 'Judul Lomba']);
        $this->buatBeasiswa(['judul' => 'Judul Beasiswa']);
        $pembaca = $this->pembaca();

        $this->actingAs($pembaca)->get(route('lomba.index'))
            ->assertSee('Judul Lomba')->assertDontSee('Judul Magang')->assertDontSee('Judul Beasiswa');
        $this->actingAs($pembaca)->get(route('beasiswa.index'))
            ->assertSee('Judul Beasiswa')->assertDontSee('Judul Lomba');
    }

    public function test_penomoran_halaman_sepuluh_per_halaman(): void
    {
        foreach (range(1, 12) as $i) {
            $this->buatMagang(['judul' => sprintf('Magang Nomor %02d', $i), 'batas_pendaftaran' => now()->addDays($i)->toDateString()]);
        }
        $pembaca = $this->pembaca();

        $this->actingAs($pembaca)->get(route('magang.index'))
            ->assertSee('Magang Nomor 10')->assertDontSee('Magang Nomor 11')
            ->assertSee('Berikutnya')->assertSee('Halaman 1 dari 2');

        $this->actingAs($pembaca)->get(route('magang.index', ['page' => 2]))
            ->assertSee('Magang Nomor 11')->assertSee('Magang Nomor 12')->assertDontSee('Magang Nomor 01');
    }

    // ---------- Pencarian ----------

    public function test_pencarian_kata_kunci_di_beberapa_kolom_tanpa_membedakan_huruf(): void
    {
        $this->buatMagang(['judul' => 'Magang Backend Laravel']);
        $this->buatMagang(['judul' => 'Magang Desain', 'penyelenggara' => 'Studio Kreatif', 'deskripsi' => 'Membuat poster.', 'persyaratan' => 'Bisa Figma.', 'lokasi' => 'Surabaya']);
        $pembaca = $this->pembaca();

        $cari = fn (string $q) => $this->actingAs($pembaca)->get(route('magang.index', ['q' => $q]));

        $cari('LARAVEL')->assertSee('Magang Backend Laravel')->assertDontSee('Magang Desain');   // judul, huruf besar
        $cari('studio')->assertSee('Magang Desain')->assertDontSee('Backend');                   // penyelenggara
        $cari('poster')->assertSee('Magang Desain');                                             // deskripsi
        $cari('figma')->assertSee('Magang Desain');                                              // persyaratan
        $cari('surabaya')->assertSee('Magang Desain')->assertDontSee('Backend');                 // lokasi
    }

    public function test_pencarian_tanpa_hasil_menampilkan_petunjuk(): void
    {
        $this->buatMagang();

        $this->actingAs($this->pembaca())->get(route('magang.index', ['q' => 'tidak-ada-yang-cocok']))
            ->assertOk()
            ->assertSee('Tidak ada hasil yang cocok')
            ->assertSee('Hapus pencarian')
            ->assertSee('0 informasi dibuka');
    }

    public function test_belum_ada_data_menampilkan_keadaan_kosong(): void
    {
        $this->actingAs($this->pembaca())->get(route('beasiswa.index'))
            ->assertOk()->assertSee('Belum ada informasi beasiswa yang dibuka');
    }

    public function test_kata_kunci_terbawa_saat_berpindah_kategori(): void
    {
        $this->actingAs($this->pembaca())->get(route('magang.index', ['q' => 'laravel']))
            ->assertSee(route('lomba.index', ['q' => 'laravel']), false);
    }

    public function test_masukan_pencarian_yang_tidak_valid_ditolak(): void
    {
        $pembaca = $this->pembaca();

        $this->actingAs($pembaca)->from(route('magang.index'))
            ->get(route('magang.index', ['q' => str_repeat('a', 101)]))
            ->assertSessionHasErrors('q');

        $this->actingAs($pembaca)->from(route('lomba.index'))
            ->get(route('lomba.index', ['tingkat' => 'galaksi']))
            ->assertSessionHasErrors('tingkat');
    }

    public function test_karakter_khusus_pada_pencarian_aman(): void
    {
        $this->buatMagang();
        $pembaca = $this->pembaca();

        foreach (["'; DROP TABLE users;--", '%', '_', '<script>alert(1)</script>'] as $q) {
            $this->actingAs($pembaca)->get(route('magang.index', ['q' => $q]))->assertOk();
        }

        $this->assertDatabaseCount('magang', 1);
        $this->assertDatabaseCount('users', 2); // tabel tidak terganggu

        $this->actingAs($pembaca)->get(route('magang.index', ['q' => '<script>alert(1)</script>']))
            ->assertDontSee('<script>alert(1)</script>', false); // keluaran di-escape
    }

    public function test_filter_tingkat_lomba(): void
    {
        $this->buatLomba(['judul' => 'Lomba Nasional', 'tingkat' => 'nasional']);
        $this->buatLomba(['judul' => 'Lomba Kampus', 'tingkat' => 'kampus']);
        $pembaca = $this->pembaca();

        $this->actingAs($pembaca)->get(route('lomba.index', ['tingkat' => 'kampus']))
            ->assertSee('Lomba Kampus')->assertDontSee('Lomba Nasional');

        $this->actingAs($pembaca)->get(route('lomba.index'))
            ->assertSee('Lomba Kampus')->assertSee('Lomba Nasional');
    }

    // ---------- Detail ----------

    public function test_detail_menampilkan_semua_isian_yang_diminta_proposal(): void
    {
        $magang = $this->buatMagang();

        $this->actingAs($this->pembaca())->get(route('magang.show', $magang))
            ->assertOk()
            ->assertSee('Magang Web Developer')
            ->assertSee('Mengembangkan aplikasi web.')   // deskripsi
            ->assertSee('Menguasai PHP.')                // persyaratan
            ->assertSee('Batas pendaftaran')             // batas waktu
            ->assertSee('hrd@contoh.example')            // kontak
            ->assertSee('href="https://contoh.example/daftar"', false) // tautan pendaftaran
            ->assertSee('rel="noopener noreferrer"', false);
    }

    public function test_detail_lomba_dan_beasiswa(): void
    {
        $lomba = $this->buatLomba();
        $beasiswa = $this->buatBeasiswa();
        $pembaca = $this->pembaca();

        $this->actingAs($pembaca)->get(route('lomba.show', $lomba))->assertOk()->assertSee('Nasional')->assertSee('Pelaksanaan');
        $this->actingAs($pembaca)->get(route('beasiswa.show', $beasiswa))->assertOk()->assertSee('IPK minimal 3,5.');
    }

    public function test_detail_yang_sudah_ditutup_tetap_bisa_dibuka_tanpa_tautan_pendaftaran(): void
    {
        $magang = $this->buatMagang(['batas_pendaftaran' => now()->subDays(3)->toDateString()]);

        $this->actingAs($this->pembaca())->get(route('magang.show', $magang))
            ->assertOk()
            ->assertSee('Pendaftaran sudah ditutup')
            ->assertDontSee('href="https://contoh.example/daftar"', false);
    }

    public function test_detail_yang_disembunyikan_atau_tidak_ada_menghasilkan_404(): void
    {
        $tersembunyi = $this->buatMagang(['status' => Magang::STATUS_DISEMBUNYIKAN]);
        $pembaca = $this->pembaca();

        $this->actingAs($pembaca)->get(route('magang.show', $tersembunyi))->assertNotFound();
        $this->actingAs($pembaca)->get('/magang/99999')->assertNotFound();
        $this->actingAs($pembaca)->get('/magang/abc')->assertNotFound();
    }

    public function test_tautan_bukan_http_tidak_dijadikan_href(): void
    {
        $magang = $this->buatMagang(['tautan_pendaftaran' => 'javascript:alert(1)']);

        $this->actingAs($this->pembaca())->get(route('magang.show', $magang))
            ->assertOk()
            ->assertDontSee('href="javascript:', false)
            ->assertSee('Tautan pendaftaran tidak tersedia');
    }

    public function test_isian_dari_mitra_di_escape(): void
    {
        $magang = $this->buatMagang(['judul' => '<b>Tebal</b>', 'deskripsi' => '<script>alert(1)</script>']);

        $this->actingAs($this->pembaca())->get(route('magang.show', $magang))
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertDontSee('<b>Tebal</b>', false);
    }

    // ---------- Landing page ----------

    public function test_baris_di_landing_page_menuju_halaman_detail(): void
    {
        $magang = $this->buatMagang();

        $this->get('/')->assertOk()->assertSee(route('magang.show', $magang), false);
    }
}
