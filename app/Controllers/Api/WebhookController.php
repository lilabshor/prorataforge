<?php
namespace App\Controllers\Api;

use App\Models\PembayaranModel;
use App\Models\TagihanModel;
use CodeIgniter\RESTful\ResourceController;

class WebhookController extends ResourceController
{
    public function pembayaran()
    {
        $body       = $this->request->getJSON(true) ?? [];
        $orderId    = (string) ($body['order_id'] ?? '');
        $statusTx   = (string) ($body['transaction_status'] ?? '');
        $statusKode = (string) ($body['status_code'] ?? '');
        $gross      = (string) ($body['gross_amount'] ?? '');
        $tanda      = (string) ($body['signature_key'] ?? '');
        $kunciServer = (string) env('MIDTRANS_SERVER_KEY');
        $diharapkan = hash(
            'sha512',
            $orderId . $statusKode . $gross . $kunciServer
        );
        if (! hash_equals($diharapkan, $tanda)) {
            return $this->failUnauthorized('Tanda tangan tidak valid.');
        }

        $bayarId = (int) str_replace('PF-', '', $orderId);
        $bayar   = model(PembayaranModel::class)->find($bayarId);
        if ($bayar === null) {
            return $this->failNotFound('Pembayaran tidak ada.');
        }

        $tagihan = model(TagihanModel::class)->find($bayar['tagihan_id']);
        $jumlahWebhookSen = (int) round(((float) $gross) * 100);
        if ($jumlahWebhookSen !== (int) $bayar['jumlah_sen']) {
            return $this->fail('Jumlah tidak cocok.', 422);
        }
        $lunas = in_array($statusTx, ['capture', 'settlement'], true);
        if ($lunas && $bayar['status'] !== 'berhasil') {
            // TODO (Bab 6.4): update pembayaran -> berhasil,
            // tagihan -> dibayar, lalu perpanjang periode
            // (untuk invoice perpanjangan) - SEMUA dalam satu
            // transaksi. Idempoten: status yang sudah 'berhasil'
            // tidak diproses ulang.
        }
        return $this->respond(['status' => 'ok']);
    }
}
