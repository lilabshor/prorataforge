<?php

declare(strict_types=1);

namespace App\Commands;

use App\Services\CreditService;
use App\Services\InvoiceService;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Database;
use Throwable;

final class RenewalCommand extends BaseCommand
{
    protected $group       = 'Billing';
    protected $name        = 'billing:renewal';
    protected $description = 'Membuat faktur perpanjangan untuk langganan yang akan berakhir.';

    private InvoiceService $invoiceSvc;

    public function run(array $params)
    {
        $this->invoiceSvc = new InvoiceService(new CreditService());

        $jatuhTempoHari = (int) ($params[0] ?? 3);
        CLI::write("Memproses perpanjangan langganan (H-{$jatuhTempoHari})...", 'cyan');

        $db = Database::connect();
        $querySql = 'SELECT l.*, p.harga_sen, p.nama AS paket_nama, p.interval_bulan '
            . 'FROM langganan l '
            . 'JOIN paket_langganan p ON l.paket_id = p.id '
            . 'WHERE l.status IN (\'active\', \'trialing\') '
            . 'AND l.auto_perpanjang = 1 '
            . 'AND l.periode_selesai <= DATE_ADD(CURDATE(), INTERVAL ? DAY)';

        $langgananList = $db->query($querySql, [$jatuhTempoHari])->getResultArray();

        $totalDiproses = count($langgananList);
        $totalSukses   = 0;
        $totalGagal    = 0;

        foreach ($langgananList as $lang) {
            $idemKey = hash('sha256', 'renewal|' . $lang['id'] . '|' . $lang['periode_selesai']);

            $paket = [
                'id'             => (int) $lang['paket_id'],
                'nama'           => (string) $lang['paket_nama'],
                'harga_sen'      => (int) $lang['harga_sen'],
                'interval_bulan' => (int) $lang['interval_bulan'],
            ];

            $db->transBegin();

            try {
                $invoiceId = $this->invoiceSvc->buatInvoicePerpanjangan($lang, $paket, $idemKey);
                $db->transCommit();

                CLI::write("Langganan #{$lang['id']} berhasil diperpanjang (Faktur #{$invoiceId})", 'green');
                $totalSukses++;
            } catch (Throwable $e) {
                $db->transRollback();

                CLI::write("Langganan #{$lang['id']} gagal diproses: " . $e->getMessage(), 'red');
                $totalGagal++;
            }
        }

        CLI::newLine();
        CLI::write("Proses perpanjangan selesai. Total: {$totalDiproses}, Sukses: {$totalSukses}, Gagal: {$totalGagal}");
    }
}
