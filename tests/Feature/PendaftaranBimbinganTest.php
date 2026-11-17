<?php

namespace Tests\Feature;

use App\Models\Bimbingan;
use App\Models\Lomba;
use App\Models\Magang;
use App\Models\Mitra;
use App\Models\Pendaftaran;
use App\Models\User;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/** Pendaftaran lewat CampusLink (CV), peninjauan oleh mitra/admin, dan bimbingan dosen. */
class PendaftaranBimbinganTest extends TestCase
{
    use RefreshDatabase;

    private int $urutan = 0;

    protected function setUp(): void
    {
        // RefreshDatabase menghapus seluruh tabel. Jangan sampai terjadi di Supabase.
        if (($_ENV['DB_CONNECTION'] ?? getenv('DB_CONNECTION')) !== 'sqlite') {
            $this->markTestSkipped('Uji ini hanya berjalan dengan SQLite. Atur DB_CONNECTION=sqlite dan DB_DATABASE=:memory: di phpunit.xml.');
        }

        parent::setUp();
        $this->withoutVite();
        Storage::fake('local');
    }

    // ---------- Pembantu ----------

    /** Disk privat palsu dengan tipe yang jelas, agar editor mengenali assertExists/assertMissing. */
    private function disk(): FilesystemAdapter
    {
        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('local');

        return $disk;
    }

    private function mahasiswa(): User
    {
        $n = ++$this->urutan;
        $u = User::factory()->create(['role' => User::ROLE_MAHASISWA, 'email' => "mhs{$n}@polinema.ac.id", 'name' => "Mahasiswa {$n}"]);
        $u->mahasiswa()->create([
            'nim' => "NIM{$n}",
            'kelas' => 'SIB-3A',
            'jurusan' => 'Teknologi Informasi',
            'program_studi' => 'Sistem Informasi Bisnis',
            'angkatan' => 2024,
            'tampil_di_direktori' => true,
        ]);

        return $u;
    }

    private function dosen(bool $magang = true, bool $lomba = true): User
    {
        $n = ++$this->urutan;
        $u = User::factory()->create(['role' => User::ROLE_DOSEN, 'email' => "dsn{$n}@polinema.ac.id", 'name' => "Dosen {$n}"]);
        $u->dosen()->create([
            'nidn' => "NIDN{$n}",
            'jurusan' => 'Teknologi Informasi',
            'program_studi' => 'Teknik Informatika',
            'bersedia_membimbing_magang' => $magang,
            'bersedia_membimbing_lomba' => $lomba,
        ]);

        return $u;
    }

    private function mitra(string $email = 'a@mitra.example'): User
    {
        $u = User::factory()->create(['role' => User::ROLE_MITRA, 'email' => $email]);
        $m = $u->mitra()->create(['nama_organisasi' => 'PT '.$email, 'kontak' => '0341']);
        $m->status_verifikasi = Mitra::STATUS_DISETUJUI;
        $m->save();

        return $u;
    }

    private function admin(): User
    {
        return User::factory()->create(['role' => User::ROLE_ADMIN, 'email' => 'admin'.(++$this->urutan).'@polinema.ac.id']);
    }

    private function magang(User $pemilik, string $cara = 'internal'): Magang
    {
        return $pemilik->magang()->create([
            'judul' => 'Magang Uji',
            'penyelenggara' => 'PT Uji',
            'deskripsi' => 'Desk.',
            'persyaratan' => 'Syarat.',
            'periode_mulai' => now()->addMonth()->toDateString(),
            'periode_selesai' => now()->addMonths(3)->toDateString(),
            'batas_pendaftaran' => now()->addDays(10)->toDateString(),
            'kontak' => 'k@uji.example',
            'tautan_pendaftaran' => 'https://uji.example/daftar',
            'cara_daftar' => $cara,
        ]);
    }

    private function lomba(User $pemilik, string $cara = 'internal'): Lomba
    {
        return $pemilik->lomba()->create([
            'judul' => 'Lomba Uji',
            'penyelenggara' => 'Kampus',
            'deskripsi' => 'Desk.',
            'tingkat' => 'kampus',
            'tanggal_mulai' => now()->addMonth()->toDateString(),
            'tanggal_selesai' => now()->addMonth()->toDateString(),
            'batas_pendaftaran' => now()->addDays(10)->toDateString(),
            'kontak' => 'k@uji.example',
            'cara_daftar' => $cara,
        ]);
    }

    private function cv(): UploadedFile
    {
        return UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf');
    }

    // ---------- Pendaftaran ----------

    public function test_mahasiswa_mendaftar_dengan_cv_ke_informasi_mitra(): void
    {
        $mhs = $this->mahasiswa();
        $item = $this->magang($this->mitra());

        $this->actingAs($mhs)->get(route('magang.show', $item))->assertOk()->assertSee('Daftar sekarang');
        $this->actingAs($mhs)->post(route('pendaftaran.store', ['magang', $item->id]), ['cv' => $this->cv()])->assertRedirect();

        $p = Pendaftaran::sole();
        $this->assertSame('menunggu', $p->status);
        $this->disk()->assertExists($p->cv);
    }

    public function test_cv_wajib_pdf_dan_tidak_boleh_mendaftar_dua_kali(): void
    {
        $mhs = $this->mahasiswa();
        $item = $this->magang($this->mitra());
        $url = route('pendaftaran.store', ['magang', $item->id]);

        $this->actingAs($mhs)->post($url, [])->assertSessionHasErrors('cv');
        $this->actingAs($mhs)->post($url, ['cv' => UploadedFile::fake()->create('cv.txt', 10, 'text/plain')])->assertSessionHasErrors('cv');
        $this->actingAs($mhs)->post($url, ['cv' => $this->cv()]);
        $this->actingAs($mhs)->post($url, ['cv' => $this->cv()])->assertSessionHas('galat');

        $this->assertDatabaseCount('pendaftaran', 1);
    }

    public function test_kegiatan_luar_tidak_bisa_didaftar_di_campuslink(): void
    {
        $item = $this->magang($this->admin(), 'eksternal');

        $this->actingAs($this->mahasiswa())->get(route('magang.show', $item))->assertSee('Buka halaman pendaftaran')->assertDontSee('Daftar sekarang');
        $this->actingAs($this->mahasiswa())->post(route('pendaftaran.store', ['magang', $item->id]), ['cv' => $this->cv()])->assertNotFound();
    }

    public function test_hanya_mahasiswa_yang_bisa_mendaftar(): void
    {
        $item = $this->magang($this->mitra());

        $this->actingAs($this->dosen())->post(route('pendaftaran.store', ['magang', $item->id]), ['cv' => $this->cv()])->assertForbidden();
    }

    // ---------- Peninjauan ----------

    public function test_mitra_pemilik_meninjau_dan_mitra_lain_tidak(): void
    {
        $pemilik = $this->mitra();
        $lain = $this->mitra('b@mitra.example');
        $mhs = $this->mahasiswa();
        $item = $this->magang($pemilik);
        $this->actingAs($mhs)->post(route('pendaftaran.store', ['magang', $item->id]), ['cv' => $this->cv()]);
        $p = Pendaftaran::sole();

        $this->actingAs($pemilik)->get(route('mitra.pendaftar.index'))->assertSee($mhs->name);
        $this->actingAs($pemilik)->get(route('pendaftaran.cv', $p->id))->assertOk();
        $this->actingAs($lain)->get(route('mitra.pendaftar.show', $p->id))->assertNotFound();
        $this->actingAs($lain)->get(route('pendaftaran.cv', $p->id))->assertForbidden();
        $this->actingAs($this->mahasiswa())->get(route('pendaftaran.cv', $p->id))->assertForbidden();
        $this->actingAs($this->admin())->get(route('admin.pendaftar.show', $p->id))->assertNotFound();

        $this->actingAs($pemilik)->patch(route('mitra.pendaftar.terima', $p->id), ['catatan' => 'Wawancara Senin']);
        $this->assertSame('diterima', $p->fresh()->status);
        $this->actingAs($mhs)->get(route('kegiatan.index'))->assertSee('Wawancara Senin')->assertSee('Diterima');

        // Yang sudah ditinjau tidak bisa dibatalkan pendaftar.
        $this->actingAs($mhs)->delete(route('pendaftaran.destroy', $p->id));
        $this->assertDatabaseCount('pendaftaran', 1);
    }

    public function test_admin_meninjau_kegiatan_kampus(): void
    {
        $admin = $this->admin();
        $mhs = $this->mahasiswa();
        $item = $this->lomba($admin);
        $this->actingAs($mhs)->post(route('pendaftaran.store', ['lomba', $item->id]), ['cv' => $this->cv()]);
        $p = Pendaftaran::sole();

        $this->actingAs($this->admin())->get(route('admin.pendaftar.index'))->assertSee($mhs->name); // admin mana pun
        $this->actingAs($this->mitra())->get(route('mitra.pendaftar.show', $p->id))->assertNotFound();
        $this->actingAs($admin)->patch(route('admin.pendaftar.tolak', $p->id));
        $this->assertSame('ditolak', $p->fresh()->status);
    }

    public function test_menghapus_informasi_menghapus_pendaftaran_dan_cv(): void
    {
        $pemilik = $this->mitra();
        $item = $this->magang($pemilik);
        $this->actingAs($this->mahasiswa())->post(route('pendaftaran.store', ['magang', $item->id]), ['cv' => $this->cv()]);
        $cv = Pendaftaran::sole()->cv;

        $this->actingAs($pemilik)->delete(route('mitra.informasi.destroy', ['magang', $item->id]));

        $this->assertDatabaseCount('pendaftaran', 0);
        $this->disk()->assertMissing($cv);
    }

    // ---------- Cara daftar di formulir ----------

    public function test_cara_daftar_dari_formulir(): void
    {
        $data = [
            'judul' => 'Beasiswa Uji',
            'deskripsi' => 'Desk.',
            'persyaratan' => 'IPK 3.',
            'kontak' => 'k@uji.example',
            'batas_pendaftaran' => now()->addDays(5)->toDateString(),
            'penyelenggara' => 'Polinema',
        ];

        $this->actingAs($this->admin())->post(route('admin.informasi.store', 'beasiswa'), $data + ['cara_daftar' => 'eksternal'])
            ->assertSessionHasErrors('tautan_pendaftaran');
        $this->actingAs($this->admin())->post(route('admin.informasi.store', 'beasiswa'), $data + ['cara_daftar' => 'internal']);
        $this->assertDatabaseHas('beasiswa', ['judul' => 'Beasiswa Uji', 'cara_daftar' => 'internal']);

        $this->actingAs($this->mitra())->post(route('mitra.informasi.store', 'beasiswa'), ['judul' => 'Beasiswa Mitra'] + $data);
        $this->assertDatabaseHas('beasiswa', ['judul' => 'Beasiswa Mitra', 'cara_daftar' => 'internal', 'penyelenggara' => 'PT a@mitra.example']);
    }

    // ---------- Bimbingan ----------

    private function bukti(): UploadedFile
    {
        return UploadedFile::fake()->create('surat-diterima.pdf', 50, 'application/pdf');
    }

    public function test_bimbingan_kegiatan_campuslink_baru_bisa_setelah_diterima(): void
    {
        $pemilik = $this->mitra();
        $mhs = $this->mahasiswa();
        $dosen = $this->dosen();
        $item = $this->magang($pemilik); // internal
        $url = route('bimbingan.store', ['magang', $item->id]);

        // Belum mendaftar: belum boleh.
        $this->actingAs($mhs)->get(route('magang.show', $item))->assertSee('setelah pendaftaran Anda')->assertDontSee('Ajukan bimbingan');
        $this->actingAs($mhs)->post($url, ['dosen_id' => $dosen->id])->assertSessionHas('galat');

        // Sudah mendaftar tapi masih menunggu: belum boleh.
        $this->actingAs($mhs)->post(route('pendaftaran.store', ['magang', $item->id]), ['cv' => $this->cv()]);
        $this->actingAs($mhs)->post($url, ['dosen_id' => $dosen->id])->assertSessionHas('galat');
        $this->assertDatabaseCount('bimbingan', 0);

        // Diterima mitra: boleh, tanpa bukti.
        $this->actingAs($pemilik)->patch(route('mitra.pendaftar.terima', Pendaftaran::sole()->id));
        $this->actingAs($mhs)->get(route('magang.show', $item))->assertSee('Ajukan bimbingan');
        $this->actingAs($mhs)->post($url, ['dosen_id' => $dosen->id]);
        $this->assertNull(Bimbingan::sole()->bukti);
        $this->actingAs($dosen)->get(route('bimbingan.index'))->assertSee('Sudah diterima');
    }

    public function test_alur_bimbingan_kegiatan_luar_dengan_bukti(): void
    {
        $mhs = $this->mahasiswa();
        $dosen = $this->dosen();
        $dosenLain = $this->dosen();
        $tidakBersedia = $this->dosen(false, false);
        $item = $this->magang($this->admin(), 'eksternal');
        $url = route('bimbingan.store', ['magang', $item->id]);

        $this->actingAs($mhs)->get(route('magang.show', $item))->assertSee('Ajukan bimbingan')->assertSee('Unggah bukti diterima')
            ->assertSee($dosen->name)->assertDontSee($tidakBersedia->name);
        $this->actingAs($mhs)->post($url, ['dosen_id' => $tidakBersedia->id, 'bukti' => $this->bukti()])->assertSessionHasErrors('dosen_id');
        $this->actingAs($mhs)->post($url, ['dosen_id' => $dosen->id])->assertSessionHasErrors('bukti');
        $this->actingAs($mhs)->post($url, ['dosen_id' => $dosen->id, 'bukti' => UploadedFile::fake()->create('x.exe', 10)])->assertSessionHasErrors('bukti');
        $this->assertDatabaseCount('bimbingan', 0);

        $this->actingAs($mhs)->post($url, ['dosen_id' => $dosen->id, 'pesan' => 'Mohon bimbingannya', 'bukti' => $this->bukti()]);
        $b = Bimbingan::sole();
        $this->disk()->assertExists($b->bukti);

        $this->actingAs($dosen)->get(route('bimbingan.index'))->assertSee('Mohon bimbingannya')->assertSee('Lihat bukti diterima');
        $this->actingAs($dosen)->get(route('bimbingan.bukti', $b->id))->assertOk();
        $this->actingAs($mhs)->get(route('bimbingan.bukti', $b->id))->assertOk();
        $this->actingAs($dosenLain)->get(route('bimbingan.bukti', $b->id))->assertForbidden();
        $this->actingAs($this->mahasiswa())->get(route('bimbingan.bukti', $b->id))->assertForbidden();
        $this->actingAs($dosenLain)->patch(route('bimbingan.terima', $b->id))->assertNotFound();
        $this->actingAs($mhs)->get(route('bimbingan.index'))->assertForbidden();

        $this->actingAs($dosen)->patch(route('bimbingan.tolak', $b->id), ['catatan' => 'Kuota penuh']);
        $this->assertSame('ditolak', $b->fresh()->status);

        // Setelah ditolak, boleh mengajukan ke dosen lain; bukti lama dihapus.
        $buktiLama = $b->bukti;
        $this->actingAs($mhs)->post($url, ['dosen_id' => $dosenLain->id, 'bukti' => $this->bukti()]);
        $this->disk()->assertMissing($buktiLama);
        $this->assertSame($dosenLain->id, Bimbingan::sole()->dosen_id);
        $this->actingAs($dosenLain)->patch(route('bimbingan.terima', Bimbingan::sole()->id));
        $this->assertSame('diterima', Bimbingan::sole()->status);
    }

    public function test_beasiswa_tidak_punya_bimbingan(): void
    {
        $item = $this->admin()->beasiswa()->create([
            'judul' => 'B',
            'penyelenggara' => 'P',
            'deskripsi' => 'D',
            'persyaratan' => 'S',
            'batas_pendaftaran' => now()->addDays(3)->toDateString(),
            'kontak' => 'k',
            'tautan_pendaftaran' => 'https://b.example',
        ]);

        $this->actingAs($this->mahasiswa())->post('/beasiswa/'.$item->id.'/bimbingan', ['dosen_id' => $this->dosen()->id])->assertNotFound();
    }

    public function test_informasi_yang_disembunyikan_admin_tidak_bisa_menerima_bimbingan(): void
    {
        $admin = $this->admin();
        $mhs = $this->mahasiswa();
        $dosen = $this->dosen();
        $item = $this->magang($admin, 'eksternal');

        // Admin menyembunyikan magang eksternal.
        $this->actingAs($admin)->patch(route('admin.informasi.sembunyikan', ['magang', $item->id]))
            ->assertRedirect();
        $this->assertSame(Magang::STATUS_DISEMBUNYIKAN, $item->fresh()->status);

        // Mahasiswa mengirim POST bimbingan dengan dosen yang bersedia dan bukti PDF valid ke informasi tersembunyi.
        $url = route('bimbingan.store', ['magang', $item->id]);
        $this->actingAs($mhs)->post($url, [
            'dosen_id' => $dosen->id,
            'bukti' => $this->bukti(),
        ])->assertNotFound();

        // Tidak ada baris baru di tabel bimbingan.
        $this->assertDatabaseCount('bimbingan', 0);

        // Informasi yang tayang tetap bisa menerima permintaan bimbingan seperti sebelumnya.
        $itemTayang = $this->magang($admin, 'eksternal');
        $this->assertSame(Magang::STATUS_TAYANG, $itemTayang->fresh()->status);
        $urlTayang = route('bimbingan.store', ['magang', $itemTayang->id]);
        $this->actingAs($mhs)->post($urlTayang, [
            'dosen_id' => $dosen->id,
            'bukti' => $this->bukti(),
        ])->assertRedirect();

        $this->assertDatabaseCount('bimbingan', 1);
        $this->assertSame($dosen->id, Bimbingan::sole()->dosen_id);
    }

    public function test_keputusan_pendaftaran_bersifat_final_dan_tidak_bisa_diubah(): void
    {
        $pemilik = $this->mitra();
        $mhs1 = $this->mahasiswa();
        $mhs2 = $this->mahasiswa();
        $item = $this->magang($pemilik);

        $this->actingAs($mhs1)->post(route('pendaftaran.store', ['magang', $item->id]), ['cv' => $this->cv()]);
        $this->actingAs($mhs2)->post(route('pendaftaran.store', ['magang', $item->id]), ['cv' => $this->cv()]);

        $p1 = Pendaftaran::where('user_id', $mhs1->id)->sole();
        $p2 = Pendaftaran::where('user_id', $mhs2->id)->sole();

        // Sebelum ditinjau: halaman detail menampilkan tombol Terima dan Tolak.
        $this->actingAs($pemilik)->get(route('mitra.pendaftar.show', $p1->id))
            ->assertOk()
            ->assertSee('Terima')
            ->assertSee('Tolak')
            ->assertDontSee('Keputusan bersifat final dan tidak bisa diubah.');

        // 1. Terima lalu tolak: pendaftaran tetap diterima dan menghasilkan pesan galat.
        $this->actingAs($pemilik)->patch(route('mitra.pendaftar.terima', $p1->id))
            ->assertRedirect()
            ->assertSessionHas('status');
        $this->assertSame('diterima', $p1->fresh()->status);

        // Setelah ditinjau: tombol terima dan tolak disembunyikan di UI detail.
        $this->actingAs($pemilik)->get(route('mitra.pendaftar.show', $p1->id))
            ->assertOk()
            ->assertSee('Keputusan bersifat final dan tidak bisa diubah.')
            ->assertDontSee('method="POST" action="'.route('mitra.pendaftar.terima', $p1->id).'"', false)
            ->assertDontSee('method="POST" action="'.route('mitra.pendaftar.tolak', $p1->id).'"', false);

        $this->actingAs($pemilik)->patch(route('mitra.pendaftar.tolak', $p1->id))
            ->assertRedirect()
            ->assertSessionHas('galat', 'Pendaftar ini sudah ditinjau dan keputusannya tidak bisa diubah.');
        $this->assertSame('diterima', $p1->fresh()->status);

        // 2. Tolak lalu terima: pendaftaran tetap ditolak dan menghasilkan pesan galat.
        $this->actingAs($pemilik)->patch(route('mitra.pendaftar.tolak', $p2->id))
            ->assertRedirect()
            ->assertSessionHas('status');
        $this->assertSame('ditolak', $p2->fresh()->status);

        $this->actingAs($pemilik)->patch(route('mitra.pendaftar.terima', $p2->id))
            ->assertRedirect()
            ->assertSessionHas('galat', 'Pendaftar ini sudah ditinjau dan keputusannya tidak bisa diubah.');
        $this->assertSame('ditolak', $p2->fresh()->status);

        // 3. Berlaku juga untuk admin yang meninjau kegiatan internal.
        $admin = $this->admin();
        $mhs3 = $this->mahasiswa();
        $lomba = $this->lomba($admin);
        $this->actingAs($mhs3)->post(route('pendaftaran.store', ['lomba', $lomba->id]), ['cv' => $this->cv()]);
        $p3 = Pendaftaran::where('user_id', $mhs3->id)->sole();

        $this->actingAs($admin)->patch(route('admin.pendaftar.tolak', $p3->id))->assertRedirect();
        $this->assertSame('ditolak', $p3->fresh()->status);

        $this->actingAs($admin)->patch(route('admin.pendaftar.terima', $p3->id))
            ->assertRedirect()
            ->assertSessionHas('galat', 'Pendaftar ini sudah ditinjau dan keputusannya tidak bisa diubah.');
        $this->assertSame('ditolak', $p3->fresh()->status);

        $this->actingAs($admin)->get(route('admin.pendaftar.show', $p3->id))
            ->assertOk()
            ->assertSee('Keputusan bersifat final dan tidak bisa diubah.')
            ->assertDontSee('method="POST" action="'.route('admin.pendaftar.terima', $p3->id).'"', false)
            ->assertDontSee('method="POST" action="'.route('admin.pendaftar.tolak', $p3->id).'"', false);
    }

    // ---------- BUG_CL_0013: Pembatalan dan pendaftaran ulang setelah ditolak ----------

    /**
     * Mahasiswa yang ditolak bisa membatalkan pendaftarannya.
     * Baris dan berkas CV harus terhapus.
     */
    public function test_mahasiswa_ditolak_bisa_membatalkan_pendaftaran(): void
    {
        $pemilik = $this->mitra();
        $mhs = $this->mahasiswa();
        $item = $this->magang($pemilik);

        $this->actingAs($mhs)->post(route('pendaftaran.store', ['magang', $item->id]), ['cv' => $this->cv()]);
        $p = Pendaftaran::sole();
        $cvLama = $p->cv;
        $this->disk()->assertExists($cvLama);

        // Mitra menolak pendaftaran.
        $this->actingAs($pemilik)->patch(route('mitra.pendaftar.tolak', $p->id));
        $this->assertSame('ditolak', $p->fresh()->status);

        // Mahasiswa membatalkan pendaftaran yang ditolak: harus berhasil.
        $this->actingAs($mhs)->delete(route('pendaftaran.destroy', $p->id))
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertDatabaseCount('pendaftaran', 0);
        $this->disk()->assertMissing($cvLama);
    }

    /**
     * Mahasiswa yang diterima tidak bisa membatalkan pendaftaran.
     * Pesan galat khusus harus muncul (berbeda dari pesan lama).
     */
    public function test_mahasiswa_diterima_tidak_bisa_membatalkan_pendaftaran(): void
    {
        $pemilik = $this->mitra();
        $mhs = $this->mahasiswa();
        $item = $this->magang($pemilik);

        $this->actingAs($mhs)->post(route('pendaftaran.store', ['magang', $item->id]), ['cv' => $this->cv()]);
        $p = Pendaftaran::sole();

        $this->actingAs($pemilik)->patch(route('mitra.pendaftar.terima', $p->id));
        $this->assertSame('diterima', $p->fresh()->status);

        // Pembatalan pendaftaran yang diterima harus ditolak dengan pesan khusus.
        $this->actingAs($mhs)->delete(route('pendaftaran.destroy', $p->id))
            ->assertRedirect()
            ->assertSessionHas('galat', 'Pendaftaran yang sudah diterima tidak bisa dibatalkan. Hubungi penyelenggara.');

        $this->assertDatabaseCount('pendaftaran', 1);
    }

    /**
     * Mahasiswa yang ditolak bisa mendaftar ulang dengan CV baru.
     * Hanya satu baris pendaftaran yang tersisa (status menunggu), CV lama terhapus.
     */
    public function test_mahasiswa_ditolak_bisa_daftar_ulang_dengan_cv_baru(): void
    {
        $pemilik = $this->mitra();
        $mhs = $this->mahasiswa();
        $item = $this->magang($pemilik);

        // Daftar pertama.
        $this->actingAs($mhs)->post(route('pendaftaran.store', ['magang', $item->id]), ['cv' => $this->cv()]);
        $p = Pendaftaran::sole();
        $cvLama = $p->cv;
        $this->disk()->assertExists($cvLama);

        // Mitra menolak.
        $this->actingAs($pemilik)->patch(route('mitra.pendaftar.tolak', $p->id));
        $this->assertSame('ditolak', $p->fresh()->status);

        // Daftar ulang dengan CV baru: harus berhasil.
        $this->actingAs($mhs)->post(route('pendaftaran.store', ['magang', $item->id]), ['cv' => $this->cv()])
            ->assertRedirect()
            ->assertSessionHas('status');

        // Hanya satu baris pendaftaran, status menunggu, CV lama terhapus.
        $this->assertDatabaseCount('pendaftaran', 1);
        $baru = Pendaftaran::sole();
        $this->assertSame('menunggu', $baru->status);
        $this->assertNotSame($cvLama, $baru->cv);
        $this->disk()->assertMissing($cvLama);
        $this->disk()->assertExists($baru->cv);
    }

    /**
     * Pendaftaran ulang setelah batas pendaftaran lewat harus ditolak,
     * meski sebelumnya pernah ditolak.
     */
    public function test_daftar_ulang_setelah_batas_ditolak(): void
    {
        $pemilik = $this->mitra();
        $mhs = $this->mahasiswa();
        // Buat magang dengan batas yang sudah lewat.
        $item = $pemilik->magang()->create([
            'judul' => 'Magang Tutup',
            'penyelenggara' => 'PT Tutup',
            'deskripsi' => 'Desk.',
            'persyaratan' => 'Syarat.',
            'periode_mulai' => now()->addMonth()->toDateString(),
            'periode_selesai' => now()->addMonths(3)->toDateString(),
            'batas_pendaftaran' => now()->subDay()->toDateString(), // sudah lewat
            'kontak' => 'k@tutup.example',
            'cara_daftar' => 'internal',
        ]);

        // Langsung buat pendaftaran yang ditolak (bypass controller karena batas sudah lewat).
        $p = new Pendaftaran(['cv' => 'cv/palsu.pdf']);
        $p->user()->associate($mhs);
        $p->informasi()->associate($item);
        $p->status = Pendaftaran::STATUS_DITOLAK;
        $p->save();

        // Daftar ulang harus ditolak karena batas sudah lewat.
        $this->actingAs($mhs)->post(route('pendaftaran.store', ['magang', $item->id]), ['cv' => $this->cv()])
            ->assertRedirect()
            ->assertSessionHas('galat', 'Pendaftaran untuk kegiatan ini sudah ditutup.');

        $this->assertDatabaseCount('pendaftaran', 1);
    }

    /**
     * Pendaftaran berstatus menunggu atau diterima menghasilkan pesan
     * "sudah mendaftar" (tidak dianggap ditolak).
     */
    public function test_pendaftaran_menunggu_atau_diterima_tidak_bisa_daftar_ulang(): void
    {
        $pemilik = $this->mitra();
        $mhs = $this->mahasiswa();
        $item = $this->magang($pemilik);
        $url = route('pendaftaran.store', ['magang', $item->id]);

        // Status menunggu: daftar ulang → galat "sudah mendaftar".
        $this->actingAs($mhs)->post($url, ['cv' => $this->cv()]);
        $this->assertSame('menunggu', Pendaftaran::sole()->status);
        $this->actingAs($mhs)->post($url, ['cv' => $this->cv()])
            ->assertSessionHas('galat', 'Anda sudah mendaftar ke kegiatan ini.');
        $this->assertDatabaseCount('pendaftaran', 1);

        // Status diterima: daftar ulang → galat "sudah mendaftar".
        $p = Pendaftaran::sole();
        $this->actingAs($pemilik)->patch(route('mitra.pendaftar.terima', $p->id));
        $this->actingAs($mhs)->post($url, ['cv' => $this->cv()])
            ->assertSessionHas('galat', 'Anda sudah mendaftar ke kegiatan ini.');
        $this->assertDatabaseCount('pendaftaran', 1);
    }

    public function test_keputusan_bimbingan_oleh_dosen_bersifat_final_dan_tidak_bisa_diubah(): void
    {
        $pemilik = $this->mitra();
        $mhs1 = $this->mahasiswa();
        $mhs2 = $this->mahasiswa();
        $dosen = $this->dosen();
        $item = $this->magang($pemilik);

        // Mendaftar dan diterima mitra agar boleh mengajukan bimbingan internal.
        $this->actingAs($mhs1)->post(route('pendaftaran.store', ['magang', $item->id]), ['cv' => $this->cv()]);
        $this->actingAs($mhs2)->post(route('pendaftaran.store', ['magang', $item->id]), ['cv' => $this->cv()]);
        $p1 = Pendaftaran::where('user_id', $mhs1->id)->sole();
        $p2 = Pendaftaran::where('user_id', $mhs2->id)->sole();
        $this->actingAs($pemilik)->patch(route('mitra.pendaftar.terima', $p1->id));
        $this->actingAs($pemilik)->patch(route('mitra.pendaftar.terima', $p2->id));

        // Ajukan bimbingan ke dosen.
        $url = route('bimbingan.store', ['magang', $item->id]);
        $this->actingAs($mhs1)->post($url, ['dosen_id' => $dosen->id]);
        $this->actingAs($mhs2)->post($url, ['dosen_id' => $dosen->id]);

        $b1 = Bimbingan::where('mahasiswa_id', $mhs1->id)->sole();
        $b2 = Bimbingan::where('mahasiswa_id', $mhs2->id)->sole();

        // 1. Terima lalu tolak: bimbingan tetap diterima dan menghasilkan pesan galat.
        $this->actingAs($dosen)->patch(route('bimbingan.terima', $b1->id))
            ->assertRedirect()
            ->assertSessionHas('status');
        $this->assertSame('diterima', $b1->fresh()->status);

        $this->actingAs($dosen)->patch(route('bimbingan.tolak', $b1->id))
            ->assertRedirect()
            ->assertSessionHas('galat', 'Permintaan bimbingan ini sudah dijawab dan tidak bisa diubah.');
        $this->assertSame('diterima', $b1->fresh()->status);

        // 2. Tolak lalu terima: bimbingan tetap ditolak dan menghasilkan pesan galat.
        $this->actingAs($dosen)->patch(route('bimbingan.tolak', $b2->id))
            ->assertRedirect()
            ->assertSessionHas('status');
        $this->assertSame('ditolak', $b2->fresh()->status);

        $this->actingAs($dosen)->patch(route('bimbingan.terima', $b2->id))
            ->assertRedirect()
            ->assertSessionHas('galat', 'Permintaan bimbingan ini sudah dijawab dan tidak bisa diubah.');
        $this->assertSame('ditolak', $b2->fresh()->status);
    }
}
