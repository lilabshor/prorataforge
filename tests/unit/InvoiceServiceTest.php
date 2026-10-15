<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\TagihanItemModel;
use App\Models\TagihanModel;
use App\Services\CreditService;
use App\Services\HasilProrata;
use App\Services\InvoiceService;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Config\Database;

final class InvoiceServiceTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $namespace = 'App';
    protected $migrate   = true;

    private InvoiceService $invoiceSvc;
    private CreditService $creditSvc;

    protected function setUp(): void
    {
        parent::setUp();
        $this->creditSvc  = new CreditService();
        $this->invoiceSvc = new InvoiceService($this->creditSvc);
    }

    public function testInvoiceServiceInstantiation(): void
    {
        $this->assertInstanceOf(InvoiceService::class, $this->invoiceSvc);
    }

    public function testBuatInvoiceProrataUpgradeDenganPajak11Persen(): void
    {
        $userId = 101;
        $lang   = ['id' => 1, 'user_id' => $userId];
        $paketLama = ['id' => 1, 'nama' => 'Paket Starter', 'harga_sen' => 10000000];
        $paketBaru = ['id' => 2, 'nama' => 'Paket Pro', 'harga_sen' => 20000000];

        $hasilProrata = new HasilProrata(
            totalHari: 30,
            hariTerpakai: 15,
            sisaHari: 15,
            kreditSen: 5000000,
            tagihanBaruSen: 10000000,
            bersihSen: 5000000
        );

        $idemKey   = 'upgrade-test-' . bin2hex(random_bytes(8));
        $invoiceId = $this->invoiceSvc->buatInvoiceProrata($lang, $paketLama, $paketBaru, $hasilProrata, $idemKey);

        $this->assertGreaterThan(0, $invoiceId);

        $tagihan = model(TagihanModel::class)->find($invoiceId);
        $this->assertNotNull($tagihan);
        $this->assertSame('prorata', $tagihan['jenis']);
        $this->assertSame('terbit', $tagihan['status']);
        $this->assertSame(5000000, (int) $tagihan['subtotal_sen']);
        $this->assertSame(550000, (int) $tagihan['pajak_sen']);
        $this->assertSame(0, (int) $tagihan['penyesuaian_sen']);
        $this->assertSame(5550000, (int) $tagihan['total_sen']);
        $this->assertSame($idemKey, $tagihan['idempotency_key']);
        $this->assertStringStartsWith('INV/' . date('Y/n') . '/', $tagihan['nomor']);

        $items = model(TagihanItemModel::class)->where('tagihan_id', $invoiceId)->findAll();
        $this->assertCount(3, $items);

        $itemJenis = array_column($items, 'jenis');
        $this->assertContains('kredit_prorata', $itemJenis);
        $this->assertContains('tagihan_prorata', $itemJenis);
        $this->assertContains('pajak', $itemJenis);

        foreach ($items as $it) {
            if ($it['jenis'] === 'kredit_prorata') {
                $this->assertSame(-5000000, (int) $it['subtotal_sen']);
            }
            if ($it['jenis'] === 'tagihan_prorata') {
                $this->assertSame(10000000, (int) $it['subtotal_sen']);
            }
            if ($it['jenis'] === 'pajak') {
                $this->assertSame(550000, (int) $it['subtotal_sen']);
            }
        }
    }

    public function testBuatInvoiceProrataDowngradeMenambahSaldoDepositKredit(): void
    {
        $userId = 202;
        $lang   = ['id' => 2, 'user_id' => $userId];
        $paketLama = ['id' => 2, 'nama' => 'Paket Pro', 'harga_sen' => 20000000];
        $paketBaru = ['id' => 1, 'nama' => 'Paket Starter', 'harga_sen' => 10000000];

        $hasilProrata = new HasilProrata(
            totalHari: 30,
            hariTerpakai: 15,
            sisaHari: 15,
            kreditSen: 10000000,
            tagihanBaruSen: 5000000,
            bersihSen: -5000000
        );

        $idemKey   = 'downgrade-test-' . bin2hex(random_bytes(8));
        $invoiceId = $this->invoiceSvc->buatInvoiceProrata($lang, $paketLama, $paketBaru, $hasilProrata, $idemKey);

        $tagihan = model(TagihanModel::class)->find($invoiceId);
        $this->assertNotNull($tagihan);
        $this->assertSame('gratis', $tagihan['status']);
        $this->assertSame(0, (int) $tagihan['total_sen']);

        $this->assertSame(5000000, $this->creditSvc->saldoSekarang($userId));

        $bukuKredit = Database::connect()->table('buku_kredit')
            ->where('user_id', $userId)
            ->where('tagihan_id', $invoiceId)
            ->get()
            ->getRowArray();

        $this->assertNotNull($bukuKredit);
        $this->assertSame('prorata_downgrade', $bukuKredit['jenis']);
        $this->assertSame(5000000, (int) $bukuKredit['delta_sen']);
        $this->assertSame(5000000, (int) $bukuKredit['saldo_setelah_sen']);
    }

    public function testBuatInvoiceProrataUpgradeMemanfaatkanSaldoDepositKredit(): void
    {
        $userId = 303;
        $this->creditSvc->tambahSaldo($userId, 2000000, 'deposit_manual', null, 'Saldo deposit awal');

        $lang = ['id' => 3, 'user_id' => $userId];
        $paketLama = ['id' => 1, 'nama' => 'Paket Starter', 'harga_sen' => 10000000];
        $paketBaru = ['id' => 2, 'nama' => 'Paket Pro', 'harga_sen' => 20000000];

        $hasilProrata = new HasilProrata(
            totalHari: 30,
            hariTerpakai: 15,
            sisaHari: 15,
            kreditSen: 5000000,
            tagihanBaruSen: 10000000,
            bersihSen: 5000000
        );

        $idemKey   = 'credit-usage-test-' . bin2hex(random_bytes(8));
        $invoiceId = $this->invoiceSvc->buatInvoiceProrata($lang, $paketLama, $paketBaru, $hasilProrata, $idemKey);

        $tagihan = model(TagihanModel::class)->find($invoiceId);
        $this->assertNotNull($tagihan);
        $this->assertSame(2000000, (int) $tagihan['kredit_dipakai_sen']);
        $this->assertSame(330000, (int) $tagihan['pajak_sen']);
        $this->assertSame(3330000, (int) $tagihan['total_sen']);

        $this->assertSame(0, $this->creditSvc->saldoSekarang($userId));

        $kreditItem = model(TagihanItemModel::class)->where('tagihan_id', $invoiceId)
            ->where('jenis', 'kredit_saldo')
            ->first();

        $this->assertNotNull($kreditItem);
        $this->assertSame(-2000000, (int) $kreditItem['subtotal_sen']);
    }

    public function testTerapkanKuponDiskon(): void
    {
        $db = Database::connect();

        $db->table('kupon')->insert([
            'kode'            => 'DISKON20',
            'jenis'           => 'persen',
            'nilai'           => 20,
            'maks_diskon_sen' => 5000000,
            'is_aktif'        => 1,
        ]);

        $this->assertSame(16000000, $this->invoiceSvc->terapkanKupon(20000000, 'DISKON20', 1));

        $db->table('kupon')->insert([
            'kode'            => 'POTONG50',
            'jenis'           => 'nominal',
            'nilai'           => 5000000,
            'maks_diskon_sen' => null,
            'is_aktif'        => 1,
        ]);

        $this->assertSame(15000000, $this->invoiceSvc->terapkanKupon(20000000, 'POTONG50', 1));
    }
}
