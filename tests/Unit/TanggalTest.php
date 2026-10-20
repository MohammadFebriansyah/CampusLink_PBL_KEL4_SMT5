<?php

namespace Tests\Unit;

use App\Support\Tanggal;
use Carbon\Carbon;
use PHPUnit\Framework\TestCase;

class TanggalTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow(Carbon::create(2026, 10, 12, 9, 30));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_sisa_hari(): void
    {
        $this->assertSame(0, Tanggal::sisaHari(Carbon::create(2026, 10, 12)));
        $this->assertSame(0, Tanggal::sisaHari(Carbon::create(2026, 10, 12, 23, 59)));
        $this->assertSame(1, Tanggal::sisaHari(Carbon::create(2026, 10, 13)));
        $this->assertSame(30, Tanggal::sisaHari(Carbon::create(2026, 11, 11)));
        $this->assertSame(-1, Tanggal::sisaHari(Carbon::create(2026, 10, 11)));
    }

    public function test_label_sisa(): void
    {
        $this->assertSame('Terakhir hari ini', Tanggal::labelSisa(Carbon::create(2026, 10, 12)));
        $this->assertSame('5 hari lagi', Tanggal::labelSisa(Carbon::create(2026, 10, 17)));
        $this->assertSame('Ditutup', Tanggal::labelSisa(Carbon::create(2026, 10, 11)));
    }

    public function test_format_tanggal_selalu_bahasa_indonesia(): void
    {
        $this->assertSame('12 Oktober 2026', Tanggal::panjang(Carbon::create(2026, 10, 12)));
        $this->assertSame('-', Tanggal::panjang(null));
    }

    public function test_rentang(): void
    {
        $a = Carbon::create(2026, 10, 12);

        $this->assertSame('12 Oktober 2026', Tanggal::rentang($a, Carbon::create(2026, 10, 12)));
        $this->assertSame('12 Oktober 2026 sampai 14 Oktober 2026', Tanggal::rentang($a, Carbon::create(2026, 10, 14)));
    }
}
