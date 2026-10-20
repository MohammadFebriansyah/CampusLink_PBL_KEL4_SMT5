<?php

namespace Tests\Feature;

use App\Models\Keahlian;
use App\Models\Mitra;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DirektoriTest extends TestCase
{
    use RefreshDatabase;

    private User $pembaca;

    private int $urutan = 0;

    protected function setUp(): void
    {
        // RefreshDatabase menghapus seluruh tabel. Jangan sampai terjadi di Supabase.
        if (($_ENV['DB_CONNECTION'] ?? getenv('DB_CONNECTION')) !== 'sqlite') {
            $this->markTestSkipped('Uji ini hanya berjalan dengan SQLite. Atur DB_CONNECTION=sqlite dan DB_DATABASE=:memory: di phpunit.xml.');
        }

        parent::setUp();

        $this->withoutVite();

        // Pembaca tanpa profil, supaya tidak ikut muncul di daftar.
        $this->pembaca = User::factory()->create(['role' => User::ROLE_MAHASISWA, 'email' => 'pembaca@polinema.ac.id', 'name' => 'Pembaca Uji']);
    }

    // ---------- Pembantu ----------

    private function buatMahasiswa(array $user = [], array $profil = [], array $keahlian = []): User
    {
        $n = ++$this->urutan;
        $u = User::factory()->create(array_merge([
            'role' => User::ROLE_MAHASISWA, 'email' => "mhs{$n}@polinema.ac.id", 'name' => "Mahasiswa Nomor {$n}",
        ], $user));

        $sembunyiAdmin = $profil['disembunyikan_admin'] ?? false;
        unset($profil['disembunyikan_admin']);

        $p = $u->mahasiswa()->create(array_merge([
            'nim' => "NIM{$n}", 'kelas' => 'SIB-3A', 'jurusan' => 'Teknologi Informasi',
            'program_studi' => 'Sistem Informasi Bisnis', 'angkatan' => 2024, 'tampil_di_direktori' => true,
        ], $profil));

        if ($sembunyiAdmin) {
            $p->disembunyikan_admin = true; // bukan $fillable: hanya admin yang boleh mengubahnya
            $p->save();
        }

        $this->lampirkan($u, $keahlian);

        return $u;
    }

    private function buatDosen(array $user = [], array $profil = [], array $keahlian = []): User
    {
        $n = ++$this->urutan;
        $u = User::factory()->create(array_merge([
            'role' => User::ROLE_DOSEN, 'email' => "dsn{$n}@polinema.ac.id", 'name' => "Dosen Nomor {$n}",
        ], $user));

        $u->dosen()->create(array_merge([
            'nidn' => "NIDN{$n}", 'jurusan' => 'Teknologi Informasi', 'program_studi' => 'Teknik Informatika',
            'tampil_di_direktori' => true,
        ], $profil));

        $this->lampirkan($u, $keahlian);

        return $u;
    }

    private function lampirkan(User $user, array $keahlian): void
    {
        foreach ($keahlian as $nama) {
            $user->keahlian()->attach(Keahlian::firstOrCreate(['nama' => $nama])->id);
        }
    }

    private function cari(array $query = [])
    {
        return $this->actingAs($this->pembaca)->get(route('direktori.index', $query));
    }

    /** Data standar untuk uji filter: 3 mahasiswa dan 2 dosen. */
    private function isiDataFilter(): array
    {
        return [
            'alya' => $this->buatMahasiswa(['name' => 'Alya Putri'], ['kelas' => 'SIB-3A', 'angkatan' => 2024], ['Web Development', 'UI/UX Design']),
            'bima' => $this->buatMahasiswa(['name' => 'Bima Aditya'], ['kelas' => 'TI-5A', 'angkatan' => 2023, 'program_studi' => 'Teknik Informatika'], ['Basis Data']),
            'citra' => $this->buatMahasiswa(['name' => 'Citra Maharani'], ['kelas' => 'AK-1A', 'angkatan' => 2025, 'jurusan' => 'Akuntansi', 'program_studi' => 'Akuntansi Manajemen'], ['Analisis Bisnis']),
            'dewi' => $this->buatDosen(['name' => 'Dewi Lestari'], ['program_studi' => 'Teknik Informatika'], ['Basis Data']),
            'eko' => $this->buatDosen(['name' => 'Eko Prasetyo'], ['jurusan' => 'Akuntansi', 'program_studi' => 'Akuntansi Manajemen'], ['Analisis Bisnis']),
        ];
    }

    private const NAMA = ['alya' => 'Alya Putri', 'bima' => 'Bima Aditya', 'citra' => 'Citra Maharani', 'dewi' => 'Dewi Lestari', 'eko' => 'Eko Prasetyo'];

    /** Memastikan tepat nama-nama $harusAda yang tampil dari lima data standar. */
    private function assertHanya($respons, array $harusAda): void
    {
        foreach (self::NAMA as $kunci => $nama) {
            in_array($kunci, $harusAda, true) ? $respons->assertSee($nama) : $respons->assertDontSee($nama);
        }
    }

    // ---------- Akses ----------

    public function test_tamu_diarahkan_ke_login(): void
    {
        $mhs = $this->buatMahasiswa();

        $this->get(route('direktori.index'))->assertRedirect(route('login'));
        $this->get(route('direktori.show', $mhs->id))->assertRedirect(route('login'));
    }

    public function test_hanya_mahasiswa_dan_dosen_yang_boleh_membuka_direktori(): void
    {
        $mhs = $this->buatMahasiswa();

        foreach ([User::ROLE_MITRA, User::ROLE_ADMIN] as $peran) {
            $user = User::factory()->create(['role' => $peran, 'email' => "$peran@contoh.example"]);
            if ($peran === User::ROLE_MITRA) {
                $user->mitra()->create(['nama_organisasi' => 'PT X', 'kontak' => '1'])->forceFill(['status_verifikasi' => Mitra::STATUS_DISETUJUI])->save();
            }

            $this->actingAs($user)->get(route('direktori.index'))->assertForbidden();
            $this->actingAs($user)->get(route('direktori.show', $mhs->id))->assertForbidden();
        }

        $dosen = $this->buatDosen();
        $this->actingAs($this->pembaca)->get(route('direktori.index'))->assertOk();
        $this->actingAs($dosen)->get(route('direktori.index'))->assertOk();
    }

    // ---------- Siapa yang terlihat ----------

    public function test_daftar_memuat_mahasiswa_dan_dosen_yang_diizinkan_saja(): void
    {
        $this->buatMahasiswa(['name' => 'Mahasiswa Terlihat']);
        $this->buatDosen(['name' => 'Dosen Terlihat']);
        $this->buatMahasiswa(['name' => 'Disembunyikan Pemilik'], ['tampil_di_direktori' => false]);
        $this->buatMahasiswa(['name' => 'Disembunyikan Admin'], ['disembunyikan_admin' => true]);
        $this->buatMahasiswa(['name' => 'Akun Nonaktif', 'status' => User::STATUS_NONAKTIF]);
        $this->buatDosen(['name' => 'Dosen Disembunyikan'], ['tampil_di_direktori' => false]);
        User::factory()->create(['role' => User::ROLE_MITRA, 'email' => 'm@contoh.example', 'name' => 'Mitra Terlarang']);
        User::factory()->create(['role' => User::ROLE_ADMIN, 'email' => 'a@contoh.example', 'name' => 'Admin Terlarang']);

        $this->cari()->assertOk()
            ->assertSee('Mahasiswa Terlihat')
            ->assertSee('Dosen Terlihat')
            ->assertDontSee('Disembunyikan Pemilik')
            ->assertDontSee('Disembunyikan Admin')
            ->assertDontSee('Akun Nonaktif')
            ->assertDontSee('Dosen Disembunyikan')
            ->assertDontSee('Mitra Terlarang')
            ->assertDontSee('Admin Terlarang')
            ->assertSee('2 profil ditemukan');
    }

    public function test_profil_yang_tidak_boleh_tampil_juga_tidak_bisa_dibuka_lewat_tautan_langsung(): void
    {
        $tidakTampil = [
            $this->buatMahasiswa([], ['tampil_di_direktori' => false]),
            $this->buatMahasiswa([], ['disembunyikan_admin' => true]),
            $this->buatMahasiswa(['status' => User::STATUS_NONAKTIF]),
            $this->buatDosen([], ['tampil_di_direktori' => false]),
            User::factory()->create(['role' => User::ROLE_MITRA, 'email' => 'm@contoh.example']),
            User::factory()->create(['role' => User::ROLE_ADMIN, 'email' => 'a@contoh.example']),
        ];

        foreach ($tidakTampil as $user) {
            $this->actingAs($this->pembaca)->get(route('direktori.show', $user->id))->assertNotFound();
        }

        $this->actingAs($this->pembaca)->get('/direktori/99999')->assertNotFound();
        $this->actingAs($this->pembaca)->get('/direktori/abc')->assertNotFound();
    }

    public function test_pengguna_tidak_bisa_menampilkan_kembali_profil_yang_disembunyikan_admin(): void
    {
        $mhs = $this->buatMahasiswa(['name' => 'Disembunyikan Admin'], ['disembunyikan_admin' => true]);

        $this->actingAs($mhs)->put(route('profil.update'), [
            'name' => 'Disembunyikan Admin', 'program_studi' => 'Sistem Informasi Bisnis', 'kelas' => 'SIB-3A',
            'angkatan' => 2024, 'tampil_di_direktori' => '1', 'disembunyikan_admin' => '0',
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('mahasiswa', ['user_id' => $mhs->id, 'tampil_di_direktori' => true, 'disembunyikan_admin' => true]);
        $this->cari()->assertDontSee('Disembunyikan Admin');
        $this->actingAs($mhs)->get(route('profil.show'))->assertSee('disembunyikan dari direktori oleh admin');
    }

    // ---------- Filter ----------

    public function test_filter_tunggal(): void
    {
        $this->isiDataFilter();

        $this->assertHanya($this->cari(['keahlian' => Keahlian::where('nama', 'Basis Data')->value('id')]), ['bima', 'dewi']);
        $this->assertHanya($this->cari(['jurusan' => 'Akuntansi']), ['citra', 'eko']);
        $this->assertHanya($this->cari(['program_studi' => 'Teknik Informatika']), ['bima', 'dewi']);
        $this->assertHanya($this->cari(['kelas' => 'SIB-3A']), ['alya']);
        $this->assertHanya($this->cari(['angkatan' => 2023]), ['bima']);
        $this->assertHanya($this->cari(['q' => 'ALYA']), ['alya']); // nama, tanpa membedakan huruf
    }

    public function test_kombinasi_filter_bersifat_dan(): void
    {
        $this->isiDataFilter();
        $basisData = Keahlian::where('nama', 'Basis Data')->value('id');

        $this->assertHanya($this->cari(['jurusan' => 'Teknologi Informasi', 'keahlian' => $basisData]), ['bima', 'dewi']);
        $this->assertHanya($this->cari(['jurusan' => 'Teknologi Informasi', 'keahlian' => $basisData, 'angkatan' => 2023]), ['bima']);
        $this->assertHanya($this->cari(['jurusan' => 'Akuntansi', 'keahlian' => $basisData]), []);
        $this->assertHanya($this->cari(['program_studi' => 'Teknik Informatika', 'kelas' => 'TI-5A', 'angkatan' => 2023]), ['bima']);
        $this->assertHanya($this->cari(['program_studi' => 'Teknik Informatika', 'kelas' => 'TI-5A', 'angkatan' => 2024]), []);
    }

    public function test_tab_jenis_dan_filter_khusus_mahasiswa_mengecualikan_dosen(): void
    {
        $this->isiDataFilter();

        $this->assertHanya($this->cari(['jenis' => 'dosen']), ['dewi', 'eko']);
        $this->assertHanya($this->cari(['jenis' => 'mahasiswa']), ['alya', 'bima', 'citra']);

        // Kelas/angkatan hanya ada pada mahasiswa: dosen tidak ikut, dan jenis dosen + kelas tidak menghasilkan apa pun.
        $this->assertHanya($this->cari(['angkatan' => 2024]), ['alya']);
        $this->cari(['jenis' => 'dosen', 'kelas' => 'SIB-3A'])->assertSee('0 profil ditemukan')->assertSee('Tidak ada profil yang cocok');
    }

    public function test_tanpa_hasil_dan_tanpa_data_menampilkan_keadaan_kosong(): void
    {
        $this->cari()->assertOk()->assertSee('Belum ada profil di direktori');

        $this->isiDataFilter();
        $this->cari(['q' => 'tidak-ada-nama-ini'])->assertSee('Tidak ada profil yang cocok')->assertSee('Atur ulang');
    }

    public function test_masukan_filter_tidak_valid_ditolak(): void
    {
        $tidakValid = [
            ['angkatan' => 1900], ['angkatan' => 'abc'], ['keahlian' => 99999], ['program_studi' => 'Prodi Karangan'],
            ['jurusan' => 'Jurusan Fiktif'], ['jenis' => 'mitra'], ['q' => str_repeat('a', 101)],
        ];

        foreach ($tidakValid as $query) {
            $this->actingAs($this->pembaca)->from(route('direktori.index'))
                ->get(route('direktori.index', $query))
                ->assertSessionHasErrors(array_keys($query));
        }
    }

    public function test_karakter_khusus_pada_nama_aman(): void
    {
        $this->buatMahasiswa();

        foreach (["'; DROP TABLE users;--", '%', '_', '<script>alert(1)</script>'] as $q) {
            $this->cari(['q' => $q])->assertOk();
        }

        $this->assertDatabaseCount('mahasiswa', 1);
        $this->cari(['q' => '<script>alert(1)</script>'])->assertDontSee('<script>alert(1)</script>', false);
    }

    // ---------- Daftar ----------

    public function test_penomoran_dua_belas_per_halaman(): void
    {
        foreach (range(1, 13) as $i) {
            $this->buatMahasiswa(['name' => sprintf('Urut %02d', $i)]);
        }

        $this->cari()->assertSee('Urut 12')->assertDontSee('Urut 13')->assertSee('Halaman 1 dari 2')->assertSee('13 profil ditemukan');
        $this->cari(['page' => 2])->assertSee('Urut 13')->assertDontSee('Urut 01');
    }

    public function test_jumlah_query_tidak_bertambah_seiring_jumlah_profil(): void
    {
        $hitung = function (): int {
            DB::flushQueryLog();
            DB::enableQueryLog();
            $this->cari()->assertOk();
            $jumlah = count(DB::getQueryLog());
            DB::disableQueryLog();

            return $jumlah;
        };

        foreach (range(1, 3) as $i) {
            $this->buatMahasiswa([], [], ['Web Development', 'UI/UX Design']);
        }
        $sedikit = $hitung();

        foreach (range(1, 9) as $i) {
            $this->buatMahasiswa([], [], ['Web Development', 'UI/UX Design']);
            $this->buatDosen([], [], ['Basis Data']);
        }
        $banyak = $hitung();

        $this->assertSame($sedikit, $banyak, 'Query bertambah seiring jumlah profil: ada N+1.');
    }

    public function test_penanda_terverifikasi_google(): void
    {
        $terverifikasi = $this->buatMahasiswa(['name' => 'Sudah Verifikasi']);
        $terverifikasi->google_id = 'g-1';
        $terverifikasi->save();
        $biasa = $this->buatMahasiswa(['name' => 'Belum Verifikasi']);

        $this->cari()->assertSee('Terverifikasi');
        $this->actingAs($this->pembaca)->get(route('direktori.show', $terverifikasi->id))->assertSee('Terverifikasi Google');
        $this->actingAs($this->pembaca)->get(route('direktori.show', $biasa->id))->assertDontSee('Terverifikasi');
    }

    // ---------- Detail ----------

    public function test_profil_lengkap_mahasiswa_tanpa_data_pribadi_yang_tidak_diperlukan(): void
    {
        $mhs = $this->buatMahasiswa(['name' => 'Alya Putri', 'email' => 'alya.rahasia@polinema.ac.id'], [
            'nim' => '2441079001', 'kontak' => '081200000001', 'tentang' => 'Suka data.',
            'proyek' => 'Sistem inventaris kampus.', 'pencapaian' => 'Juara 2 lomba basis data.',
        ], ['Basis Data']);

        $this->actingAs($this->pembaca)->get(route('direktori.show', $mhs->id))
            ->assertOk()
            ->assertSee('Alya Putri')
            ->assertSee('SIB-3A')
            ->assertSee('081200000001')                   // kontak yang sengaja dibagikan
            ->assertSee('Suka data.')
            ->assertSee('Sistem inventaris kampus.')
            ->assertSee('Juara 2 lomba basis data.')
            ->assertSee('Basis Data')
            ->assertDontSee('2441079001')                 // NIM tidak ditampilkan
            ->assertDontSee('alya.rahasia@polinema.ac.id'); // email tidak ditampilkan
    }

    public function test_profil_lengkap_dosen_dan_kontak_kosong(): void
    {
        $dsn = $this->buatDosen(['name' => 'Dewi Lestari'], [
            'nidn' => '0701019001', 'bersedia_membimbing_magang' => true, 'bersedia_membimbing_lomba' => false,
        ]);

        $this->actingAs($this->pembaca)->get(route('direktori.show', $dsn->id))
            ->assertOk()
            ->assertSee('Dewi Lestari')
            ->assertSee('Bersedia membimbing')
            ->assertSee('Magang')
            ->assertSee('Belum mencantumkan kontak')
            ->assertDontSee('0701019001');
    }

    public function test_isian_profil_di_escape(): void
    {
        $mhs = $this->buatMahasiswa(['name' => '<b>Tebal</b>'], ['tentang' => '<script>alert(1)</script>']);

        $this->actingAs($this->pembaca)->get(route('direktori.show', $mhs->id))
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertDontSee('<b>Tebal</b>', false);
    }

    // ---------- Landing page ----------

    public function test_landing_page_tidak_menampilkan_profil_asli_kepada_tamu(): void
    {
        $this->buatMahasiswa(['name' => 'Rahasia Mahasiswa'], [], ['Basis Data']);

        $this->get('/')->assertOk()
            ->assertDontSee('Rahasia Mahasiswa')
            ->assertSee('Contoh tampilan');
    }
}
