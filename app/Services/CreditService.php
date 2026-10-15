<?php
declare(strict_types=1);
namespace App\Services;

use Config\Database;

final class CreditService
{
    public function tambahSaldo(int $userId, int $sen,
                                string $jenis, ?int $tagihanId,
                                string $keterangan): int
    {
        if ($sen <= 0) {
            return $this->saldoSekarang($userId);
        }
        $db = Database::connect();
        if ($db->DBDriver === 'MySQLi') {
            $db->query(
                'INSERT INTO saldo_kredit (user_id, saldo_sen) '
                . 'VALUES (?, 0) '
                . 'ON DUPLICATE KEY UPDATE user_id = user_id',
                [$userId]
            );
            $baris = $db->query(
                'SELECT saldo_sen FROM saldo_kredit '
                . 'WHERE user_id = ? FOR UPDATE',
                [$userId]
            )->getRowArray();
        } else {
            $ada = $db->table('saldo_kredit')->where('user_id', $userId)->countAllResults();
            if ($ada === 0) {
                $db->table('saldo_kredit')->insert(['user_id' => $userId, 'saldo_sen' => 0]);
            }
            $baris = $db->table('saldo_kredit')->where('user_id', $userId)->get()->getRowArray();
        }
        $setelah = (int) $baris['saldo_sen'] + $sen;
        $db->table('saldo_kredit')->where('user_id', $userId)
            ->update(['saldo_sen' => $setelah]);
        $db->table('buku_kredit')->insert([
            'user_id' => $userId, 'jenis' => $jenis,
            'delta_sen' => $sen,
            'saldo_setelah_sen' => $setelah,
            'tagihan_id' => $tagihanId,
            'keterangan' => $keterangan,
        ]);
        return $setelah;
    }

    public function pakaiSaldo(int $userId, int $maksSen,
                               int $tagihanId): int
    {
        $db = Database::connect();
        if ($db->DBDriver === 'MySQLi') {
            $baris = $db->query(
                'SELECT saldo_sen FROM saldo_kredit '
                . 'WHERE user_id = ? FOR UPDATE',
                [$userId]
            )->getRowArray();
        } else {
            $baris = $db->table('saldo_kredit')->where('user_id', $userId)->get()->getRowArray();
        }
        if ($baris === null) {
            return 0;
        }
        $dipakai = min((int) $baris['saldo_sen'], $maksSen);
        if ($dipakai <= 0) {
            return 0;
        }
        $setelah = (int) $baris['saldo_sen'] - $dipakai;
        $db->table('saldo_kredit')->where('user_id', $userId)
            ->update(['saldo_sen' => $setelah]);
        $db->table('buku_kredit')->insert([
            'user_id' => $userId,
            'jenis' => 'pakai_di_invoice',
            'delta_sen' => -$dipakai,
            'saldo_setelah_sen' => $setelah,
            'tagihan_id' => $tagihanId,
            'keterangan' => 'Saldo kredit dipakai',
        ]);
        return $dipakai;
    }

    public function saldoSekarang(int $userId): int
    {
        $baris = Database::connect()->table('saldo_kredit')
            ->where('user_id', $userId)
            ->get()
            ->getRowArray();

        return $baris === null ? 0 : (int) $baris['saldo_sen'];
    }
}
