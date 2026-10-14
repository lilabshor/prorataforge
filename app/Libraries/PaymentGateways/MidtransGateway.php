<?php

declare(strict_types=1);

namespace App\Libraries\PaymentGateways;

final class MidtransGateway implements PaymentGatewayInterface
{
    public function __construct(
        private ?string $serverKey = null
    ) {
        $this->serverKey = $serverKey ?? (string) env('MIDTRANS_SERVER_KEY');
    }

    public function buatPembayaran(int $tagihanId, int $jumlahSen, array $meta = []): array
    {
        $orderId = 'PF-' . ($meta['bayar_id'] ?? $tagihanId);

        return [
            'gateway'  => 'midtrans',
            'order_id' => $orderId,
            'metode'   => 'qris',
            'amount'   => $jumlahSen,
            'status'   => 'pending',
        ];
    }

    public function verifikasiWebhook(array $payload): bool
    {
        $orderId    = (string) ($payload['order_id'] ?? '');
        $statusKode = (string) ($payload['status_code'] ?? '');
        $gross      = (string) ($payload['gross_amount'] ?? '');
        $tanda      = (string) ($payload['signature_key'] ?? '');

        $diharapkan = hash('sha512', $orderId . $statusKode . $gross . $this->serverKey);

        return hash_equals($diharapkan, $tanda);
    }
}
