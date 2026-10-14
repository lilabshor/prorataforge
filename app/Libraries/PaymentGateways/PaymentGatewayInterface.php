<?php

declare(strict_types=1);

namespace App\Libraries\PaymentGateways;

interface PaymentGatewayInterface
{
    /**
     * Inisiasi transaksi pembayaran ke gateway.
     */
    public function buatPembayaran(int $tagihanId, int $jumlahSen, array $meta = []): array;

    /**
     * Verifikasi keabsahan payload webhook dari gateway.
     */
    public function verifikasiWebhook(array $payload): bool;
}
