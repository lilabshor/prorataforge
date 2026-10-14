<?php
namespace App\Controllers\Api;

use App\Services\CreditService;
use App\Services\InvoiceService;
use App\Services\ProrataCalculator;
use App\Services\SubscriptionService;
use CodeIgniter\RESTful\ResourceController;
use DomainException;

class LanggananApiController extends ResourceController
{
    public function gantiPaket(int $langgananId)
    {
        $userId      = (int) session('user_id');
        $paketBaruId = (int) $this->request->getJSONVar('paket_baru_id');
        $tgl         = (string) $this->request->getJSONVar('tgl_perubahan');
        $kupon       = $this->request->getJSONVar('kode_kupon');
        $idem        = (string) $this->request->getHeaderLine('Idempotency-Key');
        if ($paketBaruId <= 0) {
            return $this->failValidationErrors(
                ['paket_baru_id' => 'Wajib diisi.']);
        }
        if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $tgl)) {
            return $this->failValidationErrors(
                ['tgl_perubahan' => 'Format YYYY-MM-DD.']);
        }
        $layanan = new SubscriptionService(
            new ProrataCalculator(),
            new InvoiceService(new CreditService()),
            new CreditService()
        );
        try {
            $hasil = $layanan->gantiPaket(
                $userId, $langgananId, $paketBaruId,
                $tgl, $idem,
                $kupon !== null ? (string) $kupon : null,
                'api'
            );
        } catch (DomainException $e) {
            $kode = $e->getCode();
            if ($kode === 404) {
                return $this->failNotFound($e->getMessage());
            }
            return $this->fail(
                $e->getMessage(),
                $kode >= 400 && $kode < 500 ? $kode : 422
            );
        }
        return $this->respond(['status' => 'sukses', 'data' => $hasil]);
    }
}
