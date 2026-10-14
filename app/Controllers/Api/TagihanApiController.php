<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Models\PembayaranModel;
use App\Models\TagihanItemModel;
use App\Models\TagihanModel;
use CodeIgniter\RESTful\ResourceController;
use Config\Database;
use Throwable;

final class TagihanApiController extends ResourceController
{
    public function detail(int $tagihanId)
    {
        $userId  = (int) session('user_id');
        $tagihan = $this->validasiAksesTagihan($tagihanId, $userId);

        if (! is_array($tagihan)) {
            return $tagihan;
        }

        $items = model(TagihanItemModel::class)
            ->where('tagihan_id', $tagihanId)
            ->findAll();

        return $this->respond([
            'tagihan' => $tagihan,
            'items'   => $items,
        ]);
    }

    public function bayar(int $tagihanId)
    {
        $userId  = (int) session('user_id');
        $tagihan = $this->validasiAksesTagihan($tagihanId, $userId);

        if (! is_array($tagihan)) {
            return $tagihan;
        }

        if ($tagihan['status'] !== 'terbit') {
            return $this->fail('Tagihan tidak dalam status terbit.', 422);
        }

        $totalSen = (int) $tagihan['total_sen'];
        $db       = Database::connect();
        $db->transBegin();

        try {
            $bayarModel = model(PembayaranModel::class);
            $bayarId    = $bayarModel->insert([
                'tagihan_id'   => $tagihanId,
                'gateway'      => 'midtrans',
                'metode'       => 'qris',
                'jumlah_sen'   => $totalSen,
                'status'       => 'pending',
                'payload_json' => null,
            ]);

            $orderId = 'PF-' . $bayarId;
            $bayarModel->update($bayarId, ['ref_gateway' => $orderId]);

            $db->transCommit();

            return $this->respond([
                'order_id' => $orderId,
                'amount'   => $totalSen,
                'status'   => 'pending',
            ]);
        } catch (Throwable) {
            $db->transRollback();

            return $this->failServerError('Gagal memproses pembuatan pembayaran.');
        }
    }

    private function validasiAksesTagihan(int $tagihanId, int $userId)
    {
        $tagihan = model(TagihanModel::class)->find($tagihanId);

        if ($tagihan === null) {
            return $this->failNotFound('Tagihan tidak ditemukan.');
        }

        if ((int) $tagihan['user_id'] !== $userId) {
            return $this->failForbidden('Akses tagihan ditolak.');
        }

        return $tagihan;
    }
}
