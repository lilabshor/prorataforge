<?php
declare(strict_types=1);
namespace App\Services;

use App\Models\LanggananModel;
use App\Models\PaketModel;
use Config\Database;
use DateTimeImmutable;
use DomainException;

final class SubscriptionService
{
    public function __construct(
        private ProrataCalculator $kalkulator,
        private InvoiceService $invoiceSvc,
        private CreditService $creditSvc,
    ) {}

    public function gantiPaket(
        int $userId,
        int $langgananId,
        int $paketBaruId,
        string $tglPerubahan,
        string $idempotencyKey,
        ?string $kodeKupon = null,
        string $sumber = 'web'
    ): array {
        if ($idempotencyKey === '') {
            throw new DomainException('Idempotency-Key wajib diisi.');
        }
        $db = Database::connect();
        $db->transBegin();
        try {
            $pernah = $db->table('idempotency_keys')
                ->where('user_id', $userId)
                ->where('endpoint', 'ganti-paket')
                ->where('kunci', $idempotencyKey)
                ->get()->getRowArray();
            if ($pernah !== null) {
                $db->transCommit();
                return json_decode((string) $pernah['respons_json'], true);
            }
            $lang = $db->query(
                'SELECT * FROM langganan WHERE id = ? FOR UPDATE',
                [$langgananId]
            )->getRowArray();
            if ($lang === null || (int) $lang['user_id'] !== $userId) {
                throw new DomainException('Langganan tidak ditemukan.', 404);
            }
            if (! in_array($lang['status'], ['active', 'trialing'], true)) {
                throw new DomainException(
                    'Status langganan tidak mengizinkan ganti paket.', 422);
            }
            if ((int) $lang['paket_id'] === $paketBaruId) {
                throw new DomainException(
                    'Paket baru sama dengan paket aktif.', 422);
            }
            $paketModel = model(PaketModel::class);
            $paketLama  = $paketModel->find($lang['paket_id']);
            $paketBaru  = $paketModel->find($paketBaruId);
            if ($paketLama === null || $paketBaru === null
                || (int) $paketBaru['is_aktif'] !== 1) {
                throw new DomainException(
                    'Paket tidak ditemukan / nonaktif.', 422);
            }
            $hargaBaruSen = (int) $paketBaru['harga_sen'];
            if ($kodeKupon !== null) {
                $hargaBaruSen = $this->invoiceSvc->terapkanKupon(
                    $hargaBaruSen, $kodeKupon, $userId);
            }
            $tgl       = new DateTimeImmutable($tglPerubahan);
            $hasil     = null;
            $invoiceId = null;
            if ($lang['status'] !== 'trialing') {
                $hasil = $this->kalkulator->hitung(
                    (int) $paketLama['harga_sen'],
                    $hargaBaruSen,
                    new DateTimeImmutable($lang['periode_mulai']),
                    new DateTimeImmutable($lang['periode_selesai']),
                    $tgl
                );
                $invoiceId = $this->invoiceSvc->buatInvoiceProrata(
                    $lang, $paketLama, $paketBaru,
                    $hasil, $idempotencyKey
                );
            }
            model(LanggananModel::class)->update($langgananId, [
                'paket_id'    => $paketBaruId,
                'versi'       => (int) $lang['versi'] + 1,
                'diubah_oleh' => $userId,
                'sumber'      => $sumber,
            ]);
            $respons = [
                'langganan_id' => $langgananId,
                'paket_baru'   => $paketBaru['kode'],
                'invoice_id'   => $invoiceId,
                'total_hari'   => $hasil?->totalHari,
                'sisa_hari'    => $hasil?->sisaHari,
                'kredit_sen'   => $hasil?->kreditSen ?? 0,
                'tagihan_sen'  => $hasil?->tagihanBaruSen ?? 0,
                'bersih_sen'   => $hasil?->bersihSen ?? 0,
            ];
            $db->table('idempotency_keys')->insert([
                'kunci'        => $idempotencyKey,
                'user_id'      => $userId,
                'endpoint'     => 'ganti-paket',
                'hash_request' => hash(
                    'sha256',
                    $langgananId . '|' . $paketBaruId . '|' . $tglPerubahan
                ),
                'respons_json' => json_encode($respons),
                'status_http'  => 200,
            ]);
            $db->transCommit();
            return $respons;
        } catch (\Throwable $e) {
            $db->transRollback();
            throw $e;
        }
    }
}
