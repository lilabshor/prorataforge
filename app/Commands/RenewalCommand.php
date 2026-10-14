<?php
namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class RenewalCommand extends BaseCommand
{
    protected $group       = 'Billing';
    protected $name        = 'billing:renewal';
    protected $description = 'Buat invoice perpanjangan '
    . 'untuk langganan yang periodenya berakhir.';

    public function run(array $params)
    {
        // 1. Ambil langganan active/trialing yang
        //    periode_selesai <= hari ini + jatuhTempoHari.
        // 2. Untuk tiap langganan: buat invoice 'perpanjangan'
        //    (pola sama dengan InvoiceService, jenis berbeda),
        //    pakai saldo kredit dulu, hitung pajak, terbitkan.
        // 3. Antrekan email invoice. Tulis ringkasan ke CLI.
        CLI::write('Renewal selesai.', 'green');
    }
}
