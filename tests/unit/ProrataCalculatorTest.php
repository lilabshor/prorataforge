<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\ProrataCalculator;
use CodeIgniter\Test\CIUnitTestCase;
use DateTimeImmutable;
use InvalidArgumentException;

/**
 * @internal
 */
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
        // Periode 30 hari: 1 Okt s/d 31 Okt
        $mulai   = new DateTimeImmutable('2026-10-01');
        $selesai = new DateTimeImmutable('2026-10-31');
        // Pindah tanggal 16 Okt (hari ke-15 terpakai, sisa 15 hari)
        $tglUbah = new DateTimeImmutable('2026-10-16');

        // Paket Lama: Rp 100.000 (10.000.000 sen)
        // Paket Baru: Rp 200.000 (20.000.000 sen)
        $hargaLama = 10000000;
        $hargaBaru = 20000000;

        $hasil = $this->kalkulator->hitung($hargaLama, $hargaBaru, $mulai, $selesai, $tglUbah);

        $this->assertSame(30, $hasil->totalHari);
        $this->assertSame(15, $hasil->hariTerpakai);
        $this->assertSame(15, $hasil->sisaHari);

        // Kredit 15/30 * 100.000 = Rp 50.000 (5.000.000 sen)
        $this->assertSame(5000000, $hasil->kreditSen);
        // Tagihan baru 15/30 * 200.000 = Rp 100.000 (10.000.000 sen)
        $this->assertSame(10000000, $hasil->tagihanBaruSen);
        // Bersih = 10.000.000 - 5.000.000 = +5.000.000 sen (upgrade tambahan bayar)
        $this->assertSame(5000000, $hasil->bersihSen);
    }

    public function testHitungProrataDowngradeKreditNegatif(): void
    {
        $mulai   = new DateTimeImmutable('2026-10-01');
        $selesai = new DateTimeImmutable('2026-10-31');
        $tglUbah = new DateTimeImmutable('2026-10-16');

        // Paket Lama: Rp 200.000
        // Paket Baru: Rp 100.000
        $hargaLama = 20000000;
        $hargaBaru = 10000000;

        $hasil = $this->kalkulator->hitung($hargaLama, $hargaBaru, $mulai, $selesai, $tglUbah);

        // Bersih = 5.000.000 - 10.000.000 = -5.000.000 sen (kelebihan kredit masuk saldo)
        $this->assertSame(-5000000, $hasil->bersihSen);
    }

    public function testTanggalLuarPeriodeMelemparException(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Tanggal perubahan di luar periode berjalan.');

        $mulai   = new DateTimeImmutable('2026-10-01');
        $selesai = new DateTimeImmutable('2026-10-31');
        $tglUbah = new DateTimeImmutable('2026-11-05'); // di luar periode

        $this->kalkulator->hitung(10000000, 20000000, $mulai, $selesai, $tglUbah);
    }

    public function testKeRupiahPenuhBulat(): void
    {
        // 100.049 sen -> Rp 1.000 (100.000 sen)
        $this->assertSame(100000, ProrataCalculator::keRupiahPenuh(100049));

        // 100.050 sen -> Rp 1.001 (100.100 sen)
        $this->assertSame(100100, ProrataCalculator::keRupiahPenuh(100050));
    }
}
