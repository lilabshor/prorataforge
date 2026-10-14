<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Libraries\PaymentGateways\GatewayFactory;
use App\Libraries\PaymentGateways\MidtransGateway;
use App\Libraries\PaymentGateways\XenditGateway;
use CodeIgniter\Test\CIUnitTestCase;
use InvalidArgumentException;

/**
 * @internal
 */
final class PaymentGatewayTest extends CIUnitTestCase
{
    public function testFactoryReturnsMidtransGateway(): void
    {
        $gateway = GatewayFactory::buat('midtrans');
        $this->assertInstanceOf(MidtransGateway::class, $gateway);
    }

    public function testFactoryReturnsXenditGateway(): void
    {
        $gateway = GatewayFactory::buat('xendit');
        $this->assertInstanceOf(XenditGateway::class, $gateway);
    }

    public function testFactoryThrowsExceptionOnUnsupportedDriver(): void
    {
        $this->expectException(InvalidArgumentException::class);
        GatewayFactory::buat('stripe');
    }

    public function testMidtransSignatureVerification(): void
    {
        $serverKey = 'sandbox-secret-123';
        $gateway   = new MidtransGateway($serverKey);

        $orderId     = 'PF-100';
        $statusCode  = '200';
        $grossAmount = '50000.00';
        $validSign   = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

        $payload = [
            'order_id'      => $orderId,
            'status_code'   => $statusCode,
            'gross_amount'  => $grossAmount,
            'signature_key' => $validSign,
        ];

        $this->assertTrue($gateway->verifikasiWebhook($payload));

        $payload['signature_key'] = 'invalid_hash';
        $this->assertFalse($gateway->verifikasiWebhook($payload));
    }

    public function testXenditWebhookVerification(): void
    {
        $token   = 'xen_token_secret_456';
        $gateway = new XenditGateway($token);

        $this->assertTrue($gateway->verifikasiWebhook(['x_callback_token' => $token]));
        $this->assertFalse($gateway->verifikasiWebhook(['x_callback_token' => 'wrong_token']));
    }
}
