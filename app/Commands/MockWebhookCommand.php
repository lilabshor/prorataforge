<?php

declare(strict_types=1);

namespace App\Commands;

use App\Models\PembayaranModel;
use App\Models\TagihanModel;
use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

final class MockWebhookCommand extends BaseCommand
{
    protected $group       = 'Billing';
    protected $name        = 'billing:mock-webhook';
    protected $description = 'Menyimulasikan pengiriman webhook pelunasan dari payment gateway Midtrans.';

    public function run(array $params)
    {
        $orderInput = (string) ($params[0] ?? '');

        if ($orderInput === '') {
            CLI::write('Gunakan: php spark billing:mock-webhook [ORDER_ID atau TAGIHAN_ID]', 'yellow');
            CLI::write('Contoh: php spark billing:mock-webhook PF-1 atau php spark billing:mock-webhook 1');

            return;
        }

        $pembayaranModel = model(PembayaranModel::class);

        // Cari berdasarkan ref_gateway 'PF-X' atau ID pembayaran
        if (str_starts_with($orderInput, 'PF-')) {
            $bayar = $pembayaranModel->where('ref_gateway', $orderInput)->first();
        } else {
            $bayar = $pembayaranModel->find((int) $orderInput);
            if ($bayar === null) {
                // Coba cari berdasarkan tagihan_id
                $bayar = $pembayaranModel->where('tagihan_id', (int) $orderInput)->orderBy('id', 'DESC')->first();
            }
        }

        if ($bayar === null) {
            CLI::write("Data pembayaran dengan rujukan '{$orderInput}' tidak ditemukan di database.", 'red');

            return;
        }

        $orderId     = $bayar['ref_gateway'] ?? ('PF-' . $bayar['id']);
        $grossAmount = number_format(((int) $bayar['jumlah_sen']) / 100, 2, '.', '');
        $statusKode  = '200';
        $statusTx    = 'settlement';
        $kunciServer = (string) env('MIDTRANS_SERVER_KEY');

        // Buat signature valid Midtrans
        $signature = hash('sha512', $orderId . $statusKode . $grossAmount . $kunciServer);

        $payload = [
            'order_id'           => $orderId,
            'status_code'        => $statusKode,
            'transaction_status' => $statusTx,
            'gross_amount'       => $grossAmount,
            'signature_key'      => $signature,
            'payment_type'       => $bayar['metode'] ?? 'qris',
            'transaction_time'   => date('Y-m-d H:i:s'),
        ];

        CLI::write("Mengirim simulasi webhook untuk {$orderId}...", 'cyan');
        CLI::write("Nominal: Rp" . number_format((float) $grossAmount, 0, ',', '.'));

        // Kirim request ke endpoint webhook lokal
        $url = site_url('webhook/pembayaran');
        $ch  = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json',
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200) {
            CLI::write("Webhook berhasil diproses! (HTTP {$httpCode})", 'green');
            $updatedBayar = $pembayaranModel->find($bayar['id']);
            $tagihan      = model(TagihanModel::class)->find($bayar['tagihan_id']);
            CLI::write("Status Pembayaran : {$updatedBayar['status']}");
            CLI::write("Status Tagihan    : {$tagihan['status']}");
        } else {
            CLI::write("Webhook gagal diproses (HTTP {$httpCode}): {$response}", 'red');
        }
    }
}
