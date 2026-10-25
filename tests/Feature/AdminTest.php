<?php

namespace Tests\Feature;

use App\Models\Beasiswa;
use App\Models\Keahlian;
use App\Models\Lomba;
use App\Models\Magang;
use App\Models\Mitra;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private int $urutan = 0;

    protected function setUp(): void
    {
        // RefreshDatabase menghapus seluruh tabel. Jangan sampai terjadi di Supabase.
        if (($_ENV['DB_CONNECTION'] ?? getenv('DB_CONNECTION')) !== 'sqlite') {
            $this->markTestSkipped('Uji ini hanya berjalan dengan SQLite. Atur DB_CONNECTION=sqlite dan DB_DATABASE=:memory: di phpunit.xml.');
        }

        parent::setUp();

        $this->withoutVite();
        $this->admin = User::factory()->create(['role' => User::ROLE_ADMIN, 'email' => 'admin@campuslink.test', 'name' => 'Admin Uji']);
    }

    // ---------- Pembantu ----------

    private function buatMitra(string $status = Mitra::STATUS_DISETUJUI, array $user = []): User
    {
        $n = ++$this->urutan;
        $u = User::factory()->create(array_merge(['role' => User::ROLE_MITRA, 'email' => "mitra{$n}@contoh.example", 'name' => "Mitra Nomor {$n}"], $user));
        $m = $u->mitra()->create(['nama_organisasi' => "PT Mitra {$n}", 'kontak' => '0341-000']);
        $m->status_verifikasi = $status;
        $m->save();

        return $u;
    }

    private function buatMahasiswa(array $user = [], array $profil = []): User
    {
        $n = ++$this->urutan;
        $u = User::factory()->create(array_merge(['role' => User::ROLE_MAHASISWA, 'email' => "mhs{$n}@polinema.ac.id", 'name' => "Mahasiswa Nomor {$n}"], $user));
        $u->mahasiswa()->create(array_merge([
            'nim' => "NIM{$n}", 'kelas' => 'SIB-3A', 'jurusan' => 'Teknologi Informasi',
            'program_studi' => 'Sistem Informasi Bisnis', 'angkatan' => 2024,
        ], $profil));

        return $u;
    }

    private function buatDosen(array $user = []): User
    {
        $n = ++$this->urutan;
        $u = User::factory()->create(array_merge(['role' => User::ROLE_DOSEN, 'email' => "dsn{$n}@polinema.ac.id", 'name' => "Dosen Nomor {$n}"], $user));
        $u->dosen()->create(['nidn' => "NIDN{$n}", 'jurusan' => 'Teknologi Informasi', 'program_studi' => 'Teknik Informatika']);

        return $u;
    }

    private function payload(string $kategori, array $ubah = []): array
    {
        $umum = [
            'judul' => 'Judul Uji', 'deskripsi' => 'Deskripsi uji.', 'penyelenggara' => 'Kampus Polinema',
            'batas_pendaftaran' => now()->addDays(10)->toDateString(),
            'kontak' => 'kontak@contoh.example', 'tautan_pendaftaran' => 'https://contoh.example/daftar',
        ];

        $khusus = match ($kategori) {
            'magang' => ['persyaratan' => 'Menguasai PHP.', 'lokasi' => 'Malang', 'periode_mulai' => now()->addMonths(2)->toDateString(), 'periode_selesai' => now()->addMonths(4)->toDateString()],
            'lomba' => ['persyaratan' => '', 'tingkat' => 'nasional', 'tanggal_mulai' => now()->addDays(30)->toDateString(), 'tanggal_selesai' => now()->addDays(31)->toDateString()],
            'beasiswa' => ['persyaratan' => 'IPK minimal 3,5.'],
        };

        return array_merge($umum, $khusus, $ubah);
    }

    private function buatItem(User $pemilik, string $kategori, array $ubah = [])
    {
        $status = $ubah['status'] ?? null;
        unset($ubah['status']);

        $item = $pemilik->{$kategori}()->create($this->payload($kategori, ['persyaratan' => 'Syarat uji.'] + $ubah));

        if ($status) {
            $item->status = $status;
            $item->save();
        }

        return $item;
    }

    private function sebagaiAdmin()
    {
        return $this->actingAs($this->admin);
    }

    private function pembaca(): User
    {
        return User::factory()->create(['role' => User::ROLE_MAHASISWA, 'email' => 'pembaca@polinema.ac.id', 'name' => 'Pembaca Uji']);
    }

    // ---------- Akses ----------

    public function test_hanya_admin_yang_boleh_membuka_panel(): void
    {
        $urls = [
            route('admin.dashboard'), route('admin.mitra.index'), route('admin.pengguna.index'),
            route('admin.keahlian.index'), route('admin.informasi.index', 'magang'),
            route('admin.informasi.index', 'lomba'), route('admin.informasi.index', 'beasiswa'),
            route('admin.informasi.create', 'magang'),
        ];

        foreach ($urls as $url) {
            $this->get($url)->assertRedirect(route('login'));
        }

        $dosen = $this->buatDosen();
        foreach ([$this->buatMahasiswa(), $dosen, $this->buatMitra()] as $user) {
            foreach ($urls as $url) {
                $this->actingAs($user)->get($url)->assertForbidden();
            }
        }

        foreach ($urls as $url) {
            $this->sebagaiAdmin()->get($url)->assertOk();
        }
    }

    public function test_aksi_ubah_data_ditolak_untuk_peran_lain_dan_tidak_mengubah_apa_pun(): void
    {
        $pending = $this->buatMitra(Mitra::STATUS_PENDING);
        $mitraLain = $this->buatMitra();
        $mhs = $this->buatMahasiswa();
        $item = $this->buatItem($mitraLain, 'magang');
        $keahlian = Keahlian::create(['nama' => 'Basis Data']);

        $penyerang = [$this->buatMitra(), $mhs];
        foreach ($penyerang as $user) {
            $this->actingAs($user)->patch(route('admin.mitra.setujui', $pending->mitra->id))->assertForbidden();
            $this->actingAs($user)->patch(route('admin.pengguna.nonaktifkan', $mhs->id))->assertForbidden();
            $this->actingAs($user)->patch(route('admin.pengguna.sembunyikan', $mhs->id))->assertForbidden();
            $this->actingAs($user)->patch(route('admin.informasi.sembunyikan', ['magang', $item->id]))->assertForbidden();
            $this->actingAs($user)->delete(route('admin.keahlian.destroy', $keahlian->id))->assertForbidden();
        }

        $this->assertSame(Mitra::STATUS_PENDING, $pending->mitra->fresh()->status_verifikasi);
        $this->assertSame('aktif', $mhs->fresh()->status);
        $this->assertFalse((bool) $mhs->mahasiswa->fresh()->disembunyikan_admin);
        $this->assertSame('tayang', $item->fresh()->status);
        $this->assertDatabaseHas('keahlian', ['id' => $keahlian->id]);
    }

    // ---------- Dasbor ----------

    public function test_dasbor_menampilkan_ringkasan_pengguna_dan_informasi(): void
    {
        $this->buatMahasiswa();
        $this->buatMahasiswa(['status' => User::STATUS_NONAKTIF]);
        $this->buatDosen();
        $mitra = $this->buatMitra();
        $this->buatMitra(Mitra::STATUS_PENDING);

        $this->buatItem($mitra, 'magang');
        $this->buatItem($mitra, 'magang', ['batas_pendaftaran' => now()->subDays(2)->toDateString()]);
        $this->buatItem($mitra, 'lomba', ['status' => Lomba::STATUS_DISEMBUNYIKAN]);

        $this->sebagaiAdmin()->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('1 akun mitra menunggu verifikasi')
            ->assertViewHas('menunggu', 1)
            ->assertViewHas('pengguna', fn (array $p) => $p['mahasiswa']['total'] === 2 && $p['mahasiswa']['nonaktif'] === 1
                && $p['dosen']['total'] === 1 && $p['mitra']['total'] === 2)   // admin tidak ikut terhitung
            ->assertViewHas('informasi', fn (array $i) => $i['magang'] == ['label' => 'Magang', 'total' => 2, 'dibuka' => 1, 'disembunyikan' => 0, 'ditutup' => 1]
                && $i['lomba']['disembunyikan'] === 1 && $i['beasiswa']['total'] === 0);
    }

    // ---------- Verifikasi mitra ----------

    public function test_daftar_verifikasi_menampilkan_yang_menunggu_dan_jumlah_tiap_status(): void
    {
        $this->buatMitra(Mitra::STATUS_PENDING, ['name' => 'Penanggung Menunggu']);
        $this->buatMitra(Mitra::STATUS_DISETUJUI, ['name' => 'Penanggung Disetujui']);
        $this->buatMitra(Mitra::STATUS_DITOLAK, ['name' => 'Penanggung Ditolak']);

        $this->sebagaiAdmin()->get(route('admin.mitra.index'))
            ->assertSee('Penanggung Menunggu')->assertDontSee('Penanggung Disetujui')->assertDontSee('Penanggung Ditolak');

        $this->sebagaiAdmin()->get(route('admin.mitra.index', ['verifikasi' => 'disetujui']))
            ->assertSee('Penanggung Disetujui')->assertDontSee('Penanggung Menunggu');

        $this->sebagaiAdmin()->get(route('admin.mitra.index'))->assertViewHas('jumlah', fn ($j) => $j['pending'] == 1 && $j['disetujui'] == 1 && $j['ditolak'] == 1);

        $this->sebagaiAdmin()->from(route('admin.mitra.index'))->get(route('admin.mitra.index', ['verifikasi' => 'bebas']))
            ->assertSessionHasErrors('verifikasi');
    }

    public function test_menyetujui_mitra_membuatnya_bisa_masuk(): void
    {
        $pending = $this->buatMitra(Mitra::STATUS_PENDING, ['email' => 'pending@contoh.example']);

        $this->sebagaiAdmin()->patch(route('admin.mitra.setujui', $pending->mitra->id))->assertSessionHas('status');
        $this->assertSame(Mitra::STATUS_DISETUJUI, $pending->mitra->fresh()->status_verifikasi);

        $this->post(route('logout'));
        $this->post(route('login.store'), ['email' => 'pending@contoh.example', 'password' => 'password'])
            ->assertRedirect(route('mitra.dashboard'));
        $this->assertAuthenticatedAs($pending);
    }

    public function test_menolak_mitra_menghalanginya_masuk_dan_bisa_dibalik(): void
    {
        $mitra = $this->buatMitra(Mitra::STATUS_PENDING, ['email' => 'pending@contoh.example']);

        $this->sebagaiAdmin()->patch(route('admin.mitra.tolak', $mitra->mitra->id));
        $this->assertSame(Mitra::STATUS_DITOLAK, $mitra->mitra->fresh()->status_verifikasi);

        $this->post(route('logout'));
        $this->post(route('login.store'), ['email' => 'pending@contoh.example', 'password' => 'password'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->sebagaiAdmin()->patch(route('admin.mitra.setujui', $mitra->mitra->id));
        $this->assertSame(Mitra::STATUS_DISETUJUI, $mitra->mitra->fresh()->status_verifikasi);
    }

    // ---------- Pengguna ----------

    public function test_daftar_pengguna_tanpa_admin_dan_dengan_filter(): void
    {
        $this->buatMahasiswa(['name' => 'Alya Putri']);
        $this->buatMahasiswa(['name' => 'Bima Nonaktif', 'status' => User::STATUS_NONAKTIF]);
        $this->buatDosen(['name' => 'Dewi Dosen']);
        $this->buatMitra(Mitra::STATUS_DISETUJUI, ['name' => 'Mitra Budi']);

        $respons = $this->sebagaiAdmin()->get(route('admin.pengguna.index'))
            ->assertOk()->assertSee('Alya Putri')->assertSee('Dewi Dosen')->assertSee('Mitra Budi')
            ->assertSee('4 pengguna');
        // Nama admin tampil di header (menu akun), jadi pemeriksaan "admin tidak ikut terdaftar" dibatasi ke isi <main>.
        preg_match('/<main\b.*<\/main>/s', $respons->getContent(), $isi);
        $this->assertStringNotContainsString('Admin Uji', $isi[0] ?? '');

        $this->sebagaiAdmin()->get(route('admin.pengguna.index', ['peran' => 'dosen']))
            ->assertSee('Dewi Dosen')->assertDontSee('Alya Putri');
        $this->sebagaiAdmin()->get(route('admin.pengguna.index', ['akun' => 'nonaktif']))
            ->assertSee('Bima Nonaktif')->assertDontSee('Alya Putri');
        $this->sebagaiAdmin()->get(route('admin.pengguna.index', ['q' => 'ALYA']))
            ->assertSee('Alya Putri')->assertDontSee('Dewi Dosen');
        $this->sebagaiAdmin()->get(route('admin.pengguna.index', ['q' => 'mitra1@contoh']))->assertOk(); // cari lewat email

        $this->sebagaiAdmin()->from(route('admin.pengguna.index'))
            ->get(route('admin.pengguna.index', ['peran' => 'admin']))->assertSessionHasErrors('peran');
    }

    public function test_jumlah_query_daftar_pengguna_tidak_bertambah_seiring_jumlah_data(): void
    {
        $hitung = function (): int {
            DB::flushQueryLog();
            DB::enableQueryLog();
            $this->sebagaiAdmin()->get(route('admin.pengguna.index'))->assertOk();
            $jumlah = count(DB::getQueryLog());
            DB::disableQueryLog();

            return $jumlah;
        };

        foreach (range(1, 3) as $i) {
            $this->buatMahasiswa();
        }
        $sedikit = $hitung();

        foreach (range(1, 4) as $i) {
            $this->buatMahasiswa();
            $this->buatDosen();
            $this->buatMitra();
        }
        $banyak = $hitung();

        $this->assertSame($sedikit, $banyak, 'Query bertambah seiring jumlah pengguna: ada N+1.');
    }

    public function test_detail_pengguna_dan_akun_admin_tidak_bisa_dikelola(): void
    {
        $mhs = $this->buatMahasiswa(['name' => 'Alya Putri'], ['nim' => '2441079001']);

        $this->sebagaiAdmin()->get(route('admin.pengguna.show', $mhs->id))
            ->assertOk()->assertSee('Alya Putri')->assertSee('2441079001');

        // Akun admin dan id yang tidak ada: 404 untuk semua tindakan.
        $this->sebagaiAdmin()->get(route('admin.pengguna.show', $this->admin->id))->assertNotFound();
        $this->sebagaiAdmin()->get(route('admin.pengguna.edit', $this->admin->id))->assertNotFound();
        $this->sebagaiAdmin()->patch(route('admin.pengguna.nonaktifkan', $this->admin->id))->assertNotFound();
        $this->sebagaiAdmin()->put(route('admin.pengguna.update', $this->admin->id), ['email' => 'baru@polinema.ac.id'])->assertNotFound();
        $this->sebagaiAdmin()->get('/admin/pengguna/99999')->assertNotFound();
        $this->sebagaiAdmin()->get('/admin/pengguna/abc')->assertNotFound();

        $this->assertSame('aktif', $this->admin->fresh()->status);
    }

    public function test_admin_mengubah_email_dan_nim_mahasiswa(): void
    {
        $mhs = $this->buatMahasiswa(['email' => 'lama@polinema.ac.id']);

        $this->sebagaiAdmin()->put(route('admin.pengguna.update', $mhs->id), ['email' => 'BARU@Polinema.ac.id', 'nim' => '2441079999'])
            ->assertRedirect(route('admin.pengguna.show', $mhs->id));

        $this->assertSame('baru@polinema.ac.id', $mhs->fresh()->email);        // huruf kecil
        $this->assertDatabaseHas('mahasiswa', ['user_id' => $mhs->id, 'nim' => '2441079999']);
    }

    public function test_aturan_ubah_data_pengguna(): void
    {
        $a = $this->buatMahasiswa(['email' => 'a@polinema.ac.id'], ['nim' => 'NIM-A']);
        $b = $this->buatMahasiswa(['email' => 'b@polinema.ac.id'], ['nim' => 'NIM-B']);
        $dosen = $this->buatDosen(['email' => 'd@polinema.ac.id']);
        $mitra = $this->buatMitra(Mitra::STATUS_DISETUJUI, ['email' => 'm@contoh.example']);

        // Mahasiswa dan dosen wajib email kampus.
        $this->sebagaiAdmin()->put(route('admin.pengguna.update', $a->id), ['email' => 'a@gmail.com', 'nim' => 'NIM-A'])->assertSessionHasErrors('email');
        $this->sebagaiAdmin()->put(route('admin.pengguna.update', $dosen->id), ['email' => 'd@gmail.com', 'nidn' => 'X1'])->assertSessionHasErrors('email');

        // Email dan NIM harus unik terhadap pengguna lain.
        $this->sebagaiAdmin()->put(route('admin.pengguna.update', $a->id), ['email' => 'b@polinema.ac.id', 'nim' => 'NIM-A'])->assertSessionHasErrors('email');
        $this->sebagaiAdmin()->put(route('admin.pengguna.update', $a->id), ['email' => 'a@polinema.ac.id', 'nim' => 'NIM-B'])->assertSessionHasErrors('nim');

        // Tidak mengubah apa pun (email dan NIM sendiri) tetap valid.
        $this->sebagaiAdmin()->put(route('admin.pengguna.update', $a->id), ['email' => 'a@polinema.ac.id', 'nim' => 'NIM-A'])->assertSessionHasNoErrors();

        // Mitra bebas memakai email apa saja.
        $this->sebagaiAdmin()->put(route('admin.pengguna.update', $mitra->id), ['email' => 'baru@gmail.com'])->assertSessionHasNoErrors();
        $this->assertSame('baru@gmail.com', $mitra->fresh()->email);
    }

    public function test_mengubah_email_membatalkan_verifikasi_google_tetapi_email_sama_tidak(): void
    {
        $mhs = $this->buatMahasiswa(['email' => 'lama@polinema.ac.id']);
        $mhs->google_id = 'g-1';
        $mhs->save();

        $this->sebagaiAdmin()->put(route('admin.pengguna.update', $mhs->id), ['email' => 'lama@polinema.ac.id', 'nim' => 'NIMBARU']);
        $this->assertTrue($mhs->fresh()->terverifikasiGoogle());

        $this->sebagaiAdmin()->put(route('admin.pengguna.update', $mhs->id), ['email' => 'baru@polinema.ac.id', 'nim' => 'NIMBARU']);
        $this->assertFalse($mhs->fresh()->terverifikasiGoogle());
    }

    // ---------- Nonaktifkan dan aktifkan ----------

    public function test_menonaktifkan_akun_menghalangi_login_dan_langsung_mengeluarkan_sesi(): void
    {
        $mhs = $this->buatMahasiswa(['email' => 'target@polinema.ac.id']);
        $this->actingAs($mhs)->get(route('profil.show'))->assertOk();

        $this->sebagaiAdmin()->patch(route('admin.pengguna.nonaktifkan', $mhs->id))->assertSessionHas('status');
        $this->assertSame('nonaktif', $mhs->fresh()->status);

        // fresh(): objek lama masih membawa status lama di memori.
        $this->actingAs($mhs->fresh())->get(route('profil.show'))->assertRedirect(route('login'));
        $this->assertGuest();

        $this->post(route('login.store'), ['email' => 'target@polinema.ac.id', 'password' => 'password'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_akun_nonaktif_hilang_dari_direktori_dan_bisa_diaktifkan_kembali(): void
    {
        $pembaca = $this->pembaca();
        $mhs = $this->buatMahasiswa(['name' => 'Target Nonaktif']);

        $this->actingAs($pembaca)->get(route('direktori.index'))->assertSee('Target Nonaktif');

        $this->sebagaiAdmin()->patch(route('admin.pengguna.nonaktifkan', $mhs->id));
        $this->actingAs($pembaca)->get(route('direktori.index'))->assertDontSee('Target Nonaktif');

        $this->sebagaiAdmin()->patch(route('admin.pengguna.aktifkan', $mhs->id));
        $this->actingAs($pembaca)->get(route('direktori.index'))->assertSee('Target Nonaktif');
    }

    public function test_menonaktifkan_mitra_dapat_sekaligus_menyembunyikan_semua_informasinya(): void
    {
        $mitra = $this->buatMitra();
        $semua = [
            $this->buatItem($mitra, 'magang'), $this->buatItem($mitra, 'lomba'), $this->buatItem($mitra, 'beasiswa'),
        ];

        $this->sebagaiAdmin()->patch(route('admin.pengguna.nonaktifkan', $mitra->id), ['sembunyikan_informasi' => '1']);

        foreach ($semua as $item) {
            $this->assertSame('disembunyikan', $item->fresh()->status);
        }

        // Mengaktifkan kembali tidak otomatis menampilkan ulang.
        $this->sebagaiAdmin()->patch(route('admin.pengguna.aktifkan', $mitra->id));
        $this->assertSame('aktif', $mitra->fresh()->status);
        $this->assertSame('disembunyikan', $semua[0]->fresh()->status);
    }

    public function test_menonaktifkan_mitra_tanpa_pilihan_tidak_menyentuh_informasinya(): void
    {
        $mitra = $this->buatMitra();
        $item = $this->buatItem($mitra, 'magang');

        $this->sebagaiAdmin()->patch(route('admin.pengguna.nonaktifkan', $mitra->id));

        $this->assertSame('nonaktif', $mitra->fresh()->status);
        $this->assertSame('tayang', $item->fresh()->status);
    }

    // ---------- Sembunyikan profil dari direktori (FA-08) ----------

    public function test_admin_menyembunyikan_dan_menampilkan_kembali_profil_di_direktori(): void
    {
        $pembaca = $this->pembaca();
        $mhs = $this->buatMahasiswa(['name' => 'Profil Bermasalah']);

        $this->sebagaiAdmin()->patch(route('admin.pengguna.sembunyikan', $mhs->id));
        $this->assertTrue((bool) $mhs->mahasiswa->fresh()->disembunyikan_admin);
        $this->actingAs($pembaca)->get(route('direktori.index'))->assertDontSee('Profil Bermasalah');
        $this->actingAs($pembaca)->get(route('direktori.show', $mhs->id))->assertNotFound();

        $this->sebagaiAdmin()->patch(route('admin.pengguna.tampilkan', $mhs->id));
        $this->assertFalse((bool) $mhs->mahasiswa->fresh()->disembunyikan_admin);
        $this->actingAs($pembaca)->get(route('direktori.index'))->assertSee('Profil Bermasalah');
    }

    public function test_mitra_tidak_punya_profil_direktori_untuk_disembunyikan(): void
    {
        $mitra = $this->buatMitra();

        $this->sebagaiAdmin()->patch(route('admin.pengguna.sembunyikan', $mitra->id))->assertNotFound();
    }

    // ---------- Informasi: kelola dan moderasi ----------

    public function test_daftar_informasi_memuat_semua_mitra_dan_bisa_disaring(): void
    {
        $a = $this->buatMitra(Mitra::STATUS_DISETUJUI, ['name' => 'Pembuat Alpha']);
        $b = $this->buatMitra(Mitra::STATUS_DISETUJUI, ['name' => 'Pembuat Beta']);
        $this->buatItem($a, 'magang', ['judul' => 'Magang Terbuka']);
        $this->buatItem($b, 'magang', ['judul' => 'Magang Lewat', 'batas_pendaftaran' => now()->subDays(3)->toDateString()]);
        $this->buatItem($b, 'magang', ['judul' => 'Magang Tersembunyi', 'status' => Magang::STATUS_DISEMBUNYIKAN]);

        $this->sebagaiAdmin()->get(route('admin.informasi.index', 'magang'))
            ->assertSee('Magang Terbuka')->assertSee('Magang Lewat')->assertSee('Magang Tersembunyi')
            ->assertSee('Pembuat Alpha')->assertSee('Pembuat Beta')->assertSee('3 informasi');

        $this->sebagaiAdmin()->get(route('admin.informasi.index', ['magang', 'tayang' => 'dibuka']))
            ->assertSee('Magang Terbuka')->assertDontSee('Magang Lewat')->assertDontSee('Magang Tersembunyi');
        $this->sebagaiAdmin()->get(route('admin.informasi.index', ['magang', 'tayang' => 'ditutup']))
            ->assertSee('Magang Lewat')->assertDontSee('Magang Terbuka');
        $this->sebagaiAdmin()->get(route('admin.informasi.index', ['magang', 'tayang' => 'disembunyikan']))
            ->assertSee('Magang Tersembunyi')->assertDontSee('Magang Terbuka');
        $this->sebagaiAdmin()->get(route('admin.informasi.index', ['magang', 'q' => 'lewat']))
            ->assertSee('Magang Lewat')->assertDontSee('Magang Terbuka');

        $this->sebagaiAdmin()->get('/admin/podcast')->assertNotFound();
    }

    public function test_admin_menambah_informasi_dengan_penyelenggara_sendiri(): void
    {
        foreach (['magang', 'lomba', 'beasiswa'] as $kategori) {
            $this->sebagaiAdmin()->post(route('admin.informasi.store', $kategori), $this->payload($kategori))
                ->assertRedirect(route('admin.informasi.index', $kategori));

            $this->assertDatabaseHas($kategori, ['user_id' => $this->admin->id, 'penyelenggara' => 'Kampus Polinema', 'status' => 'tayang']);
        }
    }

    public function test_aturan_validasi_admin_sama_dengan_mitra_ditambah_penyelenggara(): void
    {
        $this->sebagaiAdmin()->post(route('admin.informasi.store', 'magang'), $this->payload('magang', ['penyelenggara' => '']))
            ->assertSessionHasErrors('penyelenggara');
        $this->sebagaiAdmin()->post(route('admin.informasi.store', 'magang'), $this->payload('magang', ['tautan_pendaftaran' => 'javascript:alert(1)']))
            ->assertSessionHasErrors('tautan_pendaftaran');
        $this->sebagaiAdmin()->post(route('admin.informasi.store', 'magang'), $this->payload('magang', ['batas_pendaftaran' => '2000-01-01']))
            ->assertSessionHasErrors('batas_pendaftaran');

        $this->assertDatabaseCount('magang', 0);
    }

    public function test_admin_mengubah_dan_menghapus_informasi_milik_mitra(): void
    {
        $mitra = $this->buatMitra();
        $item = $this->buatItem($mitra, 'magang', ['status' => Magang::STATUS_DISEMBUNYIKAN]);

        $this->sebagaiAdmin()->get(route('admin.informasi.edit', ['magang', $item->id]))->assertOk();

        $this->sebagaiAdmin()->put(route('admin.informasi.update', ['magang', $item->id]), $this->payload('magang', ['judul' => 'Diperbaiki Admin', 'penyelenggara' => 'Nama Baru']))
            ->assertRedirect(route('admin.informasi.index', 'magang'));

        $this->assertDatabaseHas('magang', ['id' => $item->id, 'judul' => 'Diperbaiki Admin', 'penyelenggara' => 'Nama Baru', 'user_id' => $mitra->id, 'status' => 'disembunyikan']);

        $this->sebagaiAdmin()->delete(route('admin.informasi.destroy', ['magang', $item->id]))->assertRedirect();
        $this->assertDatabaseMissing('magang', ['id' => $item->id]);
    }

    public function test_moderasi_menyembunyikan_dan_menampilkan_kembali_informasi(): void
    {
        $mitra = $this->buatMitra();
        $item = $this->buatItem($mitra, 'magang', ['judul' => 'Magang Dimoderasi']);
        $pembaca = $this->pembaca();

        $this->actingAs($pembaca)->get(route('magang.index'))->assertSee('Magang Dimoderasi');

        $this->sebagaiAdmin()->patch(route('admin.informasi.sembunyikan', ['magang', $item->id]))->assertSessionHas('status');
        $this->assertSame('disembunyikan', $item->fresh()->status);
        $this->actingAs($pembaca)->get(route('magang.index'))->assertDontSee('Magang Dimoderasi');
        $this->actingAs($pembaca)->get(route('magang.show', $item))->assertNotFound();
        $this->actingAs($mitra)->get(route('mitra.informasi.index', 'magang'))->assertSee('Disembunyikan admin');

        $this->sebagaiAdmin()->patch(route('admin.informasi.tampilkan', ['magang', $item->id]))->assertSessionHas('status');
        $this->actingAs($pembaca)->get(route('magang.index'))->assertSee('Magang Dimoderasi');
    }

    // ---------- Keahlian ----------

    public function test_menambah_dan_menolak_keahlian_ganda_tanpa_membedakan_huruf(): void
    {
        $this->sebagaiAdmin()->post(route('admin.keahlian.store'), ['nama' => 'Desain Produk'])->assertRedirect(route('admin.keahlian.index'));
        $this->assertDatabaseHas('keahlian', ['nama' => 'Desain Produk']);

        $this->sebagaiAdmin()->post(route('admin.keahlian.store'), ['nama' => 'desain PRODUK'])->assertSessionHasErrors('nama');
        $this->sebagaiAdmin()->post(route('admin.keahlian.store'), ['nama' => '   '])->assertSessionHasErrors('nama');
        $this->sebagaiAdmin()->post(route('admin.keahlian.store'), ['nama' => str_repeat('a', 101)])->assertSessionHasErrors('nama');

        $this->assertDatabaseCount('keahlian', 1);
    }

    public function test_mengubah_nama_keahlian(): void
    {
        $a = Keahlian::create(['nama' => 'Basis Data']);
        Keahlian::create(['nama' => 'Jaringan']);

        $this->sebagaiAdmin()->put(route('admin.keahlian.update', $a->id), ['nama' => 'Basis Data Lanjut'])->assertRedirect(route('admin.keahlian.index'));
        $this->assertSame('Basis Data Lanjut', $a->fresh()->nama);

        // Menyimpan nama sendiri tidak dianggap duplikat; memakai nama keahlian lain, ya.
        $this->sebagaiAdmin()->put(route('admin.keahlian.update', $a->id), ['nama' => 'Basis Data Lanjut'])->assertSessionHasNoErrors();
        $this->sebagaiAdmin()->put(route('admin.keahlian.update', $a->id), ['nama' => 'jaringan'])->assertSessionHasErrors('nama');
    }

    public function test_menghapus_keahlian_melepasnya_dari_profil_tanpa_menghapus_pengguna(): void
    {
        $keahlian = Keahlian::create(['nama' => 'Basis Data']);
        $mhs = $this->buatMahasiswa();
        $mhs->keahlian()->attach($keahlian->id);

        $this->sebagaiAdmin()->get(route('admin.keahlian.index'))->assertSee('1 pengguna');

        $this->sebagaiAdmin()->delete(route('admin.keahlian.destroy', $keahlian->id))->assertRedirect(route('admin.keahlian.index'));

        $this->assertDatabaseMissing('keahlian', ['id' => $keahlian->id]);
        $this->assertDatabaseMissing('pengguna_keahlian', ['keahlian_id' => $keahlian->id]);
        $this->assertDatabaseHas('users', ['id' => $mhs->id]);
        $this->assertSame(0, $mhs->keahlian()->count());
    }
}
