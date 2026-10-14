<?php

declare(strict_types=1);
namespace App\Services;

final class HasilProrata
{
    public function __construct (
        public readonly int $totalHari,
        public readonly int $hariTerpakai,
        public readonly int $sisaHari,
        public readonly int $kreditSen,
        public readonly int $tagihanBaruSen,
        public readonly int $bersihSen,
    ){}
}