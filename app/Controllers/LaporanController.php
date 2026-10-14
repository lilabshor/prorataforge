<?php

declare(strict_types=1);

namespace App\Controllers;

use Config\Database;

final class LaporanController extends BaseController
{
    public function mrr()
    {
        $db = Database::connect();

        $querySql = 'SELECT l.id, l.user_id, l.status, l.auto_perpanjang, l.periode_mulai, l.periode_selesai, '
            . 'p.id AS paket_id, p.nama AS paket_nama, p.harga_sen, p.interval_bulan '
            . 'FROM langganan l '
            . 'JOIN paket_langganan p ON l.paket_id = p.id '
            . 'WHERE l.status IN (\'active\', \'trialing\')';

        $langgananList = $db->query($querySql)->getResultArray();

        $totalMrrSen    = 0;
        $totalAktif     = 0;
        $totalTrialing  = 0;
        $paketBreakdown = [];

        foreach ($langgananList as $lang) {
            $hargaSen      = (int) $lang['harga_sen'];
            $intervalBulan = max(1, (int) $lang['interval_bulan']);

            $mrrItemSen = intdiv($hargaSen, $intervalBulan);
            $totalMrrSen += $mrrItemSen;

            if ($lang['status'] === 'active') {
                $totalAktif++;
            } else {
                $totalTrialing++;
            }

            $paketId = (int) $lang['paket_id'];
            if (! isset($paketBreakdown[$paketId])) {
                $paketBreakdown[$paketId] = [
                    'nama'        => $lang['paket_nama'],
                    'jumlah_user' => 0,
                    'total_sen'   => 0,
                ];
            }
            $paketBreakdown[$paketId]['jumlah_user']++;
            $paketBreakdown[$paketId]['total_sen'] += $mrrItemSen;
        }

        $churnedCount = $db->table('langganan')->where('status', 'canceled')->countAllResults();
        $totalArrSen  = $totalMrrSen * 12;

        $data = [
            'totalMrrSen'    => $totalMrrSen,
            'totalArrSen'    => $totalArrSen,
            'totalPelanggan' => count($langgananList),
            'totalAktif'     => $totalAktif,
            'totalTrialing'  => $totalTrialing,
            'churnedCount'   => $churnedCount,
            'paketBreakdown' => array_values($paketBreakdown),
        ];

        if ($this->request->isAJAX() || str_contains((string) $this->request->getHeaderLine('Accept'), 'application/json')) {
            return $this->response->setJSON([
                'status' => 'sukses',
                'data'   => $data,
            ]);
        }

        return view('admin/laporan/mrr', $data);
    }
}
