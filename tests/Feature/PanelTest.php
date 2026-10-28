<?php

namespace Tests\Feature;

use App\Models\Bimbingan;
use App\Models\Mitra;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

// TAMBAHAN: uji layout panel (sidebar admin dan mitra, bar atas tanpa navbar publik).
class PanelTest extends TestCase
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

    private function admin(): User
    {
        return User::factory()->create(['role' => User::ROLE_ADMIN, 'email' => 'admin@polinema.ac.id', 'name' => 'Admin Panel']);
    }

    private function mitra(string $status = Mitra::STATUS_DISETUJUI, string $email = 'mitra@contoh.example'): User
    {
        $u = User::factory()->create(['role' => User::ROLE_MITRA, 'email' => $email, 'name' => 'Mitra Panel']);
        $m = $u->mitra()->create(['nama_organisasi' => 'PT Panel', 'kontak' => '0341-000']);
        $m->status_verifikasi = $status;
        $m->save();

        return $u;
    }

    /** Potongan HTML sidebar. */
    private function sidebar(TestResponse $respons): string
    {
        preg_match('/<aside id="sidebar-panel".*?<\/aside>/s', $respons->getContent(), $cocok);

        return $cocok[0] ?? '';
    }

    private function assertMemakaiPanel(TestResponse $respons): void
    {
        $respons->assertOk()
            ->assertSee('id="sidebar-panel"', false)
            ->assertDontSee('aria-label="Navigasi utama"', false) // navbar publik tidak dipakai
            ->assertSee('Lihat situs')
            ->assertSee('Keluar')
            ->assertSee('href="' . route('home') . '"', false);
    }

    public function test_semua_halaman_admin_memakai_layout_panel(): void
    {
        $admin = $this->admin();
        $mhs = $this->mahasiswa();
        $rute = [
            route('admin.dashboard'), route('admin.mitra.index'), route('admin.pendaftar.index'),
            route('admin.pengguna.index'), route('admin.pengguna.show', $mhs->id), route('admin.pengguna.edit', $mhs->id),
            route('admin.keahlian.index'), route('admin.informasi.index', 'magang'), route('admin.informasi.create', 'lomba'),
        ];

        foreach ($rute as $url) {
            $this->assertMemakaiPanel($this->actingAs($admin)->get($url));
        }
    }

    public function test_sidebar_admin_berkelompok_dengan_nama_aksi(): void
    {
        $sidebar = $this->sidebar($this->actingAs($this->admin())->get(route('admin.dashboard')));

        foreach (['Ringkasan', 'Verifikasi', 'Pengguna', 'Konten', 'Dashboard', 'Verifikasi mitra', 'Pendaftar',
            'Kelola magang', 'Kelola lomba', 'Kelola beasiswa', 'Keahlian'] as $teks) {
            $this->assertStringContainsString($teks, $sidebar);
        }
    }

    public function test_item_aktif_sidebar_diberi_aria_current(): void
    {
        $admin = $this->admin();
        $sidebar = $this->sidebar($this->actingAs($admin)->get(route('admin.informasi.index', 'lomba')));

        $this->assertSame(1, substr_count($sidebar, 'aria-current="page"'));
        $this->assertAktif(route('admin.informasi.index', 'lomba'), $sidebar);

        // Halaman turunan (detail pengguna) tetap menandai menu induknya.
        $sidebar = $this->sidebar($this->actingAs($admin)->get(route('admin.pengguna.show', $this->mahasiswa()->id)));
        $this->assertAktif(route('admin.pengguna.index'), $sidebar);
    }

    private function assertAktif(string $url, string $sidebar): void
    {
        $this->assertMatchesRegularExpression('/href="' . preg_quote($url, '/') . '"\s+aria-current="page"/', $sidebar);
    }

    private function mahasiswa(): User
    {
        $u = User::factory()->create(['role' => User::ROLE_MAHASISWA, 'email' => 'mhs@polinema.ac.id']);
        $u->mahasiswa()->create([
            'nim' => '2441079001', 'kelas' => 'SIB-3A', 'jurusan' => 'Teknologi Informasi',
            'program_studi' => 'Sistem Informasi Bisnis', 'angkatan' => 2024,
        ]);

        return $u;
    }

    public function test_lencana_verifikasi_mitra_menampilkan_jumlah_yang_menunggu(): void
    {
        $admin = $this->admin();
        $sidebar = $this->sidebar($this->actingAs($admin)->get(route('admin.dashboard')));
        $this->assertStringNotContainsString(' menunggu</span>', $sidebar);

        $this->mitra(Mitra::STATUS_PENDING, 'a@contoh.example');
        $this->mitra(Mitra::STATUS_PENDING, 'b@contoh.example');
        $this->mitra(Mitra::STATUS_DISETUJUI, 'c@contoh.example');

        $sidebar = $this->sidebar($this->actingAs($admin)->get(route('admin.dashboard')));
        $this->assertMatchesRegularExpression('/Verifikasi mitra<\/span>\s*<span[^>]*>2<span class="sr-only"> menunggu<\/span>/', $sidebar);
    }

    public function test_semua_halaman_mitra_memakai_layout_panel_dengan_sidebar_pendek(): void
    {
        $mitra = $this->mitra();
        foreach ([route('mitra.dashboard'), route('mitra.pendaftar.index'), route('mitra.informasi.index', 'magang'),
            route('mitra.informasi.create', 'beasiswa'), route('mitra.profil.show')] as $url) {
            $this->assertMemakaiPanel($this->actingAs($mitra)->get($url));
        }

        $sidebar = $this->sidebar($this->actingAs($mitra)->get(route('mitra.profil.show')));
        foreach (['Dashboard', 'Pendaftar', 'Kelola magang', 'Kelola lomba', 'Kelola beasiswa', 'Profil organisasi'] as $teks) {
            $this->assertStringContainsString($teks, $sidebar);
        }
        $this->assertStringNotContainsString('Verifikasi mitra', $sidebar);
        $this->assertStringNotContainsString('Keahlian', $sidebar);
        $this->assertAktif(route('mitra.profil.show'), $sidebar);
    }

    private function dosen(): User
    {
        $u = User::factory()->create(['role' => User::ROLE_DOSEN, 'email' => 'dosen@polinema.ac.id', 'name' => 'Dewi Dosen']);
        $u->dosen()->create(['nidn' => 'N1', 'jurusan' => 'Teknologi Informasi', 'program_studi' => 'Teknik Informatika']);

        return $u;
    }

    private function bimbingan(User $mhs, User $dosen, int $informasiId, string $status = 'menunggu'): Bimbingan
    {
        $b = new Bimbingan;
        $b->forceFill(['mahasiswa_id' => $mhs->id, 'dosen_id' => $dosen->id, 'informasi_type' => 'magang', 'informasi_id' => $informasiId, 'status' => $status])->save();

        return $b;
    }

    public function test_halaman_mahasiswa_memakai_layout_panel_dengan_menu_mahasiswa(): void
    {
        $mhs = $this->mahasiswa();
        foreach ([route('dasbor'), route('kegiatan.index'), route('profil.show')] as $url) {
            $this->assertMemakaiPanel($this->actingAs($mhs)->get($url));
        }

        $sidebar = $this->sidebar($this->actingAs($mhs)->get(route('kegiatan.index')));
        foreach (['Panel mahasiswa', 'Dashboard', 'Kegiatan saya', 'Magang', 'Direktori', 'Profil saya'] as $teks) {
            $this->assertStringContainsString($teks, $sidebar);
        }
        foreach (['Permintaan bimbingan', 'Kelola magang', 'Verifikasi mitra'] as $teks) {
            $this->assertStringNotContainsString($teks, $sidebar);
        }
        $this->assertAktif(route('kegiatan.index'), $sidebar);
    }

    public function test_halaman_dosen_memakai_layout_panel_dengan_lencana_bimbingan(): void
    {
        $dosen = $this->dosen();
        foreach ([route('dasbor'), route('bimbingan.index'), route('profil.show')] as $url) {
            $this->assertMemakaiPanel($this->actingAs($dosen)->get($url));
        }

        $mhs = $this->mahasiswa();
        $this->bimbingan($mhs, $dosen, 1);
        $this->bimbingan($mhs, $dosen, 2);
        $this->bimbingan($mhs, $dosen, 3, 'diterima');

        $sidebar = $this->sidebar($this->actingAs($dosen)->get(route('dasbor')));
        $this->assertStringContainsString('Panel dosen', $sidebar);
        $this->assertStringNotContainsString('Kegiatan saya', $sidebar);
        $this->assertMatchesRegularExpression('/Permintaan bimbingan<\/span>\s*<span[^>]*>2<span class="sr-only"> menunggu<\/span>/', $sidebar);
        $this->assertAktif(route('dasbor'), $sidebar);
    }

    public function test_dasbor_hanya_untuk_mahasiswa_dan_dosen(): void
    {
        $this->get(route('dasbor'))->assertRedirect(route('login'));
        $this->actingAs($this->admin())->get(route('dasbor'))->assertForbidden();
        $this->actingAs($this->mitra())->get(route('dasbor'))->assertForbidden();
    }

    public function test_dasbor_menampilkan_ringkasan_sesuai_peran(): void
    {
        $mhs = $this->mahasiswa();
        $dosen = $this->dosen();
        $this->bimbingan($mhs, $dosen, 1);

        $this->actingAs($mhs)->get(route('dasbor'))->assertOk()
            ->assertSee('Pendaftaran terbaru')->assertSee('Belum ada pendaftaran.')
            ->assertSee('1 menunggu jawaban dosen')->assertSee('Kelengkapan profil')
            ->assertDontSee('Menunggu jawaban Anda');

        $this->actingAs($dosen)->get(route('dasbor'))->assertOk()
            ->assertSee('Menunggu jawaban Anda')->assertSee($mhs->name)
            ->assertSee('Belum bersedia')->assertSee('Kelengkapan profil')
            ->assertDontSee('Pendaftaran terbaru');
    }

    public function test_halaman_publik_tetap_memakai_navbar_publik(): void
    {
        $this->actingAs($this->admin())->get(route('home'))
            ->assertOk()->assertSee('aria-label="Navigasi utama"', false)->assertDontSee('id="sidebar-panel"', false);
    }
}
