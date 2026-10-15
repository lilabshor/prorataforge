<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\ProrataCalculator;
use CodeIgniter\Test\CIUnitTestCase;
use DateTimeImmutable;
use InvalidArgumentException;

final class ProrataCalculatorTest extends CIUnitTestCase
{
    private ProrataCalculator $kalkulator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->kalkulator = new ProrataCalculator();
    }

    public function testHitungProrataUpgrade(): void
    {
        $mulai   = new DateTimeImmutable('2026-10-01');
        $selesai = new DateTimeImmutable('2026-10-31');
        $tglUbah = new DateTimeImmutable('2026-10-16');

        $hargaLama = 10000000;
        $hargaBaru = 20000000;

        $hasil = $this->kalkulator->hitung($hargaLama, $hargaBaru, $mulai, $selesai, $tglUbah);

        $this->assertSame(30, $hasil->totalHari);
        $this->assertSame(15, $hasil->hariTerpakai);
        $this->assertSame(15, $hasil->sisaHari);
        $this->assertSame(5000000, $hasil->kreditSen);
        $this->assertSame(10000000, $hasil->tagihanBaruSen);
        $this->assertSame(5000000, $hasil->bersihSen);
    }

    public function testHitungProrataDowngradeKreditNegatif(): void
    {
        $mulai   = new DateTimeImmutable('2026-10-01');
        $selesai = new DateTimeImmutable('2026-10-31');
        $tglUbah = new DateTimeImmutable('2026-10-16');

        $hargaLama = 20000000;
        $hargaBaru = 10000000;

        $hasil = $this->kalkulator->hitung($hargaLama, $hargaBaru, $mulai, $selesai, $tglUbah);

        $this->assertSame(-5000000, $hasil->bersihSen);
    }

    public function testTanggalLuarPeriodeMelemparException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Tanggal perubahan di luar periode berjalan.');

        $mulai   = new DateTimeImmutable('2026-10-01');
        $selesai = new DateTimeImmutable('2026-10-31');
        $tglUbah = new DateTimeImmutable('2026-11-05');

        $this->kalkulator->hitung(10000000, 20000000, $mulai, $selesai, $tglUbah);
    }

    public function testKeRupiahPenuhBulat(): void
    {
        $this->assertSame(100000, ProrataCalculator::keRupiahPenuh(100049));
        $this->assertSame(100100, ProrataCalculator::keRupiahPenuh(100050));
    }
}
