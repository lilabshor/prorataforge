<?php

declare(strict_types=1);

namespace App\Libraries\PaymentGateways;

final class XenditGateway implements PaymentGatewayInterface
{
    public function __construct(
        private ?string $webhookToken = null
    ) {
        $this->webhookToken = $webhookToken ?? (string) env('XENDIT_WEBHOOK_TOKEN');
    }

    public function buatPembayaran(int $tagihanId, int $jumlahSen, array $meta = []): array
    {
        $externalId = 'PF-XEN-' . ($meta['bayar_id'] ?? $tagihanId);

        return [
            'gateway'     => 'xendit',
            'external_id' => $externalId,
            'metode'      => 'invoice',
            'amount'      => $jumlahSen,
            'status'      => 'pending',
        ];
    }

    public function verifikasiWebhook(array $payload): bool
    {
        $callbackToken = (string) ($payload['x_callback_token'] ?? '');

        if ($this->webhookToken === '') {
            return true;
        }

        return hash_equals($this->webhookToken, $callbackToken);
    }
}
