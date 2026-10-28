<?php

namespace Tests\Unit;

use App\Support\Kampus;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/** Pembatasan domain email per peran: dicocokkan persis dengan bagian setelah "@", bukan substring. */
class KampusTest extends TestCase
{
    /**
     * @return array<string, array{string, string, bool}>
     */
    public static function kasus(): array
    {
        return [
            'mahasiswa @student' => ['alya@student.polinema.ac.id', 'mahasiswa', true],
            'mahasiswa huruf besar dan spasi' => ['  ALYA@Student.Polinema.AC.ID ', 'mahasiswa', true],
            'mahasiswa memakai domain dosen' => ['alya@polinema.ac.id', 'mahasiswa', false],
            'dosen @polinema' => ['rina@polinema.ac.id', 'dosen', true],
            'dosen memakai domain mahasiswa' => ['rina@student.polinema.ac.id', 'dosen', false],
            'subdomain lain' => ['alya@x.student.polinema.ac.id', 'mahasiswa', false],
            'akhiran mirip' => ['rina@evilpolinema.ac.id', 'dosen', false],
            'domain palsu di depan' => ['rina@polinema.ac.id.evil.com', 'dosen', false],
            'dua tanda @' => ['rina@polinema.ac.id@gmail.com', 'dosen', false],
            'tanpa bagian lokal' => ['@polinema.ac.id', 'dosen', false],
            'gmail' => ['alya@gmail.com', 'mahasiswa', false],
            'mitra tidak punya domain kampus' => ['budi@polinema.ac.id', 'mitra', false],
        ];
    }

    #[DataProvider('kasus')]
    public function test_email_sesuai_peran(string $email, string $peran, bool $hasil): void
    {
        $this->assertSame($hasil, Kampus::emailSesuaiPeran($email, $peran));
    }

    public function test_email_kampus_menerima_kedua_domain_saja(): void
    {
        $this->assertTrue(Kampus::emailKampus('alya@student.polinema.ac.id'));
        $this->assertTrue(Kampus::emailKampus('rina@polinema.ac.id'));
        $this->assertFalse(Kampus::emailKampus('alya@mail.polinema.ac.id'));
        $this->assertFalse(Kampus::emailKampus('alya@gmail.com'));
    }
}
