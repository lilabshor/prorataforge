<?php

declare(strict_types=1);

namespace App\Libraries\PaymentGateways;

use InvalidArgumentException;

final class GatewayFactory
{
    public static function buat(?string $driver = null): PaymentGatewayInterface
    {
        $driver = strtolower($driver ?? (string) env('PAYMENT_GATEWAY', 'midtrans'));

        return match ($driver) {
            'midtrans' => new MidtransGateway(),
            'xendit'   => new XenditGateway(),
            default    => throw new InvalidArgumentException("Driver payment gateway '{$driver}' tidak didukung."),
        };
    }
}
