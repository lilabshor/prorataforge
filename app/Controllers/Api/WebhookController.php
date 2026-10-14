<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Models\LanggananModel;
use App\Models\PaketModel;
use App\Models\PembayaranModel;
use App\Models\TagihanModel;
use CodeIgniter\RESTful\ResourceController;
use Config\Database;
use DateTimeImmutable;
use Throwable;

final class WebhookController extends ResourceController
{
    public function pembayaran()
    {
        $body        = $this->request->getJSON(true) ?? [];
        $orderId     = (string) ($body['order_id'] ?? '');
        $statusTx    = (string) ($body['transaction_status'] ?? '');
        $statusKode  = (string) ($body['status_code'] ?? '');
        $gross       = (string) ($body['gross_amount'] ?? '');
        $tanda       = (string) ($body['signature_key'] ?? '');
        $kunciServer = (string) env('MIDTRANS_SERVER_KEY');

        $diharapkan = hash('sha512', $orderId . $statusKode . $gross . $kunciServer);

        if (! hash_equals($diharapkan, $tanda)) {
            return $this->failUnauthorized('Tanda tangan signature tidak valid.');
        }

        $bayarId = (int) str_replace('PF-', '', $orderId);
        $bayar   = model(PembayaranModel::class)->find($bayarId);

        if ($bayar === null) {
            return $this->failNotFound('Data pembayaran tidak ditemukan.');
        }

        $tagihan = model(TagihanModel::class)->find($bayar['tagihan_id']);
        if ($tagihan === null) {
            return $this->failNotFound('Data tagihan tidak ditemukan.');
        }

        $jumlahWebhookSen = (int) round(((float) $gross) * 100);
        if ($jumlahWebhookSen !== (int) $bayar['jumlah_sen']) {
            return $this->fail('Jumlah pembayaran tidak sesuai dengan tagihan.', 422);
        }

        $isLunas = in_array($statusTx, ['capture', 'settlement'], true);

        if ($isLunas && $bayar['status'] !== 'berhasil') {
            $db = Database::connect();
            $db->transBegin();

            try {
                $sekarang = date('Y-m-d H:i:s');

                model(PembayaranModel::class)->update($bayarId, [
                    'status'       => 'berhasil',
                    'dibayar_pada' => $sekarang,
                    'payload_json' => json_encode($body),
                ]);

                model(TagihanModel::class)->update($tagihan['id'], [
                    'status'       => 'dibayar',
                    'dibayar_pada' => $sekarang,
                ]);

                if ($tagihan['jenis'] === 'perpanjangan') {
                    $this->perpanjangMasaLangganan((int) $tagihan['langganan_id']);
                }

                $db->transCommit();
            } catch (Throwable $e) {
                $db->transRollback();

                return $this->failServerError('Gagal memproses webhook: ' . $e->getMessage());
            }
        }

        return $this->respond(['status' => 'ok']);
    }

    private function perpanjangMasaLangganan(int $langgananId): void
    {
        $langganan = model(LanggananModel::class)->find($langgananId);
        if ($langganan === null) {
            return;
        }

        $paket         = model(PaketModel::class)->find($langganan['paket_id']);
        $intervalBulan = $paket !== null ? max(1, (int) ($paket['interval_bulan'] ?? 1)) : 1;

        $tglSelesaiLama = new DateTimeImmutable($langganan['periode_selesai']);
        $tglMulaiBaru   = $tglSelesaiLama->modify('+1 day');
        $tglSelesaiBaru = $tglMulaiBaru->modify('+' . $intervalBulan . ' month')->modify('-1 day');

        model(LanggananModel::class)->update($langganan['id'], [
            'status'          => 'active',
            'periode_mulai'   => $tglMulaiBaru->format('Y-m-d'),
            'periode_selesai' => $tglSelesaiBaru->format('Y-m-d'),
            'versi'           => (int) $langganan['versi'] + 1,
            'diubah_oleh'     => 0,
            'sumber'          => 'webhook',
        ]);
    }
}
