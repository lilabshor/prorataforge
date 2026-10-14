<?php
if (! function_exists('formatRupiah')) {
    function formatRupiah(int $sen): string
    {
        $negatif = $sen < 0;
        $abs     = abs($sen);
        $rupiah  = intdiv($abs, 100);
        $sisaSen = $abs % 100;
        return ($negatif ? '-' : '') . 'Rp'
            . number_format($rupiah, 0, ',', '.')
            . ',' . str_pad((string) $sisaSen, 2, '0',
                STR_PAD_LEFT);
    }
}
if (! function_exists('formatRupiahPenuh')) {
    function formatRupiahPenuh(int $sen): string
    {
        return formatRupiah(
            \App\Services\ProrataCalculator::keRupiahPenuh($sen));
    }
}
