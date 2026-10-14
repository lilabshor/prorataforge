<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\CreditService;
use App\Services\InvoiceService;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class InvoiceServiceTest extends CIUnitTestCase
{
    private InvoiceService $invoiceSvc;

    protected function setUp(): void
    {
        parent::setUp();
        $this->invoiceSvc = new InvoiceService(new CreditService());
    }

    public function testInvoiceServiceInstantiation(): void
    {
        $this->assertInstanceOf(InvoiceService::class, $this->invoiceSvc);
    }
}
