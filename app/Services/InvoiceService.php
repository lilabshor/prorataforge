<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\TagihanItemModel;
use App\Models\TagihanModel;
use Config\Database;
use DomainException;

final class InvoiceService
{
    public function __construct(
        private CreditService $creditSvc,
        private int $pajakBasisPoin = 1100,
    ) {}

    public function buatInvoiceProrata(
        array $lang,
        array $paketLama,
        array $paketBaru,
        HasilProrata $h,
        string $idempotencyKey
    ): int {
        $db        = Database::connect();
        $nomor     = $this->nomorBerikutnya($db);
        $bersih    = $h->bersihSen;
        $model     = model(TagihanModel::class);

        $invoiceId = $model->insert([
            'nomor'           => $nomor,
            'user_id'         => $lang['user_id'],
            'langganan_id'    => $lang['id'],
            'jenis'           => 'prorata',
            'status'          => 'terbit',
            'subtotal_sen'    => $bersih,
            'pajak_persen'    => 11.00,
            'tgl_terbit'      => date('Y-m-d'),
            'tgl_jatuh_tempo' => date('Y-m-d', strtotime('+3 days')),
            'idempotency_key' => $idempotencyKey,
        ]);

        $item = model(TagihanItemModel::class);
        $item->insert([
            'tagihan_id'   => $invoiceId,
            'jenis'        => 'kredit_prorata',
            'keterangan'   => 'Kredit sisa paket ' . $paketLama['nama'] . ' (' . $h->sisaHari . ' hari)',
            'jumlah'       => $h->sisaHari,
            'subtotal_sen' => -$h->kreditSen,
            'paket_id'     => $paketLama['id'],
        ]);

        $item->insert([
            'tagihan_id'   => $invoiceId,
            'jenis'        => 'tagihan_prorata',
            'keterangan'   => 'Paket ' . $paketBaru['nama'] . ' (' . $h->sisaHari . ' hari)',
            'jumlah'       => $h->sisaHari,
            'subtotal_sen' => $h->tagihanBaruSen,
            'paket_id'     => $paketBaru['id'],
        ]);

        if ($bersih < 0) {
            $this->creditSvc->tambahSaldo(
                (int) $lang['user_id'],
                -$bersih,
                'prorata_downgrade',
                (int) $invoiceId,
                'Kelebihan prorata downgrade ' . $nomor
            );
            $model->update($invoiceId, [
                'total_sen' => 0,
                'status'    => 'gratis',
            ]);
            return (int) $invoiceId;
        }

        if ($bersih === 0) {
            $model->update($invoiceId, [
                'total_sen' => 0,
                'status'    => 'gratis',
            ]);
            return (int) $invoiceId;
        }

        $dipakai = $this->creditSvc->pakaiSaldo((int) $lang['user_id'], $bersih, (int) $invoiceId);
        if ($dipakai > 0) {
            $item->insert([
                'tagihan_id'   => $invoiceId,
                'jenis'        => 'kredit_saldo',
                'keterangan'   => 'Saldo kredit dipakai',
                'subtotal_sen' => -$dipakai,
            ]);
        }

        $this->selesaikanKalkulasiInvoice((int) $invoiceId, $bersih - $dipakai, $dipakai);

        return (int) $invoiceId;
    }

    public function terapkanKupon(int $hargaSen, string $kode, int $userId): int
    {
        $kupon = Database::connect()->table('kupon')
            ->where('kode', $kode)
            ->where('is_aktif', 1)
            ->get()
            ->getRowArray();

        if ($kupon === null) {
            throw new DomainException('Kupon tidak valid.', 422);
        }

        $diskon = $kupon['jenis'] === 'persen'
            ? intdiv($hargaSen * (int) $kupon['nilai'], 100)
            : (int) $kupon['nilai'];

        if ($kupon['maks_diskon_sen'] !== null) {
            $diskon = min($diskon, (int) $kupon['maks_diskon_sen']);
        }

        return max(0, $hargaSen - $diskon);
    }

    public function buatInvoicePerpanjangan(
        array $lang,
        array $paket,
        string $idempotencyKey
    ): int {
        $db = Database::connect();

        $sudahAda = $db->table('tagihan_user')
            ->where('idempotency_key', $idempotencyKey)
            ->get()
            ->getRowArray();

        if ($sudahAda !== null) {
            return (int) $sudahAda['id'];
        }

        $nomor     = $this->nomorBerikutnya($db);
        $hargaSen  = (int) $paket['harga_sen'];
        $model     = model(TagihanModel::class);

        $invoiceId = $model->insert([
            'nomor'           => $nomor,
            'user_id'         => $lang['user_id'],
            'langganan_id'    => $lang['id'],
            'jenis'           => 'perpanjangan',
            'status'          => 'terbit',
            'subtotal_sen'    => $hargaSen,
            'pajak_persen'    => 11.00,
            'tgl_terbit'      => date('Y-m-d'),
            'tgl_jatuh_tempo' => date('Y-m-d', strtotime('+7 days')),
            'idempotency_key' => $idempotencyKey,
        ]);

        $item = model(TagihanItemModel::class);
        $item->insert([
            'tagihan_id'   => $invoiceId,
            'jenis'        => 'tagihan_perpanjangan',
            'keterangan'   => 'Perpanjangan Paket ' . $paket['nama'] . ' (1 periode)',
            'jumlah'       => 1,
            'satuan_sen'   => $hargaSen,
            'subtotal_sen' => $hargaSen,
            'paket_id'     => $paket['id'],
        ]);

        $dipakai = $this->creditSvc->pakaiSaldo((int) $lang['user_id'], $hargaSen, (int) $invoiceId);
        if ($dipakai > 0) {
            $item->insert([
                'tagihan_id'   => $invoiceId,
                'jenis'        => 'kredit_saldo',
                'keterangan'   => 'Saldo kredit dipakai',
                'subtotal_sen' => -$dipakai,
            ]);
        }

        $this->selesaikanKalkulasiInvoice((int) $invoiceId, $hargaSen - $dipakai, $dipakai);

        return (int) $invoiceId;
    }

    private function selesaikanKalkulasiInvoice(int $invoiceId, int $dasarPajak, int $dipakai): void
    {
        $pajak       = intdiv($dasarPajak * $this->pajakBasisPoin + 5000, 10000);
        $total       = $dasarPajak + $pajak;
        $totalPenuh  = ProrataCalculator::keRupiahPenuh($total);
        $penyesuaian = $totalPenuh - $total;

        $item = model(TagihanItemModel::class);

        if ($pajak > 0) {
            $item->insert([
                'tagihan_id'   => $invoiceId,
                'jenis'        => 'pajak',
                'keterangan'   => 'PPN 11%',
                'subtotal_sen' => $pajak,
            ]);
        }

        if ($penyesuaian !== 0) {
            $item->insert([
                'tagihan_id'   => $invoiceId,
                'jenis'        => 'penyesuaian',
                'keterangan'   => 'Pembulatan rupiah penuh',
                'subtotal_sen' => $penyesuaian,
            ]);
        }

        model(TagihanModel::class)->update($invoiceId, [
            'kredit_dipakai_sen' => $dipakai,
            'pajak_sen'          => $pajak,
            'penyesuaian_sen'    => $penyesuaian,
            'total_sen'          => $totalPenuh,
            'status'             => $totalPenuh === 0 ? 'gratis' : 'terbit',
        ]);
    }

    private function nomorBerikutnya($db): string
    {
        $tahun = (int) date('Y');
        $bulan = (int) date('n');

        if ($db->DBDriver === 'MySQLi') {
            $db->query(
                'INSERT INTO penomoran_invoice (tahun, bulan, terakhir) VALUES (?, ?, 0) '
                . 'ON DUPLICATE KEY UPDATE terakhir = terakhir',
                [$tahun, $bulan]
            );

            $baris = $db->query(
                'SELECT terakhir FROM penomoran_invoice WHERE tahun = ? AND bulan = ? FOR UPDATE',
                [$tahun, $bulan]
            )->getRowArray();
        } else {
            $ada = $db->table('penomoran_invoice')
                ->where('tahun', $tahun)
                ->where('bulan', $bulan)
                ->countAllResults();

            if ($ada === 0) {
                $db->table('penomoran_invoice')->insert([
                    'tahun'    => $tahun,
                    'bulan'    => $bulan,
                    'terakhir' => 0,
                ]);
            }

            $baris = $db->table('penomoran_invoice')
                ->where('tahun', $tahun)
                ->where('bulan', $bulan)
                ->get()
                ->getRowArray();
        }

        $berikut = (int) ($baris['terakhir'] ?? 0) + 1;

        $db->table('penomoran_invoice')
            ->where('tahun', $tahun)
            ->where('bulan', $bulan)
            ->update(['terakhir' => $berikut]);

        return sprintf('INV/%d/%02d/%06d', $tahun, $bulan, $berikut);
    }
}
