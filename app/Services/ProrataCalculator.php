<?php
declare(strict_types=1);
namespace App\Services;

use DateTimeImmutable;
use InvalidArgumentException;
use OverflowException;

final class ProrataCalculator
{
    public function hitung(
        int $hargaLamaSen,
        int $hargaBaruSen,
        DateTimeImmutable $mulai,
        DateTimeImmutable $selesai,
        DateTimeImmutable $tglUbah
    ): HasilProrata {
        if ($hargaLamaSen < 0 || $hargaBaruSen < 0) {
            throw new InvalidArgumentException('Harga tidak boleh negatif.');
        }
        $mulai   = $mulai->setTime(0, 0, 0);
        $selesai = $selesai->setTime(0, 0, 0);
        $tglUbah = $tglUbah->setTime(0, 0, 0);
        if ($selesai <= $mulai) {
            throw new InvalidArgumentException('Akhir periode harus setelah awal periode.');
        }
        if ($tglUbah < $mulai || $tglUbah >= $selesai) {
            throw new InvalidArgumentException('Tanggal perubahan di luar periode berjalan.');
        }
        $totalHari    = (int) $mulai->diff($selesai)->days;
        $hariTerpakai = (int) $mulai->diff($tglUbah)->days;
        $sisaHari     = $totalHari - $hariTerpakai;
        $kreditSen    = self::prorata($hargaLamaSen, $sisaHari, $totalHari);
        $tagihSen     = self::prorata($hargaBaruSen, $sisaHari, $totalHari);
        return new HasilProrata(
            $totalHari,
            $hariTerpakai,
            $sisaHari,
            $kreditSen,
            $tagihSen,
            $tagihSen - $kreditSen
        );
    }

    private static function prorata(int $hargaSen, int $sisa, int $total): int
    {
        if ($sisa > 0 && $hargaSen > intdiv(PHP_INT_MAX, $sisa)) {
            throw new OverflowException('Nilai terlalu besar untuk dihitung aman.');
        }
        $pembilang = $hargaSen * $sisa;
        $hasil     = intdiv($pembilang, $total);
        $sisaBagi  = $pembilang % $total;
        if (2 * $sisaBagi >= $total) {
            $hasil++;
        }
        return $hasil;
    }

    public static function keRupiahPenuh(int $sen): int
    {
        $tanda = $sen < 0 ? -1 : 1;
        $abs   = abs($sen);
        return $tanda * intdiv($abs + 50, 100) * 100;
    }
}
